@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Sales Analytics</h1>
    <a href="{{ route('restaurant.dashboard') }}" class="text-sm text-gray-600 hover:underline">← Back</a>
</div>

{{-- STATS CARDS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Total Sales</p>
        <p class="text-2xl font-bold">₱{{ number_format($stats['total_sales'], 2) }}</p>
    </div>
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Total Orders</p>
        <p class="text-2xl font-bold">{{ $stats['total_orders'] }}</p>
    </div>
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Avg Order Value</p>
        <p class="text-2xl font-bold">₱{{ number_format($stats['avg_order_value'], 2) }}</p>
    </div>
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Avg Rating</p>
        <p class="text-2xl font-bold">{{ $stats['avg_rating'] }}</p>
    </div>
</div>

{{-- FILTER --}}
<form method="GET" class="bg-white border p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs mb-1">From</label>
        <input type="date" name="from" value="{{ request('from') }}" class="border p-2">
    </div>
    <div>
        <label class="block text-xs mb-1">To</label>
        <input type="date" name="to" value="{{ request('to') }}" class="border p-2">
    </div>
    <button class="bg-orange-600 text-white px-4 py-2 rounded">Apply</button>
    <a href="{{ route('restaurant.analytics') }}" class="text-sm text-gray-600 underline">Reset</a>
</form>

{{-- TOP SELLING ITEMS --}}
<div class="bg-white border p-4 mb-6">
    <h2 class="font-semibold mb-3">Top Selling Items</h2>
    @forelse ($topItems as $item)
        <div class="flex justify-between py-2 border-b last:border-0">
            <div>
                <p class="font-medium">{{ $item->name }}</p>
                <p class="text-xs text-gray-500">{{ $item->total_quantity }} sold</p>
            </div>
            <p class="font-medium">₱{{ number_format($item->total_revenue, 2) }}</p>
        </div>
    @empty
        <p class="text-gray-500 text-sm">No data yet.</p>
    @endforelse
</div>

{{-- DAILY SALES --}}
<div class="bg-white border p-4">
    <h2 class="font-semibold mb-3">Sales (Last 7 Days)</h2>
    @forelse ($dailySales as $day)
        <div class="flex justify-between py-2 border-b last:border-0">
            <p class="text-sm">{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</p>
            <p class="text-sm">{{ $day->count }} orders — <strong>₱{{ number_format($day->total, 2) }}</strong></p>
        </div>
    @empty
        <p class="text-gray-500 text-sm">No data yet.</p>
    @endforelse
</div>
@endsection