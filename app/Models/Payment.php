<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'rider_paid_restaurant',
        'rider_collected_customer',
        'recorded_at',
    ];

    protected $casts = [
        'rider_paid_restaurant' => 'float',
        'rider_collected_customer' => 'float',
        'recorded_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}