<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Jobs\FindRiderForOrder;
use App\Models\Order;
use App\Models\SystemConfig;
use App\Notifications\OrderStatusNotification;
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

        $avgRating = Order::where('restaurant_id', $restaurant->id)
            ->whereNotNull('restaurant_rating')
            ->avg('restaurant_rating');

        $totalRatings = Order::where('restaurant_id', $restaurant->id)
            ->whereNotNull('restaurant_rating')
            ->count();

        $ratingStats = [
            'avg' => $avgRating ? round($avgRating, 1) : null,
            'total' => $totalRatings,
        ];

        return view('restaurant.dashboard', compact('restaurant', 'orders', 'ratingStats'));
    }

    public function confirm(Request $request, Order $order)
    {
        $restaurant = auth()->user()->restaurant;
        abort_unless($order->restaurant_id === $restaurant->id, 403);
        abort_unless($order->status === 'received', 422, 'Order already processed');

        $order->update(['status' => 'confirmed']);

        // Notify customer
        $order->customer->notify(new OrderStatusNotification($order->fresh()));

        FindRiderForOrder::dispatch($order);

        return back()->with('success', 'Order confirmed. Searching for rider...');
    }

    public function reject(Request $request, Order $order)
    {
        $restaurant = auth()->user()->restaurant;
        abort_unless($order->restaurant_id === $restaurant->id, 403);

        $data = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $order->update([
            'status' => 'rejected',
            'rejection_reason' => $data['rejection_reason'],
        ]);

        // Notify customer
        $order->customer->notify(new OrderStatusNotification($order->fresh()));

        return back()->with('success', 'Order rejected.');
    }

    public function ready(Request $request, Order $order)
    {
        $restaurant = auth()->user()->restaurant;
        abort_unless($order->restaurant_id === $restaurant->id, 403);

        $order->update(['status' => 'preparing']);

        // Notify customer
        $order->customer->notify(new OrderStatusNotification($order->fresh()));

        return back()->with('success', 'Order marked as preparing.');
    }

    public function markReady(Order $order)
    {
        $restaurant = auth()->user()->restaurant;
        abort_unless($order->restaurant_id === $restaurant->id, 403);

        $order->update(['status' => 'preparing']);

        // Notify customer
        $order->customer->notify(new OrderStatusNotification($order->fresh()));

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

    public function toggleOpen(Request $request)
    {
        $restaurant = auth()->user()->restaurant;
        $restaurant->update(['is_open' => !$restaurant->is_open]);

        return back()->with('success', $restaurant->is_open
            ? 'Restaurant is now OPEN.'
            : 'Restaurant is now CLOSED.');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'prep_time_minutes' => 'nullable|integer|min:1|max:120',
        ]);

        $restaurant = auth()->user()->restaurant;
        $restaurant->update($data);

        return back()->with('success', 'Restaurant profile updated.');
    }

    public function orders(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        $query = Order::with(['customer', 'rider', 'items'])
            ->where('restaurant_id', $restaurant->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        $totalSales = Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'delivered')
            ->sum('food_cost');

        $totalOrders = Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'delivered')
            ->count();

        $avgRating = Order::where('restaurant_id', $restaurant->id)
            ->whereNotNull('restaurant_rating')
            ->avg('restaurant_rating');

        $stats = [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'avg_rating' => $avgRating ? round($avgRating, 2) : 'N/A',
        ];

        return view('restaurant.orders', compact('orders', 'stats'));
    }

    public function analytics(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        $query = Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'delivered');

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $totalSales = (clone $query)->sum('food_cost');
        $totalOrders = (clone $query)->count();
        $avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        $avgRating = Order::where('restaurant_id', $restaurant->id)
            ->whereNotNull('restaurant_rating')
            ->avg('restaurant_rating');

        $stats = [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'avg_order_value' => $avgOrderValue,
            'avg_rating' => $avgRating ? round($avgRating, 2) : 'N/A',
        ];

        $topItems = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.restaurant_id', $restaurant->id)
            ->where('orders.status', 'delivered')
            ->select(
                'order_items.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue')
            )
            ->groupBy('order_items.name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        $dailySales = Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'delivered')
            ->where('created_at', '>=', now()->subDays(7))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(food_cost) as total')
            )
            ->groupBy('date')
            ->orderByDesc('date')
            ->get();

        return view('restaurant.analytics', compact('stats', 'topItems', 'dailySales'));
    }
}