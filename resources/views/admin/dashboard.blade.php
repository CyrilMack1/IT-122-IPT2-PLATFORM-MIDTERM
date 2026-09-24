@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Admin Dashboard</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.orders') }}" class="bg-white border px-4 py-2 rounded text-sm hover:bg-gray-50">All Orders</a>
        <a href="{{ route('admin.reports') }}" class="bg-white border px-4 py-2 rounded text-sm hover:bg-gray-50">Reports</a>
        <a href="{{ route('admin.accounts') }}" class="bg-orange-600 text-white px-4 py-2 rounded text-sm hover:bg-orange-700">Manage Accounts</a>
    </div>
</div>

{{-- SUMMARY STATS --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Total Orders</p>
        <p class="text-2xl font-bold">{{ $stats['total_orders'] }}</p>
    </div>
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Delivered</p>
        <p class="text-2xl font-bold text-green-600">{{ $stats['delivered_orders'] }}</p>
    </div>
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Total Sales</p>
        <p class="text-2xl font-bold text-green-600">₱{{ number_format($stats['total_sales'], 0) }}</p>
    </div>
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Active Restaurants</p>
        <p class="text-2xl font-bold">{{ $stats['active_restaurants'] }}</p>
    </div>
    <div class="bg-white border p-4">
        <p class="text-xs text-gray-500">Online Riders</p>
        <p class="text-2xl font-bold text-blue-600">{{ $stats['online_riders'] }}</p>
    </div>
</div>

{{-- PENDING APPROVALS --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white border p-4">
        <h2 class="font-semibold mb-3">Pending Restaurants</h2>
        @forelse ($pendingRestaurants as $user)
            <div class="flex justify-between items-center py-2 border-b last:border-0">
                <div>
                    <p class="text-sm font-medium">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                </div>
                <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                    @csrf
                    @method('PATCH')
                    <button class="text-green-600 text-xs hover:underline">Approve</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-500">No pending.</p>
        @endforelse
    </div>

    <div class="bg-white border p-4">
        <h2 class="font-semibold mb-3">Pending Riders</h2>
        @forelse ($pendingRiders as $user)
            <div class="flex justify-between items-center py-2 border-b last:border-0">
                <div>
                    <p class="text-sm font-medium">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                </div>
                <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                    @csrf
                    @method('PATCH')
                    <button class="text-green-600 text-xs hover:underline">Approve</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-500">No pending.</p>
        @endforelse
    </div>
</div>

{{-- SYSTEM CONFIG --}}
<div class="bg-white border p-4 mb-6" x-data="townConfig()">
    <h2 class="font-semibold mb-3">System Configuration</h2>
    <form method="POST" action="{{ route('admin.config.update') }}" class="space-y-3">
        @csrf

        {{-- TOWN ADDRESS --}}
        <div>
            <label class="block text-xs mb-1">Town / City</label>
            <input type="text" 
                   x-model="townAddress" 
                   @input.debounce.800ms="geocode()"
                   placeholder="e.g. Cagayan de Oro City"
                   class="w-full border p-2">
            <p class="text-xs mt-1" x-show="geocoding"><span class="text-gray-500">Looking up town...</span></p>
            <p class="text-xs mt-1" x-show="!geocoding && lat && lng && !locating" x-cloak><span class="text-green-600">Location set</span></p>
            <p class="text-xs mt-1" x-show="!geocoding && (!lat || !lng) && !locating" x-cloak><span class="text-red-600">Please enter a valid town</span></p>
        </div>

        {{-- HIDDEN LAT/LNG --}}
        <input type="hidden" name="town_address" :value="townAddress">
        <input type="hidden" name="town_center_lat" :value="lat">
        <input type="hidden" name="town_center_lng" :value="lng">

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs mb-1">Service Radius (km)</label>
                <input type="number" step="0.1" name="service_radius_km" value="{{ \App\Models\SystemConfig::current()->service_radius_km }}" class="w-full border p-2">
            </div>
            <div>
                <label class="block text-xs mb-1">Delivery Fee (₱)</label>
                <input type="number" step="0.01" name="default_delivery_fee" value="{{ \App\Models\SystemConfig::current()->default_delivery_fee }}" class="w-full border p-2">
            </div>
        </div>

        <button type="submit" 
                :disabled="!lat || !lng" 
                :class="(!lat || !lng) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-orange-700'" 
                class="bg-orange-600 text-white py-2 px-6 rounded">
            Save Configuration
        </button>
    </form>
</div>

{{-- RECENT ORDERS --}}
<div class="bg-white border p-4">
    <h2 class="font-semibold mb-3">Recent Orders</h2>
    @forelse ($recentOrders as $order)
        <div class="py-2 border-b last:border-0 text-sm flex justify-between items-center">
            <div>
                <span class="font-medium">#{{ $order->id }}</span> —
                <span>{{ $order->restaurant->name }}</span>
                <span class="text-gray-500">({{ $order->customer->name ?? 'N/A' }})</span>
            </div>
            <span class="text-xs px-2 py-1 rounded bg-gray-100">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
        </div>
    @empty
        <p class="text-sm text-gray-500">No orders yet.</p>
    @endforelse
</div>
@endsection

@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
<script>
function townConfig() {
    return {
        townAddress: '{{ \App\Models\SystemConfig::current()->town_address ?? "Cagayan de Oro City" }}',
        lat: '{{ \App\Models\SystemConfig::current()->town_center_lat }}',
        lng: '{{ \App\Models\SystemConfig::current()->town_center_lng }}',
        geocoding: false,

        async geocode() {
            if (!this.townAddress || this.townAddress.length < 3) {
                this.lat = '';
                this.lng = '';
                return;
            }

            this.geocoding = true;

            try {
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.townAddress)}&limit=1`,
                    { headers: { 'Accept-Language': 'en' } }
                );
                const data = await res.json();

                if (data && data.length > 0) {
                    this.lat = data[0].lat;
                    this.lng = data[0].lon;
                } else {
                    this.lat = '';
                    this.lng = '';
                }
            } catch (err) {
                console.warn('Geocoding failed:', err);
                this.lat = '';
                this.lng = '';
            }

            this.geocoding = false;
        }
    }
}
</script>
@endpush
@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
<script>
function townConfig() {
    return {
        townAddress: '{{ \App\Models\SystemConfig::current()->town_address ?? "Cagayan de Oro City" }}',
        lat: '{{ \App\Models\SystemConfig::current()->town_center_lat }}',
        lng: '{{ \App\Models\SystemConfig::current()->town_center_lng }}',
        geocoding: false,
        locating: false,

        async geocode() {
            if (!this.townAddress || this.townAddress.length < 3) {
                this.lat = '';
                this.lng = '';
                return;
            }

            this.geocoding = true;

            try {
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.townAddress)}&limit=1`,
                    { headers: { 'Accept-Language': 'en' } }
                );
                const data = await res.json();

                if (data && data.length > 0) {
                    this.lat = data[0].lat;
                    this.lng = data[0].lon;
                } else {
                    this.lat = '';
                    this.lng = '';
                }
            } catch (err) {
                console.warn('Geocoding failed:', err);
                this.lat = '';
                this.lng = '';
            }

            this.geocoding = false;
        }
    }
}
</script>
@endpush