<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email — FoodDash</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-orange-600 mb-6 text-center">FoodDash</h1>
        <h2 class="text-lg font-semibold mb-3">Verify your email</h2>
        <p class="text-sm text-gray-500 mb-4">
            We sent a verification link to your email. Please check your inbox and click the link.
        </p>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded text-sm">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded text-sm">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="space-y-3">
            @csrf
            <button type="submit" class="w-full bg-orange-600 text-white py-2 rounded hover:bg-orange-700">
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button class="w-full text-sm text-gray-500 hover:text-gray-700">Logout</button>
        </form>
    </div>
</body>
</html>