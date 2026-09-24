<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RiderAssigned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("order.{$this->order->id}")];
    }

    public function broadcastAs(): string
    {
        return 'rider.assigned';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'rider_id' => $this->order->rider_id,
            'status' => $this->order->status,
        ];
    }
}