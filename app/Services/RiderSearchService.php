<?php

namespace App\Services;

use App\Events\DeliveryOfferSent;
use App\Events\NoRiderAvailable;
use App\Models\DeliveryOffer;
use App\Models\Order;
use App\Models\Rider;
use App\Models\SystemConfig;
use Illuminate\Support\Facades\Log;

class RiderSearchService
{
    /**
     * Hinahanap ang rider para sa order, 1km steps.
     * Tinatawag ito ng FindRiderForOrder job.
     */
    public function findRider(Order $order): void
    {
        $config = SystemConfig::current();
        $maxRadius = (int) $config->service_radius_km;
        $restLat = (float) $order->restaurant->latitude;
        $restLng = (float) $order->restaurant->longitude;

        $order->update(['status' => 'finding_rider']);

        // Kunin lahat ng eligible riders ONCE (efficient)
        $candidates = Rider::query()
            ->where('is_online', true)
            ->where('is_available', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereHas('user', fn($q) => $q->where('status', 'approved'))
            ->get();

        Log::info("Searching riders for order #{$order->id}", [
            'max_radius' => $maxRadius,
            'total_candidates' => $candidates->count(),
        ]);

        // Step-by-step 1km rings: 1, 2, 3, ... maxRadius
        for ($radius = 1; $radius <= $maxRadius; $radius++) {
            $inner = $radius - 1;

            $eligible = $candidates->filter(function ($rider) use ($restLat, $restLng, $inner, $radius) {
                $d = $this->distanceKm(
                    $restLat,
                    $restLng,
                    (float) $rider->latitude,
                    (float) $rider->longitude
                );
                return $d > $inner && $d <= $radius;
            });

            Log::info("Radius {$radius}km: {$eligible->count()} eligible riders");

            if ($eligible->isEmpty()) {
                continue;
            }

            $accepted = $this->offerToRiders($order, $eligible, $radius);
            if ($accepted) {
                return;
            }
        }

        // Walang tumanggap kahit sa buong service area
        $order->update(['status' => 'no_rider']);
        broadcast(new NoRiderAvailable($order));
        Log::warning("No rider for order #{$order->id}");
    }

    /**
     * Magpadala ng offers at hintayin ang unang tumanggap.
     * Returns true kung may tumanggap, false kung timeout.
     */
    protected function offerToRiders(Order $order, $riders, int $radiusKm): bool
    {
        $timeout = 30; // seconds
        $now = now();

        // Gumawa ng offers sa DB
        $offerRows = $riders->map(fn($r) => [
            'order_id' => $order->id,
            'rider_id' => $r->id,
            'radius_km' => $radiusKm,
            'status' => 'pending',
            'expires_at' => $now->copy()->addSeconds($timeout),
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DeliveryOffer::insert($offerRows);

        // I-broadcast sa bawat rider via WebSocket
        foreach ($riders as $rider) {
            broadcast(new DeliveryOfferSent($order, $rider, $radiusKm, $timeout));
        }

        // Hintayin ang acceptance (atomic race via cache)
        $acceptedRiderId = $this->waitForAcceptance($order->id, $timeout);

        if ($acceptedRiderId) {
            // I-close ang ibang offers
            DeliveryOffer::where('order_id', $order->id)
                ->where('rider_id', '!=', $acceptedRiderId)
                ->update(['status' => 'expired']);

            DeliveryOffer::where('order_id', $order->id)
                ->where('rider_id', $acceptedRiderId)
                ->update(['status' => 'accepted']);

            // I-set rider na busy (hindi available habang may delivery)
            Rider::where('id', $acceptedRiderId)->update(['is_available' => false]);

            return true;
        }

        // Timeout — i-expire lahat ng pending offers
        DeliveryOffer::where('order_id', $order->id)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        return false;
    }

    /**
     * Naghihintay ng acceptance signal galing sa rider.
     * Gumagamit ng Cache para sa cross-process communication.
     */
    protected function waitForAcceptance(int $orderId, int $timeoutSec): ?int
    {
        $key = "order:{$orderId}:accepted_rider";
        $deadline = microtime(true) + $timeoutSec;

        while (microtime(true) < $deadline) {
            $riderId = cache()->get($key);
            if ($riderId) {
                cache()->forget($key);
                return (int) $riderId;
            }
            usleep(300_000); // 300ms
        }

        return null;
    }

    /**
     * Haversine distance sa km.
     */
    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}