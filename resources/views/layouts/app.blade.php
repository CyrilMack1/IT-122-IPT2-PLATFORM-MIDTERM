<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'FoodDash') }}</title>
    
    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#ff6b35">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="FoodDash">
    <link rel="apple-touch-icon" href="/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">

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

    {{-- PWA INSTALL PROMPT --}}
    <div x-data="pwaInstall()" x-init="init()" x-cloak>
        <template x-if="showInstall">
            <div class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-80 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-50">
                <div class="flex items-start gap-3">
                    <img src="/icon-192.png" alt="FoodDash" class="w-10 h-10 rounded">
                    <div class="flex-1">
                        <p class="font-semibold text-sm">Install FoodDash</p>
                        <p class="text-xs text-gray-500 mt-1">Add to your home screen for quick access.</p>
                    </div>
                    <button @click="dismiss()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-3 flex gap-2">
                    <button @click="install()" class="flex-1 bg-orange-600 text-white text-sm py-2 rounded hover:bg-orange-700">
                        Install
                    </button>
                    <button @click="dismiss()" class="flex-1 border text-sm py-2 rounded hover:bg-gray-50">
                        Not now
                    </button>
                </div>
            </div>
        </template>
    </div>

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

    {{-- SERVICE WORKER REGISTRATION --}}
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }
    </script>

    {{-- PWA INSTALL SCRIPT --}}
    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
    function pwaInstall() {
        return {
            showInstall: false,
            deferredPrompt: null,
            dismissed: false,

            init() {
                if (window.matchMedia('(display-mode: standalone)').matches) {
                    return;
                }

                if (localStorage.getItem('pwa-dismissed') === 'yes') {
                    return;
                }

                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.deferredPrompt = e;
                    this.showInstall = true;
                });

                window.addEventListener('appinstalled', () => {
                    this.showInstall = false;
                    this.deferredPrompt = null;
                });
            },

            async install() {
                if (!this.deferredPrompt) return;

                this.deferredPrompt.prompt();
                const { outcome } = await this.deferredPrompt.userChoice;

                if (outcome === 'accepted') {
                    this.showInstall = false;
                }

                this.deferredPrompt = null;
            },

            dismiss() {
                this.showInstall = false;
                localStorage.setItem('pwa-dismissed', 'yes');
            }
        }
    }
    </script>
</body>
</html>