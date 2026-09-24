@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Manage Accounts</h1>
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-orange-600 hover:underline">← Back to Dashboard</a>
</div>

@if (session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
@endif

{{-- ============================================ --}}
{{-- FILTER TABS --}}
{{-- ============================================ --}}
<div class="flex gap-2 mb-4 overflow-x-auto">
    <a href="{{ route('admin.accounts', ['filter' => 'all']) }}"
       class="px-4 py-2 rounded text-sm whitespace-nowrap {{ $filter === 'all' ? 'bg-orange-600 text-white' : 'bg-white border hover:bg-gray-50' }}">
        All ({{ $counts['all'] }})
    </a>
    <a href="{{ route('admin.accounts', ['filter' => 'customer']) }}"
       class="px-4 py-2 rounded text-sm whitespace-nowrap {{ $filter === 'customer' ? 'bg-orange-600 text-white' : 'bg-white border hover:bg-gray-50' }}">
        Customers ({{ $counts['customer'] }})
    </a>
    <a href="{{ route('admin.accounts', ['filter' => 'restaurant']) }}"
       class="px-4 py-2 rounded text-sm whitespace-nowrap {{ $filter === 'restaurant' ? 'bg-orange-600 text-white' : 'bg-white border hover:bg-gray-50' }}">
        Restaurants ({{ $counts['restaurant'] }})
    </a>
    <a href="{{ route('admin.accounts', ['filter' => 'rider']) }}"
       class="px-4 py-2 rounded text-sm whitespace-nowrap {{ $filter === 'rider' ? 'bg-orange-600 text-white' : 'bg-white border hover:bg-gray-50' }}">
        Riders ({{ $counts['rider'] }})
    </a>
    <a href="{{ route('admin.accounts', ['filter' => 'pending']) }}"
       class="px-4 py-2 rounded text-sm whitespace-nowrap {{ $filter === 'pending' ? 'bg-orange-600 text-white' : 'bg-white border hover:bg-gray-50' }}">
        Pending ({{ $counts['pending'] }})
    </a>
    <a href="{{ route('admin.accounts', ['filter' => 'disabled']) }}"
       class="px-4 py-2 rounded text-sm whitespace-nowrap {{ $filter === 'disabled' ? 'bg-orange-600 text-white' : 'bg-white border hover:bg-gray-50' }}">
        Disabled ({{ $counts['disabled'] }})
    </a>
</div>

{{-- ============================================ --}}
{{-- ACCOUNTS TABLE --}}
{{-- ============================================ --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-4 py-3 font-medium">Name</th>
                <th class="text-left px-4 py-3 font-medium">Email</th>
                <th class="text-left px-4 py-3 font-medium">Role</th>
                <th class="text-left px-4 py-3 font-medium">Status</th>
                <th class="text-left px-4 py-3 font-medium">Registered</th>
                <th class="text-right px-4 py-3 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr class="border-b last:border-0 hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $user->name }}</div>
                        @if ($user->phone)
                            <div class="text-xs text-gray-500">{{ $user->phone }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-1 rounded-full
                            @if($user->role === 'admin') bg-purple-100 text-purple-700
                            @elseif($user->role === 'restaurant') bg-blue-100 text-blue-700
                            @elseif($user->role === 'rider') bg-green-100 text-green-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-1 rounded-full
                            @if($user->status === 'approved') bg-green-100 text-green-700
                            @elseif($user->status === 'pending') bg-yellow-100 text-yellow-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ ucfirst($user->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $user->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex gap-2 justify-end">
                            @if ($user->status !== 'approved')
                                <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="text-green-600 text-xs hover:underline font-medium">Approve</button>
                                </form>
                            @endif

                            @if ($user->status !== 'disabled' && $user->role !== 'admin')
                                <form method="POST" action="{{ route('admin.users.disable', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="text-yellow-600 text-xs hover:underline font-medium"
                                            onclick="return confirm('Disable {{ $user->name }}? They won\'t be able to log in.')">
                                        Disable
                                    </button>
                                </form>
                            @endif

                            @if ($user->role !== 'admin')
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 text-xs hover:underline font-medium"
                                            onclick="return confirm('Delete {{ $user->name }} permanently? This cannot be undone.')">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        No accounts found for this filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="mt-4">
    {{ $users->links() }}
</div>
@endsection