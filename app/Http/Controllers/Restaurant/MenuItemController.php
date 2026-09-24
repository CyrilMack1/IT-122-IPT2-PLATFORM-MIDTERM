<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function index()
    {
        $restaurant = auth()->user()->restaurant;
        $items = MenuItem::where('restaurant_id', $restaurant->id)->latest()->get();

        return view('restaurant.menu', compact('items'));
    }

    public function store(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('menu-items', 'public');
        }

        unset($data['image']);
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
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($menuItem->image_path) {
                Storage::disk('public')->delete($menuItem->image_path);
            }
            $data['image_path'] = $request->file('image')->store('menu-items', 'public');
        }

        unset($data['image']);
        $menuItem->update($data);

        return back()->with('success', 'Menu item updated.');
    }

    public function toggleAvailability(MenuItem $menuItem)
    {
        $restaurant = auth()->user()->restaurant;
        abort_unless($menuItem->restaurant_id === $restaurant->id, 403);

        $menuItem->update(['is_available' => !$menuItem->is_available]);

        return back()->with('success', $menuItem->is_available
            ? 'Item marked as available.'
            : 'Item marked as unavailable.');
    }

    public function destroy(MenuItem $menuItem)
    {
        $restaurant = auth()->user()->restaurant;
        abort_unless($menuItem->restaurant_id === $restaurant->id, 403);

        if ($menuItem->image_path) {
            Storage::disk('public')->delete($menuItem->image_path);
        }

        $menuItem->delete();

        return back()->with('success', 'Menu item deleted.');
    }
}