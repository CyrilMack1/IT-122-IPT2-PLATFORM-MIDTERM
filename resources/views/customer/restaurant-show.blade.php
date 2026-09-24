@extends('layouts.app')

@section('content')
<div x-data="orderForm({{ $restaurant->id }}, {{ $restaurant->menuItems->toJson() }})">
    <div class="mb-8">
        <a href="{{ route('customer.restaurants') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-2">{{ $restaurant->name }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $restaurant->address }}</p>
    </div>

    @if (session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('customer.orders.store') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        <input type="hidden" name="restaurant_id" value="{{ $restaurant->id }}">

        {{-- MENU --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Menu</h2>
                </div>

                @forelse ($restaurant->menuItems as $item)
                    <div class="px-5 py-4 border-b border-gray-100 last:border-0 flex justify-between items-center gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900">{{ $item->name }}</p>
                            @if ($item->description)
                                <p class="text-sm text-gray-500 mt-0.5">{{ $item->description }}</p>
                            @endif
                            <p class="text-sm font-medium text-gray-900 mt-1">₱{{ number_format($item->price, 2) }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="dec({{ $item->id }})"
                                    class="w-8 h-8 rounded-full border border-gray-300 hover:bg-gray-50 text-gray-700">−</button>
                            <span class="w-6 text-center font-medium text-gray-900" x-text="qty({{ $item->id }})"></span>
                            <button type="button" @click="inc({{ $item->id }})"
                                    class="w-8 h-8 rounded-full bg-orange-600 hover:bg-orange-700 text-white">+</button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 text-sm">No items available.</div>
                @endforelse
            </div>
        </div>

        {{-- CART --}}
        <div class="lg:sticky lg:top-24 h-fit">
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Your order</h2>
                </div>

                <div class="p-5">
                    <template x-if="cartSize === 0">
                        <p class="text-sm text-gray-500">No items selected.</p>
                    </template>

                    <template x-for="line in cartLines" :key="line.id">
                        <div class="flex justify-between text-sm py-1">
                            <span class="text-gray-700"><span x-text="line.quantity"></span>× <span x-text="line.name"></span></span>
                            <span class="text-gray-900">₱<span x-text="line.subtotal.toFixed(2)"></span></span>
                        </div>
                    </template>

                    <template x-if="cartSize > 0">
                        <div class="mt-4 pt-4 border-t border-gray-100 space-y-1.5 text-sm">
                            <div class="flex justify-between text-gray-500">
                                <span>Food cost</span>
                                <span>₱<span x-text="foodCost.toFixed(2)"></span></span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>Delivery fee</span>
                                <span>₱{{ number_format(\App\Models\SystemConfig::current()->default_delivery_fee, 2) }}</span>
                            </div>
                            <div class="flex justify-between font-semibold text-gray-900 pt-2 border-t border-gray-100">
                                <span>Total</span>
                                <span>₱<span x-text="total.toFixed(2)"></span></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="px-5 pb-5 space-y-4">
                    {{-- ADDRESS --}}
                    <div>
                        <label class="block text-sm text-gray-700 mb-1.5">Delivery address</label>
                        <textarea x-model="address" 
                                  @input.debounce.800ms="geocodeAddress()"
                                  required rows="2"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                  placeholder="Street, barangay, city"></textarea>
                    </div>

                    {{-- HIDDEN LAT/LNG --}}
                    <input type="hidden" name="delivery_address" :value="address">
                    <input type="hidden" name="delivery_lat" :value="lat">
                    <input type="hidden" name="delivery_lng" :value="lng">

                    {{-- LOCATION BUTTON --}}
                    <button type="button" @click="useCurrentLocation()"
                            :disabled="locating"
                            class="w-full text-xs text-gray-600 hover:text-orange-600 border border-gray-300 rounded-lg py-2 disabled:opacity-50">
                        <span x-show="!locating">Use my current location</span>
                        <span x-show="locating">Getting location...</span>
                    </button>

                    {{-- STATUS MESSAGE --}}
                    <p class="text-xs" x-show="geocoding">
                        <span class="text-gray-500">Looking up address...</span>
                    </p>
                    <p class="text-xs" x-show="!geocoding && lat && lng && !locating" x-cloak>
                        <span class="text-green-600">Location set</span>
                    </p>
                    <p class="text-xs" x-show="!geocoding && (!lat || !lng) && !locating" x-cloak>
                        <span class="text-red-600">Please enter an address or use your location</span>
                    </p>

                    {{-- HIDDEN ITEMS --}}
                    <template x-for="line in cartLines" :key="'input-' + line.id">
                        <div>
                            <input type="hidden" :name="'items[' + line.index + '][menu_item_id]'" :value="line.id">
                            <input type="hidden" :name="'items[' + line.index + '][quantity]'" :value="line.quantity">
                        </div>
                    </template>

                    <button type="submit"
                            :disabled="cartSize === 0 || !lat || !lng"
                            :class="(cartSize === 0 || !lat || !lng) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-orange-700'"
                            class="w-full bg-orange-600 text-white py-2.5 rounded-lg text-sm font-medium">
                        Place order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
<script>
function orderForm(restaurantId, menuItems) {
    return {
        menu: menuItems,
        cart: {},
        deliveryFee: {{ \App\Models\SystemConfig::current()->default_delivery_fee }},
        locating: false,
        geocoding: false,
        address: '',
        lat: '',
        lng: '',

        init() {
            this.menu.forEach(m => { this.cart[m.id] = 0; });
        },

        qty(id) { return this.cart[id] || 0; },
        inc(id) { this.cart[id] = (this.cart[id] || 0) + 1; },
        dec(id) { this.cart[id] = Math.max(0, (this.cart[id] || 0) - 1); },

        get cartSize() {
            return Object.values(this.cart).reduce((a, b) => a + b, 0);
        },

        get cartLines() {
            let index = 0;
            const lines = [];
            for (const m of this.menu) {
                const qty = this.cart[m.id] || 0;
                if (qty > 0) {
                    lines.push({
                        index: index++,
                        id: m.id,
                        name: m.name,
                        quantity: qty,
                        subtotal: qty * parseFloat(m.price)
                    });
                }
            }
            return lines;
        },

        get foodCost() {
            return this.cartLines.reduce((sum, l) => sum + l.subtotal, 0);
        },

        get total() {
            return this.foodCost + (this.cartSize > 0 ? this.deliveryFee : 0);
        },

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