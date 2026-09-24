@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Order History</h1>
    <a href="{{ route('restaurant.dashboard') }}" class="text-sm text-gray-600 hover:underline">← Back</a>
</div>

{{-- STATS --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Total Sales</p>
        <p class="text-2xl font-bold">₱{{ number_format($stats['total_sales'], 2) }}</p>
    </div>
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Total Orders</p>
        <p class="text-2xl font-bold">{{ $stats['total_orders'] }}</p>
    </div>
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Average Rating</p>
        <p class="text-2xl font-bold">{{ $stats['avg_rating'] }}</p>
    </div>
</div>

{{-- FILTERS --}}
<form method="GET" class="bg-white border p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs mb-1">Status</label>
        <select name="status" class="border p-2">
            <option value="">All</option>
            <option value="received" @selected(request('status') === 'received')>Received</option>
            <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
            <option value="preparing" @selected(request('status') === 'preparing')>Preparing</option>
            <option value="finding_rider" @selected(request('status') === 'finding_rider')>Finding Rider</option>
            <option value="rider_assigned" @selected(request('status') === 'rider_assigned')>Rider Assigned</option>
            <option value="picked_up" @selected(request('status') === 'picked_up')>Picked Up</option>
            <option value="out_for_delivery" @selected(request('status') === 'out_for_delivery')>Out for Delivery</option>
            <option value="delivered" @selected(request('status') === 'delivered')>Delivered</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
            <option value="no_rider" @selected(request('status') === 'no_rider')>No Rider</option>
        </select>
    </div>

    <div>
        <label class="block text-xs mb-1">From</label>
        <input type="date" name="from" value="{{ request('from') }}" class="border p-2">
    </div>

    <div>
        <label class="block text-xs mb-1">To</label>
        <input type="date" name="to" value="{{ request('to') }}" class="border p-2">
    </div>

    <button class="bg-orange-600 text-white px-4 py-2 rounded">Filter</button>
    <a href="{{ route('restaurant.orders') }}" class="text-sm text-gray-600 underline">Reset</a>
</form>

{{-- LIST --}}
<div class="bg-white border">
    @forelse ($orders as $order)
        <div class="p-4 border-b last:border-0">
            <div class="flex justify-between items-start">
                <div>
                    <p class="font-semibold">#{{ $order->id }} — {{ $order->items->count() }} items</p>
                    <p class="text-sm text-gray-500">
                        ₱{{ number_format($order->total_amount, 2) }} • 
                        {{ $order->created_at->format('M d, Y H:i') }}
                    </p>
                    @if ($order->rider)
                        <p class="text-xs text-gray-500 mt-1">Rider: {{ $order->rider->user->name }}</p>
                    @endif
                </div>
                <span class="text-xs px-2 py-1 rounded bg-gray-100 whitespace-nowrap">
                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
            </div>
        </div>
    @empty
        <div class="p-8 text-center text-gray-500">No orders found.</div>
    @endforelse
</div>

<div class="mt-4">
    {{ $orders->links() }}
</div>
@endsection