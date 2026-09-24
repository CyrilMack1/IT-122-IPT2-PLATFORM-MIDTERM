<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoRiderNotification extends Notification
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
            ->subject("Order #{$this->order->id} — No Rider Available")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Unfortunately, no rider is available for your order #{$this->order->id}.")
            ->line("The restaurant has been notified. You can wait or cancel the order.")
            ->action('View Order', url("/orders/{$this->order->id}"))
            ->line('We apologize for the inconvenience.');
    }
}