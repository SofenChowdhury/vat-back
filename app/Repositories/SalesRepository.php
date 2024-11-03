<?php
namespace App\Repositories;

use App\Models\Sales;
use App\Models\Branch;
use App\Classes\Helper;
use App\Models\Company;
use App\Models\Product;
use App\Jobs\SendSmsJob;
use App\Models\Category;
use App\Models\Customer;
use App\Models\NonOrder;
use App\Models\ItemStock;
use App\Models\MushokSix;
use App\Models\SalesItem;
use AWS\CRT\HTTP\Request;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class SalesRepository implements BaseRepository{
     protected $model;
     protected $salesItem;
     protected $product;
     protected $stock;
     protected $mushok;
     protected $branch;
     protected $return;
     protected $returnItems;
     protected $company;
     protected $customer;
     protected $nonOrder;
     protected $category;
     
     public function __construct(Sales $model, SalesItem $salesItem, Product $product, ItemStock $stock, MushokSix $mushok, Branch $branch, SalesReturn $return, SalesReturnItem $returnItems, Company $company, Customer $customer, NonOrder $nonOrder, Category $category)
     {
        $this->model = $model;
        $this->salesItem = $salesItem;
        $this->product = $product;
        $this->stock = $stock;
        $this->mushok = $mushok;
        $this->branch = $branch;  
        $this->return = $return;  
        $this->returnItems = $returnItems;  
        $this->company = $company;  
        $this->customer = $customer;
        $this->nonOrder = $nonOrder;
        $this->category = $category;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll($company_id = NULL){          
          if (!empty($company_id) && $company_id !=0) {
               $query = $this->model::with('customer', 'company', 'branch')
               ->where('company_id', $company_id);
               if (auth()->user()->branch_id> 0) {
                    $query->where('branch_id', auth()->user()->branch_id);
               }
               return $query->latest()->paginate(20);
          }else{
               return $this->model::with('customer', 'company', 'branch')->latest()->paginate(20);
          }               
          
     }

     public function returnList($company_id = NULL)
     {
          if (!empty($company_id)) {
               $returnList = $this->return::with('customer', 'sales.company', 'sales.branch', 'sales.customer', 'returnItems.info')
               ->where('company_id', $company_id)
               // ->whereHas('sales', function($query) use($company_id){
               //      $query->where('company_id', $company_id);
               // })
               ->latest()->paginate(20);
               return response()->json([
                    'status' => true,
                    'data' => $returnList,
                    'errors' => '', 
                    'message' => "Return list has been loaded"
               ]);
          }else{
               $returnList = $this->return::with('customer', 'sales.company', 'sales.branch', 'sales.customer', 'returnItems.info')->latest()->paginate(20);
               return response()->json([
                    'status' => true,
                    'data' => $returnList,
                    'errors' => '', 
                    'message' => "Return list has been loaded"
               ]);
          }
     }

     public function returnSubForm($request)
     {
          $user = auth()->user();
          $returnList = $this->return::with('sales.company', 'sales.branch', 'sales.customer', 'returnItems.info');
          $returnList->where('company_id', $user->company_id);
          // if ($user->company_id !="") {
          //      $returnList->whereHas('sales', function ($query) {
          //           $user = auth()->user();
          //           $query->where('company_id', $user->company_id);
          //       });
          // }
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $returnList->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
          }          
          return $returnList->latest()->get();
     }

     public function getReturnById($id)
     {
          return $this->return::with('customer', 'company', 'sales.company', 'sales.branch', 'sales.customer', 'sales.SalesItems', 'returnItems.info')
          ->where('id', $id)->first();
     }

     public function getReturnByChallan($challan)
     {
          return $this->return::with('customer', 'company', 'sales.company', 'sales.branch', 'sales.customer', 'sales.SalesItems', 'returnItems.info')
          ->where('return_no', $challan)->first();
     }

     /**
      * all resource get
      * @return Collection
      */
      public function search($request, $user = NULL){
          // $data['start_date'] = date('Y-m-01 00:00:00');
          // $data['end_date'] = date('Y-m-d 23:59:59');
          
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
               $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
          }
          $user = auth()->user();
          $query = $this->model::query();
          $query->with('customer', 'company', 'branch');
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
          }
          if ((isset($request->branch_id) && $request->branch_id !="")) {
               $query->where('branch_id', $request->branch_id);
          }
          if ((isset($request->customer_id) && $request->customer_id !="")) {
               $query->where('customer_id', $request->customer_id);
          }
          if ((isset($request->mobile) && $request->mobile !="")) {
               $query->where('customer_phone', $request->mobile);
          }

          if ((isset($request->sales_no) && $request->sales_no !="")) {
               // $query->where('sales_no', $request->sales_no);
               $query->where('sales_no', 'LIKE', '%' . $request->sales_no . '%');
          }

          if ($user->company_id> 0) {
               $query->where('company_id', $user->company_id);
          }  

          if ($user->branch_id> 0) {
               $query->where('branch_id', $user->branch_id);
          }
          if ($request->reference_no != "") {
               
               $query->where('reference_no', 'LIKE', '%' . $request->reference_no . '%');
          }
          // $query->orderBy('sl_no', 'desc');
          return $query->latest('created_at')->paginate(20);
     }
     
     /**
      * all resource get
      * @return Collection
      */
      public function download($request, $user = NULL){
          $data['start_date'] = date('Y-m-01 00:00:00');
          $data['end_date'] = date('Y-m-d 23:59:59');
          
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
               $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
          }
          
          $query = $this->model::query();
          $query->with('salesItems.itemInfo', 'customer', 'company', 'branch');
          
          $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
          
          if ((isset($request->branch_id) && $request->branch_id !="")) {
               $query->where('branch_id', $request->branch_id);
          }
          if ((isset($request->mobile) && $request->mobile !="")) {
               $query->where('customer_phone', $request->mobile);
          }

          if ((isset($request->sales_no) && $request->sales_no !="")) {
               $query->where('sales_no', $request->sales_no);
          }
          if ((isset($request->customer_id) && $request->customer_id !="")) {
               $query->where('customer_id', $request->customer_id);
          }
          
          if (!empty($user) && $user->company_id !="") {
               return $query->where('company_id', $user->company_id)
               ->latest()->get();
          }else{
               return $query->latest()->get();
          }          
     }

     /**
      * all resource get
      * @return Collection
      */
      public function getFull($company_id = NULL){
          if (!empty($company_id)) {
               return $this->model::where('company_id', $company_id)
               ->with('salesItem.itemInfo', 'customer', 'company', 'branch')
               ->latest()->get();
          }else{
               return $this->model::with('orderItem.itemInfo', 'customer', 'company', 'branch')->latest()->get();
          }  

     }

     public function rollBackOrder($request) {
          $filename = $request->file('csvfile');
          if($_FILES["csvfile"]["size"] > 0)
          {
               $file = fopen($filename, "r");
               $i = 0;
               return true;
               try {
                    DB::beginTransaction();
                    while (($item_info = fgetcsv($file, 10000, ",")) !== FALSE)
                    {
                         $i++;
                         if ($i>1) { 

                              $stock = new $this->stock;
                              $mushok = new $this->mushok;
                              $orderItem = new $this->salesItem;
                              $sales = new $this->model;

                              $itemStock = $stock::where(['branch_id'=> $item_info[1], 'product_id' => $item_info[2]])->first();
                              $itemStock->stock = ($itemStock->stock+$item_info[3]);
                              $itemStock->update();
                              $mushok::where('sales_id', $item_info[0])->delete();
                              $orderItem::where('sales_id',  $item_info[0])->delete();
                              
                              $sale = $sales->find($item_info[0]);
                              if (!empty($sale)) {
                                   $sale->delete();
                              }  
                              $product = ['id' => $item_info[2], 'qty' => $item_info[3]];
                         
                              Helper::postDataUpdate($product, $item_info[1], date('2023-07-10'), 'credit');
                              $product = [];
                         }
                    }
                    DB::commit();
                    $returnData['status'] = true;
                    $returnData['data'] = [];
                    $returnData['message'] = $i." Items has been rollback of sales";
                    return $returnData;
               } catch (\Throwable $th) {
                    DB::rollBack();
                    $returnData['status'] = false;
                    $returnData['data'] = [];
                    $returnData['message'] = $th->getMessage();
                    return $returnData;
               }
               
          }
     }

     /**
      * all resource get
      * @return Collection
      */
      public function getLatest($company_id = NULL){
          return $this->model::take(20)->get();
     }
     
     /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function sales_list($request) {
          $category = $this->category::withCount(['salesItems' => function($query) {
               // $query->selectRaw('sales_items.id, sales_items.product_id,sales_items.price');
                $query->select(DB::raw('SUM(sales_items.price) as totalPrice'));  
           }])
           ->withCount('salesItems')
           ->where('company_id', 5)
           ->get();
           return $category;
     }
     
     public function getById($id, $company_id = NULL) {
          return $this->model::with(['salesItems.itemInfo.hscode', 'salesItems.itemInfo.category', 'salesItems.itemInfo.brand', 'mushokItems', 'customer', 'company', 'branch'])
          ->where('id', $id)->first();
     }  

     public function getByChallan($challan){
          return $this->model::with(['salesItems.itemInfo.hscode', 'salesItems.itemInfo.category', 'salesItems.itemInfo.brand', 'customer', 'company', 'branch'])
          ->where('sales_no', $challan)
          ->first();
     }  

     /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function getShowById(int $id, $company_id = NULL){
          return $this->model::with(['orderItems.itemInfo.category', 'customer', 'company', 'branch'])
          ->where('id', $id)->first();
     }

     /**
     * resource create
     * @param  $request
     * @return \Illuminate\Http\Response
     */

     public function create($request){
          $returnData = [];
          // Posting Date
          $time = date('H:i:s');
          if ($request->challan_time != "") {
               $time = date('H:i:s', strtotime($request->challan_time));
          }
          $challanDate = $request->challan_date !=""? date('Y-m-d', strtotime($request->challan_date)): date('Y-m-d');
          $challanDate  = $challanDate.' '.$time;

          try {
               $user = auth()->user();
               $orderPrefix = "";
               $company = $this->company::where('id', auth()->user()->company_id)->first();
               
               $branch_id = $user->branch_id? $user->branch_id: $request->branch_id;
               $branch = $this->branch::with('company')
               ->where('id', $branch_id)
               ->first();
               if ($company->business_type != 2) {
                    $orderPrefix = $company->order_prefix;
                    if ($request->issue_type ==1) {
                         $orderPrefix = $orderPrefix."-CON";
                    }elseif ($request->issue_type ==2) {
                         $orderPrefix = $orderPrefix."-ISSUE";
                    }
               }else{
                    $orderPrefix = $company->order_prefix;
                    $orderPrefix = $orderPrefix."-".$branch->order_prefix;
               }
               $is_contractual = 0;
               if ($request->issue_type > 0) {
                    $is_contractual = $request->issue_type;
               }
               // DB::beginTransaction();
               $challanNo = Helper::challanNo($branch_id, $challanDate, $is_contractual);
               $challanNoFormat = $orderPrefix.'-'.$challanNo;
               if ($request->challan_no != "") {
                    $challanNoFormat = $request->challan_no;
               }
               $sl_no = explode("-",$challanNo); 
               
               $sales = $this->model;
               $sales->customer_id     = $request->customer_id;
               $sales->customer_code   = $request->customer_code;
               $sales->company_id      = (int) auth()->user()->company_id;
               $sales->branch_id       = $branch_id;
               $sales->sales_by        = (int) auth()->user()->id;
               $sales->sales_no        = $challanNoFormat;
               $sales->sl_no           = ($request->challan_no == "")? (int) $sl_no[2]: NULL;
               $sales->reference_no    = $request->reference_no;
               $sales->customer_name   = $request->customer_name;
               $sales->customer_email  = $request->customer_email;
               $sales->customer_phone  = $request->customer_phone;
               $sales->customer_address= $request->customer_address? $request->customer_address: NULL;
               $sales->shipping_address= $request->shipping_address? $request->shipping_address: $request->customer_address;
               $sales->customer_national_id   = $request->customer_national_id? $request->customer_national_id:NULL;
               $sales->ref_name  = $request->ref_name;
               $sales->ref_address      = $request->ref_address;
               $sales->ref_national_id      = $request->ref_national_id;
               $sales->vehicle_no      = $request->vehicle_no? $request->vehicle_no: NULL;
               $sales->driver_name      = $request->driver_name? $request->driver_name: NULL;
               $sales->driver_mobile      = $request->driver_mobile? $request->driver_mobile: NULL;
               $sales->destination_address      = $request->destination_address;
               $sales->is_contractual      = (!empty($request->issue_type) && $request->issue_type > 0)? $request->issue_type:0;
               $sales->challan_date      = date('Y-m-d', strtotime($challanDate));
               $sales->note = $request->note? $request->note: NULL;
               $sales->is_exported = $request->is_exported? 1: 0;
               $sales->created_at = $challanDate;
               $sales->save();
               $totalSalesValue = 0;
               foreach ($request->salesItems as $item) {

                    $productInfo = $this->product::select('id', 'title', 'sku','model', 'type', 'price', 'status')
                              ->where('id', $item['id'])->first();
                    $stockParams = array('id' => $productInfo->id, 'qty' => $item['qty'], 'company_id' => $user->company_id, 'branch_id' => $sales->branch_id);
                    $mushok_no = ($productInfo->type == 2)?'six_one':'six_two';
                    if ($company->business_type == 2) {
                         $mushok_no = 'six_two_one';
                    }
                    // return $stockParams;
                    $checkStock = $this->stockUpdate($stockParams);
                    // $checkStock = true;
                    if ($checkStock || $productInfo->type == 4) {
                         $orderItem = new  $this->salesItem;
                         $orderItem->sales_id     = (int) $sales->id;
                         $orderItem->product_id   = $item['id'];
                         $orderItem->item_info    = json_encode($productInfo);
                         $orderItem->price        = $item['price'];
                         $orderItem->qty          = $item['qty'];
                         $orderItem->vat_rate     = $item['vat_rate'];
                         $orderItem->is_vat_exempted = ($item['vat_rate'] < 1 && $request->is_exported !=1)? 1:0;
                         $orderItem->vat_amount   = ((( $item['price']* (float) $item['vat_rate'])/100)* $item['qty']);
                         $orderItem->total_price = ( $item['price'] * $item['qty']);
                         $orderItem->created_at = $sales->created_at;
                         $orderItem->save();
                         $totalSalesValue += ($orderItem->total_price+$orderItem->vat_amount);
                         // Mushok Insert
                         $mushokItems = new $this->mushok;
                         $companyLastMushok = $mushokItems::where(['product_id' => $orderItem->product_id, 'company_id' => $company->id, 'mushok' => $mushok_no])
                         ->where('created_at', '<=', $challanDate)
                         ->orderBy('created_at', 'desc')
                         ->where('is_transfer', 0)
                         ->first();

                         $branchLastMushok = $mushokItems::where(['product_id' => $orderItem->product_id, 'branch_id' => $sales->branch_id, 'mushok' => $mushok_no])
                         ->where('created_at', '<=', $challanDate)
                         ->orderBy('created_at', 'desc')
                         ->first();

                         $lastAverage = $mushokItems::where(['product_id' => $orderItem->product_id, 'company_id' => $company->id, 'mushok' => $mushok_no])
                         ->where('price', '>', 0)
                         ->avg('price');
                        
                         $mushokItems->sales_id      =  $sales->id;
                         $mushokItems->product_id    = $orderItem->product_id;
                         $mushokItems->company_id    = $sales->company_id;
                         $mushokItems->branch_id    = $sales->branch_id;
                         $mushokItems->type          = 'debit';
                         $mushokItems->mushok        = ($productInfo->type == 1)? $mushok_no: "six_one";
                         $mushokItems->nature         = (!empty($request->issue_type) && $request->issue_type > 0)? (($request->issue_type == 1)? "Contractual":"Issue to Production Floor"):0;
                         //  ($is_contractual > 0)? 'Contractual': NULL;
                         $mushokItems->price         = $orderItem->price;
                         $mushokItems->average_price = !empty($lastAverage)? $lastAverage:0;
                         $mushokItems->qty           = $orderItem->qty;
                         $mushokItems->vat_rate     = $orderItem->vat_rate;
                         $mushokItems->vat_amount   = $orderItem->vat_amount;
                         // BRANCH balance
                         // $mushokItems->branch_opening   = !empty($branchLastMushok)? $branchLastMushok->closing_qty:0;
                         // $mushokItems->branch_closing   = $mushokItems->branch_opening-$orderItem->qty;
                         if ($productInfo->type == 4) {
                              $mushokItems->branch_opening = 0;
                              $mushokItems->branch_closing = 0;
                              $mushokItems->opening_qty   = 0;
                              $mushokItems->closing_qty   = 0;
                         }else {
                              if (!empty($branchLastMushok)) {
                                   $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                                   $mushokItems->branch_closing = $mushokItems->branch_opening-$mushokItems->qty;
                              }else{
                                   $mushokItems->branch_opening = $mushokItems->qty;
                                   $mushokItems->branch_closing = $mushokItems->qty;
                              }
                              // Company Balance 
                              $mushokItems->opening_qty   = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
                              $mushokItems->closing_qty   = $mushokItems->opening_qty-$orderItem->qty;
                         }
                         

                         $mushokItems->created_by    = (int) auth()->user()->id;
                         $mushokItems->created_at    = $sales->created_at;
                         $product = ['id' => $productInfo->id, 'qty' => $mushokItems->qty];
                         $mushokItems->save();
                         Helper::postDataUpdate($product, $mushokItems->branch_id, $sales->created_at, $mushokItems->type);
                         DB::commit();
                    }else{
                         DB::rollBack();
                         $returnData['status'] = false;
                         $returnData['data'] = [];
                         $returnData['message'] = "Sales item stock is not available";
                         return $returnData;
                    }
               }
               
               $totalSalesValue = number_format($totalSalesValue);
               $smsData = NULL;
               if ($sales->company_id == 4) {
                    $smsData = $this->sendSms($sales, $totalSalesValue);
                    Log::info($smsData);
               }               
               $returnData['status'] = true;
               $returnData['data'] = $smsData;
               $returnData['message'] = "Your Sales has been successfully completed";
               return $returnData;
          } catch (\Throwable $th) {
               $returnData['status'] = false;
               $returnData['data'] = [];
               $returnData['message'] = $th->getMessage();
               return $returnData;
          }
     }

     function smsTest($id) {
          $sales = Sales::find($id);
          return $this->sendSms($sales, 25000);
     }

     function sendSms($sales, $totalSalesValue) {
          // return $sales;
          
          if (strlen($sales->customer_phone) == 11 && $sales->company_id == 4) {
               $mobileNumbers = [$sales->customer_phone,"01777702222", "01777702025"];
               // $mobileNumbers = [$sales->customer_phone, "01700718853"];
               $smsContent = "Dear ".$sales->customer_name.", your Mushok# ". $sales->sales_no.", amount: ".$totalSalesValue." has been dispatched.
Driver ".$sales->driver_name." ".$sales->driver_mobile."
Vehicle# ". $sales->vehicle_no."
FairElectronics";
               return Helper::sendSms($mobileNumbers, $smsContent, $sales->sales_no);
          }
          
          // return $data;
          // return response()->json($data);
          // SendSmsJob::dispatch($sales, $totalSalesValue);
     }

     public function itemAddToSales($request) {
          $filename = $request->file('csvfile');
          if($_FILES["csvfile"]["size"] > 0)
          {
               // DB::beginTransaction();
               $file = fopen($filename, "r");
              
               $i = 0;
               $nonStock = [];
               DB::beginTransaction();
               while (($item_info = fgetcsv($file, 10000, ",")) !== FALSE)
               {
                    $i++;
                    if ($i>1) { 
                         
                         $sales = $this->model::find($item_info[0]);
                         
                         $productInfo = $this->product::find($item_info[2]);
                         $company = $this->company::find($sales->company_id);
                         $stockParams = array('id' => $productInfo->id, 'qty' => $item_info[4], 'company_id' => $sales->company_id, 'branch_id' => $sales->branch_id);
                         
                         if ($this->stockUpdate($stockParams)) {
                              $salesItem = new  $this->salesItem;
                              $salesItem->sales_id = $sales->id;
                              $salesItem->product_id = $productInfo->id;
                              $salesItem->item_info = json_encode($productInfo);
                              $salesItem->qty = $item_info[4];
                              $salesItem->price = $item_info[8];
                              $salesItem->vat_rate = $item_info[6];
                              $salesItem->vat_amount = $item_info[7];
                              $salesItem->total_price = ($salesItem->price*$salesItem->qty);
                              $salesItem->save();

                              // Mushok Insert
                              $mushok_no = 'six_two';
                              if ($company->business_type == 2) {
                                   $mushok_no = 'six_two_one';
                              }
                              $mushokItems = new $this->mushok;
                              $companyLastMushok = $mushokItems::where(['product_id' => $salesItem->product_id, 'company_id' => $company->id, 'mushok' => $mushok_no])
                              ->where('created_at', '<=', date('Y-m-d H:i:s', strtotime($sales->created_at)))
                              ->latest('created_at')->first();

                              $branchLastMushok = $mushokItems::where(['product_id' => $salesItem->product_id, 'branch_id' => $sales->branch_id, 'mushok' => $mushok_no])
                              ->where('created_at', '<=', date('Y-m-d H:i:s', strtotime($sales->created_at)))
                              ->latest('created_at')->first();

                              $lastAverage = $mushokItems::where(['product_id' => $salesItem->product_id, 'company_id' => $company->id, 'mushok' => $mushok_no])
                              ->where('price', '>', 0)
                              
                              ->avg('price');
                         
                              $mushokItems->sales_id      =  $sales->id;
                              $mushokItems->product_id    = $productInfo->id;
                              $mushokItems->company_id    = $sales->company_id;
                              $mushokItems->branch_id    = $sales->branch_id;
                              $mushokItems->type          = 'debit';
                              $mushokItems->mushok        = $mushok_no;
                              $mushokItems->price         = $salesItem->price;
                              $mushokItems->average_price = !empty($lastAverage)? $lastAverage:0;
                              $mushokItems->qty           = $salesItem->qty;
                              $mushokItems->vat_rate     = $salesItem->vat_rate;
                              $mushokItems->vat_amount   = $salesItem->vat_amount;
                              
                              // BRANCH balance
                              // $mushokItems->branch_opening   = !empty($branchLastMushok)? $branchLastMushok->closing_qty:0;
                              // $mushokItems->branch_closing   = $mushokItems->branch_opening-$orderItem->qty;
                              if (!empty($branchLastMushok)) {
                                   $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                                   $mushokItems->branch_closing = $mushokItems->branch_opening-$mushokItems->qty;
                              }else{
                                   $mushokItems->branch_opening = $mushokItems->qty;
                                   $mushokItems->branch_closing = $mushokItems->qty;
                              }
                              // Company Balance 
                              $mushokItems->opening_qty   = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
                              $mushokItems->closing_qty   = ($mushokItems->opening_qty-$mushokItems->qty);
                              $mushokItems->created_by   = $sales->created_by;
                              $mushokItems->created_at    = date('Y-m-d H:i:s', strtotime($sales->created_at));
                              $mushokItems->save();
                              $product = ['id' => $productInfo->id, 'qty' => $mushokItems->qty];
                              Helper::postDataUpdate($product, $mushokItems->branch_id, date('Y-m-d H:i:s', strtotime($sales->created_at)), $mushokItems->type);
                         }else{
                              $ref['ref_no'] = $item_info[1];
                              $ref['sku_no'] = $item_info[3];
                              $nonStock[] = $ref;
                         }                        
                    }
               }
               DB::commit(); 
               $returnData['status'] = false;
               $returnData['data'] = $nonStock;
               $returnData['message'] = "Uploaded";
               return $returnData;
          }
          
     }

     public function branchBulkUpload($request) {
          $user = auth()->user(); 
          $filename = $request->file('csvfile');
          if($_FILES["csvfile"]["size"] > 0)
          {
               // DB::beginTransaction();
               $file = fopen($filename, "r");
               $i = 0;               
               $company = $this->company::where('id', $user->company_id)->first();
              

               $i = 0;
               $previousRef = NULL;
               $salesId = NULL;
               $sales = [];
               $wrongSkus = [];
               $notFoundSkus = 0;
               while (($item_info = fgetcsv($file, 10000, ",")) !== FALSE)
               {
                    $i++;
                    if ($i>1) { 
                         $branch = $this->branch::with('company')->where('code', $item_info[4])->first();
                         $productInfo = $this->product::select('id', 'hs_code_id', 'hs_code', 'sku', 'title', 'model', 'type', 'price', 'status')
                         ->where('sku', $item_info[13])
                         ->where('company_id', auth()->user()->company_id)
                         ->first();
                         // Posting Date
                         $time = date('H:i:s', strtotime("+$i minutes" , time()));
                         $challanDate = $item_info[5]? date("Y-m-d", strtotime($item_info[5])):date("Y-m-d");
                         $challanDate = $challanDate.' '.$time;
                         // Posting Date
                         $refNo = explode("-",$item_info[6]);
                         $refPrefix = $refNo[0];
                         
                         
                         try {
                              if (!empty($branch)) {
                                   if (!empty($productInfo) && $refPrefix == "SR") {
                                        $salesExist = $this->model::whereHas('salesItems', function($query) use ($productInfo){
                                             $query->where('product_id', $productInfo->id);
                                        })->where('reference_no', $item_info[6])->first();
                                        
                                        $salesExist = [];
                                        if (empty($salesExist)) {
                                             
                                             $stockParams = array('id' => $productInfo->id, 'qty' => $item_info[17], 'company_id' => $user->company_id, 'branch_id' => $branch->id);
                                            
                                             $checkStock = $this->stockUpdate($stockParams);
                                             // VAT Calculation
                                             $vatAblePrice = ($item_info[42]- $item_info[36]);
                                             $vatRate = ($vatAblePrice>0)?(($item_info[36] / $vatAblePrice)*100):0;
                                             
                                             // Sales Entry
                                             $unitPrice =  ($vatAblePrice>0)? (($item_info[42]-$item_info[36]) /$item_info[17]):0;
                                             DB::beginTransaction();
                                             if ($checkStock || $productInfo->type == 4) {
                                                  if ($previousRef != $item_info[6]) {
                                                       $salesInfo = [
                                                            'customer_name' => $item_info[8],
                                                            'ref_no' => $item_info[6],
                                                            'branch_id' => $branch->id,
                                                            'address' => $item_info[9],
                                                            'date' => $challanDate
                                                       ];
                                                       $sales = $this->createOutletSales($salesInfo);
                                                  }
                                                  // return $sales;
                                                  $previousRef = $item_info[6];
                                                  // $sales = $sales;
                                                  
                                                  $salesItem = new  $this->salesItem;
                                                  $salesItem->sales_id = $sales->id;
                                                  $salesItem->product_id = $productInfo->id;
                                                  $salesItem->item_info = json_encode($productInfo);
                                                  $salesItem->price = $unitPrice;
                                                  $salesItem->qty = $item_info[17];
                                                  $salesItem->vat_rate = round($vatRate);
                                                  $salesItem->vat_amount = $item_info[36];
                                                  $salesItem->total_price = ($salesItem->price*$salesItem->qty);
                                                  // return $salesItem;
                                                  $salesItem->save();
                    
                                                  // Mushok Insert
                                                  $mushok_no = 'six_two';
                                                  if ($company->business_type == 2) {
                                                       $mushok_no = 'six_two_one';
                                                  }
                                                  $mushokItems = new $this->mushok;
                                                  $companyLastMushok = $mushokItems::where(['product_id' => $salesItem->product_id, 'company_id' => $company->id, 'mushok' => $mushok_no])
                                                  ->where('created_at', '<=', $challanDate)
                                                  ->latest('created_at')->first();
          
                                                  $branchLastMushok = $mushokItems::where(['product_id' => $salesItem->product_id, 'branch_id' => $branch->id, 'mushok' => $mushok_no])
                                                  ->where('created_at', '<=', $challanDate)
                                                  ->latest('created_at')->first();
          
                                                  $lastAverage = $mushokItems::where(['product_id' => $salesItem->product_id, 'company_id' => $company->id, 'mushok' => $mushok_no])
                                                  ->where('price', '>', 0)
                                                  
                                                  ->avg('price');
                                             
                                                  $mushokItems->sales_id      =  $sales->id;
                                                  $mushokItems->product_id    = $productInfo->id;
                                                  $mushokItems->company_id    = $sales->company_id;
                                                  $mushokItems->branch_id    = $sales->branch_id;
                                                  $mushokItems->type          = 'debit';
                                                  $mushokItems->mushok        = $mushok_no;
                                                  $mushokItems->price         = $salesItem->price;
                                                  $mushokItems->average_price = !empty($lastAverage)? $lastAverage:0;
                                                  $mushokItems->qty           = $salesItem->qty;
                                                  $mushokItems->vat_rate     = round($vatRate);
                                                  $mushokItems->vat_amount   = $salesItem->vat_amount;
                                                  
                                                  // BRANCH balance
                                                  // $mushokItems->branch_opening   = !empty($branchLastMushok)? $branchLastMushok->closing_qty:0;
                                                  // $mushokItems->branch_closing   = $mushokItems->branch_opening-$orderItem->qty;
                                                  if (!empty($branchLastMushok)) {
                                                       $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                                                       $mushokItems->branch_closing = $mushokItems->branch_opening-$mushokItems->qty;
                                                  }else{
                                                       $mushokItems->branch_opening = $mushokItems->qty;
                                                       $mushokItems->branch_closing = $mushokItems->qty;
                                                  }
                                                  // Company Balance 
                                                  $mushokItems->opening_qty   = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
                                                  $mushokItems->closing_qty   = ($mushokItems->opening_qty-$mushokItems->qty);
                                                  $mushokItems->created_by   = $user->id;
                                                  $mushokItems->created_at    = $challanDate;
                                                  $mushokItems->save();
                                                  $product = ['id' => $productInfo->id, 'qty' => $mushokItems->qty];
                                                  Helper::postDataUpdate($product, $mushokItems->branch_id, $challanDate, $mushokItems->type);
                                                  DB::commit();                                                  
                                             }else{
                                                  $nonOrder = new $this->nonOrder;
                                                  $nonOrder->branch_id = $branch->id;
                                                  $nonOrder->product_id = $productInfo->id;
                                                  $nonOrder->raw_data = json_encode($item_info);
                                                  $nonOrder->type = 1;
                                                  $nonOrder->branch_code = $item_info[4];
                                                  $nonOrder->branch_name = $item_info[3];
                                                  $nonOrder->order_number = $item_info[6];
                                                  $nonOrder->sku = $item_info[13];
                                                  $nonOrder->product_title = $item_info[14];
                                                  $nonOrder->price = $unitPrice;
                                                  $nonOrder->qty = $item_info[17];
                                                  $nonOrder->vat_rate = $vatRate;
                                                  $nonOrder->vat_amount = $item_info[36];
                                                  $nonOrder->order_date = $item_info[5]? date("Y-m-d", strtotime($item_info[5])):date("Y-m-d");
                                                  
                                                  $nonOrder->save();
                                                  DB::commit();
                                                  // array_push($wrongSkus, $notFound);
                                             }
                                        }
                                   }
                              }
                         } catch (\Throwable $th) {
                              DB::rollBack();
                              $returnData['status'] = false;
                              $returnData['data'] = ['order_no' => $item_info[6], 'sku' => $item_info[13]];
                              $returnData['message'] = $th->getMessage();
                              return $returnData;
                         }                  
                    }
               }
               DB::commit();
               $returnData['status'] = true;
               $returnData['data'] = $wrongSkus;
               $returnData['message'] = "Bulk Sales Has been Uploaded";
               return $returnData;
          }
     }

     public function regularBulkUpload($request) {
          $user = auth()->user(); 
          $filename = $request->file('csvfile');
          if($_FILES["csvfile"]["size"] > 0)
          {
               // DB::beginTransaction();
               $file = fopen($filename, "r");
               $i = 0;               
               
              

               $i = 0;
               $previousRef = NULL;
               $salesId = NULL;
               $sales = [];
               $wrongSkus = [];
               $notFoundSkus = 0;
               while (($item_info = fgetcsv($file, 10000, ",")) !== FALSE)
               {
                    $i++;
                    if ($i>1) { 
                         
                         $productInfo = $this->product::where('sku',$item_info[3])->first();
                         $company = $this->company::where('id', $user->company_id)->first();
                         
                         $challanDate = date('Y-m-d H:i:s', strtotime($item_info[2].' '.$item_info[13]));
                         
                         // Posting Date
                         try {
                              DB::beginTransaction();
                              $salesExist = [];
                              if (empty($salesExist)) {
                                   
                                   $stockParams = array('id' => $productInfo->id, 'qty' => $item_info[8], 'branch_id' => $item_info[4]);
                                   if ($this->stockUpdate($stockParams)) {
                                        // VAT Calculation
                                        
                                        $vatRate = $item_info[10];
                                        
                                        // Sales Entry
                                        $unitPrice =  $item_info[9];
                                        $salesInfo = ['customer_id' => NULL, 'customer_name' => $item_info[6], 'address' => $item_info[7], 'ref_no' => $item_info[1], 'branch_id' => $item_info[4], 'date' => $challanDate];
                                        // return $salesInfo;
                                        $sales = $this->createSales($salesInfo);
                                        // $sales = $sales;
                                        
                                        $salesItem = new  $this->salesItem;
                                        $salesItem->sales_id = $sales->id;
                                        $salesItem->product_id = $productInfo->id;
                                        $salesItem->item_info = json_encode($productInfo);
                                        $salesItem->price = $unitPrice;
                                        $salesItem->qty = $item_info[8];
                                        $salesItem->vat_rate = round($vatRate);
                                        $salesItem->vat_amount = $item_info[11];
                                        $salesItem->total_price = ($salesItem->price*$salesItem->qty);
                                        $salesItem->save();

                                        // Mushok Insert
                                        $mushok_no = 'six_two';
                                        if ($company->business_type == 2) {
                                             $mushok_no = 'six_two_one';
                                        }
                                        $mushokItems = new $this->mushok;
                                         // Mushok Insert
                                        $mushokItems = new $this->mushok;
                                        
                                        $companyLastMushok = $mushokItems::where(['product_id' => $salesItem->product_id, 'company_id' => $company->id, 'mushok' => $mushok_no])
                                        ->where('created_at', '<=', $challanDate)
                                        ->orderBy('created_at', 'DESC')
                                        ->first();

                                        $branchLastMushok = $mushokItems::where(['product_id' => $salesItem->product_id, 'branch_id' => $sales->branch_id, 'mushok' => $mushok_no])
                                        ->where('created_at', '<=', $challanDate)
                                        ->orderBy('created_at', 'DESC')
                                        ->first();

                                        $lastAverage = $mushokItems::where(['product_id' => $salesItem->product_id, 'company_id' => $company->id, 'mushok' => $mushok_no])
                                        ->where('price', '>', 0)
                                        ->avg('price');
                                   
                                        $mushokItems->sales_id      =  $sales->id;
                                        $mushokItems->product_id    = $productInfo->id;
                                        $mushokItems->company_id    = $sales->company_id;
                                        $mushokItems->branch_id    = $sales->branch_id;
                                        $mushokItems->type          = 'debit';
                                        $mushokItems->mushok        = $mushok_no;
                                        $mushokItems->price         = $salesItem->price;
                                        $mushokItems->average_price = !empty($lastAverage)? $lastAverage:0;
                                        $mushokItems->qty           = $salesItem->qty;
                                        $mushokItems->vat_rate     = round($vatRate);
                                        $mushokItems->vat_amount   = $salesItem->vat_amount;
                                        
                                        if (!empty($branchLastMushok)) {
                                             $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                                             $mushokItems->branch_closing = $mushokItems->branch_opening-$mushokItems->qty;
                                        }else{
                                             $mushokItems->branch_opening = $mushokItems->qty;
                                             $mushokItems->branch_closing = $mushokItems->qty;
                                        }
                                        // Company Balance 
                                        $mushokItems->opening_qty   = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
                                        $mushokItems->closing_qty   = ($mushokItems->opening_qty-$mushokItems->qty);
                                        $mushokItems->created_by   = $user->id;
                                        $mushokItems->created_at   = $challanDate? $challanDate : date("Y-m-d H:i:s");

                                        $product = ['id' => $productInfo->id, 'qty' => $mushokItems->qty];
                                        Helper::postDataUpdate($product, $mushokItems->branch_id, $challanDate, $mushokItems->type);
                                        $mushokItems->save();  
                                   }else {
                                        DB::rollBack();  
                                        $returnData['status'] = true;
                                        $returnData['data'] = $wrongSkus;
                                        $returnData['message'] = "Wrong stock";
                                        return $returnData; 
                                   }                      
                              }
                              DB::commit(); 
                         } catch (\Throwable $th) {
                              DB::rollBack();  
                              $returnData['status'] = true;
                              $returnData['data'] = $wrongSkus;
                              $returnData['message'] = $th->getMessage();
                              return $returnData; 
                         } 
                                               
                    }
               }
               // DB::commit();
               $returnData['status'] = true;
               $returnData['data'] = $wrongSkus;
               $returnData['message'] = "Bulk Sales Has been Uploaded";
               return $returnData;
          }
     }

     public function createOutletSales($sales_info) {
          // Posting Date
          $challanDate = $sales_info['date']? date("Y-m-d H:i:s", strtotime($sales_info['date'])):date("Y-m-d H:i:s");
          
          // Posting Date
          
          $user = auth()->user();
                    
          $branch = $this->branch::with('company')
          ->where('id', $sales_info['branch_id'])
          ->first();
          if ($branch->company->business_type == 1) {
               $orderPrefix = $branch->company->order_prefix;
          }else{
               $orderPrefix = $branch->company->order_prefix;
               $orderPrefix = $orderPrefix."-".$branch->order_prefix;
          }
          
          $challanNo = Helper::challanNo($branch->id, $sales_info['date']);
          $sl_no = explode("-",$challanNo); 

          $sales = new $this->model;
          $sales->customer_id     = NULL;
          $sales->customer_code   = NULL;
          $sales->company_id      = $branch->company_id;
          $sales->branch_id       = $branch->id;
          $sales->sales_by        = $user->id;
          $sales->sales_no        = $orderPrefix.'-'.$challanNo;
          $sales->sl_no           = (int) $sl_no[2];
          $sales->reference_no    = $sales_info['ref_no'];
          $sales->customer_name   = $sales_info['customer_name'];
          $sales->customer_email  = NULL;
          $sales->customer_phone  = NULL;
          $sales->customer_address=  $sales_info['address'];
          $sales->shipping_address= NULL;
          $sales->customer_national_id = NULL;
          $sales->ref_name  = NULL;
          $sales->ref_address     = NULL;
          $sales->ref_national_id = NULL;
          $sales->vehicle_no      = NULL;
          $sales->destination_address = $sales_info['address'];
          $sales->challan_date    = $challanDate;    
          $sales->created_at    = $challanDate; 
          $sales->save();
          return $sales;
     }

     public function createSales($item_info) {
          $customerPhone = "01710000000";
          $user = auth()->user();
                    
          $branch = $this->branch::with('company')
          ->where('id', $item_info['branch_id'])
          ->first();
          if ($branch->company->business_type == 1) {
               $orderPrefix = $branch->company->order_prefix;
          }else{
               $orderPrefix = $branch->company->order_prefix;
               $orderPrefix = $orderPrefix."-".$branch->order_prefix;
          }
          
          $challanNo = Helper::challanNo($branch->id, $item_info['date']);
          $sl_no = explode("-",$challanNo); 

          $sales = new $this->model;
          $sales->customer_id     = $item_info['customer_id'];
          $sales->customer_code   = NULL;
          $sales->company_id      = $branch->company_id;
          $sales->branch_id       = $branch->id;
          $sales->sales_by        = $user->id;
          $sales->sales_no        = $orderPrefix.'-'.$challanNo;
          $sales->sl_no           = (int) $sl_no[2];
          $sales->reference_no    = $item_info['ref_no'];
          $sales->customer_name   = $item_info['customer_name'];
          $sales->customer_email  = NULL;
          $sales->customer_phone  = $customerPhone;
          $sales->customer_address= $customerPhone;
          $sales->shipping_address= NULL;
          $sales->customer_national_id = NULL;
          $sales->ref_name  = NULL;
          $sales->ref_address     = NULL;
          $sales->ref_national_id = NULL;
          $sales->vehicle_no      = NULL;
          $sales->destination_address = $customerPhone;
          $sales->challan_date    = date("Y-m-d", strtotime($item_info['date']));      
          $sales->created_at    = $item_info['date']; 
          $sales->save();
          return $sales;
     }

     public function salesReturn($request)
     {
          try {
               
               $validator= Validator::make($request->all(), [
                    'sales_id' => 'required'
               ]);
     
               if( $validator->fails()){
                    return ['status' => false , 'errors' => $validator->errors()];
               } 
               $company = $this->company::where('id', auth()->user()->company_id)->first();
               $mushok_no = 'six_two';
               if ($company->business_type == 2) {
                    $mushok_no = 'six_two_one';
               }
               
               $salesInfo = $this->getById($request->sales_id);
               $user = auth()->user();
               $orderPrefix = $company->order_prefix;

               $branch_id = $user->branch_id? $user->branch_id: $salesInfo->branch_id;
               if ($company->business_type != 1) {
                    $branch = $this->branch::where('id', $salesInfo->branch_id)->first();
                    $orderPrefix = $branch->order_prefix;
               }else{
                    $orderPrefix = $company->order_prefix;
               }
               
               $reChallanNo = Helper::reChallanNo($branch_id, date('Y-m-d'));
               $sl_no = explode("-",$reChallanNo);
                        
               if (!empty($salesInfo)) {
                    DB::beginTransaction();  
                    $return_no    =   $orderPrefix."-CREDIT-".$reChallanNo;
                    $salesReturn = $this->return;
                    $salesReturn->company_id      = auth()->user()->company_id;
                    $salesReturn->sales_id      = $salesInfo->id;
                    $salesReturn->branch_id      = $salesInfo->branch_id;
                    $salesReturn->customer_id      = $salesInfo->customer_id;
                    $salesReturn->return_no     = $return_no;   
                    $salesReturn->sl_no   = (int) $sl_no[2];   

                    $salesReturn->challan_no     = $salesInfo->sales_no;   
                    $salesReturn->reference_no   = $salesInfo->reference_no;   
                    $salesReturn->challan_date   = date('Y-m-d', strtotime($salesInfo->created_at));  

                    $salesReturn->return_reason     = $request->reason;   
                    $salesReturn->created_by    = (int) auth()->user()->id;
                    $salesReturn->created_at    = date("Y-m-d H:i:s");
                    $salesReturn->save();
                    
                    // return $purchaseInfo;
                    foreach ($request->returnedItems as $item) {
                         
                         $companyLastMushok = $this->mushok::where(['product_id' => $item['id'], 'company_id' => $company->id, 'mushok' => $mushok_no])
                         ->where('created_at', '<=', date("Y-m-d H:i:s"))
                         ->orderBy('created_at', 'DESC')
                         ->where('is_transfer', 0)
                         ->latest('id')->first();
                         


                         $branchLastMushok = $this->mushok::where(['product_id' => $item['id'], 'branch_id' => $salesInfo->branch_id, 'mushok' => $mushok_no])
                         ->where('created_at', '<=', date("Y-m-d H:i:s"))
                         ->orderBy('created_at', 'DESC')
                         ->first();


                         $lastAverage = $this->mushok::where(['product_id' => $item['id'], 'company_id' => $company->id, 'mushok' => $mushok_no])
                         ->where('price', '>', 0)
                         ->avg('price');
                         
                         if ($this->whatever($salesInfo->salesItems, 'product_id', $item['id'])) {
                              $salesItem = new $this->salesItem;
                              $salesItem = $salesItem::where(['sales_id' => $salesInfo->id, 'product_id' => $item['id']])->first();
                              $returnItems = new $this->returnItems;
                              $returnItems->sales_return_id    = (int) $salesReturn->id;
                              $returnItems->product_id  = $item['id'];
                              $returnItems->sd       = $item['sd']? $item['sd']: 0;
                              $returnItems->vat_rate = $item['vat_rate'];
                              $returnItems->vat_amount = ((($item['price']* (float) $item['vat_rate'])/100) * $item['qty']);
                              $returnItems->price      = $item['price'];
                              $returnItems->qty        = $item['qty'];

                              $returnItems->challan_item_vat = $salesItem->vat_amount;
                              $returnItems->challan_item_value = $salesItem->total_price;
                              $returnItems->challan_item_qty = $salesItem->qty;

                              $returnItems->total_price = ($item['price']*$item['qty']);
                              $returnItems->created_at        = $salesReturn->created_at;
                              
                              $returnItems->save();
                              
                              // Mushok Insert
                              $totalPrice = ( $item['price'] * $item['qty']);
                              $vatAmount = ($totalPrice * $item['vat_rate'])/100;
                              $mushokItems = new $this->mushok;
                              $mushokItems->sales_return_id    = (int) $salesReturn->id;
                              $mushokItems->sales_id    = (int) $salesInfo->id;
                              $mushokItems->product_id  = $item['id'];
                              $mushokItems->company_id  = $salesInfo->company_id;
                              $mushokItems->branch_id  = $salesInfo->branch_id;
                              $mushokItems->type  = 'credit';
                              $mushokItems->mushok  = $mushok_no;
                              $mushokItems->price    = $item['price'];
                              $mushokItems->average_price = !empty($lastAverage)? $lastAverage:0;
                              $mushokItems->qty         = $item['qty'];
                              $mushokItems->sales_return_qty = $item['qty'];
                              $mushokItems->vat_rate = $item['vat_rate'];
                              $mushokItems->vat_amount = $vatAmount;
                              $mushokItems->sd_amount = $item['sd']? $item['sd']: 0;
                              // Branch Balance
                              // $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                              // $mushokItems->branch_closing = $mushokItems->branch_opening+$mushokItems->qty;
                              if (!empty($branchLastMushok)) {
                                   $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                                   $mushokItems->branch_closing = $mushokItems->branch_opening+$mushokItems->qty;
                              }else{
                                   $mushokItems->branch_opening = 0;
                                   $mushokItems->branch_closing = $mushokItems->qty;
                              }

                              // Company Balance
                              $mushokItems->opening_qty = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
                              $mushokItems->closing_qty = $mushokItems->opening_qty+$mushokItems->qty;
                              $mushokItems->created_by = (int) auth()->user()->id;
                              $mushokItems->created_at = $salesReturn->created_at;
                              // return $mushokItems;
                              $mushokItems->save();
          
                              // Update Stock
                              $this->stockUpdate(array('id' => $item['id'], 'qty' => $item['qty'], 'company_id' => $salesInfo->company_id, 'branch_id' => $salesInfo->branch_id), 'addition');
                              $product = ['id' => $mushokItems->product_id, 'qty' => $mushokItems->qty];
                              Helper::postDataUpdate($product, $mushokItems->branch_id, date('Y-m-d H:i:s'), $mushokItems->type);
                         }else{
                              DB::rollBack();
                              return response()->json([
                                   'status' => false,
                                   'data' => [],
                                   'errors' => '', 
                                   'message' => "Return item is not exist in the sales"
                              ]);
                         }
                         
                    }
                    DB::commit();
                    $returnedInfo = $this->getReturnById($salesReturn->id);
                    return response()->json([
                         'status' => true,
                         'data' => $returnedInfo,
                         'errors' => '', 
                         'message' => $return_no." New sales return has been successfully created",
                    ]);
               }else{
                    return response()->json([
                         'status' => false,
                         'data' => [],
                         'errors' => '', 
                         'message' => "Your purchase number is invalid"
                    ]);
               }
               
          }catch (\Throwable $th) {
               return response()->json([
                    'status' => false,
                    'data' => [],
                    'errors' => '', 
                    'message' => $th->getMessage()
               ]);
                    
          }
     }

     function returnDownload($request, $company_id) {
          if (!empty($company_id)) {
               $query = $this->return::query();
               $query->with('sales.customer','sales.salesItems', 'company', 'sales.branch', 'returnItems.info');
               
               if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
                    $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
               }
               if ((isset($request->branch_id) && $request->branch_id !="")) {
                    $query->where('branch_id', $request->branch_id);
               }
               $query->where('company_id', auth()->user()->company_id);
               $returnList = $query->get();
               return response()->json([
                    'status' => true,
                    'data' => $returnList,
                    'errors' => '', 
                    'message' => "Return list has been loaded"
               ]);
          }else{
               $returnList = $this->return::with('customer', 'sales.company', 'sales.branch', 'sales.customer', 'returnItems.info')->latest()->paginate(20);
               return response()->json([
                    'status' => true,
                    'data' => $returnList,
                    'errors' => '', 
                    'message' => "Return list has been loaded"
               ]);
          }
     }

     function manualCreditNote($request) {
          try {
               $validator= Validator::make($request->all(), [
                    'challan_no' => 'required',
                    'challan_date' => 'required'
               ]);
     
               if( $validator->fails()){
                    return ['status' => false , 'errors' => $validator->errors()];
               } 
               $company = $this->company::where('id', auth()->user()->company_id)->first();
               $mushok_no = 'six_two';
               if ($company->business_type == 2) {
                    $mushok_no = 'six_two_one';
               }
               
               $user = auth()->user();
               $orderPrefix = $company->order_prefix;

               $branch_id = $user->branch_id;

               if ($company->business_type != 1 && $branch_id !="") {
                    $branch = $this->branch::where('id', $branch_id)->first();
                    $orderPrefix = $branch->order_prefix;
               }else{
                    $orderPrefix = $company->order_prefix;
               }
               
               $reChallanNo = Helper::reChallanNo($branch_id);
               $sl_no = explode("-",$reChallanNo);

                    DB::beginTransaction();  
                    $return_no    =   $orderPrefix."-CREDIT-".$reChallanNo;
                    $salesReturn = $this->return;
                    $salesReturn->company_id    = $company->id;
                    $salesReturn->branch_id      = $branch_id;
                    $salesReturn->customer_id    = $request->customer_id;
                    $salesReturn->sales_id      = NULL;
                    $salesReturn->return_no     = $return_no;   
                    $salesReturn->sl_no          = (int) $sl_no[2];   
                    $salesReturn->return_reason  = $request->reason;   
                    $salesReturn->challan_no     = $request->challan_no;   
                    $salesReturn->reference_no   = $request->reference_no;   
                    $salesReturn->challan_date   = date('Y-m-d', strtotime($request->challan_date));   
                    $salesReturn->created_by     = (int) auth()->user()->id;
                    $salesReturn->created_at     = date("Y-m-d H:i:s");

                    // return $salesReturn;
                    $salesReturn->save();
                    
                    // return $purchaseInfo;
                    foreach ($request->returnedItems as $item) {
                         
                         $companyLastMushok = $this->mushok::where(['product_id' => $item['id'], 'company_id' => $company->id, 'mushok' => $mushok_no])
                         ->where('created_at', '<=', $salesReturn->created_at)
                         ->orderBy('created_at', 'DESC')
                         ->first();
                         
                         $branchLastMushok = $this->mushok::where(['product_id' => $item['id'], 'branch_id' => auth()->user()->branch_id, 'mushok' => $mushok_no])
                         ->where('created_at', '<=', $salesReturn->created_at)
                         ->orderBy('created_at', 'DESC')
                         ->first();
                         $lastAverage = $this->mushok::where(['product_id' => $item['id'], 'company_id' => $company->id, 'mushok' => $mushok_no])
                         ->where('price', '>', 0)
                         ->avg('price');

                              $returnItems = new $this->returnItems;
                              $returnItems->sales_return_id = (int) $salesReturn->id;
                              $returnItems->product_id  = $item['id'];
                              $returnItems->sd       = 0;
                              $returnItems->challan_item_vat = $item['challan_vat'];
                              $returnItems->challan_item_value = $item['challan_value'];
                              $returnItems->challan_item_qty = $item['challan_qty'];

                              $returnItems->vat_rate = $item['vat_rate'];
                              $returnItems->vat_amount = $item['return_vat'];
                              $returnItems->price = ($item['return_value'] / $item['return_qty']);
                              $returnItems->qty = $item['return_qty'];
                              $returnItems->total_price = $item['return_value'];
                              $returnItems->created_at = $salesReturn->created_at;
                              $returnItems->save();
                              
                              // Mushok Insert
                              $mushokItems = new $this->mushok;
                              $mushokItems->sales_return_id    = $salesReturn->id;
                              $mushokItems->sales_id    = NULL;
                              $mushokItems->product_id  = $item['id'];
                              $mushokItems->company_id  = $company->id;
                              $mushokItems->branch_id  = $branch_id;
                              $mushokItems->type  = 'credit';
                              $mushokItems->mushok  = $mushok_no;
                              $mushokItems->price    = $returnItems->price;
                              $mushokItems->average_price = !empty($lastAverage)? $lastAverage:0;
                              $mushokItems->qty         = $returnItems->qty;
                              $mushokItems->sales_return_qty = $returnItems->qty;
                              $mushokItems->vat_rate = $item['vat_rate'];
                              $mushokItems->vat_amount = $returnItems->vat_amount;
                              
                              if (!empty($branchLastMushok)) {
                                   $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                                   $mushokItems->branch_closing = $mushokItems->branch_opening+$mushokItems->qty;
                              }else{
                                   $mushokItems->branch_opening = 0;
                                   $mushokItems->branch_closing = $mushokItems->qty;
                              }

                              // Company Balance
                              $mushokItems->opening_qty = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
                              $mushokItems->closing_qty = $mushokItems->opening_qty+$mushokItems->qty;
                              $mushokItems->created_by = (int) auth()->user()->id;
                              $mushokItems->created_at = $salesReturn->created_at;
                              // return $mushokItems;
                              $mushokItems->save();
          
                              // Update Stock
                              $this->stockUpdate(array('id' => $item['id'], 'qty' => $mushokItems->qty, 'company_id' => $company->id, 'branch_id' => $branch_id), 'addition');
                              $product = ['id' => $mushokItems->product_id, 'qty' => $mushokItems->qty];
                              // Helper::postDataUpdate($product, $mushokItems->branch_id, $salesReturn->created_at, $mushokItems->type);
                              Helper::postDataUpdate2($mushokItems);
                        
                         
                    }
                    DB::commit();
                    $returnedInfo = $this->getReturnById($salesReturn->id);
                    return response()->json([
                         'status' => true,
                         'data' => $returnedInfo,
                         'errors' => '', 
                         'message' => $return_no." New sales return has been successfully created",
                    ]);
               
               
          }catch (\Throwable $th) {
               return response()->json([
                    'status' => false,
                    'data' => [],
                    'errors' => '', 
                    'message' => $th->getMessage()
               ]);
                    
          }
     }
     function whatever($array, $key, $val) {
          foreach ($array as $item)
               if (isset($item[$key]) && $item[$key] == $val)
                    return true;
          return false;
     }

    /**
      * specified resource update
      *
      * @param int $id
      * @param  $request
      * @return \Illuminate\Http\Response
      */

     public function update( int $id,  $request){
          $order = $this->model->find($id);
          if ($order->order_status == 'delivered') {
               return false;
          }elseif ($order->order_status == 'declined') {
               return false;
          }
          $order->order_status = $request['title'];
          return $order->update();
     }

     function salesUpdate($request) {
          try {
               $sales = $this->model->find($request->sales_id);
               $sales->customer_name = $request->customer_name;
               $sales->shipping_address = $request->shipping_address;
               $sales->destination_address = $request->shipping_address;
               $sales->reference_no = $request->ref_no;
               $sales->vehicle_no = $request->vehicle_no;
               $sales->sales_no = $request->sales_no;
               $sales->note = $request->note;
               $sales->printed = $request->printed;
               $sales->is_exported = $request->is_exported==1? 1:0;
               $sales->update();
               
               $this->salesItem::where('sales_id', $request->sales_id)->update(['is_vat_exempted' => $sales->is_exported == 1?0:1]);

               return response()->json([
                    'status' => true,
                    'data' => $this->getById($sales->id),
                    'errors' => '', 
                    'message' => $sales->sales_no." sales has been updated!",
               ]); 
               
          } catch (\Throwable $th) {
               return response()->json([
                    'status' => false,
                    'data' => [],
                    'errors' => '', 
                    'message' => $th->getMessage(),
               ]); 
          }
     }

     function updateItem($request) {
          try {
               $sales = $this->model->with('salesItems')->find($request->sales_id);
               foreach ($sales->salesItems as $key => $item) {
                    // return $item->product_id;
                    if ($item->product_id == $request->salesItem['id']) {

                         if ($item->qty > $request->salesItem['qty']) {
                              return ['Qty Smaller'];
                         }elseif ($item->qty < $request->salesItem['qty']) {
                              return ['Qty Bigger'];
                         }
                         // Update Sales Item
                         $salesItem = $this->salesItem->find($item->id);
                         $salesItem->price = $request->salesItem['price'];
                         $salesItem->qty = $request->salesItem['qty'];
                         $salesItem->vat_rate = $request->salesItem['vat_rate'];
                         $salesItem->total_price = ($salesItem->price*$salesItem->qty);

                         $salesItem->vat_amount = ($salesItem->total_price*$salesItem->vat_rate)/100;
                         $salesItem->update();
                         // Update Mushok
                         $mushokItem = $this->mushok->where('product_id', $item->product_id)
                         ->where('sales_id', $item->sales_id)->first();

                         $mushokItem->price = $request->salesItem['price'];
                         $mushokItem->qty = $request->salesItem['qty'];
                         $mushokItem->vat_rate = $request->salesItem['vat_rate'];

                         $mushokItem->vat_amount = ($salesItem->total_price*$salesItem->vat_rate)/100;
                         $mushokItem->update();
                         return response()->json([
                              'status' => true,
                              'data' => $this->getById($sales->id),
                              'errors' => '', 
                              'message' => $sales->sales_no." sales item has been updated!",
                         ]); 
                         
                    }
               }
               
          } catch (\Throwable $th) {
               return response()->json([
                    'status' => false,
                    'data' => [],
                    'errors' => '', 
                    'message' => $th->getMessage(),
               ]); 
          }
     }
        
     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function delete($request){
          try {
               $sales = $this->getById($request->id);
               if ($sales->company_id != auth()->user()->company_id) {
                    return response()->json([
                         'status' => false,
                         'data' => [],
                         'errors' => '', 
                         'message' => "You have no access to delete this sales",
                    ]);
               }

               DB::beginTransaction();
               $this->stockUpdateMultiple($sales);
               
               if (!empty($sales)) {
                    $sales->delete();
               }    
               DB::commit();
               return response()->json([
                    'status' => true,
                    'data' => [],
                    'errors' => '', 
                    'message' => "Your order has been deleted"
               ]);
          } catch (\Throwable $th) {
               return response()->json([
                    'status' => false,
                    'data' => [],
                    'errors' => '', 
                    'message' => $th->getMessage(),
               ]);
          }
          
     }

     function deleteSalesBulk($request){
          try {
               $user = auth()->user();
               $startDate = $request->start_date? date('Y-m-d 00:00:00', strtotime($request->start_date)):date('Y-m-d 00:00:00');
               $endDate = $request->end_date? date('Y-m-d 23:59:59', strtotime($request->end_date)):date('Y-m-d 23:59:59');
               $sales = $this->model::with('salesItems')->where('company_id', $user->company_id)
               ->whereNotIn('branch_id', [21,22])
               ->whereBetween('created_at', [$startDate, $endDate])
               ->take(50)
               ->get();
               
               $i = 0;
               foreach ($sales as $key => $sale) {
                    foreach ($sale->salesItems as $key => $item) {
                         $this->deleteSalesItemBulk($item->id);                         
                    }
                    $sale->delete();
                    $i++;
               }
               return $i;

          } catch (\Throwable $th) {
               return response()->json([
                    'status' => false,
                    'data' => [],
                    'errors' => '', 
                    'message' => $th->getMessage(),
               ]);
          }
     }

     function deleteSalesItemBulk($id) {
          try {
               // $sales = $this->model::with('salesItems')->find($item_id);
               $salesItem = $this->salesItem::with('sales')->find($id);
               $stock = new $this->stock;
               $itemStock = $stock::where(['branch_id'=> $salesItem->sales->branch_id, 'product_id' => $salesItem->product_id])
               ->orderBy('stock', 'desc')
               ->first();
               $itemStock->stock = ($itemStock->stock+$salesItem->qty);
               $itemStock->update();
               $product = ['id' => $salesItem->product_id, 'qty' => $salesItem->qty];
               // Delete Order's item
               $item = new $this->salesItem;
               $item::where(['sales_id' => $salesItem->sales_id, 'product_id' => $salesItem->product_id])->delete();
               // Delete Mushok
               $this->mushok::where(['sales_id' => $salesItem->sales_id, 'product_id' => $salesItem->product_id])->delete();

               // Update Mushok 6.2
               Helper::postDataUpdateOnDelete($product, $salesItem->sales->branch_id, date('Y-m-d H:i:s', strtotime($salesItem->created_at)), 'credit');
               return true;
          } catch (\Throwable $th) {
               return response()->json([
                    'status' => false,
                    'data' => [],
                    'message' => $th->getMessage()
               ]);
          }
     }

     function removeSalesItem($request) {
          try {
               $sales = $this->model::with('salesItems')->find($request->sales_id);
               $salesItem = $this->salesItem::where(['sales_id' => $request->sales_id, 'product_id' => $request->item_id])->first();
               
               $stock = new $this->stock;
               $itemStock = $stock::where(['branch_id'=> $sales->branch_id, 'product_id' => $salesItem->product_id])->first();
               $itemStock->stock = ($itemStock->stock+$salesItem->qty);
               $itemStock->update();
               $product = ['id' => $salesItem->product_id, 'qty' => $salesItem->qty];
               // Delete Order's item
               $item = new $this->salesItem;
               $item::where(['sales_id' => $request->sales_id, 'product_id' => $request->item_id])->delete();
               // Delete Mushok
               $this->mushok::where(['sales_id' => $request->sales_id, 'product_id' => $request->item_id])->delete();

               // Update Mushok 6.2
               Helper::postDataUpdateOnDelete($product, $sales->branch_id, date('Y-m-d H:i:s', strtotime($sales->created_at)), 'credit');
               
               return response()->json([
                    'status' => true,
                    'data' => $this->getById($request->sales_id),
                    'message' => "Item has been successfully removed"
               ]);
          } catch (\Throwable $th) {
               return response()->json([
                    'status' => false,
                    'data' => [],
                    'message' => $th->getMessage()
               ]);
          }
     }

     function addSalesItem($request) {
          try {
               $sales = $this->model::with('salesItems')->find($request->sales_id);
               $productInfo = $this->product::select('id', 'title', 'sku','model', 'type', 'price', 'status')
               ->where('id', $request->id)->first();
               $company = $this->company::where('id', $sales->company_id)->first();

               $mushok_no = 'six_two';
               if ($company->business_type == 2) {
                    $mushok_no = 'six_two_one';
               }
               $salesItem = $this->salesItem::where(['sales_id' => $request->sales_id, 'product_id' => $request->id])->first();

               // if (empty($salesItem)) {
                    $stockParams = array('id' => $productInfo->id, 'qty' => $request->qty, 'company_id' => $sales->company_id, 'branch_id' => $sales->branch_id);
                    // return $stockParams;
                    DB::beginTransaction();
                    $checkStock = $this->stockUpdate($stockParams);
                    if ($checkStock || $productInfo->type == 4) {
                         $salesItem = new  $this->salesItem;
                         $salesItem->sales_id     = (int) $sales->id;
                         $salesItem->product_id   = $request->id;
                         $salesItem->item_info    = json_encode($productInfo);
                         $salesItem->price        = $request->price;
                         $salesItem->qty          = $request->qty;
                         $salesItem->vat_rate     = $request->vat_rate;
                         $salesItem->is_vat_exempted = ($request->vat_rate < 1 && $request->is_exported !=1)? 1:0;
                         $salesItem->vat_amount   = ((( $salesItem->price * $salesItem->vat_rate)/100)* $salesItem->qty);
                         $salesItem->total_price = ($salesItem->price * $salesItem->qty);
                         $salesItem->created_at = $sales->created_at;
                         $salesItem->save();

                         // Mushok Insert
                         $mushokItems = new $this->mushok;
                         $companyLastMushok = $mushokItems::where(['product_id' => $salesItem->product_id, 'company_id' => $company->id, 'mushok' => $mushok_no])
                         ->where('created_at', '<=', $sales->created_at)
                         ->orderBy('created_at', 'desc')
                         ->where('is_transfer', 0)
                         ->first();

                         $branchLastMushok = $mushokItems::where(['product_id' => $salesItem->product_id, 'branch_id' => $sales->branch_id, 'mushok' => $mushok_no])
                         ->where('created_at', '<=', $sales->created_at)
                         ->orderBy('created_at', 'desc')
                         ->first();

                         $lastAverage = $mushokItems::where(['product_id' => $salesItem->product_id, 'company_id' => $company->id, 'mushok' => $mushok_no])
                         ->where('price', '>', 0)
                         ->avg('price');
                         
                         $mushokItems->sales_id      =  $sales->id;
                         $mushokItems->product_id    = $salesItem->product_id;
                         $mushokItems->company_id    = $sales->company_id;
                         $mushokItems->branch_id    = $sales->branch_id;
                         $mushokItems->type          = 'debit';
                         $mushokItems->mushok        = $mushok_no;
                         $mushokItems->price         = $salesItem->price;
                         $mushokItems->average_price = !empty($lastAverage)? $lastAverage:0;
                         $mushokItems->qty           = $salesItem->qty;
                         $mushokItems->vat_rate     = $salesItem->vat_rate;
                         $mushokItems->vat_amount   = $salesItem->vat_amount;
                         // BRANCH balance
                         // $mushokItems->branch_opening   = !empty($branchLastMushok)? $branchLastMushok->closing_qty:0;
                         // $mushokItems->branch_closing   = $mushokItems->branch_opening-$orderItem->qty;
                         if (!empty($branchLastMushok)) {
                              $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                              $mushokItems->branch_closing = $mushokItems->branch_opening-$mushokItems->qty;
                         }else{
                              $mushokItems->branch_opening = $mushokItems->qty;
                              $mushokItems->branch_closing = $mushokItems->qty;
                         }
                         // Company Balance 
                         $mushokItems->opening_qty   = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
                         $mushokItems->closing_qty   = $mushokItems->opening_qty-$salesItem->qty;
                         $mushokItems->created_by    = (int) auth()->user()->id;
                         $mushokItems->created_at    = $sales->created_at;
                         $product = ['id' => $productInfo->id, 'qty' => $mushokItems->qty];
                         $mushokItems->save();
                         Helper::postDataUpdate2($mushokItems);
                    }
                    DB::commit();
                    return response()->json([
                         'status' => true,
                         'data' => $this->getById($request->sales_id),
                         'message' => "Item has been successfully removed"
                    ]);
               // }
               // return response()->json([
               //      'status' => false,
               //      'data' => [],
               //      'message' => "Item already added"
               // ]);
               
          } catch (\Throwable $th) {
               return response()->json([
                    'status' => false,
                    'data' => [],
                    'message' => $th->getMessage()
               ]);
          }
     }

     protected function stockUpdateMultiple($sales){    
          foreach ($sales->salesItems as $key => $item) {
               $stock = new $this->stock;
               $itemStock = $stock::where(['branch_id'=> $sales->branch_id, 'product_id' => $item->product_id])
               ->orderBy('stock', 'desc')
               ->first();
               $itemStock->stock = ($itemStock->stock+$item->qty);
               $itemStock->update();
               $product = ['id' => $item->product_id, 'qty' => $item->qty];
          
               Helper::postDataUpdateOnDelete($product, $sales->branch_id, date('Y-m-d H:i:s', strtotime($sales->created_at)), 'credit');
          }   
          return true;
     }
     protected function stockUpdate($item, $type = "deduction")
     {   
          $stock = new $this->stock;
          $stockInfo = $stock::where(['product_id'=> $item['id'], 'branch_id'=> $item['branch_id']])->first();
          
          if ($type == 'deduction') {
               if (!empty($stockInfo)) {
                    if($stockInfo->stock >= $item['qty']){
                         $stockInfo->stock -= $item['qty'];
                         $stockInfo->update();
                         return true;
                    }else{
                         return false;
                    }
               }
          }elseif($type == 'addition' && !empty($stockInfo)){
               
               $stockInfo->stock += $item['qty'];
               $stockInfo->update();
               return true;
          }else {
               $stock = new $this->stock;                 
               $stockInfo = $stock::where(['product_id'=> $item['id'], 'company_id'=> $item['company_id'], 'branch_id'=> $item['branch_id']])->first();
               
               $stock->product_id = $item['id'];
               $stock->company_id = $item['company_id'];
               $stock->branch_id = $item['branch_id'];
               $stock->stock = $item['qty'];
               $stock->save();
               return true;
          }
     }

     public function draftSales($request)
     {
          try {
               $orderPrefix = "FTL";
               if (!empty($request->branch_id)) {
                    $branch = $this->branch::where('id', $request->branch_id)->first();
                    $orderPrefix = $branch->order_prefix;
               }
               $challanNo = Helper::challanNo($request->branch_id);

               DB::beginTransaction();              
               $sales = $this->model;
               $sales->customer_id     = $request->customer_id;
               $sales->customer_code   = $request->customer_code;
               $sales->company_id      = (int) auth()->user()->company_id;
               $sales->branch_id       = $request->branch_id;
               $sales->sales_by        = (int) auth()->user()->id;;
               $sales->sales_no        = $orderPrefix.date('ymdHis').rand(10,20);
               $sales->reference_no    = $request->reference_no;
               $sales->customer_name   = $request->customer_name;
               $sales->customer_email  = $request->customer_email;
               $sales->customer_phone  = $request->customer_phone;
               $sales->customer_address= $request->customer_address? $request->customer_address: NULL;
               $sales->customer_national_id   = $request->customer_national_id? $request->customer_national_id:NULL;
               $sales->ref_name  = $request->ref_name;
               $sales->status = 0;
               $sales->ref_address      = $request->ref_address;
               $sales->ref_national_id      = $request->ref_national_id;
               $sales->vehicle_no      = $request->vehicle_no? $request->vehicle_no: NULL;
               $sales->destination_address      = $request->destination_address;
               // return $sales;
               $sales->save();

               foreach ($request->salesItems as $item) {
                    $productInfo = $this->product::select('id', 'title', 'sku','model', 'type', 'price', 'status')
                         ->where('id', $item['id'])->first();
                    $checkStock = FALSE;
                    if ($productInfo->type != 4) {
                         $stockParams = array('id' => $productInfo->id, 'qty' => $item['qty'], 'company_id' => auth()->user()->company_id, 'branch_id' => $request->branch_id);
                         $checkStock = $this->stockUpdate($stockParams, 'update');
                    }
                    if ($checkStock || $productInfo->type == 4) {
                         $orderItem = new  $this->salesItem;
                         $orderItem->sales_id     = (int) $sales->id;
                         $orderItem->product_id   = $item['id'];
                         $orderItem->item_info    = json_encode($productInfo);
                         $orderItem->price        = $item['price'];
                         $orderItem->qty          = $item['qty'];
                         $orderItem->vat_rate     = $item['vat_rate'];
                         $orderItem->vat_amount   = ((( $item['price']* (float) $item['vat_rate'])/100)* $item['qty']);
                         $orderItem->total_price = ( $item['price'] * $item['qty']);
                         $orderItem->save();   
                    }else{
                         DB::rollBack();
                         return "Sales item stock is not available";
                    }                    
               }
               DB::commit();
               return "A Sales has been successfully completed";
          } catch (\Throwable $th) {
               return $th->getMessage();
          }
     }

     public function draftUpdate($request)
     {
          try {
               $orderPrefix = "FTL";
               if (!empty($request->branch_id)) {
                    $branch = $this->branch::where('id', $request->branch_id)->first();
                    $orderPrefix = $branch->order_prefix;
               }
               $sales = $this->getById($request->sales_id);
               DB::beginTransaction();  
               $sales->customer_id     = $request->customer_id;
               $sales->customer_code     = $request->customer_code;
               $sales->company_id      = (int) auth()->user()->company_id;
               $sales->branch_id       = $request->branch_id;
               $sales->sales_by        = (int) auth()->user()->id;;
               $sales->sales_no        = $orderPrefix.date('ymdHis').rand(10,20);
               $sales->reference_no   = $request->reference_no;
               $sales->customer_name   = $request->customer_name;
               $sales->customer_email  = $request->customer_email;
               $sales->customer_phone  = $request->customer_phone;
               $sales->customer_address= $request->customer_address? $request->customer_address: NULL;
               $sales->customer_national_id   = $request->customer_national_id? $request->customer_national_id:NULL;
               $sales->ref_name  = $request->ref_name;
               $sales->status = 0;
               $sales->ref_address      = $request->ref_address;
               $sales->ref_national_id      = $request->ref_national_id;
               $sales->vehicle_no      = $request->vehicle_no? $request->vehicle_no: NULL;
               $sales->created_at = date('Y-m-d', strtotime($request->challan_date));
               $sales->destination_address      = $request->destination_address;
               $sales->update();
               foreach ($request->salesItems as $item) {
                    $orderItem = new $this->salesItem;
                    $orderItem = $orderItem::where('id', $item['sales_item_id'])->first();
                    $orderItem->price        = $item['price'];
                    $orderItem->qty          = $item['qty'];
                    $orderItem->vat_rate     = $item['vat_rate'];
                    $orderItem->vat_amount   = ((( $item['price']* (float) $item['vat_rate'])/100)* $item['qty']);
                    $orderItem->total_price = ( $item['price'] * $item['qty']);
                    $orderItem->update();   
                                      
               }
               DB::commit();
               return $this->getById($sales->id);
          } catch (\Throwable $th) {
               return $th->getMessage();
          }
     }

     public function draftSalesComplete($sales_id)
     {
          if (!empty($sales_id)) {
               $salesInfo = $this->getById($sales_id);
               return $salesInfo;
               if (!empty($salesInfo)) {

                    $productInfo = $this->product::select('id', 'title', 'sku','model', 'type', 'price', 'status')
                    ->where('id', $item['id'])->first();
                    $stockParams = array('id' => $productInfo->id, 'qty' => $item['qty'], 'company_id' => auth()->user()->company_id, 'branch_id' => $request->branch_id);
                    // return $stockParams;
                    $checkStock = $this->stockUpdate($stockParams);
                    return true;
               }
               
          }
          return false;
     }

     // Mushok Sub Forms
     /**
     * all resource get
     * @return Collection
     */
     //  Sub-form for local Supply (for note 1,3,4,5,7,10,12,14,18,19,20 and 21)  							
     public function salesSubForm($request, $company_id, $exempted = 0){
          
          $data['start_date'] = date('Y-m-01 00:00:00');
          $data['end_date'] = date('Y-m-d 23:59:59');
          
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
               $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
          }

          $company = $this->company::find($company_id);
          $exempted = 0;

          if ($request->percentage < 1) {
               $exempted = $request->exempted;
          }
          DB::enableQueryLog(); // Enable query log
          $query = $this->salesItem::join('sales', 'sales_items.sales_id', '=', 'sales.id')
               ->leftJoin('products', 'sales_items.product_id', '=', 'products.id')
               ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
               ->leftJoin('hs_codes', 'products.hs_code_id', '=', 'hs_codes.id')
               ->select(
               'sales_items.id', 'sales_items.sales_id', 'sales_items.product_id', 'sales_items.ait', 'sales_items.price', 'sales_items.qty', 
               'sales_items.vat_rate', 'sales_items.vat_amount', 'sales_items.total_price', 'sales_items.created_at', 'products.title', 'products.sku', 'products.hs_code_id', 'categories.name as category_name',
     'hs_codes.code', 'hs_codes.code_dot', 'hs_codes.description'
               )
               ->selectRaw('sum(sales_items.total_price) as total_value')
               ->selectRaw('sum(sales_items.vat_amount) as total_vat_amount')
               ->where('sales.company_id', $company_id);
               if ($company->business_type == 1) {
                    if ($request->percentage == 0) {
                         $query->where('sales_items.vat_rate', $request->percentage);
                    }elseif($request->percentage > 0 && $request->percentage < 15){
                         $query->whereBetween('sales_items.vat_rate', [5, 14]);
                    }else{
                         $query->where('sales_items.vat_rate', $request->percentage);
                    }
               }elseif($company->business_type == 2){
                    $query->where('sales_items.vat_rate', $request->percentage);
               }

               // $query->where('sales_items.vat_rate', $request->percentage);
               if ($request->percentage == 0) {
                    $query->where('sales_items.is_vat_exempted', '=', $exempted);
               }
               
               $query->whereBetween('sales_items.created_at', [date('Y-m-d 00:00:00',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))])
               // ->groupBy('products.hs_code_id');
               ->groupBy('products.title');
               $data = $query->get();
               return response()->json([
                    'status' => true,
                    'company' => $company,
                    'data' => $data,                    
                    'message' => DB::getQueryLog()
               ]);
     }

     public function comparisonReport($request, $company_id) {
          $previous_year_start = date("Y-m-d", strtotime("$request->start_date -1 year"));
          $previous_year_end = date("Y-m-d", strtotime("$request->end_date -1 year"));
         
          $data['previous_year'] = $this->salesItem::with('sales')
          ->select(DB::raw('sum(total_price) as total_value'), DB::raw('sum(vat_amount) as total_vat'), DB::raw("DATE_FORMAT(created_at, '%m-%Y') month_year"), DB::raw("DATE_FORMAT(created_at, '%Y') year"))
          ->whereHas('sales', function($query) use ($company_id) {
               $query->where('company_id', $company_id);
          })
          ->whereBetween('created_at', ["{$previous_year_start}", "{$previous_year_end}"])
          ->groupBy('month_year')
          ->get();

          $data['current_year'] = $this->salesItem::with('sales')
          ->select(DB::raw('sum(total_price) as total_value'), DB::raw('sum(vat_amount) as total_vat'), DB::raw("DATE_FORMAT(created_at, '%m-%Y') month_year"), DB::raw("DATE_FORMAT(created_at, '%Y') year"))
          ->whereHas('sales', function($query) use ($company_id) {
               $query->where('company_id', $company_id);
          })
          ->whereBetween('created_at', ["{$request->start_date}", "{$request->end_date}"])
          ->groupBy('month_year')
          ->get();
          return response()->json([
               'status' => true,
               'data' => $data,                    
               'message' => "Report has been loaded"
          ]);
     }

     function customerStatement($request, $company_id) {
          // return $request->all();
          $data['start_date'] = date('Y-m-01 00:00:00');
          $data['end_date'] = date('Y-m-d 23:59:59');
          
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
               $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
          }
          $query = $this->customer::query();
          $query->join("sales", "customers.id","=","sales.customer_id")
          ->join('sales_items', 'sales.id', '=', 'sales_items.sales_id')
          ->join('products', 'sales_items.product_id', '=', 'products.id')
          ->select('customers.id','customers.company_id','customers.name', 'customers.bin', 'sales.branch_id', 'sales.customer_id', 'sales.created_at')
          ->selectRaw('sum(sales_items.qty) as total_qty')
          ->selectRaw('sum(sales_items.total_price) as total_value')
          ->selectRaw('sum(sales_items.vat_amount) as total_vat')
          ->where('sales.company_id', $company_id);
          if($request->has('branch_id') && $request->branch_id != '')
          {
               $query->where('sales.branch_id', $request->branch_id);
          }
          if($request->has('customer_id') && $request->customer_id != '')
          {
               $query->where('sales.customer_id', $request->customer_id);
          }
          if($request->has('product_id') && $request->product_id != '')
          {
               $query->where('sales_items.product_id', $request->product_id);
          }
          $query->whereBetween('sales.created_at', [$data['start_date'], $data['end_date']]);
          $query->groupBy('sales.customer_id');
          $query->orderBy('total_vat', 'desc');
          $salesStatement = $query->get();
          return response()->json([
               'status' => true,
               'data' => $salesStatement,                    
               'message' => "Report has been loaded"
          ]);
     }

     function productSalesStatement($request, $company_id) {
          $data['start_date'] = date('Y-m-01 00:00:00');
          $data['end_date'] = date('Y-m-d 23:59:59');
          
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
               $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
          }
          $query = $this->product::query();
          $query->join("sales_items", "products.id","=","sales_items.product_id")
          ->join("sales", "sales_items.sales_id","=","sales.id")
          ->join("customers", "sales.customer_id","=","customers.id")          
          ->select('products.id','products.title','products.sku', 'products.price', 'sales.branch_id', 'sales.customer_id', 'sales.created_at')
          ->selectRaw('sum(sales_items.qty) as total_qty')
          ->selectRaw('sum(sales_items.total_price) as total_value')
          ->selectRaw('sum(sales_items.vat_amount) as total_vat')
          ->where('sales.company_id', $company_id);
          if($request->has('branch_id') && $request->branch_id != '')
          {
               $query->where('sales.branch_id', $request->branch_id);
          }
          if($request->has('customer_id') && $request->customer_id != '')
          {
               $query->where('sales.customer_id', $request->customer_id);
          }
          if($request->has('product_id') && $request->product_id != '')
          {
               $query->where('sales_items.product_id', $request->product_id);
          }
          $query->whereBetween('sales.created_at', [$data['start_date'], $data['end_date']]);
          $query->groupBy('products.id');
          $query->orderBy('total_vat', 'desc');
          $salesStatement = $query->get();
          return response()->json([
               'status' => true,
               'data' => $salesStatement,                    
               'message' => "Report has been loaded"
          ]);
     }
}
