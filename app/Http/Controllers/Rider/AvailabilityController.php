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

        $currentOrder = Order::with(['restaurant', 'customer', 'items', 'payment'])
            ->where('rider_id', $rider->id)
            ->whereIn('status', ['rider_assigned', 'picked_up', 'out_for_delivery'])
            ->latest()
            ->first();

        $completedToday = Order::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->whereDate('updated_at', today())
            ->count();

        $earningsToday = Order::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->whereDate('updated_at', today())
            ->sum('delivery_fee');

        return view('rider.dashboard', compact('rider', 'currentOrder', 'completedToday', 'earningsToday'));
    }

    public function history()
    {
        $rider = auth()->user()->rider;

        $orders = Order::with(['restaurant', 'customer', 'items'])
            ->where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->latest()
            ->paginate(20);

        $totalEarnings = Order::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->sum('delivery_fee');

        $totalDeliveries = Order::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->count();

        $avgRating = Order::where('rider_id', $rider->id)
            ->whereNotNull('rider_rating')
            ->avg('rider_rating');

        $stats = [
            'total_earnings' => $totalEarnings,
            'total_deliveries' => $totalDeliveries,
            'avg_rating' => $avgRating ? round($avgRating, 2) : 'N/A',
        ];

        return view('rider.history', compact('orders', 'stats'));
    }

    public function profile()
    {
        $rider = auth()->user()->rider;

        return view('rider.profile', compact('rider'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $rider = $user->rider;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_plate' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
        ]);

        $rider->update([
            'vehicle_type' => $data['vehicle_type'] ?? null,
            'vehicle_plate' => $data['vehicle_plate'] ?? null,
        ]);

        return back()->with('success', 'Profile updated.');
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