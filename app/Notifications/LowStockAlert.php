<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class LowStockAlert extends Notification
{
    use Queueable;

    /**
     * @param Collection $products Products at or below reorder_level.
     */
    public function __construct(private Collection $products)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Low stock alert — {$this->products->count()} product(s) need reordering")
            ->greeting("Hi {$notifiable->name},")
            ->line('The following products have hit or dropped below their reorder level:');

        foreach ($this->products as $product) {
            $mail->line("- {$product->name} (SKU {$product->sku}): {$product->stock_quantity} left, reorder at {$product->reorder_level}");
        }

        return $mail->line('Please arrange restocking with the relevant supplier.');
    }
}