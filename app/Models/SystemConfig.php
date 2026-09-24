<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'town_center_lat',
        'town_center_lng',
        'service_radius_km',
        'default_delivery_fee',
    ];

    protected $casts = [
        'town_center_lat' => 'float',
        'town_center_lng' => 'float',
        'service_radius_km' => 'float',
        'default_delivery_fee' => 'float',
    ];

    public static function current(): self
{
    return static::first() ?? static::create([
        'town_center_lat' => 8.4822,
        'town_center_lng' => 124.6472,
        'service_radius_km' => 10,
        'default_delivery_fee' => 60,
    ]);
}
}
