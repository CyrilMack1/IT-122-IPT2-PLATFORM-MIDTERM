@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Restaurants</h1>
    <p class="text-sm text-gray-500 mt-1">Choose from available restaurants</p>
</div>

{{-- SEARCH + FILTER --}}
<form method="GET" action="{{ route('customer.restaurants') }}" class="mb-6 space-y-3">
    <div class="relative">
        <input type="text" 
               name="search" 
               value="{{ request('search') }}"
               placeholder="Search restaurants or locations..."
               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 pl-10 pr-10 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
        
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" 
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>

        @if (request('search'))
            <a href="{{ route('customer.restaurants', ['cuisine' => request('cuisine')]) }}"
               class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        @endif
    </div>

    {{-- CUISINE CHIPS --}}
    @if (isset($cuisines) && $cuisines->count() > 0)
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('customer.restaurants', ['search' => request('search')]) }}"
               class="text-xs px-3 py-1.5 rounded-full border transition {{ !request('cuisine') ? 'bg-orange-600 text-white border-orange-600' : 'bg-white text-gray-600 border-gray-300 hover:border-gray-400' }}">
                All
            </a>
            @foreach ($cuisines as $cuisine)
                <a href="{{ route('customer.restaurants', ['search' => request('search'), 'cuisine' => $cuisine]) }}"
                   class="text-xs px-3 py-1.5 rounded-full border transition {{ request('cuisine') === $cuisine ? 'bg-orange-600 text-white border-orange-600' : 'bg-white text-gray-600 border-gray-300 hover:border-gray-400' }}">
                    {{ $cuisine }}
                </a>
            @endforeach
        </div>
    @endif

    @if (request('search') || request('cuisine'))
        <p class="text-xs text-gray-500">
            Showing 
            @if (request('search'))
                results for "<strong>{{ request('search') }}</strong>"
            @endif
            @if (request('cuisine'))
                in <strong>{{ request('cuisine') }}</strong>
            @endif
            ({{ $restaurants->count() }} {{ Str::plural('result', $restaurants->count()) }})
        </p>
    @endif
</form>

{{-- RESTAURANTS LIST --}}
@if ($restaurants->isEmpty())
    <div class="bg-white rounded-lg border border-gray-200 p-16 text-center">
        <p class="text-gray-500 mb-4">
            @if (request('search') || request('cuisine'))
                No restaurants found.
            @else
                No restaurants available.
            @endif
        </p>
        @if (request('search') || request('cuisine'))
            <a href="{{ route('customer.restaurants') }}"
               class="inline-block text-sm text-orange-600 hover:underline">
                Clear filters
            </a>
        @endif
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($restaurants as $restaurant)
            <a href="{{ route('customer.restaurants.show', $restaurant) }}"
               class="group bg-white rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-sm transition overflow-hidden">
                <div class="h-28 bg-gray-100 border-b border-gray-200"></div>

                <div class="p-5">
                    <div class="flex items-start justify-between mb-1">
                        <h2 class="font-semibold text-gray-900 group-hover:text-orange-600 transition">
                            {{ $restaurant->name }}
                        </h2>
                        <span class="text-xs text-gray-500 mt-1">{{ $restaurant->menu_items_count }} items</span>
                    </div>

                    @if ($restaurant->cuisine)
                        <p class="text-xs text-orange-600 mb-2">{{ $restaurant->cuisine }}</p>
                    @endif

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