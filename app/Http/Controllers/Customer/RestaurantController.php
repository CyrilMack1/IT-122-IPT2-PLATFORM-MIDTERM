<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;

class RestaurantController extends Controller
{
    public function index()
    {
        $restaurants = Restaurant::where('is_open', true)
            ->withCount('menuItems')
            ->get();

        return view('customer.restaurants', compact('restaurants'));
    }

    public function show(Restaurant $restaurant)
    {
        $restaurant->load(['menuItems' => function ($q) {
            $q->where('is_available', true);
        }]);

        return view('customer.restaurant-show', compact('restaurant'));
    }
}