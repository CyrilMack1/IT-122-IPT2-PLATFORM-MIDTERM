<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemConfig;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'town_center_lat' => 'required|numeric',
            'town_center_lng' => 'required|numeric',
            'service_radius_km' => 'required|numeric|min:1',
            'default_delivery_fee' => 'required|numeric|min:0',
        ]);

        $config = SystemConfig::current();
        $config->update($data);

        return back()->with('success', 'System configuration updated.');
    }
}