@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Menu Items</h1>
    <a href="{{ route('restaurant.dashboard') }}" class="text-sm text-orange-600 hover:underline">← Back to Orders</a>
</div>

<div class="bg-white rounded-lg shadow p-4 mb-6">
    <h2 class="font-semibold mb-3">Add New Item</h2>
    <form method="POST" action="{{ route('menu-items.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        @csrf
        <input type="text" name="name" placeholder="Item Name" required class="border rounded px-3 py-2">
        <input type="text" name="description" placeholder="Description (optional)" class="border rounded px-3 py-2">
        <input type="number" name="price" placeholder="Price (₱)" step="0.01" required class="border rounded px-3 py-2">
        <button class="bg-orange-600 text-white rounded px-3 py-2 hover:bg-orange-700">Add Item</button>
    </form>
</div>

<div class="bg-white rounded-lg shadow">
    @forelse ($items as $item)
        <div class="p-4 border-b last:border-0 flex justify-between items-center">
            <div>
                <p class="font-semibold">{{ $item->name }}</p>
                @if ($item->description)
                    <p class="text-sm text-gray-500">{{ $item->description }}</p>
                @endif
                <p class="text-sm text-gray-700">₱{{ number_format($item->price, 2) }}</p>
            </div>
            <form method="POST" action="{{ route('menu-items.destroy', $item) }}">
                @csrf
                @method('DELETE')
                <button class="text-red-600 text-sm hover:underline">Delete</button>
            </form>
        </div>
    @empty
        <div class="p-8 text-center text-gray-500">
            No menu items yet. Add your first item above.
        </div>
    @endforelse
</div>
@endsection