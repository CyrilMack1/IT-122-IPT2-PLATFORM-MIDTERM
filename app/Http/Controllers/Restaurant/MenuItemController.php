<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index()
    {
        $restaurant = auth()->user()->restaurant;
        $items = MenuItem::where('restaurant_id', $restaurant->id)->get();

        return view('restaurant.menu', compact('items'));
    }

    public function store(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        $restaurant->menuItems()->create($data);

        return back()->with('success', 'Menu item added.');
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $restaurant = auth()->user()->restaurant;
        abort_unless($menuItem->restaurant_id === $restaurant->id, 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_available' => 'boolean',
        ]);

        $menuItem->update($data);

        return back()->with('success', 'Menu item updated.');
    }

    public function destroy(MenuItem $menuItem)
    {
        $restaurant = auth()->user()->restaurant;
        abort_unless($menuItem->restaurant_id === $restaurant->id, 403);

        $menuItem->delete();

        return back()->with('success', 'Menu item deleted.');
    }
}