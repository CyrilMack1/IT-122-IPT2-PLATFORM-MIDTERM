<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\SystemConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['restaurant', 'rider', 'items'])
            ->where('customer_id', auth()->id())
            ->latest()
            ->get();

        return view('customer.orders', compact('orders'));
    }

    public function show(Order $order)
    {
        abort_unless($order->customer_id === auth()->id(), 403);
        $order->load(['restaurant', 'rider', 'items', 'payment']);

        return view('customer.track', compact('order'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_address' => 'required|string',
            'delivery_lat' => 'required|numeric',
            'delivery_lng' => 'required|numeric',
        ]);

        $menuItems = MenuItem::whereIn('id', collect($data['items'])->pluck('menu_item_id'))
            ->where('restaurant_id', $data['restaurant_id'])
            ->get()
            ->keyBy('id');

        abort_if($menuItems->count() !== count($data['items']), 422, 'Invalid menu items');

        $foodCost = 0;
        foreach ($data['items'] as $item) {
            $foodCost += $menuItems[$item['menu_item_id']]->price * $item['quantity'];
        }

        $deliveryFee = SystemConfig::current()->default_delivery_fee;

        $order = DB::transaction(function () use ($data, $foodCost, $deliveryFee, $menuItems) {
            $order = Order::create([
                'customer_id' => auth()->id(),
                'restaurant_id' => $data['restaurant_id'],
                'status' => 'received',
                'food_cost' => $foodCost,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $foodCost + $deliveryFee,
                'delivery_address' => $data['delivery_address'],
                'delivery_lat' => $data['delivery_lat'],
                'delivery_lng' => $data['delivery_lng'],
            ]);

            foreach ($data['items'] as $item) {
                $m = $menuItems[$item['menu_item_id']];
                $order->items()->create([
                    'menu_item_id' => $m->id,
                    'name' => $m->name,
                    'price' => $m->price,
                    'quantity' => $item['quantity'],
                ]);
            }

            return $order;
        });

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Order placed successfully!');
    }

    public function rate(Request $request, Order $order)
    {
        abort_unless($order->customer_id === auth()->id(), 403);
        abort_unless($order->status === 'delivered', 422, 'Order not yet delivered');

        $data = $request->validate([
            'restaurant_rating' => 'required|integer|min:1|max:5',
            'rider_rating' => 'required|integer|min:1|max:5',
        ]);

        $order->update($data);

        return back()->with('success', 'Ratings submitted. Thank you!');
    }
}