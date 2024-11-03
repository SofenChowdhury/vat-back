<?php
namespace App\Repositories;

use App\Models\Branch;
use App\Classes\Helper;
use App\Models\Company;
use App\Models\Product;
use App\Models\NonOrder;
use App\Models\Transfer;
use App\Models\ItemStock;
use App\Models\MushokSix;
use App\Models\PurchaseItem;

use App\Models\TransferItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class TransferRepository implements BaseRepository{

     protected  $model;
     protected  $transferItem;
     protected  $product;
     protected  $stock;
     protected  $mushok;
     protected  $purchaseReturn;
     protected  $purchaseReturnItem;
     protected  $salesReturn;
     protected  $salesReturnItem;
     protected  $user;
     protected  $company;
     protected  $branch;
     protected  $purchaseItem;
     protected  $nonOrder;
     
     public function __construct(Transfer $model, TransferItem $transferItem, Product $product, ItemStock $stock, MushokSix $mushok, Company $company, Branch $branch, PurchaseItem $purchaseItem, NonOrder $nonOrder)
     {
        $this->model = $model;
        $this->transferItem = $transferItem;
        $this->product = $product;
        $this->stock = $stock;
        $this->mushok = $mushok;
        $this->company = $company;
        $this->branch = $branch;
        $this->purchaseItem = $purchaseItem;
        $this->nonOrder = $nonOrder;
        $this->user = auth()->user();
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll($company_id = NULL){ 
          $user = auth()->user();        
          if (!empty($user->company_id)) {
               return $this->model::with('fromBranch.company', 'toBranch')
               ->where('company_id', $user->company_id)
               ->latest()->paginate(20);
          }else{
               return $this->model::with('fromBranch.company', 'toBranch')->latest()->paginate(20);
          }
     }

     public function search($request) {
          // $data['start_date'] = date('Y-m-01 00:00:00');
          // $data['end_date'] = date('Y-m-d 23:59:59');
          
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
               $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
          }

          $user = auth()->user();
          $query = $this->model::query();
          $query->with('fromBranch.company', 'toBranch', 'user');
          
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
          }
          
          
          if ((!empty($request->branch_from) && $request->branch_from !="")) {
               $query->where('branch_from_id', $request->branch_from);
          }
          if ((!empty($request->branch_to) && $request->branch_to !="")) {
               $query->where('branch_to_id', $request->branch_to);
          }

          if ($user->company_id> 0) {
               $query->where('company_id', $user->company_id);
          }  

          // if ($user->branch_id> 0) {
          //      $query->where('branch_from_id', $user->branch_from);
          // }
          if ($request->reference_no != "") {
               $query->where('reference_no', 'LIKE', '%' . $request->reference_no . '%');
          }
          if ($request->transfer_no != "") {
               $query->where('transfer_no', 'LIKE', '%' . $request->transfer_no . '%');
          }
          
          return $query->latest()->paginate(20);
     }

     /**
      * all resource get
      * @return Collection
      */
      public function getFull($company_id = NULL){
        if (!empty($company_id)) {
            return $this->model::with('fromBranch.company', 'toBranch', 'user')
            ->where('company_id', $company_id)
            ->latest()->get();
        }else{
            return $this->model::with('fromBranch.company', 'toBranch')->latest()->get();
        }
    }

     /**
      * all resource get
      * @return Collection
      */
      public function getLatest($company_id = NULL){
          $user = $this->user;
          if ($user->company_id != "") {
               return $this->model::with('fromBranch.company', 'toBranch', 'transferItems.itemInfo.hscode', 'user')
               ->where('company_id', $user->company_id)->take(20)->get();
          }else{
               return $this->model::take(20)->get();
          }
          
     }
     
     /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function getById($id, $company_id = NULL){
          return $this->model::with('fromBranch.company', 'toBranch', 'transferItems.itemInfo.hscode', 'user')
          ->where('id', $id)->first();
     }

     public function getByChallan($challan){
          return $this->model::with('fromBranch.company', 'toBranch', 'transferItems.itemInfo.hscode', 'user')
          ->where('transfer_no', $challan)->first();
     }

     public function download($request){
          $user = auth()->user();
          if (!empty($user->company_id)) {
               $query = $this->model::query();
               $query->with('fromBranch.company', 'toBranch', 'transferItems.itemInfo.hscode', 'user');
                              
               if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
                    $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
               }
               if ((!empty($request->branch_from) && $request->branch_from !="")) {
                    $query->where('branch_from_id', $request->branch_from);
               }
               if ((!empty($request->branch_to) && $request->branch_to !="")) {
                    $query->where('branch_to_id', $request->branch_to);
               }

               if ($user->company_id> 0) {
                    $query->where('company_id', $user->company_id);
               }  

               // if ($user->branch_id> 0) {
               //      $query->where('branch_from_id', $user->branch_from);
               // }
               if ($request->reference_no != "") {
                    $query->where('reference_no', 'LIKE', '%' . $request->reference_no . '%');
               }
               if ($request->transfer_no != "") {
                    $query->where('transfer_no', 'LIKE', '%' . $request->transfer_no . '%');
               }
               return $query->lazy();
          }else{
               return $this->model::with('fromBranch.company', 'toBranch', 'transferItems.itemInfo.hscode')
               ->lazy();
          }
     }
     

     /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function getShowById(int $id, $company_id = NULL){
          return $this->model::with('fromBranch.company', 'toBranch', 'transferItems.itemInfo.hscode', 'user')
          ->where('id', $id)->first();
     }

     /**
     * resource create
     * @param  $request
     * @return \Illuminate\Http\Response
     */

    public function create($request){     
        try {
          // Posting Date
          $time = date('H:i:s');
          $posting_date = $request->posting_date !=""? date('Y-m-d', strtotime($request->posting_date)): date('Y-m-d');
          $posting_date  =$posting_date.' '.$time;


          $orderPrefix = "";
          
          $branch = $this->branch::with('company')
          ->where('id', $request->branch_from)
          ->first();
          
          if ($branch->company->business_type == 1) {
               $orderPrefix = $branch->company->order_prefix."-TRS";
          }else{
               $orderPrefix = $branch->company->order_prefix;
               $orderPrefix = $orderPrefix."-".$branch->order_prefix."-TRS";
          }

          $challanNo = Helper::transChallan($branch->id);
          $sl_no = explode("-",$challanNo); 
          
          $transfer_no    =   $orderPrefix.'-'.$challanNo;
          DB::beginTransaction();
          
          $transfer = $this->model;
          $transfer->transfer_no = $transfer_no;
          $transfer->sl_no = $sl_no[2];
          $transfer->company_id = (int) auth()->user()->company_id? (int) auth()->user()->company_id: 1;
          $transfer->branch_from_id = $request->branch_from;
          $transfer->branch_to_id = $request->branch_to;
          $transfer->vehicle_info = $request->vehicle_info;   
          $transfer->reference_no = $request->reference_no? $request->reference_no: NULL;
          $transfer->created_by = auth()->user()->id;
          $transfer->created_at = $posting_date;
          $transfer->note = $request->note;
          
          $transfer->save();
          $trnsItems = [];
          $transData = [];
          $saveTransfer = 1;
          $notAvailSku = "";
          foreach ($request->transferItems as $item) {  
               $productInfo = $this->product::select('id', 'hs_code_id', 'hs_code', 'sku', 'title', 'model', 'type', 'price', 'status')
                    ->where('id', $item['item_id'])->first();     
               if (!empty($productInfo)) {
                    $trnsItem['transfer_id'] = $transfer->id;
                    $trnsItem['product_id'] = $item['item_id'];
                    $trnsItem['price'] = $item['price'];
                    $trnsItem['qty'] = $item['qty'];
                    $trnsItems[] = $trnsItem;
                    
                    $transferData['transfer_id'] = $transfer->id;
                    $transferData['item_id'] = $item['item_id'];
                    $transferData['price'] = $item['price']; 
                    $transferData['qty'] = $item['qty'];
                    $transferData['branch_from'] = $transfer->branch_from_id;
                    $transferData['branch_to'] = $transfer->branch_to_id;
                    $transferData['date'] = $transfer->created_at;
                    $transData[] = $transferData;
                    
                    if (!$this->checkStock($transferData)) {
                         $saveTransfer = 0;
                         $notAvailSku = $productInfo->title ." - [". $productInfo->sku.'] Item stock is not available';
                         break;
                    }
               }
          }
          
          if ($saveTransfer > 0 && !empty($trnsItems)) {
               $transferItem = new $this->transferItem;
               $transferItem::insert($trnsItems);
               $this->updateStock($transData);
               DB::commit();
               return response()->json([
                    'status' => true,
                    'data' => $this->getById($transfer->id),
                    'errors' => '', 
                    'message' => "Transfer successfully created!",
               ]);
          }else{
               DB::rollBack();
               $branch = $this->branch::where('id', $request->branch_from)->first();
               return response()->json([
                    'status' => false,
                    'data' => [],
                    'errors' => '', 
                    'message' => $notAvailSku. "stock is not available at ".$branch->name." branch",
               ]);
          }
          
        }catch (\Throwable $th) {
          DB::rollBack();
          return response()->json([
               'status' => false,
               'data' => [],
               'errors' => '', 
               'message' => $th->getMessage()
          ]);
        }
     }

     function bulkTransferReg($request) {
          $user = auth()->user(); 
          $filename = $request->file('csvfile');
          if($_FILES["csvfile"]["size"] > 0)
          {
               $file = fopen($filename, "r");
               $i = 0; 
               $previousRef = NULL;
               $transfer = [];
               while (($item_info = fgetcsv($file, 10000, ",")) !== FALSE)
               {
                    $i++;
                    if ($i>1) { 
                         $fromBranch = $this->branch::where('code', $item_info[5])
                         // ->WhereNull('type')
                         ->whereNotIn('code', ["8000-001","8000-004"])
                         ->first();
                        
                         $toBranch = $this->branch::where('code', $item_info[7])
                         ->whereNotIn('code', ["8000-001","8000-004"])
                         ->first();
                         
                         $productInfo = $this->product::select('id', 'hs_code_id', 'hs_code', 'sku', 'title', 'model', 'type', 'price', 'status')
                         ->where('sku', $item_info[11])
                         ->where('company_id', auth()->user()->company_id)
                         ->first();
                         
                         if(!empty($fromBranch) && !empty($toBranch)){
                              // Posting Date
                              $time = date('H:i:s', strtotime("+$i minutes" , time()));
                              $challanDate = date('Y-m-d H:i:s', strtotime($item_info[8].' '.$time));
                              // Posting Date
                              $refNo = explode("-", $item_info[9]);
                              $refPrefix = $refNo[0];
                              if (!empty($productInfo) && $refPrefix == "TD") {
                                   
                                   $tranExist = $this->model::whereHas('transferItems', function($query) use ($productInfo){
                                        $query->where('product_id', $productInfo->id);
                                   })
                                   ->where('reference_no', $item_info[9])
                                   ->first();
                                   $tranExist = [];
                                   if (empty($tranExist)) {
                                        try {
                                             // return $item_info;
                                             DB::beginTransaction();
                                             if ($previousRef != $item_info[9]) {
                                                  $transferData = [];
                                                  $transferData['from_branch'] = $fromBranch;
                                                  $transferData['to_branch'] = $toBranch;
                                                  $transferData['from_branch'] = $fromBranch;
                                                  $transferData['item'] = $item_info;
                                                  $transferData['created_at'] = $challanDate;
                                                  $transfer = $this->makeTransfer($transferData);
                                             }
                                             $previousRef = $item_info[9];
                                             $transfer = $transfer;

                                             $transferItem = new $this->transferItem;
                                             $transferItem->transfer_id = $transfer->id;
                                             $transferItem->product_id = $productInfo->id;
                                             $transferItem->price = $productInfo->price;
                                             $transferItem->qty = $item_info[13];
                                             $transferItem->save();
                                             $transferData = array(
                                                  'transfer_id' => $transfer->id,
                                                  'item_id' => $transferItem->product_id, 
                                                  'price' => $transferItem->price, 
                                                  'qty' => $transferItem->qty, 
                                                  'branch_from' => $fromBranch->id, 'branch_to' => $toBranch->id,
                                                  'date' => $transfer->created_at
                                             );
                                             if ($this->stockTransfer($transferData)) {
                                                  DB::commit();
                                             }                                           
                                        }catch (\Throwable $th) {
                                             DB::rollBack();
                                             $returnData['status'] = true;
                                             $returnData['data'] = [];
                                             $returnData['message'] = $th->getMessage();;
                                             return $returnData;
                                        }
                                   }
                              }
                         }

                    }

               }
               $returnData['status'] = true;
               $returnData['data'] = $i;
               $returnData['message'] = "Bulk Sales Has been Uploaded";
               return $returnData;
          }
     }
    
     public function bulkUpload($request) {
          $user = auth()->user(); 
          $filename = $request->file('csvfile');
          if($_FILES["csvfile"]["size"] > 0)
          {
               $file = fopen($filename, "r");
               $i = 0; 
               $previousRef = NULL;
               $transfer = [];
               while (($item_info = fgetcsv($file, 10000, ",")) !== FALSE)
               {
                    $i++;
                    if ($i>1) { 
                         $fromBranch = $this->branch::where('code', $item_info[5])
                         // ->WhereNull('type')
                         ->whereNotIn('code', ["8000-001","8000-004"])
                         ->first();
                        
                         $toBranch = $this->branch::where('code', $item_info[7])
                         ->whereNotIn('code', ["8000-001","8000-004"])
                         ->first();
                         
                         $productInfo = $this->product::select('id', 'hs_code_id', 'hs_code', 'sku', 'title', 'model', 'type', 'price', 'status')
                         ->where('sku', $item_info[11])
                         ->where('company_id', auth()->user()->company_id)
                         ->first();
                         
                         if(!empty($fromBranch) && !empty($toBranch)){
                              // Posting Date
                              $time = date('H:i:s', strtotime("+$i minutes" , time()));
                              $challanDate = date('Y-m-d H:i:s', strtotime($item_info[8].' '.$time));
                              // Posting Date
                              $refNo = explode("-", $item_info[9]);
                              $refPrefix = $refNo[0];
                              if (!empty($productInfo) && $refPrefix == "TD") {
                                   
                                   $tranExist = $this->model::whereHas('transferItems', function($query) use ($productInfo){
                                        $query->where('product_id', $productInfo->id);
                                   })
                                   ->where('reference_no', $item_info[9])
                                   ->first();
                                   $tranExist = [];
                                   if (empty($tranExist)) {
                                        try {
                                             // return $item_info;
                                             DB::beginTransaction();
                                             if ($previousRef != $item_info[9]) {
                                                  $transferData = [];
                                                  $transferData['from_branch'] = $fromBranch;
                                                  $transferData['to_branch'] = $toBranch;
                                                  $transferData['from_branch'] = $fromBranch;
                                                  $transferData['item'] = $item_info;
                                                  $transferData['created_at'] = $challanDate;
                                                  $transfer = $this->makeTransfer($transferData);
                                             }
                                             $previousRef = $item_info[9];
                                             $transfer = $transfer;

                                             $transferItem = new $this->transferItem;
                                             $transferItem->transfer_id = $transfer->id;
                                             $transferItem->product_id = $productInfo->id;
                                             $transferItem->price = $productInfo->price;
                                             $transferItem->qty = $item_info[13];
                                             $transferItem->save();
                                             $transferData = array(
                                                  'transfer_id' => $transfer->id,
                                                  'item_id' => $transferItem->product_id, 
                                                  'price' => $transferItem->price, 
                                                  'qty' => $transferItem->qty, 
                                                  'branch_from' => $fromBranch->id, 'branch_to' => $toBranch->id,
                                                  'date' => $transfer->created_at
                                             );
                                             if ($this->stockTransfer($transferData)) {
                                                  DB::commit();
                                             }else{
                                                  DB::rollBack();
                                                  DB::beginTransaction();
                                                  $nonOrder = new $this->nonOrder;
                                                  $nonOrder->branch_id = $fromBranch->id;
                                                  $nonOrder->product_id = $productInfo->id;
                                                  $nonOrder->raw_data = json_encode($item_info);
                                                  $nonOrder->type = 2;
                                                  $nonOrder->branch_code = $item_info[5];
                                                  $nonOrder->branch_name = $fromBranch->name;
                                                  $nonOrder->order_number = $item_info[9];
                                                  $nonOrder->sku = $productInfo->sku;
                                                  $nonOrder->product_title = $productInfo->title;
                                                  $nonOrder->price = $productInfo->price;
                                                  $nonOrder->qty = $item_info[13];
                                                  $nonOrder->vat_rate = NULL;
                                                  $nonOrder->vat_amount = NULL;
                                                  $nonOrder->order_date = $item_info[8]? date("Y-m-d", strtotime($item_info[8])):date("Y-m-d");
                                                  
                                                  $nonOrder->save();
                                                  DB::commit();
                                             }                                             
                                        }catch (\Throwable $th) {
                                             DB::rollBack();
                                             $returnData['status'] = true;
                                             $returnData['data'] = [];
                                             $returnData['message'] = $th->getMessage();;
                                             return $returnData;
                                        }
                                   }
                              }
                         }else{
                              DB::beginTransaction();
                              $nonOrder = new $this->nonOrder;
                              $nonOrder->branch_id = !empty($fromBranch)? $fromBranch->id: 1;
                              $nonOrder->product_id = !empty($productInfo)? $productInfo->id: NULL;
                              $nonOrder->raw_data = json_encode($item_info);
                              $nonOrder->type = 2;
                              $nonOrder->branch_code = $item_info[5];
                              $nonOrder->branch_name = !empty($fromBranch)? $fromBranch->name: $item_info[6];
                              $nonOrder->order_number = $item_info[9];
                              $nonOrder->sku = !empty($productInfo)? $productInfo->sku : $item_info[11];
                              $nonOrder->product_title = !empty($productInfo)? $productInfo->title : $item_info[12];
                              $nonOrder->price = !empty($productInfo)? $productInfo->price : NULL;
                              $nonOrder->qty = $item_info[13];
                              $nonOrder->vat_rate = NULL;
                              $nonOrder->vat_amount = NULL;
                              $nonOrder->order_date = $item_info[8]? date("Y-m-d", strtotime($item_info[8])):date("Y-m-d");
                              
                              $nonOrder->save();
                              DB::commit();
                         }

                    }

               }
               $returnData['status'] = true;
               $returnData['data'] = $i;
               $returnData['message'] = "Bulk Sales Has been Uploaded";
               return $returnData;
          }
     }

     public function makeTransfer($data) {
          $orderPrefix = "";
          
          $branch = $this->branch::with('company')
          ->where('id', $data['from_branch']->id)
          ->first();
          
          if ($branch->company->business_type == 1) {
               $orderPrefix = $branch->company->order_prefix."-TRS-";
          }else{
               $orderPrefix = $branch->company->order_prefix;
               $orderPrefix = $orderPrefix."-TRS-".$branch->order_prefix;
          }
          
          $challanNo = Helper::transChallan($branch->id);
          $sl_no = explode("-",$challanNo); 
          
          $transfer_no    =   $orderPrefix.'-'.$challanNo;
          $transfer = new $this->model;
          $transfer->sl_no = $sl_no[2];
          $transfer->transfer_no = $transfer_no;
          $transfer->company_id = $branch->company->id;
          $transfer->branch_from_id = $data['from_branch']->id;
          $transfer->branch_to_id = $data['to_branch']->id;;
          $transfer->vehicle_info = NULL;   
          $transfer->created_by = auth()->user()->id;   
          $transfer->note = "BULK TRANSFER";
          $transfer->reference_no = $data['item'][9];
          $transfer->created_at = $data['created_at'];
          
          $transfer->save();
          return $transfer;
     }

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
        
     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function delete($id){
       return $this->getById($id)->delete();
     }

     public function guard()
     {
          return Auth::guard('api');
     }

     protected function stockTransfer($item, $mushokData = array()){
          $fromStock = new $this->stock;                 
          $fromStockInfo = $fromStock::where(['product_id'=> $item['item_id'], 'branch_id'=> $item['branch_from']])->first();
          if(!empty($fromStockInfo) && $fromStockInfo->stock >= $item['qty'] ){
               $toStock = new $this->stock;                 
               $toStockInfo = $toStock::where(['product_id'=> $item['item_id'], 'branch_id'=> $item['branch_to']])->first();
               if (!empty($toStockInfo)) {
                    $toStockInfo->stock += $item['qty'];
                    $toStockInfo->update();
                    $this->mushokUpdate($item, 'credit');
               }else{
                    $toStock->product_id = $item['item_id'];
                    $toStock->company_id = auth()->user()->company_id;
                    $toStock->branch_id = $item['branch_to'];
                    $toStock->stock = $item['qty'];
                    $toStock->save();
                    $this->mushokUpdate($item, 'credit');
               }
               $fromStockInfo->stock -= $item['qty'];
               $fromStockInfo->update();
               $this->mushokUpdate($item, 'debit');
               return true;
          }else{
               return false;
          }
     }

     protected function checkStock($item) {
          $fromStock = new $this->stock;  
          $fromStockInfo = $fromStock::where(['product_id'=> $item['item_id'], 'branch_id'=> $item['branch_from']])->first();
          if(!empty($fromStockInfo) && $fromStockInfo->stock >= $item['qty'] ){
               return true;
          }else{
               return false;
          }
     }

     protected function updateStock($items){
          foreach ($items as $key => $item) {
               $toStock = new $this->stock;                 
               $toStockInfo = $toStock::where(['product_id'=> $item['item_id'], 'branch_id'=> $item['branch_to']])->first();
               if (!empty($toStockInfo)) {
                    $toStockInfo->stock += $item['qty'];
                    $toStockInfo->update();
                    $this->mushokUpdate($item, 'credit');
               }else{
                    $toStock->product_id = $item['item_id'];
                    $toStock->company_id = auth()->user()->company_id;
                    $toStock->branch_id = $item['branch_to'];
                    $toStock->stock = $item['qty'];
                    $toStock->save();
                    $this->mushokUpdate($item, 'credit');
               }
               $fromStock = new $this->stock;  
               $fromStockInfo = $fromStock::where(['product_id'=> $item['item_id'], 'branch_id'=> $item['branch_from']])->first();

               $fromStockInfo->stock -= $item['qty'];
               $fromStockInfo->update();
               $this->mushokUpdate($item, 'debit');               
          }
          return true;
          
     }

     public function mushokUpdate($item, $type)
     {
          $company = $this->company::where('id', $this->guard()->user()->company_id)->first();
          $mushok_no = 'six_one';
          if ($company->business_type == 2) {
               $mushok_no = 'six_two_one';
          }
          $companyLastMushok = $this->mushok::where(['product_id' => $item['item_id'], 'company_id' => $company->id, 'mushok' => $mushok_no])
               ->where('created_at', '<=', $item['date'])
               ->orderBy('created_at', 'DESC')
               ->first();
          if ($type == 'debit') {
               $branchLastMushok = $this->mushok::where(['product_id' => $item['item_id'], 'branch_id' => $item['branch_from'], 'mushok' => $mushok_no]) 
               ->where('created_at', '<=', $item['date'])
               ->orderBy('created_at', 'DESC')
               ->first();
               // Mushok Insert
               $mushokItems = new $this->mushok;
               $mushokItems->transfer_id = (int) $item['transfer_id'];
               $mushokItems->product_id = $item['item_id'];
               $mushokItems->company_id = $company->id;
               $mushokItems->branch_id = $item['branch_from'];
               $mushokItems->type = 'debit';
               $mushokItems->mushok = $mushok_no;
               $mushokItems->nature = 'Transfer';
               $mushokItems->price = $item['price'];
               $mushokItems->average_price = !empty($branchLastMushok)? $branchLastMushok->average_price:$item['price'];
               $mushokItems->qty = $item['qty'];
               $mushokItems->is_transfer = 1;
               // New add     
               $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
               $mushokItems->branch_closing = $mushokItems->branch_opening-$mushokItems->qty;
               // New Add
               $mushokItems->opening_qty = $companyLastMushok? $companyLastMushok->opening_qty:0;
               $mushokItems->closing_qty = $companyLastMushok? $companyLastMushok->closing_qty:0;
               $mushokItems->created_by = (int) $this->guard()->user()->id;
               $mushokItems->created_at = !empty($item['date'])? $item['date'] : date('Y-m-d H:i:s');
               $product = ['id' => $mushokItems->product_id, 'qty' => $mushokItems->qty];
                         
               Helper::postDataUpdate($product, $mushokItems->branch_id, $mushokItems->created_at, $mushokItems->type, 'branch');

               $mushokItems->save();
          }elseif ($type == 'credit') {
               $branchLastMushok = $this->mushok::where(['product_id' => $item['item_id'], 'branch_id' => $item['branch_to'], 'mushok' => $mushok_no]) 
               ->whereDate('created_at', '<=', date('Y-m-d', strtotime($item['date'])))
               ->latest('id')
               ->first();
               // Mushok Insert
               $mushokItems = new $this->mushok;
               $mushokItems->transfer_id = (int) $item['transfer_id'];
               $mushokItems->product_id = $item['item_id'];
               $mushokItems->company_id = $company->id;
               $mushokItems->branch_id = $item['branch_to'];
               $mushokItems->type = 'credit';
               $mushokItems->mushok = $mushok_no;
               $mushokItems->nature = 'Transfer';
               $mushokItems->price = $item['price'];
               $mushokItems->average_price = !empty($branchLastMushok)? $branchLastMushok->average_price:$item['price'];
               $mushokItems->qty = $item['qty'];
               $mushokItems->is_transfer = 1;
               // New add                
               $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
               $mushokItems->branch_closing = $mushokItems->branch_opening+$mushokItems->qty;
               // New Add
               $mushokItems->opening_qty = $companyLastMushok? $companyLastMushok->opening_qty:0;
               $mushokItems->closing_qty = $companyLastMushok? $companyLastMushok->closing_qty:0;
               $mushokItems->created_by = (int) $this->guard()->user()->id;
               $mushokItems->created_at = !empty($item['date'])? $item['date'] : date('Y-m-d H:i:s');
               $mushokItems->save();
               $product = ['id' => $mushokItems->product_id, 'qty' => $mushokItems->qty];
               Helper::postDataUpdate($product, $mushokItems->branch_id, $mushokItems->created_at, $mushokItems->type, 'branch');
          }
     }
}
