<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Jobs\FindRiderForOrder;
use App\Models\Order;
use App\Models\SystemConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function dashboard()
    {
        $restaurant = auth()->user()->restaurant;

        $orders = Order::with(['customer', 'rider', 'items'])
            ->where('restaurant_id', $restaurant->id)
            ->latest()
            ->limit(50)
            ->get();

        return view('restaurant.dashboard', compact('restaurant', 'orders'));
    }

    public function confirm(Request $request, Order $order)
    {
        $restaurant = auth()->user()->restaurant;
        abort_unless($order->restaurant_id === $restaurant->id, 403);
        abort_unless($order->status === 'received', 422, 'Order already processed');

        $order->update(['status' => 'confirmed']);

        // Trigger rider search async (queue)
        FindRiderForOrder::dispatch($order);

        return back()->with('success', 'Order confirmed. Searching for rider...');
    }

    public function reject(Request $request, Order $order)
    {
        $restaurant = auth()->user()->restaurant;
        abort_unless($order->restaurant_id === $restaurant->id, 403);

        $order->update(['status' => 'rejected']);

        return back()->with('success', 'Order rejected.');
    }

    public function ready(Request $request, Order $order)
    {
        $restaurant = auth()->user()->restaurant;
        abort_unless($order->restaurant_id === $restaurant->id, 403);

        $order->update(['status' => 'preparing']);

        return back()->with('success', 'Order marked as preparing.');
    }

    public function storeExternal(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        $data = $request->validate([
            'customer_name' => 'required|string',
            'delivery_address' => 'required|string',
            'delivery_lat' => 'required|numeric',
            'delivery_lng' => 'required|numeric',
            'food_cost' => 'required|numeric|min:0',
        ]);

        $deliveryFee = SystemConfig::current()->default_delivery_fee;

        $order = Order::create([
            'customer_id' => auth()->id(),
            'restaurant_id' => $restaurant->id,
            'status' => 'confirmed',
            'food_cost' => $data['food_cost'],
            'delivery_fee' => $deliveryFee,
            'total_amount' => $data['food_cost'] + $deliveryFee,
            'delivery_address' => $data['delivery_address'],
            'delivery_lat' => $data['delivery_lat'],
            'delivery_lng' => $data['delivery_lng'],
            'is_external_order' => true,
        ]);

        FindRiderForOrder::dispatch($order);

        return back()->with('success', 'External order added. Searching for rider...');
    }
}