<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class PurchaseNotification extends Notification
{
    use Queueable;

    private $purchase;
    private $type;

    public function __construct($purchase, $type)
    {
        $this->purchase = $purchase;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $total = $this->purchase->mprice * $this->purchase->quantity;
        
        $message = (new MailMessage)
            ->greeting('Hello ' . $this->purchase->user->name . '!');

        switch($this->type) {
            case 'confirmed':
                return $message
                    ->subject('Purchase Confirmed - PuremedPharmacy')
                    ->line('Your purchase has been confirmed.')
                    ->line(new HtmlString('<strong>Order Details:</strong>'))
                    ->line('Medicine: ' . $this->purchase->name)
                    ->line('Quantity: ' . $this->purchase->quantity)
                    ->line('Total Amount: ₱' . number_format($total, 2))
                    ->action('View Purchase', url('/purchase'))
                    ->line('Thank you for choosing PuremedPharmacy!');

            case 'ready':
                return $message
                    ->subject('Order Ready for Pickup - PuremedPharmacy')
                    ->line('Your order is ready for pickup!')
                    ->line(new HtmlString('<strong>Order Details:</strong>'))
                    ->line('Medicine: ' . $this->purchase->name)
                    ->line('Quantity: ' . $this->purchase->quantity)
                    ->line('Total Amount: ₱' . number_format($total, 2))
                    ->line('Pickup Before: ' . $this->purchase->pickup_deadline)
                    ->line(new HtmlString('<strong>Store Address:</strong>'))
                    ->line('NAVARRO STREET, SURIGAO CITY (near Crispy King beside Bridgestone)')
                    ->line('Operating Hours: 8AM-10PM DAILY')
                    ->action('View Purchase', url('/purchase'))
                    ->line('Please bring a valid ID when picking up your medicine.');

            case 'completed':
                return $message
                    ->subject('Purchase Completed - PuremedPharmacy')
                    ->line('Your purchase has been completed.')
                    ->line('Medicine: ' . $this->purchase->name)
                    ->line('Total Amount: ₱' . number_format($total, 2))
                    ->line('Thank you for choosing PuremedPharmacy!')
                    ->line('We hope to serve you again soon!')
                    ->action('View Purchase History', url('/purchase'));
        }
    }
} 