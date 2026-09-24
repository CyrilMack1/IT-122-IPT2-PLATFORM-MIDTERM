<?php

namespace App\Http\Controllers;

use App\Models\Rider;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $creds = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($creds)) {
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }

        $user = Auth::user();

        // Check status BEFORE proceeding
        if ($user->status !== 'approved') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Your account is ' . $user->status . '. Please wait for admin approval.'
            ])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:customer,restaurant,rider',
            'phone' => 'nullable|string',
            'restaurant_name' => 'required_if:role,restaurant|string|max:255',
            'restaurant_address' => 'required_if:role,restaurant|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'status' => $data['role'] === 'customer' ? 'approved' : 'pending',
                'phone' => $data['phone'] ?? null,
            ]);

            if ($data['role'] === 'restaurant') {
                Restaurant::create([
    'user_id' => $user->id,
    'name' => $data['restaurant_name'],
    'address' => $data['restaurant_address'],
    'latitude' => $data['latitude'] ?? 8.4822,
    'longitude' => $data['longitude'] ?? 124.6472,
]);
            }

            if ($data['role'] === 'rider') {
                Rider::create([
                    'user_id' => $user->id,
                    'is_online' => false,
                    'is_available' => true,
                ]);
            }

            return $user;
        });

        // CUSTOMER → auto-login
        if ($user->status === 'approved') {
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        // RESTAURANT / RIDER → wait for admin approval
        return redirect()->route('login')
            ->with('success', 'Registration successful! Please wait for admin approval before you can log in.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}