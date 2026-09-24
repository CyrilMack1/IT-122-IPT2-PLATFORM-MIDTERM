@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Restaurant Profile</h1>
        <a href="{{ route('restaurant.dashboard') }}" class="text-sm text-gray-600 hover:underline">← Back</a>
    </div>

    @if (session('success'))
        <div class="p-3 bg-green-100 text-green-800 mb-4">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="p-3 bg-red-100 text-red-800 mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border p-6">
        <form method="POST" action="{{ route('restaurant.profile.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-medium mb-1">Restaurant Name</label>
                <input type="text" name="name" value="{{ old('name', $restaurant->name) }}" required
                       class="w-full border p-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Address</label>
                <input type="text" name="address" value="{{ old('address', $restaurant->address) }}" required
                       class="w-full border p-2">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Latitude</label>
                    <input type="text" name="latitude" value="{{ old('latitude', $restaurant->latitude) }}" required
                           class="w-full border p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Longitude</label>
                    <input type="text" name="longitude" value="{{ old('longitude', $restaurant->longitude) }}" required
                           class="w-full border p-2">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Preparation Time (minutes)</label>
                <input type="number" name="prep_time_minutes"
                       value="{{ old('prep_time_minutes', $restaurant->prep_time_minutes ?? 20) }}"
                       min="1" max="120" required
                       class="w-full border p-2">
                <p class="text-xs text-gray-500 mt-1">How long it takes to prepare an order.</p>
            </div>

            <div class="flex gap-2">
                <button class="bg-orange-600 text-white px-6 py-2 rounded">Save Profile</button>
                <a href="{{ route('restaurant.dashboard') }}" class="border px-6 py-2 rounded">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection