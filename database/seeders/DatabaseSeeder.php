<?php

namespace Database\Seeders;

use App\Models\Rider;
use App\Models\Restaurant;
use App\Models\SystemConfig;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // ADMIN ACCOUNT
        // ============================================
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@fooddash.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'approved',
            'phone' => '09000000000',
        ]);

        // ============================================
        // DEMO CUSTOMER
        // ============================================
        User::create([
            'name' => 'Maria Customer',
            'email' => 'customer@fooddash.test',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'status' => 'approved',
            'phone' => '09111111111',
        ]);

        // ============================================
        // DEMO RESTAURANT
        // ============================================
        $restaurantUser = User::create([
            'name' => 'PizzaKid Owner',
            'email' => 'restaurant@fooddash.test',
            'password' => Hash::make('password'),
            'role' => 'restaurant',
            'status' => 'approved',
            'phone' => '09222222222',
        ]);

        $restaurant = Restaurant::create([
    'user_id' => $restaurantUser->id,
    'name' => 'PizzaKid',
    'address' => 'Cagayan de Oro City',
    'latitude' => 8.4822,
    'longitude' => 124.6472,
    'is_open' => true,
]);

        // Menu items
        $restaurant->menuItems()->createMany([
            ['name' => 'Pepperoni Pizza', 'description' => 'Classic', 'price' => 400, 'is_available' => true],
            ['name' => 'Coke 1.5L', 'description' => 'Softdrink', 'price' => 80, 'is_available' => true],
            ['name' => 'Garlic Bread', 'description' => '6 pcs', 'price' => 120, 'is_available' => true],
        ]);

        // ============================================
        // DEMO RIDER
        // ============================================
        $riderUser = User::create([
            'name' => 'Ben Rider',
            'email' => 'rider@fooddash.test',
            'password' => Hash::make('password'),
            'role' => 'rider',
            'status' => 'approved',
            'phone' => '09333333333',
        ]);

        Rider::create([
    'user_id' => $riderUser->id,
    'is_online' => false,
    'is_available' => true,
    'latitude' => 8.4822,
    'longitude' => 124.6472,
]);

        // ============================================
        // SYSTEM CONFIG
        // ============================================
        SystemConfig::create([
    'town_center_lat' => 8.4822,
    'town_center_lng' => 124.6472,
    'service_radius_km' => 10,
    'default_delivery_fee' => 60,
]);
    }
}