<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $labels = [
            'confirmed' => 'Order Confirmed',
            'preparing' => 'Preparing Your Food',
            'finding_rider' => 'Finding a Rider',
            'rider_assigned' => 'Rider Assigned',
            'picked_up' => 'Order Picked Up',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Order Delivered',
            'cancelled' => 'Order Cancelled',
            'rejected' => 'Order Rejected',
            'no_rider' => 'No Rider Available',
        ];

        $label = $labels[$this->order->status] ?? ucfirst(str_replace('_', ' ', $this->order->status));

        $mail = (new MailMessage)
            ->subject("Order #{$this->order->id} — {$label}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Your order from **{$this->order->restaurant->name}** is now: **{$label}**");

        if ($this->order->status === 'rejected' && $this->order->rejection_reason) {
            $mail->line("Reason: {$this->order->rejection_reason}");
        }

        if ($this->order->status === 'rider_assigned' && $this->order->rider) {
            $mail->line("Rider: **{$this->order->rider->user->name}**");
        }

        $mail->action('Track Order', url("/orders/{$this->order->id}"));

        if ($this->order->status === 'delivered') {
            $mail->line('Thank you for ordering with FoodDash!');
        }

        return $mail;
    }
}