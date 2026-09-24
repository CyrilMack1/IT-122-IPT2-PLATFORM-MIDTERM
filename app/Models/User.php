<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function restaurant()
    {
        return $this->hasOne(Restaurant::class);
    }

    public function rider()
    {
        return $this->hasOne(Rider::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isRestaurant(): bool
    {
        return $this->role === 'restaurant';
    }

    public function isRider(): bool
    {
        return $this->role === 'rider';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}