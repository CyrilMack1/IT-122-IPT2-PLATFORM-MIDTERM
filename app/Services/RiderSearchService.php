<?php

namespace App\Services;

use App\Events\DeliveryOfferSent;
use App\Events\NoRiderAvailable;
use App\Models\DeliveryOffer;
use App\Models\Order;
use App\Models\Rider;
use App\Models\SystemConfig;
use App\Notifications\NoRiderNotification;
use Illuminate\Support\Facades\Log;

class RiderSearchService
{
    public function findRider(Order $order): void
    {
        $config = SystemConfig::current();
        $maxRadius = (int) $config->service_radius_km;
        $restLat = (float) $order->restaurant->latitude;
        $restLng = (float) $order->restaurant->longitude;

        $order->update(['status' => 'finding_rider']);

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

        // Notify customer via email
        $order->customer->notify(new NoRiderNotification($order->fresh()));

        Log::warning("No rider for order #{$order->id}");
    }

    protected function offerToRiders(Order $order, $riders, int $radiusKm): bool
    {
        $timeout = 30;
        $now = now();

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

        foreach ($riders as $rider) {
            broadcast(new DeliveryOfferSent($order, $rider, $radiusKm, $timeout));
        }

        $acceptedRiderId = $this->waitForAcceptance($order->id, $timeout);

        if ($acceptedRiderId) {
            DeliveryOffer::where('order_id', $order->id)
                ->where('rider_id', '!=', $acceptedRiderId)
                ->update(['status' => 'expired']);

            DeliveryOffer::where('order_id', $order->id)
                ->where('rider_id', $acceptedRiderId)
                ->update(['status' => 'accepted']);

            Rider::where('id', $acceptedRiderId)->update(['is_available' => false]);

            return true;
        }

        DeliveryOffer::where('order_id', $order->id)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        return false;
    }

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
            usleep(300_000);
        }

        return null;
    }

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