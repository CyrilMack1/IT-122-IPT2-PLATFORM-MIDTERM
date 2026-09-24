@extends('layouts.app')

@section('content')
<div x-data="externalOrderForm()">

    {{-- HEADER --}}
    <div class="mb-6 flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-bold">{{ $restaurant->name }}</h1>
            <p class="text-sm text-gray-500">
                Status:
                <strong class="{{ $restaurant->is_open ? 'text-green-600' : 'text-red-600' }}">
                    {{ $restaurant->is_open ? 'OPEN' : 'CLOSED' }}
                </strong>
            </p>
        </div>

        {{-- RATING DISPLAY --}}
        <div class="text-right">
            @if ($ratingStats['avg'])
                <p class="text-xs text-gray-500">Your Rating</p>
                <p class="text-xl font-bold">
                    ⭐ {{ $ratingStats['avg'] }}
                    <span class="text-xs text-gray-400 font-normal">({{ $ratingStats['total'] }} reviews)</span>
                </p>
            @else
                <p class="text-xs text-gray-400">No ratings yet</p>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="p-3 bg-green-100 text-green-800 mb-4">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="p-3 bg-red-100 text-red-800 mb-4">{{ session('error') }}</div>
    @endif

    {{-- NAVIGATION --}}
    <div class="flex flex-wrap gap-2 mb-6">
        <form method="POST" action="{{ route('restaurant.toggle-open') }}">
            @csrf
            <button class="{{ $restaurant->is_open ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded text-sm">
                {{ $restaurant->is_open ? 'Close Restaurant' : 'Open Restaurant' }}
            </button>
        </form>

        <a href="{{ route('restaurant.orders') }}" class="bg-white border px-4 py-2 rounded text-sm hover:bg-gray-50">Order History</a>
        <a href="{{ route('restaurant.analytics') }}" class="bg-white border px-4 py-2 rounded text-sm hover:bg-gray-50">Sales Analytics</a>
        <a href="{{ route('menu-items.index') }}" class="bg-white border px-4 py-2 rounded text-sm hover:bg-gray-50">Manage Menu</a>
        <a href="{{ route('restaurant.profile') }}" class="bg-white border px-4 py-2 rounded text-sm hover:bg-gray-50">Profile</a>
        <button type="button" @click="showForm = !showForm"
                class="bg-orange-600 text-white px-4 py-2 rounded text-sm hover:bg-orange-700">
            + External Order
        </button>
    </div>

    {{-- EXTERNAL ORDER FORM --}}
    <div x-show="showForm" x-cloak class="bg-white border p-4 mb-6">
        <h2 class="font-semibold mb-3">Add External Order (Phone / Walk-in)</h2>
        <form method="POST" action="{{ route('restaurant.orders.external') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @csrf

            <div>
                <label class="block text-xs text-gray-500 mb-1">Customer Name</label>
                <input type="text" name="customer_name" placeholder="Juan Dela Cruz" required class="border p-2 w-full">
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Food Cost</label>
                <input type="number" step="0.01" name="food_cost" placeholder="0.00" required class="border p-2 w-full">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Delivery Address</label>
                <textarea x-model="address" @input.debounce.800ms="geocodeAddress()"
                          required rows="2"
                          class="border p-2 w-full"
                          placeholder="Street, barangay, city"></textarea>
            </div>

            <input type="hidden" name="delivery_address" :value="address">
            <input type="hidden" name="delivery_lat" :value="lat">
            <input type="hidden" name="delivery_lng" :value="lng">

            <div class="md:col-span-2">
                <button type="button" @click="useCurrentLocation()" :disabled="locating"
                        class="text-sm text-orange-600 hover:underline disabled:opacity-50">
                    <span x-show="!locating">Use my current location</span>
                    <span x-show="locating">Getting location...</span>
                </button>
            </div>

            <div class="md:col-span-2">
                <p class="text-xs" x-show="geocoding"><span class="text-gray-500">Looking up address...</span></p>
                <p class="text-xs" x-show="!geocoding && lat && lng && !locating" x-cloak><span class="text-green-600">Location set</span></p>
                <p class="text-xs" x-show="!geocoding && (!lat || !lng) && !locating" x-cloak><span class="text-red-600">Please enter an address or use location</span></p>
            </div>

            <div class="md:col-span-2">
                <button type="submit" :disabled="!lat || !lng"
                        :class="(!lat || !lng) ? 'opacity-40 cursor-not-allowed' : ''"
                        class="bg-orange-600 text-white rounded p-2 px-6">
                    Create & Find Rider
                </button>
            </div>
        </form>
    </div>

    {{-- ORDERS --}}
    @if ($orders->isEmpty())
        <div class="bg-white border p-8 text-center text-gray-500">No orders yet.</div>
    @else
        <div class="space-y-3">
            @foreach ($orders as $order)
                <div class="bg-white border p-4">
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

                        {{-- STATUS + PREP TIME COUNTDOWN --}}
                        <div class="text-right">
                            <span class="text-xs px-2 py-1 rounded-full
                                @if($order->status === 'delivered') bg-green-100 text-green-700
                                @elseif(in_array($order->status, ['rejected','cancelled','no_rider'])) bg-red-100 text-red-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>

                            @if (in_array($order->status, ['confirmed', 'preparing']))
                                @php
                                    $prepTime = $restaurant->prep_time_minutes ?? 20;
                                    $elapsed = (int) $order->created_at->diffInMinutes(now());
                                    $remaining = $prepTime - $elapsed;
                                @endphp
                                <p class="text-xs mt-1 font-medium {{ $remaining > 0 ? 'text-gray-500' : 'text-red-600' }}">
                                    @if ($remaining > 0)
                                        {{ $remaining }} min left
                                    @else
                                        {{ abs($remaining) }} min overdue
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="mt-3 flex flex-wrap gap-2">
                        @if ($order->status === 'received')
                            <form method="POST" action="{{ route('restaurant.orders.confirm', $order) }}">
                                @csrf
                                <button class="bg-green-600 text-white px-4 py-1.5 rounded text-sm">Confirm</button>
                            </form>
                            <button type="button" onclick="document.getElementById('reject-{{ $order->id }}').classList.toggle('hidden')"
                                    class="bg-red-600 text-white px-4 py-1.5 rounded text-sm">Reject</button>
                        @elseif ($order->status === 'confirmed')
                            <form method="POST" action="{{ route('restaurant.orders.ready', $order) }}">
                                @csrf
                                <button class="bg-blue-600 text-white px-4 py-1.5 rounded text-sm">Mark as Preparing</button>
                            </form>
                        @endif
                    </div>

                    {{-- REJECT FORM --}}
                    @if ($order->status === 'received')
                        <div id="reject-{{ $order->id }}" class="hidden mt-3 p-3 bg-red-50 border border-red-200 rounded">
                            <form method="POST" action="{{ route('restaurant.orders.reject', $order) }}" class="space-y-2">
                                @csrf
                                <label class="block text-xs font-medium text-red-800">Reason for rejection</label>
                                <select name="rejection_reason" required class="w-full border p-2 text-sm rounded">
                                    <option value="">Select a reason...</option>
                                    <option value="Out of stock">Out of stock</option>
                                    <option value="Too busy at the moment">Too busy at the moment</option>
                                    <option value="Closing soon">Closing soon</option>
                                    <option value="Cannot deliver to this area">Cannot deliver to this area</option>
                                    <option value="Other">Other</option>
                                </select>
                                <button class="bg-red-600 text-white px-4 py-1.5 rounded text-sm">Confirm Rejection</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
<script>
function externalOrderForm() {
    return {
        showForm: false,
        address: '',
        lat: '',
        lng: '',
        locating: false,
        geocoding: false,

        async useCurrentLocation() {
            if (!navigator.geolocation) {
                alert('Geolocation not supported.');
                return;
            }

            this.locating = true;

            navigator.geolocation.getCurrentPosition(async (pos) => {
                this.lat = pos.coords.latitude;
                this.lng = pos.coords.longitude;

                try {
                    const res = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${this.lat}&lon=${this.lng}&zoom=18&addressdetails=1`,
                        { headers: { 'Accept-Language': 'en' } }
                    );
                    const data = await res.json();
                    if (data && data.display_name) {
                        this.address = data.display_name;
                    }
                } catch (err) {
                    console.warn('Reverse geocoding failed:', err);
                }

                this.locating = false;
            }, () => {
                alert('Could not get your location.');
                this.locating = false;
            });
        },

        async geocodeAddress() {
            if (!this.address || this.address.length < 5) {
                this.lat = '';
                this.lng = '';
                return;
            }
            if (this.locating) return;

            this.geocoding = true;

            try {
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.address)}&limit=1`,
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