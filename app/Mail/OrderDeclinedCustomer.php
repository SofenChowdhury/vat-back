<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderDeclinedCustomer extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $order;
    protected $items;
    public function __construct($order, $items)
    {
        $this->order = $order;
        $this->items = $items;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $order = $this->order;
        $items = $this->items;
        return $this->subject('Order declined - '. $order->order_number)
        ->view('mail.admin.OrderDeclinedCustomer', compact('order', 'items'));
    }
}