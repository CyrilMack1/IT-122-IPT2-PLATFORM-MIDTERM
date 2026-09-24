<?php

namespace App\Http\Controllers\Rider;

use App\Events\OrderStatusUpdated;
use App\Events\RiderAssigned;
use App\Http\Controllers\Controller;
use App\Models\DeliveryOffer;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function accept(Request $request, Order $order)
    {
        $rider = $request->user()->rider;
        abort_unless($rider && $rider->is_online, 403, 'Rider not online');

        $offer = DeliveryOffer::where('order_id', $order->id)
            ->where('rider_id', $rider->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (!$offer) {
            return redirect()->route('rider.dashboard')
                ->with('error', 'Offer expired or not found.');
        }

        // ATOMIC: kunin ang order kung wala pang rider
        $updated = Order::where('id', $order->id)
            ->whereNull('rider_id')
            ->whereIn('status', ['finding_rider', 'confirmed'])
            ->update([
                'rider_id' => $rider->id,
                'status' => 'rider_assigned',
            ]);

        if ($updated === 0) {
            return redirect()->route('rider.dashboard')
                ->with('error', 'Another rider accepted first.');
        }

        // Signal sa naghihintay na job
        cache()->put("order:{$order->id}:accepted_rider", $rider->id, 60);

        // I-set rider na busy
        $rider->update(['is_available' => false]);

        broadcast(new RiderAssigned($order->fresh()));

        return redirect()->route('rider.dashboard')
            ->with('success', 'Order accepted! Proceed to the restaurant.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:picked_up,out_for_delivery,delivered',
        ]);

        $rider = $request->user()->rider;
        abort_unless($order->rider_id === $rider->id, 403);

        $order->update(['status' => $data['status']]);

        if ($data['status'] === 'delivered') {
            $rider->update(['is_available' => true]);
        }

        broadcast(new OrderStatusUpdated($order->fresh()));

        $label = str_replace('_', ' ', $data['status']);

        return redirect()->route('rider.dashboard')
            ->with('success', "Status updated to: " . ucfirst($label));
    }

    public function recordPayment(Request $request, Order $order)
    {
        $rider = $request->user()->rider;
        abort_unless($order->rider_id === $rider->id, 403);

        if ($order->payment()->exists()) {
            return redirect()->route('rider.dashboard')
                ->with('error', 'Payment already recorded.');
        }

        Payment::create([
            'order_id' => $order->id,
            'rider_paid_restaurant' => $order->food_cost,
            'rider_collected_customer' => $order->total_amount,
            'recorded_at' => now(),
        ]);

        return redirect()->route('rider.dashboard')
            ->with('success', 'Payment recorded successfully!');
    }
}