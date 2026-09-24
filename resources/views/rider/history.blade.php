@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Delivery History</h1>
    <a href="{{ route('rider.dashboard') }}" class="text-sm text-gray-600 hover:underline">← Back</a>
</div>

{{-- STATS --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Total Earnings</p>
        <p class="text-2xl font-bold text-green-600">₱{{ number_format($stats['total_earnings'], 2) }}</p>
    </div>
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Total Deliveries</p>
        <p class="text-2xl font-bold">{{ $stats['total_deliveries'] }}</p>
    </div>
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Average Rating</p>
        <div class="flex items-center gap-1 mt-1">
            @if ($stats['avg_rating'] !== 'N/A')
                @for ($i = 1; $i <= 5; $i++)
                    <span class="text-lg {{ $i <= floor($stats['avg_rating']) ? 'text-orange-500' : 'text-gray-300' }}">★</span>
                @endfor
                <span class="text-sm font-bold ml-1">{{ $stats['avg_rating'] }}</span>
            @else
                <span class="text-lg text-gray-400">N/A</span>
            @endif
        </div>
    </div>
</div>

{{-- LIST --}}
<div class="bg-white border">
    @forelse ($orders as $order)
        <div class="p-4 border-b last:border-0 flex justify-between items-center">
            <div>
                <p class="font-semibold">Order #{{ $order->id }}</p>
                <p class="text-sm text-gray-600">{{ $order->restaurant->name }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $order->delivery_address }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $order->updated_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="text-right">
                <p class="text-lg font-bold text-green-600">₱{{ number_format($order->delivery_fee, 2) }}</p>
                <p class="text-xs text-gray-500">earned</p>
            </div>
        </div>
    @empty
        <div class="p-8 text-center text-gray-500">No completed deliveries yet.</div>
    @endforelse
</div>

<div class="mt-4">
    {{ $orders->links() }}
</div>
@endsection