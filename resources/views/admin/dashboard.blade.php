@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Admin Dashboard</h1>
    <a href="{{ route('admin.accounts') }}" class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 text-sm font-medium">
        Manage All Accounts →
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-semibold mb-3">Pending Restaurants</h2>
        @forelse ($pendingRestaurants as $user)
            <div class="flex justify-between items-center py-2 border-b last:border-0">
                <span class="text-sm">{{ $user->name }}</span>
                <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                    @csrf
                    @method('PATCH')
                    <button class="text-green-600 text-sm hover:underline font-medium">Approve</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-500">No pending restaurant accounts.</p>
        @endforelse
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-semibold mb-3">Pending Riders</h2>
        @forelse ($pendingRiders as $user)
            <div class="flex justify-between items-center py-2 border-b last:border-0">
                <span class="text-sm">{{ $user->name }}</span>
                <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                    @csrf
                    @method('PATCH')
                    <button class="text-green-600 text-sm hover:underline font-medium">Approve</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-500">No pending rider accounts.</p>
        @endforelse
    </div>
</div>

<div class="bg-white rounded-lg shadow p-4 mb-6">
    <h2 class="font-semibold mb-3">System Configuration</h2>
    <form method="POST" action="{{ route('admin.config.update') }}" class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @csrf
        <div>
            <label class="block text-xs text-gray-600 mb-1">Town Center Lat</label>
            <input type="number" step="0.0000001" name="town_center_lat" value="{{ \App\Models\SystemConfig::current()->town_center_lat }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">Town Center Lng</label>
            <input type="number" step="0.0000001" name="town_center_lng" value="{{ \App\Models\SystemConfig::current()->town_center_lng }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">Service Radius (km)</label>
            <input type="number" step="0.1" name="service_radius_km" value="{{ \App\Models\SystemConfig::current()->service_radius_km }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">Default Delivery Fee (₱)</label>
            <input type="number" step="0.01" name="default_delivery_fee" value="{{ \App\Models\SystemConfig::current()->default_delivery_fee }}" class="w-full border rounded px-3 py-2">
        </div>
        <button class="col-span-2 md:col-span-4 bg-orange-600 text-white py-2 rounded hover:bg-orange-700 font-medium">
            Save Configuration
        </button>
    </form>
</div>

<div class="bg-white rounded-lg shadow p-4">
    <h2 class="font-semibold mb-3">Recent Orders</h2>
    @forelse ($recentOrders as $order)
        <div class="py-2 border-b last:border-0 text-sm flex justify-between items-center">
            <div>
                <span class="font-medium">#{{ $order->id }}</span> — 
                <span>{{ $order->restaurant->name }}</span>
                <span class="text-gray-500">({{ $order->customer->name }})</span>
            </div>
            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700">
                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
            </span>
        </div>
    @empty
        <p class="text-sm text-gray-500">No orders yet.</p>
    @endforelse
</div>
@endsection