<?php

use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('rider.{riderId}', function ($user, $riderId) {
    return $user->rider && (int) $user->rider->id === (int) $riderId;
});

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);
    if (!$order) {
        return false;
    }

    if ($user->id === $order->customer_id) {
        return true;
    }

    if ($user->restaurant && $user->restaurant->id === $order->restaurant_id) {
        return true;
    }

    if ($user->rider && $user->rider->id === $order->rider_id) {
        return true;
    }

    if ($user->isAdmin()) {
        return true;
    }

    return false;
});