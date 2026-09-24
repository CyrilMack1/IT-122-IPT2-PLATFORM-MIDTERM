<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function dashboard()
    {
        $rider = auth()->user()->rider;

        // Kasalukuyang delivery (kung meron)
        $currentOrder = Order::with(['restaurant', 'customer', 'items', 'payment'])
            ->where('rider_id', $rider->id)
            ->whereIn('status', ['rider_assigned', 'picked_up', 'out_for_delivery'])
            ->latest()
            ->first();

        // Mga nagawang delivery ngayong araw
        $completedToday = Order::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->whereDate('updated_at', today())
            ->count();

        // Kabuuang kita ngayong araw
        $earningsToday = Order::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->whereDate('updated_at', today())
            ->sum('delivery_fee');

        return view('rider.dashboard', compact('rider', 'currentOrder', 'completedToday', 'earningsToday'));
    }

    public function toggleOnline(Request $request)
    {
        $rider = auth()->user()->rider;

        $rider->update([
            'is_online' => !$rider->is_online,
            'is_available' => !$rider->is_online,
        ]);

        return response()->json([
            'is_online' => $rider->is_online,
            'is_available' => $rider->is_available,
        ]);
    }

    public function updateLocation(Request $request)
    {
        $data = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $rider = auth()->user()->rider;

        $rider->update([
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'last_location_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }
}