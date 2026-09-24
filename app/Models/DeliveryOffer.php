<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'rider_id',
        'radius_km',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'radius_km' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }
}