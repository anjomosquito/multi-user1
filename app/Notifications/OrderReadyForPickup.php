<?php



namespace App\Notifications;



use Illuminate\Bus\Queueable;

use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Notifications\Messages\MailMessage;

use Illuminate\Notifications\Notification;

use App\Models\Purchase;



class OrderReadyForPickup extends Notification implements ShouldQueue

{

    use Queueable;



    protected $purchase;



    public function __construct(Purchase $purchase)

    {

        $this->purchase = $purchase;

    }



    public function via($notifiable)

    {

        return ['mail'];

    }



    public function toMail($notifiable)

    {

        $total_amount = $this->purchase->mprice * $this->purchase->quantity;



        return (new MailMessage)

            ->subject('Your Order is Ready for Pickup')

            ->greeting('Hello ' . $this->purchase->user->name . '!')

            ->line('Your order #' . $this->purchase->id . ' is now ready for pickup.')

            ->line('')

            ->line('Order Details:')

            ->line('')

            ->line('Total Amount: ₱' . number_format($total_amount, 2))

            ->line('')

            ->line('Please proceed to our store to pick up your order.')

            ->line('')

            ->line('Store Address: NAVARRO STREET, SURIGAO CITY (near Crispy King beside Bridgestone)')

            ->line('')

            ->line('Operating Hours: 8AM-10PM DAILY')

            ->action('View Order Details', route('purchase.index'))

            ->line('Thank you for choosing Puremed Pharmacy!')

            ->line('')

            ->line('Regards')

            ->salutation('Puremed,');

    }

} 


