@extends('layouts.app')

@section('content')
<div x-data="orderTracker({{ $order->id }})" x-init="init()">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Order #{{ $order->id }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $order->restaurant->name }}</p>
        </div>
        <a href="{{ route('customer.orders') }}" class="text-sm text-gray-500 hover:text-gray-700">
            Back
        </a>
    </div>

    {{-- STATUS --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-4">
        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Current status</p>
        <p class="text-lg font-semibold text-gray-900" x-text="statusLabel"></p>

        {{-- PROGRESS --}}
        <div class="mt-6 flex items-center">
            <template x-for="(step, i) in steps" :key="i">
                <div class="flex items-center flex-1">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium flex-shrink-0"
                         :class="stepIndex >= i ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-500'">
                        <span x-text="i + 1"></span>
                    </div>
                    <div class="flex-1 h-0.5 mx-1"
                         :class="stepIndex > i ? 'bg-orange-600' : 'bg-gray-200'"
                         x-show="i < steps.length - 1"></div>
                </div>
            </template>
        </div>
        <div class="flex justify-between mt-2">
            <template x-for="(step, i) in steps" :key="'lbl' + i">
                <span class="text-xs" :class="stepIndex >= i ? 'text-gray-700 font-medium' : 'text-gray-400'" x-text="step"></span>
            </template>
        </div>
    </div>

    {{-- DELIVERY --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-4">
        <p class="text-xs uppercase tracking-wide text-gray-400 mb-2">Delivery address</p>
        <p class="text-sm text-gray-700">{{ $order->delivery_address }}</p>

        @if ($order->rider)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Your rider</p>
                <p class="text-sm font-medium text-gray-900">{{ $order->rider->user->name }}</p>
            </div>
        @endif
    </div>

    {{-- ITEMS --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-4">
        <p class="text-xs uppercase tracking-wide text-gray-400 mb-4">Order summary</p>

        <div class="space-y-2">
            @foreach ($order->items as $item)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-700">{{ $item->quantity }}× {{ $item->name }}</span>
                    <span class="text-gray-900">₱{{ number_format($item->price * $item->quantity, 2) }}</span>
                </div>
            @endforeach
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100 space-y-1.5 text-sm">
            <div class="flex justify-between text-gray-500">
                <span>Food cost</span>
                <span>₱{{ number_format($order->food_cost, 2) }}</span>
            </div>
            <div class="flex justify-between text-gray-500">
                <span>Delivery fee</span>
                <span>₱{{ number_format($order->delivery_fee, 2) }}</span>
            </div>
            <div class="flex justify-between font-semibold text-gray-900 pt-2 border-t border-gray-100">
                <span>Total</span>
                <span>₱{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- RATING --}}
    @if ($order->status === 'delivered' && !$order->restaurant_rating)
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <p class="text-xs uppercase tracking-wide text-gray-400 mb-4">Rate your order</p>
            <form method="POST" action="{{ route('customer.orders.rate', $order) }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm text-gray-700 mb-2">Restaurant</label>
                    <div class="flex gap-2">
                        @for ($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer">
                                <input type="radio" name="restaurant_rating" value="{{ $i }}" class="peer sr-only" required>
                                <span class="block w-10 h-10 rounded-lg border border-gray-200 flex items-center justify-center text-sm peer-checked:bg-orange-600 peer-checked:text-white peer-checked:border-orange-600 hover:border-gray-300">
                                    {{ $i }}
                                </span>
                            </label>
                        @endfor
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-700 mb-2">Rider</label>
                    <div class="flex gap-2">
                        @for ($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer">
                                <input type="radio" name="rider_rating" value="{{ $i }}" class="peer sr-only" required>
                                <span class="block w-10 h-10 rounded-lg border border-gray-200 flex items-center justify-center text-sm peer-checked:bg-orange-600 peer-checked:text-white peer-checked:border-orange-600 hover:border-gray-300">
                                    {{ $i }}
                                </span>
                            </label>
                        @endfor
                    </div>
                </div>

                <button class="w-full bg-orange-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-orange-700">
                    Submit
                </button>
            </form>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
<script>
function orderTracker(orderId) {
    return {
        status: '{{ $order->status }}',
        steps: ['Placed', 'Confirmed', 'Preparing', 'Rider', 'On the way', 'Delivered'],

        init() {
            window.Echo.private(`order.${orderId}`)
                .listen('.order.status', (e) => { this.status = e.status; location.reload(); })
                .listen('.rider.assigned', (e) => { this.status = e.status; location.reload(); })
                .listen('.no.rider', () => { this.status = 'no_rider'; });
        },

        get statusLabel() {
            const labels = {
                'received': 'Waiting for restaurant confirmation',
                'confirmed': 'Restaurant confirmed your order',
                'preparing': 'Preparing your food',
                'finding_rider': 'Finding a rider',
                'rider_assigned': 'Rider on the way to restaurant',
                'picked_up': 'Rider picked up your order',
                'out_for_delivery': 'Order is on the way',
                'delivered': 'Order delivered',
                'no_rider': 'No rider available',
                'cancelled': 'Order cancelled',
                'rejected': 'Order rejected by restaurant',
            };
            return labels[this.status] || this.status.replace(/_/g, ' ');
        },

        get stepIndex() {
            const map = {
                'received': 0, 'confirmed': 1, 'preparing': 2,
                'finding_rider': 3, 'rider_assigned': 3,
                'picked_up': 4, 'out_for_delivery': 4, 'delivered': 5,
                'no_rider': 0, 'cancelled': 0, 'rejected': 0,
            };
            return map[this.status] ?? 0;
        }
    }
}
</script>
@endpush