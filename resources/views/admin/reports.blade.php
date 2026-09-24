@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Reports</h1>
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-600 hover:underline">← Dashboard</a>
</div>

{{-- FILTER --}}
<form method="GET" class="bg-white border p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs mb-1">From</label>
        <input type="date" name="from" value="{{ $from }}" class="border p-2 text-sm">
    </div>
    <div>
        <label class="block text-xs mb-1">To</label>
        <input type="date" name="to" value="{{ $to }}" class="border p-2 text-sm">
    </div>
    <button class="bg-orange-600 text-white px-4 py-2 rounded text-sm">Apply</button>
    <a href="{{ route('admin.reports.export', ['from' => $from, 'to' => $to]) }}"
       class="bg-green-600 text-white px-4 py-2 rounded text-sm">Export CSV</a>
</form>

{{-- SUMMARY --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Total Orders</p>
        <p class="text-2xl font-bold">{{ $salesStats['total_orders'] }}</p>
    </div>
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Food Cost</p>
        <p class="text-2xl font-bold">₱{{ number_format($salesStats['total_food_cost'], 2) }}</p>
    </div>
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Delivery Fees</p>
        <p class="text-2xl font-bold">₱{{ number_format($salesStats['total_delivery_fees'], 2) }}</p>
    </div>
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Total Revenue</p>
        <p class="text-2xl font-bold text-green-600">₱{{ number_format($salesStats['total_revenue'], 2) }}</p>
    </div>
</div>

{{-- TOP RESTAURANTS --}}
<div class="bg-white border p-4 mb-6">
    <h2 class="font-semibold mb-3">Top Restaurants</h2>
    @forelse ($topRestaurants as $r)
        <div class="flex justify-between py-2 border-b last:border-0">
            <div>
                <p class="font-medium">{{ $r->name }}</p>
                <p class="text-xs text-gray-500">{{ $r->total_orders }} orders</p>
            </div>
            <p class="font-medium">₱{{ number_format($r->total_sales, 2) }}</p>
        </div>
    @empty
        <p class="text-gray-500 text-sm">No data.</p>
    @endforelse
</div>

{{-- TOP RIDERS --}}
<div class="bg-white border p-4 mb-6">
    <h2 class="font-semibold mb-3">Top Riders</h2>
    @forelse ($topRiders as $r)
        <div class="flex justify-between py-2 border-b last:border-0">
            <div>
                <p class="font-medium">{{ $r->name }}</p>
                <p class="text-xs text-gray-500">{{ $r->total_deliveries }} deliveries</p>
            </div>
            <p class="font-medium">₱{{ number_format($r->total_earnings, 2) }}</p>
        </div>
    @empty
        <p class="text-gray-500 text-sm">No data.</p>
    @endforelse
</div>

{{-- DAILY SALES --}}
<div class="bg-white border p-4">
    <h2 class="font-semibold mb-3">Daily Sales</h2>
    @forelse ($dailySales as $d)
        <div class="flex justify-between py-2 border-b last:border-0">
            <p class="text-sm">{{ \Carbon\Carbon::parse($d->date)->format('M d, Y') }}</p>
            <p class="text-sm">{{ $d->count }} orders — <strong>₱{{ number_format($d->total, 2) }}</strong></p>
        </div>
    @empty
        <p class="text-gray-500 text-sm">No data.</p>
    @endforelse
</div>
@endsection