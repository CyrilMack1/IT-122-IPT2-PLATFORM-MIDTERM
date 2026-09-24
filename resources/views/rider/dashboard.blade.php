@extends('layouts.app')

@section('content')
<div x-data="riderDash({{ $rider->id }})" x-init="init()">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Rider Dashboard</h1>
            <p class="text-sm text-gray-500">Welcome, {{ auth()->user()->name }}</p>
        </div>
        <button @click="toggleOnline()"
                :class="online ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 hover:bg-gray-500'"
                class="px-6 py-2 text-white rounded font-medium transition">
            <span x-text="online ? '● Online' : '○ Offline'"></span>
        </button>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Completed Today</p>
            <p class="text-2xl font-bold">{{ $completedToday }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Earnings Today</p>
            <p class="text-2xl font-bold text-green-600">₱{{ number_format($earningsToday, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Status</p>
            <p class="text-sm font-bold mt-1" x-text="online ? '🟢 Ready' : '⚪ Offline'"></p>
        </div>
    </div>

    {{-- INCOMING OFFER --}}
    <template x-if="currentOffer">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-4 border-2 border-orange-500 animate-pulse">
            <h2 class="font-bold text-lg mb-3">🔔 New Delivery Offer</h2>
            <div class="space-y-2 text-sm">
                <p>Restaurant: <strong x-text="currentOffer.restaurant"></strong></p>
                <p>Delivery Address: <strong x-text="currentOffer.delivery_address"></strong></p>
                <p>Food Cost: <strong>₱<span x-text="currentOffer.food_cost"></span></strong></p>
                <p>Delivery Fee: <strong class="text-green-600">₱<span x-text="currentOffer.delivery_fee"></span></strong></p>
                <p>Total to Collect: <strong>₱<span x-text="currentOffer.total_amount"></span></strong></p>
            </div>
            <div class="mt-3 flex items-center justify-between">
                <p class="text-xs text-red-600 font-medium">⏱ Expires in <span x-text="secondsLeft"></span>s</p>
            </div>
            <button @click="acceptOffer()"
                    class="mt-4 w-full bg-orange-600 text-white px-4 py-3 rounded hover:bg-orange-700 font-medium">
                Accept Delivery
            </button>
        </div>
    </template>

    {{-- CURRENT ORDER --}}
    @if ($currentOrder)
        <div class="bg-white rounded-lg shadow-lg p-6 mb-4 border-2 border-blue-500">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="font-bold text-lg">🚚 Current Delivery — Order #{{ $currentOrder->id }}</h2>
                </div>
                <span class="text-xs px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-medium">
                    {{ ucfirst(str_replace('_', ' ', $currentOrder->status)) }}
                </span>
            </div>

            {{-- PICKUP INFO --}}
            <div class="bg-gray-50 rounded p-4 mb-3">
                <p class="text-xs font-medium text-gray-500 uppercase mb-1">🏪 Pickup From</p>
                <p class="font-semibold">{{ $currentOrder->restaurant->name }}</p>
                <p class="text-sm text-gray-600">{{ $currentOrder->restaurant->address }}</p>
                <p class="text-sm mt-2">Pay restaurant: <strong class="text-red-600">₱{{ number_format($currentOrder->food_cost, 2) }}</strong></p>
            </div>

            {{-- DELIVERY INFO --}}
            <div class="bg-gray-50 rounded p-4 mb-3">
                <p class="text-xs font-medium text-gray-500 uppercase mb-1">📍 Deliver To</p>
                <p class="text-sm text-gray-800">{{ $currentOrder->delivery_address }}</p>
                <p class="text-sm mt-2">Collect from customer: <strong class="text-green-600">₱{{ number_format($currentOrder->total_amount, 2) }}</strong></p>
            </div>

            {{-- ORDER ITEMS --}}
            <div class="border-t pt-3 mb-4">
                <p class="text-xs font-medium text-gray-500 uppercase mb-2">Order Items</p>
                @foreach ($currentOrder->items as $item)
                    <p class="text-sm">{{ $item->quantity }}x {{ $item->name }}</p>
                @endforeach
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex flex-wrap gap-2">
                @if ($currentOrder->status === 'rider_assigned')
                    <form method="POST" action="{{ route('rider.orders.status', $currentOrder) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="picked_up">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm font-medium">
                            ✓ Mark as Picked Up
                        </button>
                    </form>
                @elseif ($currentOrder->status === 'picked_up')
                    <form method="POST" action="{{ route('rider.orders.status', $currentOrder) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="out_for_delivery">
                        <button class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm font-medium">
                            🚀 Out for Delivery
                        </button>
                    </form>
                @elseif ($currentOrder->status === 'out_for_delivery')
                    <form method="POST" action="{{ route('rider.orders.status', $currentOrder) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="delivered">
                        <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm font-medium">
                            ✓ Mark as Delivered
                        </button>
                    </form>
                @endif
            </div>

            {{-- PAYMENT RECORDING --}}
            @if ($currentOrder->status === 'delivered' && !$currentOrder->payment)
                <div class="mt-4 p-4 bg-green-50 rounded border border-green-200">
                    <p class="text-sm font-medium text-green-800 mb-2">💰 Record Payment</p>
                    <p class="text-xs text-gray-600 mb-3">
                        You paid restaurant <strong>₱{{ number_format($currentOrder->food_cost, 2) }}</strong>
                        and collected from customer <strong>₱{{ number_format($currentOrder->total_amount, 2) }}</strong>.
                        Your delivery fee: <strong class="text-green-700">₱{{ number_format($currentOrder->delivery_fee, 2) }}</strong>
                    </p>
                    <form method="POST" action="{{ route('rider.orders.payment', $currentOrder) }}">
                        @csrf
                        <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm font-medium">
                            Record Payment
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @endif

    {{-- WAITING / OFFLINE STATE --}}
    @if (!$currentOrder && $rider->is_online)
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <p class="text-sm text-gray-600">🟢 You are online. Waiting for delivery offers...</p>
        </div>
    @elseif (!$rider->is_online)
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <p class="text-sm text-gray-600">⚪ You are offline. Toggle online to start receiving delivery offers.</p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
<script>
function riderDash(riderId) {
    return {
        online: {{ $rider->is_online ? 'true' : 'false' }},
        currentOffer: null,
        secondsLeft: 0,
        timer: null,

        init() {
            window.Echo.private(`rider.${riderId}`)
                .listen('.delivery.offer', (e) => {
                    this.currentOffer = e;
                    this.secondsLeft = e.expires_in;
                    this.startCountdown();
                    this.playBeep();
                });

            if (this.online) {
                this.startLocationSharing();
            }
        },

        startCountdown() {
            clearInterval(this.timer);
            this.timer = setInterval(() => {
                this.secondsLeft--;
                if (this.secondsLeft <= 0) {
                    clearInterval(this.timer);
                    this.currentOffer = null;
                }
            }, 1000);
        },

        playBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = 800;
                gain.gain.value = 0.3;
                osc.start();
                setTimeout(() => { osc.stop(); ctx.close(); }, 200);
            } catch (e) { /* silent */ }
        },

        async toggleOnline() {
            const res = await fetch('{{ route('rider.online') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            });
            const data = await res.json();
            this.online = data.is_online;
            if (this.online) this.startLocationSharing();
        },

        startLocationSharing() {
            const send = () => {
                navigator.geolocation.getCurrentPosition(pos => {
                    fetch('{{ route('rider.location') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            latitude: pos.coords.latitude,
                            longitude: pos.coords.longitude
                        })
                    });
                }, () => {
                    console.warn('Could not get location');
                });
            };
            send();
            setInterval(send, 10000);
        },

        async acceptOffer() {
            const res = await fetch(`/rider/orders/${this.currentOffer.order_id}/accept`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            });

            if (res.ok || res.redirected) {
                alert('Order accepted! Proceed to the restaurant.');
                location.href = '{{ route('rider.dashboard') }}';
            } else {
                alert('Offer expired or another rider accepted first.');
                this.currentOffer = null;
            }
        }
    }
}
</script>
@endpush