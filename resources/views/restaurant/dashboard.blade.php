@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">{{ $restaurant->name }} — Orders</h1>
    <div class="flex gap-2">
        <a href="{{ route('menu-items.index') }}" class="text-sm bg-white border px-4 py-2 rounded hover:bg-gray-50">
            Manage Menu
        </a>
        <a href="#" onclick="event.preventDefault(); document.getElementById('external-order-form').classList.toggle('hidden')"
           class="text-sm bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700">
            + External Order
        </a>
    </div>
</div>

<div id="external-order-form" class="hidden bg-white rounded-lg shadow p-4 mb-6">
    <h2 class="font-semibold mb-3">Add External Order (Phone / Walk-in)</h2>
    <form method="POST" action="{{ route('restaurant.orders.external') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @csrf
        <input type="text" name="customer_name" placeholder="Customer Name" required class="border rounded px-3 py-2">
        <input type="text" name="delivery_address" placeholder="Delivery Address" required class="border rounded px-3 py-2">
        <input type="number" step="0.0000001" name="delivery_lat" placeholder="Latitude" required class="border rounded px-3 py-2">
        <input type="number" step="0.0000001" name="delivery_lng" placeholder="Longitude" required class="border rounded px-3 py-2">
        <input type="number" step="0.01" name="food_cost" placeholder="Food Cost" required class="border rounded px-3 py-2">
        <button class="bg-orange-600 text-white rounded px-3 py-2 md:col-span-2">Create & Find Rider</button>
    </form>
</div>

@if ($orders->isEmpty())
    <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        No orders yet.
    </div>
@else
    <div class="space-y-3">
        @foreach ($orders as $order)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-semibold">Order #{{ $order->id }}</p>
                        <p class="text-sm text-gray-600">
                            {{ $order->items->count() }} items • ₱{{ number_format($order->total_amount, 2) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ $order->delivery_address }}</p>
                        @if ($order->rider)
                            <p class="text-xs text-green-600 mt-1">Rider: {{ $order->rider->user->name }}</p>
                        @endif
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full
                        @if($order->status === 'delivered') bg-green-100 text-green-700
                        @elseif(in_array($order->status, ['rejected','cancelled','no_rider'])) bg-red-100 text-red-700
                        @else bg-yellow-100 text-yellow-700 @endif">
                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                    </span>
                </div>

                @if ($order->status === 'received')
                    <div class="mt-3 flex gap-2">
                        <form method="POST" action="{{ route('restaurant.orders.confirm', $order) }}">
                            @csrf
                            <button class="bg-green-600 text-white px-4 py-1.5 rounded text-sm hover:bg-green-700">Confirm</button>
                        </form>
                        <form method="POST" action="{{ route('restaurant.orders.reject', $order) }}">
                            @csrf
                            <button class="bg-red-600 text-white px-4 py-1.5 rounded text-sm hover:bg-red-700">Reject</button>
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
@endsection