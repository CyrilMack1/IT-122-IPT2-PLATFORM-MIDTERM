@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Rider Profile</h1>
        <a href="{{ route('rider.dashboard') }}" class="text-sm text-gray-600 hover:underline">← Back</a>
    </div>

    @if (session('success'))
        <div class="p-3 bg-green-100 text-green-800 mb-4">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="p-3 bg-red-100 text-red-800 mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="bg-white border p-6">
        <form method="POST" action="{{ route('rider.profile.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                       class="w-full border p-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" value="{{ auth()->user()->email }}" disabled
                       class="w-full border p-2 bg-gray-100 text-gray-500">
                <p class="text-xs text-gray-500 mt-1">Email cannot be changed.</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}"
                       class="w-full border p-2">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Vehicle Type</label>
                    <select name="vehicle_type" class="w-full border p-2">
                        <option value="">Select...</option>
                        <option value="Motorcycle" @selected($rider->vehicle_type === 'Motorcycle')>Motorcycle</option>
                        <option value="Bicycle" @selected($rider->vehicle_type === 'Bicycle')>Bicycle</option>
                        <option value="Car" @selected($rider->vehicle_type === 'Car')>Car</option>
                        <option value="E-bike" @selected($rider->vehicle_type === 'E-bike')>E-bike</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Plate Number</label>
                    <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate', $rider->vehicle_plate) }}"
                           placeholder="ABC-1234"
                           class="w-full border p-2">
                </div>
            </div>

            <div class="flex gap-2">
                <button class="bg-orange-600 text-white px-6 py-2 rounded">Save Profile</button>
                <a href="{{ route('rider.dashboard') }}" class="border px-6 py-2 rounded">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection