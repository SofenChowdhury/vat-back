<?php

namespace App\Jobs;

use App\Classes\Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $sales;
    protected $totalSalesValue;

    public function __construct($sales, $totalSalesValue)
    {
        $this->sales = $sales;
        $this->totalSalesValue = $totalSalesValue;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $customer_mobile = $this->sales->customer_phone;

        $smsContent = "Dear Partner, your order number[". $this->sales->sales_no."], amount: ".$this->totalSalesValue." has been dispatched.
        Driver: ".$this->sales->driver_name."-".$this->sales->driver_mobile."
        Vehicle No: ". $this->sales->vehicle_no;
        Helper::sendSms($customer_mobile, $smsContent, $this->sales->sales_no);
    }
}
