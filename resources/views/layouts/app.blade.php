<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'FoodDash') }}</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#ff6b35">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">

    @auth
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center h-14">
                <div class="flex items-center gap-6">
                    <a href="{{ route('dashboard') }}" class="font-semibold text-lg text-gray-900">
                        FoodDash
                    </a>

                    @if (auth()->user()->isCustomer())
                        <div class="hidden md:flex items-center gap-1">
                            <a href="{{ route('customer.restaurants') }}"
                               class="px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('customer.restaurants*') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-600 hover:text-gray-900' }}">
                                Restaurants
                            </a>
                            <a href="{{ route('customer.orders') }}"
                               class="px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('customer.orders*') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-600 hover:text-gray-900' }}">
                                Orders
                            </a>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-700 hidden sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-sm text-gray-500 hover:text-gray-900">Logout</button>
                    </form>
                </div>
            </div>
        </div>

        @if (auth()->user()->isCustomer())
        <div class="md:hidden border-t border-gray-100">
            <div class="flex">
                <a href="{{ route('customer.restaurants') }}"
                   class="flex-1 text-center py-2.5 text-sm {{ request()->routeIs('customer.restaurants*') ? 'text-orange-600 font-medium' : 'text-gray-600' }}">
                    Restaurants
                </a>
                <a href="{{ route('customer.orders') }}"
                   class="flex-1 text-center py-2.5 text-sm {{ request()->routeIs('customer.orders*') ? 'text-orange-600 font-medium' : 'text-gray-600' }}">
                    Orders
                </a>
            </div>
        </div>
        @endif
    </nav>
    @endauth

    <main class="max-w-6xl mx-auto px-4 py-8">
        @if (session('success'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>