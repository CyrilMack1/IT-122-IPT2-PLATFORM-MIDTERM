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
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium whitespace-nowrap ml-4
                        @if($order->status === 'delivered') bg-green-50 text-green-700
                        @elseif(in_array($order->status, ['cancelled','rejected','no_rider'])) bg-red-50 text-red-700
                        @elseif($order->status === 'received') bg-gray-100 text-gray-600
                        @else bg-amber-50 text-amber-700 @endif">
                        @switch($order->status)
                            @case('received') Pending
                            @case('confirmed') Confirmed
                            @case('preparing') Preparing
                            @case('finding_rider') Finding rider
                            @case('rider_assigned') Rider assigned
                            @case('picked_up') Picked up
                            @case('out_for_delivery') On the way
                            @case('delivered') Delivered
                            @case('no_rider') No rider
                            @case('cancelled') Cancelled
                            @case('rejected') Rejected
                            @default {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        @endswitch
                    </span>
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection