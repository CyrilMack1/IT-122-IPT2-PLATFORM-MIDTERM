@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">All Orders</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-600 hover:underline">← Dashboard</a>
        <a href="{{ route('admin.orders.export', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded text-sm">Export CSV</a>
    </div>
</div>

{{-- FILTERS --}}
<form method="GET" class="bg-white border p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs mb-1">Status</label>
        <select name="status" class="border p-2 text-sm">
            <option value="">All</option>
            @foreach(['received','confirmed','preparing','finding_rider','rider_assigned','picked_up','out_for_delivery','delivered','rejected','cancelled','no_rider'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs mb-1">Restaurant</label>
        <select name="restaurant_id" class="border p-2 text-sm">
            <option value="">All</option>
            @foreach ($restaurants as $r)
                <option value="{{ $r->id }}" @selected(request('restaurant_id') == $r->id)>{{ $r->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs mb-1">Rider</label>
        <select name="rider_id" class="border p-2 text-sm">
            <option value="">All</option>
            @foreach ($riders as $r)
                <option value="{{ $r->id }}" @selected(request('rider_id') == $r->id)>{{ $r->user->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs mb-1">From</label>
        <input type="date" name="from" value="{{ request('from') }}" class="border p-2 text-sm">
    </div>

    <div>
        <label class="block text-xs mb-1">To</label>
        <input type="date" name="to" value="{{ request('to') }}" class="border p-2 text-sm">
    </div>

    <button class="bg-orange-600 text-white px-4 py-2 rounded text-sm">Filter</button>
    <a href="{{ route('admin.orders') }}" class="text-sm text-gray-600 underline">Reset</a>
</form>

{{-- LIST --}}
<div class="bg-white border overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left p-3 font-medium">#</th>
                <th class="text-left p-3 font-medium">Date</th>
                <th class="text-left p-3 font-medium">Customer</th>
                <th class="text-left p-3 font-medium">Restaurant</th>
                <th class="text-left p-3 font-medium">Rider</th>
                <th class="text-left p-3 font-medium">Total</th>
                <th class="text-left p-3 font-medium">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $o)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-medium">{{ $o->id }}</td>
                    <td class="p-3 text-gray-500 text-xs">{{ $o->created_at->format('M d, H:i') }}</td>
                    <td class="p-3">{{ $o->customer->name ?? '-' }}</td>
                    <td class="p-3">{{ $o->restaurant->name }}</td>
                    <td class="p-3">{{ $o->rider->user->name ?? '—' }}</td>
                    <td class="p-3">₱{{ number_format($o->total_amount, 2) }}</td>
                    <td class="p-3">
                        <span class="text-xs px-2 py-1 rounded
                            @if($o->status === 'delivered') bg-green-100 text-green-700
                            @elseif(in_array($o->status, ['rejected','cancelled','no_rider'])) bg-red-100 text-red-700
                            @else bg-yellow-100 text-yellow-700 @endif">
                            {{ ucfirst(str_replace('_', ' ', $o->status)) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-8 text-center text-gray-500">No orders found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $orders->links() }}</div>
@endsection