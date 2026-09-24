<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderForRestaurantNotification extends Notification
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
            ->subject("New Order #{$this->order->id} — FoodDash")
            ->greeting("New order received!")
            ->line("You have a new order from **{$this->order->customer->name}**.")
            ->line("**Order Details:**")
            ->line("• Items: " . $this->order->items->count())
            ->line("• Total: ₱" . number_format($this->order->total_amount, 2))
            ->line("**Delivery Address:**")
            ->line($this->order->delivery_address)
            ->action('View Order', url("/restaurant/dashboard"))
            ->line('Please confirm or reject this order as soon as possible.');
    }
}