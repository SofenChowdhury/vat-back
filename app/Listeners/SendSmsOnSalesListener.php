<?php

namespace App\Listeners;

use App\Classes\Helper;
use App\Events\SendSMSOnSales;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSmsOnSalesListener implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(SendSMSOnSales $event)
    {
        $sales = $event->sales;
        $items = $event->salesItems;
        $sendSms = Helper::sendSms($sales, $items);

        

        Mail::to($event->order->company->contact_email)->send(new OrderPlacedCompany($event->order, $event->orderProducts));


        Mail::to($event->order->customer_email)->send(new EmailOrderPlaced($event->order, $event->orderProducts));
        if ($event->order->customer_email != $event->order->shipping_email ) {
            Mail::to($event->order->shipping_email)->send(new EmailOrderPlaced($event->order, $event->orderProducts));
        }        
        Mail::to(['foyshal.hossain@fel.com.bd', 'mahbub.bhuiyan@fel.com.bd', 'iftekhar.hossain@fdl.com.bd'])->send(new OrderPlacedB2b($event->order, $event->orderProducts));
    }
}
