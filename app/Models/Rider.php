<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'is_online',
        'is_available',
        'vehicle_type',
        'vehicle_plate',
        'latitude',
        'longitude',
        'last_location_at',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'is_available' => 'boolean',
        'last_location_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function offers()
    {
        return $this->hasMany(DeliveryOffer::class);
    }
}