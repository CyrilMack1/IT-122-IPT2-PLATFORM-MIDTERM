<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Rider;
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

        // Summary stats
        $stats = [
            'total_orders' => Order::count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'total_sales' => Order::where('status', 'delivered')->sum('food_cost'),
            'active_restaurants' => Restaurant::where('is_open', true)->count(),
            'online_riders' => Rider::where('is_online', true)->count(),
        ];

        return view('admin.dashboard', compact('pendingRestaurants', 'pendingRiders', 'recentOrders', 'stats'));
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

    public function orders(Request $request)
    {
        $query = Order::with(['customer', 'restaurant', 'rider']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->filled('rider_id')) {
            $query->where('rider_id', $request->rider_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $orders = $query->latest()->paginate(30)->withQueryString();

        $restaurants = Restaurant::orderBy('name')->get(['id', 'name']);
        $riders = Rider::with('user')->get();

        return view('admin.orders', compact('orders', 'restaurants', 'riders'));
    }

    public function exportOrders(Request $request)
    {
        $query = Order::with(['customer', 'restaurant', 'rider']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }
        if ($request->filled('rider_id')) {
            $query->where('rider_id', $request->rider_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $orders = $query->latest()->get();

        $filename = 'orders_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order ID', 'Date', 'Customer', 'Restaurant', 'Rider', 'Status', 'Food Cost', 'Delivery Fee', 'Total', 'Address']);

            foreach ($orders as $o) {
                fputcsv($file, [
                    $o->id,
                    $o->created_at->format('Y-m-d H:i'),
                    $o->customer->name ?? '-',
                    $o->restaurant->name ?? '-',
                    $o->rider->user->name ?? 'Unassigned',
                    $o->status,
                    $o->food_cost,
                    $o->delivery_fee,
                    $o->total_amount,
                    $o->delivery_address,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function reports(Request $request)
    {
        $from = $request->filled('from') ? $request->from : now()->subDays(30)->format('Y-m-d');
        $to = $request->filled('to') ? $request->to : now()->format('Y-m-d');

        // Sales report
        $salesQuery = Order::where('status', 'delivered')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        $salesStats = [
            'total_orders' => (clone $salesQuery)->count(),
            'total_food_cost' => (clone $salesQuery)->sum('food_cost'),
            'total_delivery_fees' => (clone $salesQuery)->sum('delivery_fee'),
            'total_revenue' => (clone $salesQuery)->sum('total_amount'),
        ];

        // Top restaurants
        $topRestaurants = Order::where('orders.status', 'delivered')
            ->whereDate('orders.created_at', '>=', $from)
            ->whereDate('orders.created_at', '<=', $to)
            ->join('restaurants', 'orders.restaurant_id', '=', 'restaurants.id')
            ->select(
                'restaurants.name',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.food_cost) as total_sales')
            )
            ->groupBy('restaurants.name')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();

        // Top riders
        $topRiders = Order::where('orders.status', 'delivered')
            ->whereDate('orders.created_at', '>=', $from)
            ->whereDate('orders.created_at', '<=', $to)
            ->join('riders', 'orders.rider_id', '=', 'riders.id')
            ->join('users', 'riders.user_id', '=', 'users.id')
            ->select(
                'users.name',
                DB::raw('COUNT(orders.id) as total_deliveries'),
                DB::raw('SUM(orders.delivery_fee) as total_earnings')
            )
            ->groupBy('users.name')
            ->orderByDesc('total_deliveries')
            ->limit(10)
            ->get();

        // Daily sales
        $dailySales = Order::where('status', 'delivered')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderByDesc('date')
            ->get();

        return view('admin.reports', compact(
            'from', 'to', 'salesStats', 'topRestaurants', 'topRiders', 'dailySales'
        ));
    }

    public function exportReports(Request $request)
    {
        $from = $request->filled('from') ? $request->from : now()->subDays(30)->format('Y-m-d');
        $to = $request->filled('to') ? $request->to : now()->format('Y-m-d');

        $dailySales = Order::where('status', 'delivered')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderByDesc('date')
            ->get();

        $filename = 'reports_' . $from . '_to_' . $to . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($dailySales) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Orders', 'Total Sales']);

            foreach ($dailySales as $d) {
                fputcsv($file, [$d->date, $d->count, $d->total]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

        $hasActiveOrders = $user->orders()
            ->whereNotIn('status', ['delivered', 'cancelled', 'rejected', 'no_rider'])
            ->exists();

        if ($hasActiveOrders) {
            return back()->with('error', "Cannot delete {$user->name} — may active order(s).");
        }

        DB::transaction(function () use ($user) {
            if ($user->restaurant) {
                $user->restaurant->menuItems()->delete();
            }
            $user->delete();
        });

        return back()->with('success', "{$user->name} has been deleted permanently.");
    }
}