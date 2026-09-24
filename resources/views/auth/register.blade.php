<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — FoodDash</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-10">
    <div class="w-full max-w-md bg-white rounded-lg shadow p-6" x-data="{ role: 'customer' }">
        <h1 class="text-2xl font-bold text-orange-600 mb-6 text-center">🍕 FoodDash</h1>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Account Type</label>
                <select name="role" x-model="role" required
                        class="w-full border rounded px-3 py-2">
                    <option value="customer">Customer</option>
                    <option value="restaurant">Restaurant</option>
                    <option value="rider">Rider</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       class="w-full border rounded px-3 py-2">
            </div>

            <template x-if="role === 'restaurant'">
                <div class="space-y-4 p-3 bg-orange-50 rounded">
                    <div>
                        <label class="block text-sm font-medium mb-1">Restaurant Name</label>
                        <input type="text" name="restaurant_name" value="{{ old('restaurant_name') }}"
                               class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Restaurant Address</label>
                        <input type="text" name="restaurant_address" value="{{ old('restaurant_address') }}"
                               class="w-full border rounded px-3 py-2">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs">Latitude</label>
                            <input type="text" name="latitude" value="{{ old('latitude') }}"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs">Longitude</label>
                            <input type="text" name="longitude" value="{{ old('longitude') }}"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                </div>
            </template>

            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" required
                       class="w-full border rounded px-3 py-2">
            </div>

            <button type="submit"
                    class="w-full bg-orange-600 text-white py-2 rounded hover:bg-orange-700">
                Register
            </button>

            <p class="text-center text-sm text-gray-600">
                Already have an account?
                <a href="{{ route('login') }}" class="text-orange-600 hover:underline">Login</a>
            </p>
        </form>
    </div>

    <script src="//unpkg.com/alpinejs" defer></script>
</body>
</html>