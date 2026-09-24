<?php

namespace App\Events;

use App\Models\Order;
use App\Models\Rider;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryOfferSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public Rider $rider,
        public int $radiusKm,
        public int $timeoutSec,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("rider.{$this->rider->id}")];
    }

    public function broadcastAs(): string
    {
        return 'delivery.offer';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'radius_km' => $this->radiusKm,
            'expires_in' => $this->timeoutSec,
            'restaurant' => $this->order->restaurant->name,
            'food_cost' => $this->order->food_cost,
            'delivery_fee' => $this->order->delivery_fee,
            'total_amount' => $this->order->total_amount,
            'delivery_address' => $this->order->delivery_address,
        ];
    }
}