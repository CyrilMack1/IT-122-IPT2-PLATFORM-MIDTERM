<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'restaurant_id',
        'rider_id',
        'status',
        'food_cost',
        'delivery_fee',
        'total_amount',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'is_external_order',
        'restaurant_rating',
        'rider_rating',
    ];

    protected $casts = [
        'food_cost' => 'float',
        'delivery_fee' => 'float',
        'total_amount' => 'float',
        'delivery_lat' => 'float',
        'delivery_lng' => 'float',
        'is_external_order' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function offers()
    {
        return $this->hasMany(DeliveryOffer::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}