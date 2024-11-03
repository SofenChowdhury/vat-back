<?php 

namespace App\Classes;

use App\Models\Sales;
use App\Models\Transfer;
use App\Models\ItemStock;
use App\Models\MushokSix;
use App\Models\SalesReturn;
use Illuminate\Support\Str;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class Helper {
    public static $cod = 'COD';
    public static $ssl = 'SSLCOMMERZ'; 
    public static $bkash = 'BKASH';
    public static $nagad = 'NAGAD';
    public static $sslEmi = 'SSLCOMMERZ-EMI';
    public static $tap = 'TAP';
    public static $bank = 'bank';

    public static $prePaid = 'PRE-PAID';
    public static $credit = 'CREDIT';
    public static $debit = 'DEBIT';
    public static $paymentStatusPending="Pending";
    public static $orderType="regular";

    public static $completed = 'Completed';
    public static $pending   = 'Pending';
    public static $canceled =  'Canceled';
    public static $processing = 'Processing';
    public static $failed = 'Failed';
    public static $requested = 'Requested';
    public static $initiated = 'Initiated';
    public static $reversed = 'Reversed';
    public static $deviceType="Android";
    //Payment Status
    public static $paid = 'Paid';
    public static $partial = 'Partial';
    public static $unpaid = 'Unpaid';

    public static $fairBook = [
        'token' => 'bearer fwewieadfa534##4@1ruh7y98y4$efdw44334FairBookgrwgw5',
        'name'  => 'fairBook'
    ];

    public static function getFinYear()
    { 
        $endEconomicYear = date('Y');
        $currentMonth = date('n');
        $startEconomicYear = $endEconomicYear-1;
        if ($currentMonth >= 7) {
            $startEconomicYear = $endEconomicYear;
            $endEconomicYear +=1; 
        }

        $endEconomicYear = date('y', strtotime($endEconomicYear.'-01-01'));
        return $startEconomicYear.'-'.$endEconomicYear;        
    }

    public static function challanNo($branch_id, $date = NULL, $is_contractual = 0)
    { 
        $endEconomicYear = date('Y');
        $currentMonth = date('n');
        if ($date != NULL) {
            $endEconomicYear = date('Y', strtotime($date));
            $currentMonth = date('n', strtotime($date));
        }
        $startEconomicYear = $endEconomicYear-1;
        if ($currentMonth >= 7) {
            $startEconomicYear = $endEconomicYear;
            $endEconomicYear +=1; 
        }
        // DB::enableQueryLog(); // Enable query log
        $startDate = date('Y-m-d 00:00:00', strtotime($startEconomicYear."-07-01"));
        $endDate = date('Y-m-d 23:59:59', strtotime($endEconomicYear."-06-31"));

        // SL No.
        if ($is_contractual == 1){
            $lastSales = Sales::where('branch_id', $branch_id)
            ->where('is_contractual', 1)
            ->whereNotNull('sl_no')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('id', 'desc')
            ->first();
        }elseif ($is_contractual == 2) {
            $lastSales = Sales::where('branch_id', $branch_id)
            ->where('is_contractual', 2)
            ->whereNotNull('sl_no')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('id', 'desc')
            ->first();
        }else{
            $lastSales = Sales::where('branch_id', $branch_id)
            ->where('is_contractual', 0)
            ->whereNotNull('sl_no')
            ->whereBetween('created_at', [$startDate, $endDate])            
            ->orderBy('id', 'desc')
            ->first();
        }
        
        $challanSl = 0;
        if (!empty($lastSales) && $lastSales->sl_no > 0) {
            $challanSl = $lastSales->sl_no;
        }
        $challanSl +=1;
        $endEconomicYear = date('y', strtotime($endEconomicYear.'-01-01'));
        $challanSl = str_pad($challanSl, 5, '0', STR_PAD_LEFT);
        return $startEconomicYear.'-'.$endEconomicYear.'-'.$challanSl;
        
        
    }

    public static function reChallanNo($branch_id, $date = NULL)
    { 
        $endEconomicYear = date('Y');
        $currentMonth = date('n');
        if ($date != NULL) {
            $endEconomicYear = date('Y', strtotime($date));
            $currentMonth = date('n', strtotime($date));
        }
        $startEconomicYear = $endEconomicYear-1;
        if ($currentMonth >= 7) {
            $startEconomicYear = $endEconomicYear;
            $endEconomicYear +=1; 
        }
        // DB::enableQueryLog(); // Enable query log
        $startDate = date('Y-m-d 00:00:00', strtotime($startEconomicYear."-07-01"));
        $endDate = date('Y-m-d 23:59:59', strtotime($endEconomicYear."-06-31"));

        // SL No.
        $lastSalesReturn = SalesReturn::where('branch_id', $branch_id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->orderBy('id', 'desc')
        ->first();
        $challanSl = 0;
        if (!empty($lastSalesReturn) && $lastSalesReturn->sl_no > 0) {
            $challanSl = $lastSalesReturn->sl_no;
        }
        $challanSl +=1;
        $challanSl = str_pad($challanSl, 3, '0', STR_PAD_LEFT);
        
        $endEconomicYear = date('y', strtotime($endEconomicYear.'-01-01'));
        return $startEconomicYear.'-'.$endEconomicYear.'-'.$challanSl;        
    }

    public static function purRetChallanNo()
    { 
        $endEconomicYear = date('Y');
        $currentMonth = date('n');
        $startEconomicYear = $endEconomicYear-1;
        if ($currentMonth >= 7) {
            $startEconomicYear = $endEconomicYear;
            $endEconomicYear +=1; 
        }
        // DB::enableQueryLog(); // Enable query log
        $startDate = date('Y-m-d 00:00:00', strtotime($startEconomicYear."-07-01"));
        $endDate = date('Y-m-d 23:59:59', strtotime($endEconomicYear."-06-31"));

        // SL No.
        $lastSalesReturn = PurchaseReturn::where('company_id', auth()->user()->company_id)
        ->orderBy('sl_no', 'desc')
        ->first();
        $challanSl = 0;
        if (!empty($lastSalesReturn) && $lastSalesReturn->sl_no > 0) {
            $challanSl = $lastSalesReturn->sl_no;
        }
        $challanSl +=1;
        $challanSl = str_pad($challanSl, 3, '0', STR_PAD_LEFT);
        
        $endEconomicYear = date('y', strtotime($endEconomicYear.'-01-01'));
        return $startEconomicYear.'-'.$endEconomicYear.'-'.$challanSl;
    }

    public static function transChallan($branch_id)
    { 
        $endEconomicYear = date('Y');
        $currentMonth = date('n');
        $startEconomicYear = $endEconomicYear-1;
        if ($currentMonth >= 7) {
            $startEconomicYear = $endEconomicYear;
            $endEconomicYear +=1; 
        }
        // DB::enableQueryLog(); // Enable query log
        $startDate = date('Y-m-d 00:00:00', strtotime($startEconomicYear."-07-01"));
        $endDate = date('Y-m-d 23:59:59', strtotime($endEconomicYear."-06-31"));

        // SL No.
        
        $lastTransfer = Transfer::where('company_id', auth()->user()->company_id)
        ->where('branch_from_id', $branch_id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->orderBy('id', 'desc')
        ->first();
        $challanSl = 0;
        if (!empty($lastTransfer) && $lastTransfer->sl_no > 0) {
            $challanSl = $lastTransfer->sl_no;
        }
        $challanSl +=1;
        $endEconomicYear = date('y', strtotime($endEconomicYear.'-01-01'));
        $challanSl = str_pad($challanSl, 5, '0', STR_PAD_LEFT);
        return $startEconomicYear.'-'.$endEconomicYear.'-'.$challanSl;        
    }

    public static function postDataUpdate($product, $branch_id, $date, $trx_type = NULL, $branch = NULL) {
        // $mushokUpdates = new $this->mushok;
        $comMus = new MushokSix;
        $companyMushokItems = $comMus::where('product_id', $product['id'])
             ->where('created_at', '>', $date)
             ->orderBy('created_at', 'asc')
             ->get();

        $brnsMush = new MushokSix;
        $branchMushokItems = $brnsMush::where(['product_id' => $product['id'], 'branch_id' =>$branch_id])
        ->where('created_at', '>', $date)
        ->orderBy('created_at', 'asc')
        ->get();
        if ($branch == NULL && !empty($companyMushokItems)) {
            foreach ($companyMushokItems as $key => $item) {
                $upMus = new MushokSix;
                $update = $upMus::find($item['id']);
                $update->opening_qty = ($trx_type == "credit")? ($update->opening_qty + $product['qty']): ($update->opening_qty - $product['qty']);
                $update->closing_qty = ($trx_type == "credit")? ($update->closing_qty + $product['qty']): ($update->closing_qty - $product['qty']);
                $update->update();
           }
        }
        
        if (!empty($branchMushokItems)) {
            foreach ($branchMushokItems as $key => $item) {
                $brMusUp = new MushokSix;
                $updateBr = $brMusUp::find($item['id']);
                // Branch Stock
                $updateBr->branch_opening = ($trx_type == "credit")? ($updateBr->branch_opening + $product['qty']): ($updateBr->branch_opening - $product['qty']);
                $updateBr->branch_closing = ($trx_type == "credit")? ($updateBr->branch_closing + $product['qty']) : ($updateBr->branch_closing - $product['qty']);
                $updateBr->update();
           }
        }
        return true;
   }
   public static function postDataUpdate2($lastMushok) {
        $comMus = new MushokSix;
        $companyMushokItems = $comMus::where('product_id', $lastMushok->product_id)
            ->where('created_at', '>', $lastMushok->created_at)
            ->orderBy('created_at', 'asc')
            ->get();
        $companyMushokItems;

        $brnsMush = new MushokSix;

        $branchMushokItems = $brnsMush::where(['product_id' => $lastMushok->product_id, 'branch_id' => $lastMushok->branch_id])
        ->where('created_at', '>', $lastMushok->created_at)
        ->orderBy('created_at', 'asc')
        ->get();
        if (!empty($companyMushokItems)) {
            $previous = $lastMushok;
            $i = 0;
            
            foreach ($companyMushokItems as $key => $item) {
               
                    $upMus = new MushokSix;
                
                    $update = $upMus::find($item['id']);
                    
                    $update->opening_qty = $previous->closing_qty;
                    $update->closing_qty = ($update->type == 'credit')? ($update->opening_qty + $update->qty):($update->opening_qty - $update->qty);
                    
                    $update->update();
                    $previous = $update;
               
            }
        }
        
        if (!empty($branchMushokItems)) {
            $previous = $lastMushok;
            foreach ($branchMushokItems as $key => $item) {
                $brMusUp = new MushokSix;
                $update = $upMus::find($item['id']);
                
                $update->branch_opening = $previous->branch_closing;
                $update->branch_closing = ($update->type == 'credit')? ($update->branch_opening + $update->qty):($update->branch_opening - $update->qty);
            
                $update->update();
                $previous = $update;
            }
        }
        return true;
    }

    public static function postDataUpdateOnDelete($product, $branch_id, $date, $trx_type = NULL, $branch = NULL) {
        // $mushokUpdates = new $this->mushok;
        $comMus = new MushokSix;
        $companyMushokItems = $comMus::where('product_id', $product['id'])
             ->where('created_at', '>', $date)
             ->orderBy('created_at', 'asc')
             ->get();

        $brnsMush = new MushokSix;
        $branchMushokItems = $brnsMush::where(['product_id' => $product['id'], 'branch_id' =>$branch_id])
        ->where('created_at', '>', $date)
        ->orderBy('created_at', 'asc')
        ->get();
        if ($branch == NULL && !empty($companyMushokItems)) {
            $latestData = $comMus::where('product_id', $product['id'])
            ->where('created_at', '<', $date)
            ->orderBy('created_at', 'desc')
            ->first();
            foreach ($companyMushokItems as $key => $item) {
                $upMus = new MushokSix;
                $update = $upMus::find($item['id']);
                if ($update->is_transfer == 1) {
                    $update->opening_qty = $update->opening_qty;
                    $update->closing_qty = $update->closing_qty;

                    $update->branch_opening = ($trx_type == "credit")? ($update->branch_opening + $product['qty']): ($update->branch_opening - $product['qty']);
                    $update->branch_closing = ($trx_type == "credit")? ($update->branch_closing + $product['qty']) : ($update->branch_closing - $product['qty']);
                }else{
                    $update->opening_qty = !empty($latestData)? $latestData->closing_qty: 0;
                    $update->closing_qty = ($update->type == 'credit')? ($update->opening_qty + $update->qty):($update->opening_qty - $update->qty);
                }
                $update->update();
                $latestData = [];
                $latestData = $update;
           }
        }
        
        if (!empty($branchMushokItems)) {
            $brLasMusUp = new MushokSix;
            $latestBranchData = $brLasMusUp::where(['product_id' => $product['id'], 'branch_id' =>$branch_id])
            ->where('created_at', '<', $date)
            ->orderBy('created_at', 'desc')
            ->first();

            foreach ($branchMushokItems as $key => $item) {
                $brMusUp = new MushokSix;
                $updateBr = $brMusUp::find($item['id']);
                
                $updateBr->branch_opening = !empty($latestBranchData)? $latestBranchData->branch_closing: 0;
                $updateBr->branch_closing = ($updateBr->type == 'credit')? ($updateBr->branch_opening + $updateBr->qty): ($updateBr->branch_opening - $updateBr->qty);
            
                $updateBr->update();
                $latestBranchData = $updateBr;
           }
        }
        return true;
    }
    public static function postDataUpdateOnAdd($product, $branch_id, $date, $trx_type = NULL, $branch = NULL) {
        // $mushokUpdates = new $this->mushok;
        $comMus = new MushokSix;
        $companyMushokItems = $comMus::where('product_id', $product['id'])
             ->where('created_at', '>', $date)
             ->orderBy('created_at', 'desc')
             ->get();

        $brnsMush = new MushokSix;
        $branchMushokItems = $brnsMush::where(['product_id' => $product['id'], 'branch_id' =>$branch_id])
        ->where('created_at', '<=', $date)
        ->orderBy('created_at', 'desc')
        ->get();
        if ($branch == NULL && !empty($companyMushokItems)) {
            $latestData = $comMus::where('product_id', $product['id'])
            ->where('created_at', '>', $date)
            ->orderBy('created_at', 'asc')
            ->first();
            foreach ($companyMushokItems as $key => $item) {
                $upMus = new MushokSix;
                $update = $upMus::find($item['id']);
                if ($update->is_transfer == 1) {
                    $update->opening_qty = $update->opening_qty;
                    $update->closing_qty = $update->closing_qty;
                }else{
                    $update->opening_qty = !empty($latestData)? $latestData->closing_qty: 0;
                    $update->closing_qty = ($update->type == 'credit')? ($update->opening_qty + $update->qty):($update->opening_qty - $update->qty);
                }
                $update->update();
                $latestData = [];
                $latestData = $update;
           }
        }
        
        if (!empty($branchMushokItems)) {
            $brLasMusUp = new MushokSix;
            $latestBranchData = $brLasMusUp::where(['product_id' => $product['id'], 'branch_id' =>$branch_id])
            ->where('created_at', '>', $date)
            ->orderBy('created_at', 'asc')
            ->first();

            foreach ($branchMushokItems as $key => $item) {
                $brMusUp = new MushokSix;
                $updateBr = $brMusUp::find($item['id']);
                
                $updateBr->branch_opening = !empty($latestBranchData)? $latestBranchData->branch_closing: 0;
                $updateBr->branch_closing = ($updateBr->type == 'credit')? ($updateBr->branch_opening + $updateBr->qty): ($updateBr->branch_opening - $updateBr->qty);
            
                $updateBr->update();
                $latestBranchData = $updateBr;
           }
        }
        return true;
    }

    public static function updateMushokCompany($product, $date) {
        // $mushokUpdates = new $this->mushok;
       
        $comMus = new MushokSix;
        $companyMushokItems = $comMus::select('id', 'company_id', 'branch_id', 'product_id', 'type', 'nature', "mushok", "qty", "sales_return_qty", "purchase_return_qty", "opening_qty",  "closing_qty", "branch_opening", "branch_closing", "created_at", "updated_at")
        ->where('product_id', $product['id'])
        ->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($date['start_date'])), date('Y-m-d 23:59:59', strtotime($date['end_date']))])
             ->orderBy('created_at', 'asc')
             ->get();
        // return $companyMushokItems;
        if (!empty($companyMushokItems)) {
            $firstData = $companyMushokItems[0];
            $i = 0;
            $lastClosing = 0;
            foreach ($companyMushokItems as $key => $item) {
                if ($i>0) {
                    $upMus = new MushokSix;
                    $update = $upMus::find($item['id']);
                    if ($update->is_transfer == 1) {
                        $update->opening_qty = $update->opening_qty;
                        $update->closing_qty = $update->closing_qty;

                        $update->branch_opening = !empty($firstData)? $firstData->branch_closing: 0;;
                        $update->branch_closing = ($update->type == 'credit')? ($update->branch_opening + $update->qty):($update->branch_opening - $update->qty);
                    }else{
                        if ($item->nature == "OpeningStock" && $item->qty == 0) {
                            $update->qty = $item->branch_opening_stock;
                        }
                        $update->opening_qty = !empty($firstData)? $firstData->closing_qty: 0;
                        $update->closing_qty = ($update->type == 'credit')? ($update->opening_qty + $update->qty):($update->opening_qty - $update->qty);
                    }
                    $update->update();
                    $firstData = [];
                    $firstData = $update;
                    $lastClosing = $update->closing_qty;    
                }  
                
                $lastItem = $item['product_id'];    
                $lastBranch = $item['branch_id'];
                $i++;
            }
            
            if ($lastClosing>0) {

                $stock = ItemStock::where('product_id', $lastItem)->where('branch_id', $lastBranch)->first();
                
                $stock->stock = $lastClosing;
                $stock->update();
                return $stock;
            }
        }
        return true;
    }

    public static function getUserIP()
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
                $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }
        return $ip;
    }

    public static function sendSms($mobile, $content, $sales_no) {
        try {
            $url = "https://smsplus.sslwireless.com/api/v3/send-sms/bulk";
            $params = [
                "api_token" => "pknbjypf-ma2jfl2p-ji3uwudj-jz93m9ev-qwzsqrxc",
                "sid" => "FELDSPMASK",
                "msisdn" => $mobile, //"01716167744",
                "sms" => $content,
                "batch_csms_id" => $sales_no
            ];
            // $params = json_encode($params);
            $response = Http::withOptions([
                'verify' => false, // Set to false to skip SSL verification
                'Content-Type' => 'application/json',
                'accept' => 'application/json'
            ])->post($url, $params);
            return $response->body();
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
        
    }
}