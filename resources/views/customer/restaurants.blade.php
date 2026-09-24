@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-semibold text-gray-900">Restaurants</h1>
    <p class="text-sm text-gray-500 mt-1">Choose from available restaurants</p>
</div>

@if ($restaurants->isEmpty())
    <div class="bg-white rounded-lg border border-gray-200 p-16 text-center">
        <p class="text-gray-500">No restaurants available.</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($restaurants as $restaurant)
            <a href="{{ route('customer.restaurants.show', $restaurant) }}"
               class="group bg-white rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-sm transition overflow-hidden">
                <div class="h-28 bg-gray-100 border-b border-gray-200"></div>

                <div class="p-5">
                    <div class="flex items-start justify-between mb-2">
                        <h2 class="font-semibold text-gray-900 group-hover:text-orange-600 transition">
                            {{ $restaurant->name }}
                        </h2>
                        <span class="text-xs text-gray-500 mt-1">{{ $restaurant->menu_items_count }} items</span>
                    </div>

                    <p class="text-sm text-gray-500 line-clamp-1">{{ $restaurant->address }}</p>

                    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-xs text-gray-400">Open</span>
                        <span class="text-sm text-gray-700 group-hover:text-orange-600">View menu</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection