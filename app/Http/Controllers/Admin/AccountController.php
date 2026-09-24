<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function dashboard()
    {
        $pendingRestaurants = User::where('role', 'restaurant')
            ->where('status', 'pending')
            ->get();

        $pendingRiders = User::where('role', 'rider')
            ->where('status', 'pending')
            ->get();

        $recentOrders = Order::with(['customer', 'restaurant', 'rider'])
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.dashboard', compact('pendingRestaurants', 'pendingRiders', 'recentOrders'));
    }

    public function accounts(Request $request)
    {
        $filter = $request->query('filter', 'all');

        $query = User::query();

        switch ($filter) {
            case 'customer':
                $query->where('role', 'customer');
                break;
            case 'restaurant':
                $query->where('role', 'restaurant');
                break;
            case 'rider':
                $query->where('role', 'rider');
                break;
            case 'pending':
                $query->where('status', 'pending');
                break;
            case 'disabled':
                $query->where('status', 'disabled');
                break;
            // 'all' → no filter
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        $counts = [
            'all' => User::count(),
            'customer' => User::where('role', 'customer')->count(),
            'restaurant' => User::where('role', 'restaurant')->count(),
            'rider' => User::where('role', 'rider')->count(),
            'pending' => User::where('status', 'pending')->count(),
            'disabled' => User::where('status', 'disabled')->count(),
        ];

        return view('admin.accounts', compact('users', 'filter', 'counts'));
    }

    public function approve(User $user)
    {
        $user->update(['status' => 'approved']);

        return back()->with('success', "{$user->name} has been approved.");
    }

    public function disable(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Cannot disable an admin account.');
        }

        $user->update(['status' => 'disabled']);

        return back()->with('success', "{$user->name} has been disabled.");
    }

    public function destroy(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Cannot delete an admin account.');
        }

        // Optional: prevent deleting user with active orders
        $hasActiveOrders = $user->orders()
            ->whereNotIn('status', ['delivered', 'cancelled', 'rejected', 'no_rider'])
            ->exists();

        if ($hasActiveOrders) {
            return back()->with('error', "Cannot delete {$user->name} — may active order(s) na hindi pa tapos.");
        }

        DB::transaction(function () use ($user) {
            // I-delete ang related records
            if ($user->restaurant) {
                // I-delete ang menu items at orders (cascade via foreign keys)
                $user->restaurant->menuItems()->delete();
            }

            $user->delete(); // cascade delete rider/restaurant via foreign key
        });

        return back()->with('success', "{$user->name} has been deleted permanently.");
    }
}