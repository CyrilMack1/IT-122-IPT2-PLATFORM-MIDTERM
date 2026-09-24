<?php

use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Restaurant\OrderController as RestaurantOrderController;
use App\Http\Controllers\Restaurant\MenuItemController;
use App\Http\Controllers\Rider\OrderController as RiderOrderController;
use App\Http\Controllers\Rider\AvailabilityController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ConfigController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// ============================================
// CUSTOMER ROUTES
// ============================================
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/restaurants', [\App\Http\Controllers\Customer\RestaurantController::class, 'index'])->name('customer.restaurants');
    Route::get('/restaurants/{restaurant}', [\App\Http\Controllers\Customer\RestaurantController::class, 'show'])->name('customer.restaurants.show');

    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('customer.orders');
    Route::post('/orders', [CustomerOrderController::class, 'store'])->name('customer.orders.store');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');
    Route::post('/orders/{order}/rate', [CustomerOrderController::class, 'rate'])->name('customer.orders.rate');
});

// ============================================
// RESTAURANT ROUTES
// ============================================
Route::middleware(['auth', 'role:restaurant'])->prefix('restaurant')->group(function () {
    Route::get('/dashboard', [RestaurantOrderController::class, 'dashboard'])->name('restaurant.dashboard');
    Route::post('/orders/{order}/confirm', [RestaurantOrderController::class, 'confirm'])->name('restaurant.orders.confirm');
    Route::post('/orders/{order}/reject', [RestaurantOrderController::class, 'reject'])->name('restaurant.orders.reject');
    Route::post('/orders/{order}/ready', [RestaurantOrderController::class, 'ready'])->name('restaurant.orders.ready');
    Route::post('/orders/external', [RestaurantOrderController::class, 'storeExternal'])->name('restaurant.orders.external');

    Route::resource('menu-items', MenuItemController::class);
});

// ============================================
// RIDER ROUTES
// ============================================
Route::middleware(['auth', 'role:rider'])->prefix('rider')->group(function () {
    Route::get('/dashboard', [AvailabilityController::class, 'dashboard'])->name('rider.dashboard');
    Route::post('/online', [AvailabilityController::class, 'toggleOnline'])->name('rider.online');
    Route::post('/location', [AvailabilityController::class, 'updateLocation'])->name('rider.location');

    Route::post('/orders/{order}/accept', [RiderOrderController::class, 'accept'])->name('rider.orders.accept');
    Route::patch('/orders/{order}/status', [RiderOrderController::class, 'updateStatus'])->name('rider.orders.status');
    Route::post('/orders/{order}/payment', [RiderOrderController::class, 'recordPayment'])->name('rider.orders.payment');
});

// ============================================
// ADMIN ROUTES
// ============================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AccountController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/accounts', [AccountController::class, 'accounts'])->name('admin.accounts');
    Route::patch('/users/{user}/approve', [AccountController::class, 'approve'])->name('admin.users.approve');
    Route::patch('/users/{user}/disable', [AccountController::class, 'disable'])->name('admin.users.disable');
    Route::delete('/users/{user}', [AccountController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/config', [ConfigController::class, 'update'])->name('admin.config.update');
});

// ============================================
// DASHBOARD REDIRECT
// ============================================
Route::get('/dashboard', function () {
    $user = auth()->user();
    return match ($user->role) {
        'customer' => redirect()->route('customer.orders'),
        'restaurant' => redirect()->route('restaurant.dashboard'),
        'rider' => redirect()->route('rider.dashboard'),
        'admin' => redirect()->route('admin.dashboard'),
        default => redirect('/'),
    };
})->middleware('auth')->name('dashboard');

require __DIR__.'/auth.php';