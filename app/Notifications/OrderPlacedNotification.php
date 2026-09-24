<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Order #{$this->order->id} Placed — FoodDash")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Your order from **{$this->order->restaurant->name}** has been placed.")
            ->line("**Order Details:**")
            ->line("• Items: " . $this->order->items->count())
            ->line("• Food Cost: ₱" . number_format($this->order->food_cost, 2))
            ->line("• Delivery Fee: ₱" . number_format($this->order->delivery_fee, 2))
            ->line("• **Total: ₱" . number_format($this->order->total_amount, 2) . "**")
            ->line("**Delivery Address:**")
            ->line($this->order->delivery_address)
            ->action('Track Order', url("/orders/{$this->order->id}"))
            ->line('We will notify you once the restaurant confirms your order.');
    }
}