@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">My Orders</h1>
        <p class="text-sm text-gray-500 mt-1">Track and view your orders</p>
    </div>
    <a href="{{ route('customer.restaurants') }}"
       class="bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-orange-700">
        New Order
    </a>
</div>

@if ($orders->isEmpty())
    <div class="bg-white rounded-lg border border-gray-200 p-16 text-center">
        <p class="text-gray-500 mb-4">You have no orders yet.</p>
        <a href="{{ route('customer.restaurants') }}"
           class="inline-block text-sm text-orange-600 hover:underline">
            Browse restaurants
        </a>
    </div>
@else
    <div class="space-y-2">
        @foreach ($orders as $order)
            @php
                $statusLabels = [
                    'received' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'preparing' => 'Preparing',
                    'finding_rider' => 'Finding rider',
                    'rider_assigned' => 'Rider assigned',
                    'picked_up' => 'Picked up',
                    'out_for_delivery' => 'On the way',
                    'delivered' => 'Delivered',
                    'no_rider' => 'No rider',
                    'cancelled' => 'Cancelled',
                    'rejected' => 'Rejected',
                ];
                $statusColors = [
                    'delivered' => 'bg-green-50 text-green-700',
                    'cancelled' => 'bg-red-50 text-red-700',
                    'rejected' => 'bg-red-50 text-red-700',
                    'no_rider' => 'bg-red-50 text-red-700',
                    'received' => 'bg-gray-100 text-gray-600',
                ];
                $label = $statusLabels[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status));
                $color = $statusColors[$order->status] ?? 'bg-amber-50 text-amber-700';
            @endphp

            <a href="{{ route('customer.orders.show', $order) }}"
               class="block bg-white rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-sm transition p-5">
                <div class="flex justify-between items-start">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="font-semibold text-gray-900 truncate">{{ $order->restaurant->name }}</h3>
                            <span class="text-xs text-gray-400">#{{ $order->id }}</span>
                        </div>
                        <p class="text-sm text-gray-500">
                            {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }} ·
                            <span class="font-medium text-gray-700">₱{{ number_format($order->total_amount, 2) }}</span>
                        </p>
                        <p class="text-xs text-gray-400 mt-1">{{ $order->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium whitespace-nowrap ml-4 {{ $color }}">
                        {{ $label }}
                    </span>
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection