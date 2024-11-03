<?php

namespace App\Jobs;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StockBulkUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $column;
    public $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($column, $data)
    {
        $this->column = $column;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->data as $item) {
            unset($item[0]);
            $item_csv_data = array_combine($this->column, $item);
            try {
                Customer::create($item_csv_data);
            } catch (\Throwable $th) {
                return $th->getMessage();
            }
            
        }
    }
}
