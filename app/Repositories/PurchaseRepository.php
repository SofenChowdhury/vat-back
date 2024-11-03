<?php
namespace App\Repositories;

use App\Models\Branch;
use App\Models\Vendor;
use App\Classes\Helper;
use App\Models\Company;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\ItemStock;
use App\Models\MushokSix;
use App\Models\SalesReturn;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\SalesReturnItem;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Validator;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class PurchaseRepository implements BaseRepository{

     protected  $model;
     protected  $purchaseItem;
     protected  $product;
     protected  $stock;
     protected  $mushok;
     protected  $purchaseReturn;
     protected  $purchaseReturnItem;
     protected  $salesReturn;
     protected  $salesReturnItem;     
     protected  $company;
     protected  $branch;
     protected  $vendor;
     
     public function __construct(Purchase $model, PurchaseItem $purchaseItem, Product $product, ItemStock $stock, MushokSix $mushok, PurchaseReturn $purchaseReturn, PurchaseReturnItem $purchaseReturnItem, SalesReturn $salesReturn, SalesReturnItem $salesReturnItem, Company $company, Branch $branch, Vendor $vendor)
     {
        $this->model = $model;
        $this->purchaseItem = $purchaseItem;
        $this->product = $product;
        $this->stock = $stock;
        $this->mushok = $mushok;
        $this->purchaseReturn = $purchaseReturn;
        $this->purchaseReturnItem = $purchaseReturnItem;
        $this->salesReturn = $salesReturn;
        $this->salesReturnItem = $salesReturnItem;
        $this->company = $company;
        $this->branch = $branch;
        $this->vendor = $vendor;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll($company_id = NULL){ 
          $user = auth()->user();         
          if (!empty($user->company_id)) {
               return $this->model::with('company', 'vendor', 'branch', 'user')
               ->where('company_id', $user->company_id)
               ->orderBy('id', 'desc')
               ->paginate(20);
          }else{
               return $this->model::with('company', 'vendor', 'branch', 'user')
               ->orderBy('id', 'desc')
               ->paginate(20);
          }
     }

     /**
      * all resource get
      * @return Collection
      */
      public function getFull($company_id = NULL){
        if (!empty($company_id)) {
            return $this->model::where('company_id', $company_id)
            ->with('company', 'vendor', 'user')
            ->orderBy('id', 'desc')
            ->get();
        }else{
            return $this->model::with('company', 'vendor')->latest()->get();
        }
    }

     /**
      * all resource get
      * @return Collection
      */
      public function getLatest($company_id = NULL){
          $user = $this->user;
          if ($user->company_id != "") {
               return $this->model::where('company_id', $user->company_id)->take(20)->get();
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
          return $this->model::with('company', 'vendor', 'user', 'purchaseItems.info.hscode')
          ->where('id', $id)->first();
     }

     public function download($request, $user){
          $query = $this->model::query();
          $query->with('company', 'purchaseItems.info.hscode', 'vendor', 'branch', 'user');
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $query->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
          }
          if ((isset($request->purchase_no) && $request->purchase_no !="")) {
               $query->where('purchase_no', 'LIKE', '%' . $request->purchase_no . '%');
          }
          if ((isset($request->challan_no) && $request->challan_no !="")) {
               $query->where('challan_no', 'LIKE', '%' . $request->challan_no . '%');
          }
          if ((isset($request->vendor_id) && $request->vendor_id !="")) {
               $query->where('vendor_id', $request->vendor_id);
          }
          if ((isset($request->type) && $request->type !="")) {
               $query->where('type', $request->type);
          }
          $query->where('company_id', $user->company_id);
          $purchases = $query->lazy();  
          return response()->json([
               'status' => true,
               'data' => $purchases,
               'errors' => '', 
               'message' => "Purchase List Loaded",
          ]);        
     }


     public function getReturnByChallan($challan)
     {
          return $this->purchaseReturn::with('company', 'vendor', 'purchase.company', 'purchase.vendor', 'purchase.purchaseItems', 'returnItems.info')
          ->where('return_no', $challan)->first();
     }

     public function getReturnByid($id)
     {
          return $this->purchaseReturn::with('company', 'vendor', 'purchase.company', 'purchase.vendor', 'purchase.purchaseItems', 'returnItems.info')
          ->where('id', $id)->first();
     }

     /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function getShowById(int $id, $company_id = NULL){
          return $this->model::with('company', 'vendor')
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
               $posting_date = $posting_date.' '.$time;

               DB::beginTransaction();
               $user = auth()->user();
               $company = $this->company::where('id', $user->company_id)->first();
               $mushok_no = 'six_one';
               if ($company->business_type == 2) {
                    $mushok_no = 'six_two_one';
               }
               $branch_id = $request->branch_id>0? $request->branch_id: auth()->user()->branch_id;
               if ($branch_id > 0) {
                    $branch = $this->branch::with('company')->where('id', $branch_id)->first();
               }else{
                    $branch = $this->branch::with('company')->where(['company_id' => $company->id, 'type' => 1])->first();
               }
               
               if (empty($branch)) {
                    return "Branch not created, please create the branch first!";
               }
               $pu_sl = 1;

               $goosFinYear = Helper::getFinYear();
               if ($request->type == "Contractual") {
                    $purchaseLastSl = $this->model::where('company_id', $company->id)
                    ->where('type', 'Contractual')
                    ->orderBy('id', 'desc')
                    ->first();
               }else{
                    $purchaseLastSl = $this->model::where('company_id', $company->id)
                    ->where('type', 'Local')->orWhere('type', 'Imported')
                    ->orderBy('id', 'desc')
                    ->first();
               }
               if(!empty($purchaseLastSl)){
                    $pu_sl = $purchaseLastSl->sl_no > 0? $purchaseLastSl->sl_no+1:1;
               }
               $pu_sl = str_pad($pu_sl, 4, '0', STR_PAD_LEFT);

               if ($request->type == "Contractual") {
                    $purchase_no    =   $company->order_prefix."-CON-".$goosFinYear."-".$pu_sl;
               }else{
                    $purchase_no    =   $company->order_prefix."-PUR-".$goosFinYear."-".$pu_sl;
               }
                              
               $purchase = $this->model;
               $purchase->company_id       = auth()->user()->company_id? auth()->user()->company_id: 1;
               $purchase->branch_id        = $branch->id;
               $purchase->vendor_id        = $request->vendor_id;
               $purchase->purchase_no      = $purchase_no;
               $purchase->sl_no            = (int) $pu_sl;
               $purchase->custom_house     = $request->custom_house;        
               $purchase->type             = $request->type == 'Local'? $request->type:($request->type == 'Imported'? $request->type:"Contractual");
               $purchase->challan_no       = $request->challan_no;
               $purchase->challan_date     = date('Y-m-d', strtotime($request->challan_date));
               $purchase->admin_id         = auth()->user()->id;
               $purchase->status           = $request->status? $request->status:1;
               $purchase->created_at       = $posting_date;
               $purchase->save();

               foreach ($request->purchaseItems as $item) {  
                    $productInfo = $this->product::select('id', 'hs_code_id', 'hs_code', 'sku', 'title', 'model', 'type', 'price', 'status')
                         ->where('id', $item['item_id'])->first();

                    $companyLastMushok = $this->mushok::where(['product_id' => $item['item_id'], 'company_id' => $company->id, 'mushok' => $mushok_no]) 
                    ->where('created_at', '<=', $posting_date)
                    ->orderBy('created_at', 'desc')
                    ->where('is_transfer', 0)
                    ->first();

                    

                    $branchLastMushok = $this->mushok::where(['product_id' => $item['item_id'], 'branch_id' => $purchase->branch_id, 'mushok' => $mushok_no]) 
                    ->where('created_at', '<=', $posting_date)
                    ->orderBy('created_at', 'desc')
                    ->first();
                    $lastAverage = $this->mushok::where(['product_id' => $item['item_id'], 'company_id' => $company->id, 'mushok' => $mushok_no])->avg('price');
                         
                    if (!empty($productInfo)) {
                         $purchaseItems = new $this->purchaseItem;
                         $purchaseItems->purchase_id    = (int) $purchase->id;
                         // $purchaseItems->purchase_id    = 1;
                         $purchaseItems->product_id  = $productInfo->id;
                         $purchaseItems->hs_code_id  = $productInfo->hs_code_id;
                         $purchaseItems->item_info   = json_encode($productInfo);
                         $purchaseItems->price    = $item['price'];
                         $purchaseItems->qty      = $item['qty'];
                         $purchaseItems->cd       = $item['cd']? $item['cd']: 0;
                         $purchaseItems->rd       = $item['rd']? $item['rd']: 0;
                         $purchaseItems->sd       = $item['sd']? $item['sd']: 0;
                         $purchaseItems->ait      = $item['ait']? $item['ait']: 0;
                         $purchaseItems->at       = $item['at']? $item['at']: 0;
                         $purchaseItems->tti      = $item['tti']? $item['tti']: 0;
                         $purchaseItems->vat_rate = $item['vat_rate'];                    
                         $purchaseItems->vat_amount = $item['vat_amount'];
                         $purchaseItems->vds_receive_amount = $item['vds_receive_amount'];
                         $purchaseItems->vat_rebetable_amount = $item['vat_rebetable_amount'];                    
                         $purchaseItems->total_price = ($item['price']*$item['qty']);
                         $purchaseItems->created_at =  $purchase->created_at;
                         $purchaseItems->save();
                         
                         // Mushok Insert
                         $mushokItems = new $this->mushok;
                         $mushokItems->purchase_id = (int) $purchase->id;
                         // $mushokItems->purchase_id = 1;
                         $mushokItems->product_id = $purchaseItems->product_id;
                         $mushokItems->company_id = $purchase->company_id;
                         $mushokItems->branch_id = $purchase->branch_id ;
                         $mushokItems->type = 'credit';
                         $mushokItems->mushok = $mushok_no;
                         $mushokItems->nature = $purchase->type;
                         $mushokItems->price = $purchaseItems->price;
                         $mushokItems->average_price = !empty($lastAverage)? $lastAverage:0;
                         $mushokItems->qty = $purchaseItems->qty;
                         $mushokItems->vat_rate = $purchaseItems->vat_rate;
                         $mushokItems->vds_receive_amount = $purchaseItems->vds_receive_amount;
                         $mushokItems->vat_rebetable_amount = $purchaseItems->vat_rebetable_amount; 
                         $mushokItems->vat_amount = $purchaseItems->vat_amount;
                         $mushokItems->sd_amount = $purchaseItems->sd;
                         $mushokItems->cd_amount = $purchaseItems->cd;
                         $mushokItems->rd_amount = $purchaseItems->rd;
                         $mushokItems->ait_amount = $purchaseItems->ait;
                         $mushokItems->at_amount = $purchaseItems->at;
                         $mushokItems->created_at = $purchase->created_at;
                         // New add                    
                         $mushokItems->at_amount = $item['at']? $item['at']: 0;
                         $mushokItems->tti = $item['tti']? $item['tti']: 0;
                         $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                         $mushokItems->branch_closing = $mushokItems->branch_opening+$mushokItems->qty;
                         // New Add
                         $mushokItems->opening_qty = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
                         $mushokItems->closing_qty = $mushokItems->opening_qty+$mushokItems->qty;
                         $mushokItems->created_by = (int) auth()->user()->id;
                         $mushokItems->created_at = $purchase->created_at;
                         $mushokItems->save();
                         $product = ['id' => $productInfo->id, 'qty' => $purchaseItems->qty];
                         
                         Helper::postDataUpdate($product, $mushokItems->branch_id, $purchase->created_at, $mushokItems->type);
                         $this->stockUpdate(array('id' => $item['item_id'], 'qty' => $item['qty'], 'company_id' => $purchase->company_id, 'branch_id' => $purchase->branch_id));
                    }
               }
               DB::commit();
               return $this->getById($purchase->id);

               
          }catch (\Throwable $th) {
               DB::rollBack();
               return $th->getMessage();
          }
    } 

    /**
     * resource create
     * @param  $request
     * @return \Illuminate\Http\Response
     */

     public function createBulk($request){     
          try {
               
               // Posting Date
               $time = date('H:i:s');

               $posting_date = $request->posting_date !=""? date('Y-m-d', strtotime($request->posting_date)): date('Y-m-d');
               $posting_date  = $posting_date.' '.$time;

               DB::beginTransaction();
               $filename = $request->file('csvfile');
               if ($_FILES["csvfile"]["size"] > 0) {
                    $file = fopen($filename, "r");
                    $company = $this->company::where('id', auth()->user()->company_id)->first();
                    $mushok_no = 'six_one';
                    if ($company->business_type == 2) {
                         $mushok_no = 'six_two_one';
                    }
                    $user = auth()->user();
                    $branch = $this->branch::where(['company_id' => $company->id, 'type' => 1])->first();
                    if (empty($branch)) {
                         return "Branch not created, please create the branch first!";
                    }

                    $pu_sl = 1;

                    $goosFinYear = Helper::getFinYear();
                    $purchaseLastSl = $this->model::where('company_id', $company->id)
                    ->orderBy('id', 'desc')
                    ->first();
                    
                    if(!empty($purchaseLastSl)){
                         $pu_sl = $purchaseLastSl->sl_no>0 ? $purchaseLastSl->sl_no+1:1;
                    }
                    $pu_sl = str_pad($pu_sl, 4, '0', STR_PAD_LEFT);

                    $purchase_no    =   $company->order_prefix."-PUR-".$goosFinYear."-".$pu_sl;
                    $purchase = $this->model;
                    $purchase->company_id       = auth()->user()->company_id;
                    $purchase->branch_id        = $request->stock_branch;
                    $purchase->vendor_id        = $request->vendor_id;
                    $purchase->purchase_no      = $purchase_no;   
                    $purchase->sl_no            = (int) $pu_sl;   
                    $purchase->type             = $request->type;
                    $purchase->challan_no       = $request->challan_no;
                    $purchase->challan_date     = date('Y-m-d', strtotime($request->challan_date));
                    $purchase->admin_id         = (int) auth()->user()->id;
                    $purchase->status           = 1;
                    
                    $purchase->created_at       = $posting_date;
                    // return $purchase;
                    $purchase->save();
                    $i = 0;
                    while (($product_info = fgetcsv($file, 10000, ",")) !== FALSE) {
                         
                         if ($i > 0) {
                              // return $product_info;
                              $productInfo = $this->product::select('id', 'hs_code_id', 'hs_code', 'sku', 'title', 'model', 'type', 'price', 'status')
                                   ->find($product_info[3]);
                              // return $productInfo;
                              if (!empty($productInfo)) {
          
                                   
                                   $createdAt = date("Y-m-d H:i:s", strtotime($posting_date)+$i);

                                   $companyLastMushok = $this->mushok::where(['product_id' => $productInfo->id, 'company_id' => $company->id, 'mushok' => $mushok_no]) 
                                   ->where('created_at', '<=', $createdAt)
                                   ->orderBy('created_at', 'desc')
                                   ->where('is_transfer', 0)
                                   ->first();

                                   $branchLastMushok = $this->mushok::where(['product_id' => $productInfo->id, 'branch_id' => $purchase->branch_id, 'mushok' => $mushok_no]) 
                                   ->where('created_at', '<=', $createdAt)
                                   ->orderBy('created_at', 'desc')
                                   ->first();


                                   $lastAverage = $this->mushok::where(['product_id' => $productInfo->id, 'company_id' => $company->id, 'mushok' => $mushok_no])
                                   ->where('price', '>', 0)
                                   ->avg('price');
                                   
                                   $purchaseItems = new $this->purchaseItem;
                                   $purchaseItems->purchase_id    = (int) $purchase->id;
                                   // $purchaseItems->purchase_id    = 1;
                                   $purchaseItems->product_id  = $productInfo->id;
                                   $purchaseItems->hs_code_id  = $productInfo->hs_code_id;
                                   $purchaseItems->item_info   = json_encode($productInfo);
                                   $purchaseItems->qty      = $product_info[5];
                                   $purchaseItems->price    = $product_info[6];
                                   $purchaseItems->total_price = $product_info[7];
                                   
                                   $purchaseItems->vat_rate = $product_info[8];                    
                                   $purchaseItems->vat_amount = $product_info[9];                  
                                   $purchaseItems->cd = isset($product_info[10])? $product_info[10]:0;                  
                                   $purchaseItems->rd = isset($product_info[11])? $product_info[11]: 0;                  
                                   
                                   $purchaseItems->created_at = $createdAt;
                                   // return $purchaseItems;
                                   $purchaseItems->save();
                                   
                                   // Mushok Insert
                                   $mushokItems = new $this->mushok;
                                   $mushokItems->purchase_id =  $purchase->id;
                                   // $mushokItems->purchase_id =  1;
                                   $mushokItems->product_id = $productInfo->id;
                                   $mushokItems->company_id = $purchase->company_id;
                                   $mushokItems->branch_id = $purchase->branch_id;
                                   $mushokItems->type = 'credit';
                                   $mushokItems->mushok = $mushok_no;
                                   $mushokItems->nature = 'Imported';
                                   $mushokItems->price = $purchaseItems->price;
                                   $mushokItems->average_price = !empty($purchaseItems->price)? $purchaseItems->price:0;
                                   $mushokItems->qty = $purchaseItems->qty;
                                   $mushokItems->vat_rate = $purchaseItems->vat_rate;
                                   $mushokItems->cd_amount = $purchaseItems->cd;
                                   $mushokItems->rd_amount = $purchaseItems->rd;
                                   $mushokItems->vds_receive_amount = 0;
                                   $mushokItems->vat_rebetable_amount = 0; 
                                   $mushokItems->vat_amount = $purchaseItems->vat_amount;
                                   $mushokItems->created_at = $posting_date;                  
                                   $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                                   $mushokItems->branch_closing = $mushokItems->branch_opening+$mushokItems->qty;
                                   // New Add
                                   $mushokItems->opening_qty = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
                                   $mushokItems->closing_qty = $mushokItems->opening_qty+$mushokItems->qty;
                                   $mushokItems->created_by = (int) auth()->user()->id;
                                   // return $mushokItems;
                                   $mushokItems->save();

                                   $product = ['id' => $productInfo->id, 'qty' => $purchaseItems->qty];
                         
                                   Helper::postDataUpdate($product, $mushokItems->branch_id, $createdAt, $mushokItems->type);
                                   $this->stockUpdate(array('id' => $productInfo->id, 'qty' => $purchaseItems->qty, 'company_id' => $purchase->company_id, 'branch_id' => $purchase->branch_id));
                                   
                              }
                         }
                         $i++;
                    }
               }
               DB::commit();
               return true;
               
          }catch (\Throwable $th) {
               return $th->getMessage();
          }        
    }


    function getPostData($data) {
          // return $data;
          $response['lastCompanyMushok'] = $this->mushok::where('created_at', '<=', $data->created_at)
               ->where('product_id', $data->product_id)
               ->where('company_id', $data->company_id)
               ->orderBy('id', 'desc')
               ->first();

          if ($data->branch_id == $response['lastCompanyMushok']->branch_id) {
               $response['lastBranchMushok'] = $response['lastCompanyMushok'];
          }else{
               $response['lastBranchMushok'] = $this->mushok::where('created_at', '<=', $data->created_at)
               ->where('product_id', $data->product_id)
               ->where('branch_id', $data->branch_id)
               ->orderBy('id', 'desc')
               ->get();
          }
          return $response;
     }

     public function updatePostData($data) {
          try {
               // Company Data Update
               $companyUpdate = $this->mushok;
               $companyData = $companyUpdate::whereDate('created_at', '>', date('Y-m-d', strtotime($data->created_at)))
               ->where('product_id', $data->product_id)
               ->where('company_id', $data->company_id)
               ->get();
               foreach ($companyData as $key => $data) {
                    $update = new $this->mushok;
                    $mushokInfo = $update->find($data->id);
                    $mushokInfo->opening_qty += $data['qty'];
                    $mushokInfo->closing_qty += $data['qty'];
                    $mushokInfo->update();
               }

               // Branch Data Update
               $branchObj = new $this->mushok;
               $branchData = $branchObj::whereDate('created_at', '>', date('Y-m-d', strtotime($data->created_at)))
               ->where('product_id', $data->product_id)
               ->where('company_id', $data->company_id)
               ->where('branch_id', $data->branch_id)
               ->get();

               foreach ($branchData as $key => $data) {
                    $update = new $this->mushok;
                    $updateInfo = $update->find($data->id);
                    $updateInfo->branch_opening += $data['qty'];
                    $updateInfo->branch_closing += $data['qty'];
                    $updateInfo->update();
               }
          } catch (\Throwable $th) {
               return $th->getMessage();
          }
     }

     function removePurchaseItem($request) {
          try {
               $purchase = $this->model::with('purchaseItems')->find($request->purchase_id);
               $purchaseItem = $this->purchaseItem::where(['purchase_id' => $request->purchase_id, 'product_id' => $request->item_id])->first();
               
               $stock = new $this->stock;
               $itemStock = $stock::where(['branch_id'=> $purchase->branch_id, 'product_id' => $purchaseItem->product_id])->first();
               $itemStock->stock = ($itemStock->stock-$purchaseItem->qty);
               $itemStock->update();
               $product = ['id' => $purchaseItem->product_id, 'qty' => $purchaseItem->qty];
               // Delete Order's item
               $item = new $this->purchaseItem;
               $item::where(['purchase_id' => $request->purchase_id, 'product_id' => $request->item_id])->delete();
               // Delete Mushok
               $this->mushok::where(['purchase_id' => $request->purchase_id, 'product_id' => $request->item_id])->delete();

               // Update Mushok 6.2
               Helper::postDataUpdateOnDelete($product, $purchase->branch_id, date('Y-m-d H:i:s', strtotime($purchase->created_at)), 'debit');
               
               return response()->json([
                    'status' => true,
                    'data' => $this->getById($request->purchase_id),
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

     function updateItem($request) {
          try {
               $purchaseItem = $this->purchaseItem->with('purchase')->find($request->id);  
               // return $purchaseItem;
               DB::beginTransaction();
               $purchaseItem->price    = ($request['total_price']/$purchaseItem->qty);
               $purchaseItem->cd       = $request['cd']? $request['cd']: $purchaseItem->cd;
               $purchaseItem->rd       = $request['rd']? $request['rd']: $purchaseItem->rd;
               $purchaseItem->sd       = $request['sd'];
               $purchaseItem->ait      = $request['ait']? $request['ait']: $purchaseItem->ait;
               $purchaseItem->at       = $request['at']? $request['at']: $purchaseItem->at;
               $purchaseItem->tti      = $request['tti']? $request['tti']: $purchaseItem->tti;
               $purchaseItem->vat_rate = $request['vat_rate'];                    
               $purchaseItem->vat_amount = $request['vat_amount'];
               $purchaseItem->vds_receive_amount = $request['vds_receive_amount'];
               $purchaseItem->vat_rebetable_amount = $request['vat_rebetable_amount'];                    
               $purchaseItem->total_price = $request['total_price'];
               // return $purchaseItem;
               $purchaseItem->update();

               // Mushok Insert
               $mushokItems = $this->mushok::where(['purchase_id' => $purchaseItem->purchase->id, 'product_id' => $purchaseItem->product_id])->first();

               $productInfo = $this->product::select('id', 'hs_code_id', 'hs_code', 'sku', 'title', 'model', 'type', 'price', 'status')
                    ->where('id', $purchaseItem->product_id)->first();

               // $branchLastMushok = $this->mushok::where(['product_id' => $productInfo->id, 'branch_id' => $purchaseItem->purchase->branch_id, 'mushok' => $purchaseItem->purchase->mushok])
               // ->where('created_at', '<', $purchaseItem->purchase->created_at)
               // ->orderBy('created_at', 'DESC')
               // ->first();
                    
               $lastAverage = $this->mushok::where(['product_id' => $purchaseItem->product_id, 'company_id' => 
               $purchaseItem->purchase->company_id, 'mushok' => $purchaseItem->purchase->mushok])->avg('price');


               
               $mushokItems->price = $purchaseItem->price;
               $mushokItems->average_price = !empty($lastAverage)? $lastAverage:0;
               // $mushokItems->qty = $purchaseItem->qty;
               $mushokItems->vat_rate = $purchaseItem->vat_rate;
               $mushokItems->vds_receive_amount = $purchaseItem->vds_receive_amount;
               $mushokItems->vat_rebetable_amount = $purchaseItem->vat_rebetable_amount; 
               $mushokItems->vat_amount = $purchaseItem->vat_amount;
               $mushokItems->sd_amount = $purchaseItem->sd;
               $mushokItems->cd_amount = $purchaseItem->cd;
               $mushokItems->rd_amount = $purchaseItem->rd;
               $mushokItems->ait_amount = $purchaseItem->ait;
               $mushokItems->at_amount = $purchaseItem->at;
               // New add    
               $mushokItems->tti = $purchaseItem->tti;
               // $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
               // $mushokItems->branch_closing = $mushokItems->branch_opening+$mushokItems->qty;
               // New Add
               // $mushokItems->opening_qty = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
               // $mushokItems->closing_qty = $mushokItems->opening_qty+$mushokItems->qty;
               $mushokItems->update();
               DB::commit();
               return response()->json([
                    'status' => true,
                    'data' => $this->getById($purchaseItem->purchase->id),
                    'errors' => '', 
                    'message' => $purchaseItem->purchase->purchase_no." sales item has been updated!",
               ]);               
          } catch (\Throwable $th) {
               return response()->json([
                    'status' => false,
                    'data' => [],
                    'errors' => '', 
                    'message' => $th->getMessage()
               ]); 
          }
     }

     function addPurchaseItem($request) {
          // try {
               $purchase = $this->model::with('purchaseItems')->find($request->purchase_id);
               // return $purchase->id;
               $item = $request->purchaseItem;
               $productInfo = $this->product::select('id', 'hs_code_id', 'hs_code', 'sku', 'title', 'model', 'type', 'price', 'status')
                         ->where('id', $item['item_id'])->first();
               // return $productInfo;
               $company = $this->company::where('id', $purchase->company_id)->first();
               $mushok_no = 'six_one';
               if ($company->business_type == 2) {
                    $mushok_no = 'six_two_one';
               }
               // Item Entry
               $purchaseItems = new $this->purchaseItem;
               $purchaseItems->purchase_id    = (int) $purchase->id;
               // $purchaseItems->purchase_id    = 1;
               $purchaseItems->product_id  = $productInfo->id;
               $purchaseItems->hs_code_id  = $productInfo->hs_code_id;
               $purchaseItems->item_info   = json_encode($productInfo);
               $purchaseItems->price    = $item['price'];
               $purchaseItems->qty      = $item['qty'];
               $purchaseItems->cd       = $item['cd']? $item['cd']: 0;
               $purchaseItems->rd       = $item['rd']? $item['rd']: 0;
               $purchaseItems->sd       = $item['sd']? $item['sd']: 0;
               $purchaseItems->ait      = $item['ait']? $item['ait']: 0;
               $purchaseItems->at       = $item['at']? $item['at']: 0;
               $purchaseItems->tti      = $item['tti']? $item['tti']: 0;
               $purchaseItems->vat_rate = $item['vat_rate'];                    
               $purchaseItems->vat_amount = $item['vat_amount'];
               $purchaseItems->vds_receive_amount = $item['vds_receive_amount'];
               $purchaseItems->vat_rebetable_amount = $item['vat_rebetable_amount'];                    
               $purchaseItems->total_price = $item['total_price'];
               $purchaseItems->created_at = $purchase->created_at;
               $purchaseItems->save();
               $createdAt = date("Y-m-d H:i:s", strtotime($purchase->created_at));

               $companyLastMushok = $this->mushok::where(['product_id' => $productInfo->id, 'company_id' => $company->id, 'mushok' => $mushok_no]) 
               ->where('created_at', '<=', $createdAt)
               ->orderBy('created_at', 'desc')
               ->where('is_transfer', 0)
               ->first();

               $branchLastMushok = $this->mushok::where(['product_id' => $productInfo->id, 'branch_id' => $purchase->branch_id, 'mushok' => $mushok_no]) 
               ->where('created_at', '<=', $createdAt)
               ->orderBy('created_at', 'desc')
               ->first();
               
               // Mushok Insert
               $mushokItems = new $this->mushok;
               $mushokItems->purchase_id = (int) $purchase->id;
               // $mushokItems->purchase_id = 1;
               $mushokItems->product_id = $purchaseItems->product_id;
               $mushokItems->company_id = $purchase->company_id;
               $mushokItems->branch_id = $purchase->branch_id ;
               $mushokItems->type = 'credit';
               $mushokItems->mushok = $mushok_no;
               $mushokItems->nature = $purchase->type =="Local"? $purchase->type: "Imported";
               $mushokItems->price = $purchaseItems->price;
               $mushokItems->average_price = !empty($lastAverage)? $lastAverage:0;
               $mushokItems->qty = $purchaseItems->qty;
               $mushokItems->vat_rate = $purchaseItems->vat_rate;
               $mushokItems->vds_receive_amount = $purchaseItems->vds_receive_amount;
               $mushokItems->vat_rebetable_amount = $purchaseItems->vat_rebetable_amount; 
               $mushokItems->vat_amount = $purchaseItems->vat_amount;
               $mushokItems->sd_amount = $purchaseItems->sd;
               $mushokItems->cd_amount = $purchaseItems->cd;
               $mushokItems->rd_amount = $purchaseItems->rd;
               $mushokItems->ait_amount = $purchaseItems->ait;
               $mushokItems->at_amount = $purchaseItems->at;
               // New add                    
               $mushokItems->tti = $item['tti']? $item['tti']: 0;
               $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
               $mushokItems->branch_closing = $mushokItems->branch_opening+$mushokItems->qty;
               // New Add
               $mushokItems->opening_qty = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
               $mushokItems->closing_qty = $mushokItems->opening_qty+$mushokItems->qty;
               $mushokItems->created_by = (int) auth()->user()->id;
               $mushokItems->created_at =  date("Y-m-d H:i:s", strtotime($purchase->created_at));
               $mushokItems->save();
               $product = ['id' => $productInfo->id, 'qty' => $purchaseItems->qty];
               
               Helper::postDataUpdateOnAdd($product, $mushokItems->branch_id, $purchase->created_at, $mushokItems->type);
               $this->stockUpdate(array('id' => $productInfo->id, 'qty' => $item['qty'], 'company_id' => $purchase->company_id, 'branch_id' => $purchase->branch_id));
               return response()->json([
                    'status' => true,
                    'data' => $this->getById($purchase->id),
                    'message' => "Item has been successfully added"
               ]);
     }

    public function returnList($company_id = NULL)
    {       
          if (!empty($company_id)) {
               return $this->purchaseReturn::with('company', 'vendor', 'purchase.company', 'purchase.vendor', 'user', 'returnItems.info')
               ->where('company_id', $company_id)
               ->latest('created_at')->paginate(20);
          }else{
               return $this->purchaseReturn::with('purchase.company', 'purchase.vendor', 'returnItems.info')->latest('id')->paginate(20);
          }    
    }

    public function returnDownload($company_id = NULL)
    {       
          if (!empty($company_id)) {
               return $this->purchaseReturn::with('company', 'vendor', 'purchase.company', 'purchase.purchaseItems', 'purchase.vendor', 'user', 'returnItems.info')
               ->where('company_id', $company_id)
               ->latest('id')
               ->get();
          }else{
               return $this->purchaseReturn::with('purchase.company', 'purchase.vendor', 'returnItems.info')->latest('id')->paginate(20);
          }    
    }

     public function returnSubForm($request)
     {          
          $query = $this->purchaseReturn::with('purchase.company', 'purchase.vendor', 'returnItems.info.hscode');  
          if (auth()->user()->company_id !="") {
               $query->whereHas('purchase', function ($query) {
                    $user = auth()->user();
                    $query->where('company_id', $user->company_id);
                });
          } 
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $query->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
          }
          return $query->latest()->get();
     }

     
     public function purchaseSubForm($request, $company_id)
     {          
          $data['start_date'] = date('Y-m-01 00:00:00');
          $data['end_date'] = date('Y-m-d 23:59:59');
          
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
               $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
          }
          // return $request->all();

          $company = $this->company::find($company_id);
          $purchaseType = 'Local';
          $vat_rate = 0;
          $rebatable = FALSE;
          if ($request->note == 13) {
               $purchaseType = "Imported";
               $vat_rate = 0;
               $rebatable = FALSE;
          }
          if ($request->note == 14) {
               $purchaseType = "Local";
               $vat_rate = 15;
               $rebatable = TRUE;
          }
          if ($request->note == 15) {
               $purchaseType = "Imported";
               $vat_rate = 15;
               $rebatable = TRUE;
          }
          if ($request->note == 16) {
               $purchaseType = "Local";
               $vat_rate = 15;
               $rebatable = TRUE;
          }
          if ($request->note == 17) {
               $purchaseType = "Imported";
               $vat_rate = 15;
               $rebatable = TRUE;
          }
          if ($request->note == 21) {
               $purchaseType = "Local";
               $vat_rate = 14;
               $rebatable = FALSE;
          }
          if ($request->note == 22) {
               $purchaseType = "Imported";
               $vat_rate = 14;
               $rebatable = FALSE;
          }
          DB::enableQueryLog(); // Enable query log
          $query = $this->purchaseItem::join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
               ->leftJoin('products', 'purchase_items.product_id', '=', 'products.id')
               ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
               ->leftJoin('hs_codes', 'products.hs_code_id', '=', 'hs_codes.id')
               ->select(
               'purchase_items.id', 'purchase_items.purchase_id', 'purchase_items.product_id', 'purchase_items.ait', 'purchase_items.price', 'purchase_items.qty', 
               'purchase_items.vat_rate', 'purchase_items.vat_amount', 'purchase_items.total_price', 'purchase_items.created_at', 'products.title', 'products.sku', 'products.hs_code_id', 'categories.name as category_name',
     'hs_codes.code', 'hs_codes.code_dot', 'hs_codes.description'
               )
               ->selectRaw('sum(purchase_items.total_price+purchase_items.cd+purchase_items.sd+purchase_items.rd) as total_value');
               // ->selectRaw('sum(purchase_items.total_price) as total_value')
               if ($rebatable) {
                    $query->selectRaw('sum(purchase_items.vat_rebetable_amount) as total_vat_amount');
               }else{
                    $query->selectRaw('sum(purchase_items.vat_amount) as total_vat_amount');
               }
               
               $query->whereBetween('purchase_items.created_at', [date('Y-m-d 00:00:00',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))])
               ->where('purchases.company_id', $company_id)
               ->where('purchases.type', $purchaseType);
               if ($request->note == 16 || $request->note == 17) {
                    $query->where('purchase_items.vat_rate', '<', 15);
               }elseif ($request->note == 21 || $request->note == 22) {
                    $query->where('purchase_items.vat_rate', '>', 0);
               }               
               else{
                    $query->where('purchase_items.vat_rate', $vat_rate);
               }
               
               if ($rebatable) {
                    $query->where('vat_rebetable_amount', '>', 0);
               }else{
                    $query->where('vat_rebetable_amount', 0);
               }
               
          $data = $query->get();
          return response()->json([
               'status' => true,
               'company' => $company,
               'data' => $data,                    
               'message' => DB::getQueryLog()
          ]);
     }


     public function purchaseReturn($request)
     {
          try {               
               $validator = Validator::make($request->all(), [
                    'purchase_id' => 'required'
               ]);
     
               if( $validator->fails()){
                    return ['status' => false , 'errors' => $validator->errors()];
               }  
               $purchaseInfo = $this->getById($request->purchase_id);
               $company = $this->company::where('id', auth()->user()->company_id)->first();
               
               $mushok_no = 'six_one';
               if ($company->business_type == 2) {
                    $mushok_no = 'six_two_one';
               }
               
               if (!empty($purchaseInfo)) {
                    DB::beginTransaction();

                    $reChallanNo = Helper::purRetChallanNo();
                    
                    $sl_no = explode("-",$reChallanNo);
                    $return_no    =   $company->order_prefix."-PRE-".$reChallanNo;
                    $user = auth()->user();

                    $purchaseReturn = $this->purchaseReturn;
                    $purchaseReturn->purchase_id      = $purchaseInfo->id;
                    $purchaseReturn->company_id      = $purchaseInfo->company_id;
                    $purchaseReturn->vendor_id      = $purchaseInfo->vendor_id;
                    $purchaseReturn->branch_id      = auth()->user()->branch_id;
                    $purchaseReturn->sl_no      = (int) $sl_no[2];
                    $purchaseReturn->purchase_id      = $purchaseInfo->id;
                    $purchaseReturn->return_no        = $return_no;
                    $purchaseReturn->return_reason = $request->return_reason;
                    $purchaseReturn->created_by       = $user->id;
                    $purchaseReturn->created_at       = date("Y-m-d H:i:s");
                    $purchaseReturn->save();
                    

                    foreach ($request->returnedItems as $item) {
                         $alreadyReturned = $this->purchaseReturnItem::with('purchaseReturn.purchase.purchaseItems')
                              ->whereHas('purchaseReturn', function ($query) use ($purchaseInfo) {
                                   $query->where('purchase_id', $purchaseInfo->id);
                              })
                              ->where('product_id', $item['id'])
                              ->sum('qty');
                              
                         $purchaseItem = $this->purchaseItem::with('purchase')
                         ->whereHas('purchase', function ($query) use ($purchaseInfo) {
                              $query->where('purchase_id', $purchaseInfo->id);
                         })
                         ->where('product_id', $item['id'])
                         ->first();

                         $totalReturn = ($item['qty'] + $alreadyReturned);
                         if ($totalReturn > $purchaseItem->qty) {
                              DB::rollback();
                              return response()->json([
                                   'status' => false,
                                   'data' => $purchaseItem,
                                   'errors' => '', 
                                   'message' => "The purchase Qty is ".$purchaseItem->qty." but your return with previous is ". $totalReturn ." items so, it can't be return"
                              ]);
                         }
                         $lastMushok = $this->mushok::where(['product_id' => $item['id'], 'company_id' => $company->id, 'mushok' => $mushok_no])
                         ->where('created_at', '<=', date("Y-m-d H:i:s"))
                         ->orderBy('created_at', 'DESC')
                         ->where('is_transfer', 0)
                         ->first();
                         // Last Branch Mushok
                         $branchLastMushok = $this->mushok::where(['product_id' => $item['id'], 'branch_id' => $purchaseInfo->branch_id, 'mushok' => $mushok_no])
                         ->where('created_at', '<=', date("Y-m-d H:i:s"))
                         ->orderBy('created_at', 'DESC')
                         ->first();


                         $lastAverage = $this->mushok::where(['product_id' => $item['id'], 'company_id' => $company->id, 'mushok' => $mushok_no])
                         ->where('price', '>', 0)
                         ->avg('price');


                         // return $lastAverage;
                         // if ($this->whatever($purchaseInfo->items, 'product_id', $item['id'])) {
                              $returnItems = new $this->purchaseReturnItem;
                              $returnItems->purchase_return_id    = (int) $purchaseReturn->id;
                              $returnItems->product_id  = $item['id'];
                              $returnItems->cd       = $item['cd']? $item['cd']: 0;
                              $returnItems->rd       = $item['rd']? $item['rd']: 0;
                              $returnItems->sd       = $item['sd']? $item['sd']: 0;
                              $returnItems->vat_rate = $item['vat_rate'];
                              $returnItems->vat_amount = ((($item['price']* $item['vat_rate'])/100) * $item['qty']);
                              $returnItems->price    = $item['price'];
                              $returnItems->qty      = $item['qty'];
                              $returnItems->total_price = ($returnItems->price*$returnItems->qty);
                              
                              $returnItems->save();
                              
                              // Mushok Insert
                              $totalPrice = ($item['price'] * $item['qty']);
                              $vatAmount = ($totalPrice * $item['vat_rate'])/100;
                              $mushokItems = new $this->mushok;
                              $mushokItems->purchase_return_id    = (int) $purchaseReturn->id;
                              $mushokItems->purchase_id    = (int) $purchaseInfo->id;
                              $mushokItems->product_id  = $item['id'];
                              $mushokItems->company_id  = $purchaseInfo->company_id;
                              $mushokItems->branch_id  = $purchaseInfo->branch_id;
                              $mushokItems->type  = 'debit';
                              $mushokItems->mushok  = $mushok_no;
                              $mushokItems->price    = $item['price'];
                              $mushokItems->average_price = !empty($lastAverage)? $lastAverage:0;
                              $mushokItems->qty         = $item['qty'];
                              $mushokItems->purchase_return_qty = $item['qty'];
                              $mushokItems->vat_rate = $item['vat_rate'];
                              $mushokItems->vat_amount = $vatAmount;
                              $mushokItems->sd_amount = $item['sd']? $item['sd']: 0;
                              $mushokItems->cd_amount = $item['cd']? $item['cd']: 0;
                              $mushokItems->rd_amount = $item['rd']? $item['rd']: 0;
                              // Branch Balance
                              $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                              $mushokItems->branch_closing = $mushokItems->branch_opening-$mushokItems->qty;
                              // Company Balance
                              $mushokItems->opening_qty = !empty($lastMushok)? $lastMushok->closing_qty:0;
                              $mushokItems->closing_qty = $mushokItems->opening_qty-$mushokItems->qty;
                              $mushokItems->created_by = (int) auth()->user()->id;
                              $mushokItems->created_at = $purchaseReturn->created_at;
                              // return $mushokItems;
                              $mushokItems->save();
          
                              // Update Stock
                              $this->stockUpdate(array('id' => $mushokItems->product_id, 'qty' => $item['qty'], 'company_id' => $mushokItems->company_id, 'branch_id' => $mushokItems->branch_id), 'deduction');

                              $product = ['id' => $mushokItems->product_id, 'qty' => $mushokItems->qty];
                              Helper::postDataUpdate($product, $mushokItems->branch_id, date('Y-m-d H:i:s'), $mushokItems->type);
                         // }else{
                         //      DB::rollback();
                         //      return response()->json([
                         //           'status' => false,
                         //           'data' => [],
                         //           'errors' => '', 
                         //           'message' => "Return item is not exist in purchase"
                         //      ]);
                         // }
                         
                    }
                    DB::commit();
                    $returnedInfo = $this->getReturnById($purchaseReturn->id);
          
                    return response()->json([
                         'status' => true,
                         'data' => $returnedInfo,
                         'errors' => '', 
                         'message' => $return_no." New purchase return has been successfully created",
                    ]);
               }else{
                    DB::rollback();
                    return response()->json([
                         'status' => false,
                         'data' => [],
                         'errors' => '', 
                         'message' => "Your purchase number is invalid"
                    ]);
               }
               
          }catch (\Throwable $th) {
               DB::rollback();
               return response()->json([
                    'status' => false,
                    'data' => $purchaseInfo,
                    'errors' => 'An Error has occurred', 
                    'message' => $th->getMessage()
               ]);
                    
          }
     }

     public function manualPurchaseReturn($request) {
          try {
               $user = auth()->user();
               DB::beginTransaction();
               $company = $this->company::where('id', $user->company_id)->first();
               $mushok_no = 'six_one';
               if ($company->business_type == 2) {
                    $mushok_no = 'six_two_one';
               }
               $time = date("h:i:s");
               $reChallanNo = Helper::purRetChallanNo();
          
               $sl_no = explode("-",$reChallanNo);
               $return_no    =   "FEL-PRE-".$reChallanNo;
               
               $purchaseReturn = new $this->purchaseReturn;
               $purchaseReturn->purchase_id      = 0;
               $purchaseReturn->company_id      = $user->company_id;
               $purchaseReturn->branch_id      = $user->branch_id;
               $purchaseReturn->vendor_id      = $request->vendor_id;
               $purchaseReturn->sl_no      = (int) $sl_no[2];
               $purchaseReturn->return_no        = $return_no;
               $purchaseReturn->challan_no        = $request->challan_no;
               $purchaseReturn->challan_date        = date('Y-m-d', strtotime($request->challan_date));
               $purchaseReturn->return_reason        = $request->return_reason;
               $purchaseReturn->created_by       = $user->id;
               $purchaseReturn->created_at       = date('Y-m-d H:i:s', strtotime($request->issue_date. $time));
               $purchaseReturn->save();

               foreach ($request->returnedItems as $key => $item) {
                    $item = (object) $item;
                    $productInfo = $this->product::where('id', $item->id)->where('company_id', 5)->first();
                    
                    $stockStock = $this->stock::where(['product_id'=> $item->id, 'branch_id'=> $purchaseReturn->branch_id])->sum('stock');
                    if ($stockStock>=$item->return_qty) {                         
                         $companyLastMushok = $this->mushok::where(['product_id' => $productInfo->id, 'company_id' => $purchaseReturn->company_id, 'mushok' => 'six_two_one'])
                         ->where('created_at', '<=', $purchaseReturn->created_at)
                         ->orderBy('created_at', 'DESC')
                         ->where('is_transfer', 0)
                         ->first();
                         
                         // Last Branch Mushok
                         $branchLastMushok = $this->mushok::where(['product_id' => $productInfo->id, 'branch_id' => $purchaseReturn->branch_id, 'mushok' => 'six_two_one'])
                         ->where('created_at', '<=',  $purchaseReturn->created_at)
                         ->orderBy('created_at', 'DESC')
                         ->first();


                         $lastAverage = $this->mushok::where(['product_id' => $productInfo->id, 'company_id' => $purchaseReturn->company_id, 'mushok' => 'six_two_one'])
                         ->where('price', '>', 0)
                         ->avg('price');


                         $returnItems = new $this->purchaseReturnItem;
                         $returnItems->purchase_return_id    = (int) $purchaseReturn->id;
                         $returnItems->product_id  = $productInfo->id;
                         $returnItems->challan_item_value = $item->challan_value;               
                         $returnItems->challan_item_vat = $item->challan_vat;
                         $returnItems->challan_item_qty = $item->challan_qty;
                         $returnItems->vat_rate = $item->vat_rate;
                         $returnItems->vat_amount = $item->return_vat;
                         $returnItems->qty      = $item->return_qty;
                         $returnItems->price    = ($item->return_value / $item->return_qty);                         
                         $returnItems->total_price = $item->return_value;    
                         $returnItems->save();
                         
                         $vatAmount = $returnItems->vat_amount;
                         $mushokItems = new $this->mushok;
                         $mushokItems->purchase_return_id    = (int) $purchaseReturn->id;
                         $mushokItems->purchase_id    = NULL;
                         $mushokItems->product_id  = $returnItems->product_id;
                         $mushokItems->company_id  = $purchaseReturn->company_id;
                         $mushokItems->branch_id  = $purchaseReturn->branch_id;
                         $mushokItems->type  = 'debit';
                         $mushokItems->mushok  = $mushok_no;
                         $mushokItems->price    = $returnItems->price;
                         $mushokItems->average_price = !empty($lastAverage)? $lastAverage:0;
                         $mushokItems->qty = $returnItems->qty;
                         $mushokItems->purchase_return_qty = $returnItems->qty;
                         $mushokItems->vat_rate =  $returnItems->vat_rate;
                         $mushokItems->vat_amount = $vatAmount;
                         // Branch Balance
                         if (!empty($branchLastMushok)) {
                              $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                              $mushokItems->branch_closing = $mushokItems->branch_opening-$mushokItems->qty;
                         }else{
                              $mushokItems->branch_opening = 0;
                              $mushokItems->branch_closing = $mushokItems->qty;
                         }

                         // Company Balance
                         $mushokItems->opening_qty = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
                         $mushokItems->closing_qty = $mushokItems->opening_qty-$mushokItems->qty;

                         $mushokItems->created_by = (int) auth()->user()->id;
                         $mushokItems->created_at = $purchaseReturn->created_at;
                         $mushokItems->save();

                         // Update Stock
                         $this->stockUpdate(array('id' => $mushokItems->product_id, 'qty' => $mushokItems->qty, 'company_id' => $mushokItems->company_id, 'branch_id' => $mushokItems->branch_id), 'deduction');
                         $product = ['id' => $mushokItems->product_id, 'qty' => $mushokItems->qty];
                         Helper::postDataUpdate($product, $mushokItems->branch_id, $purchaseReturn->created_at, $mushokItems->type);
                    }else{
                         DB::rollBack();
                         return response()->json([
                              'status' => false,
                              'data' => [],
                              'errors' => '', 
                              'message' => $productInfo->sku." product stock is not available",
                         ]);
                    }
               }
               DB::commit();
               return response()->json([
                    'status' => true,
                    'data' => [],
                    'errors' => '', 
                    'message' => "Purchases Return (Mushok 6.8) Successfully Issued",
               ]); 
          } catch (\Throwable $th) {
               return response()->json([
                    'status' => false,
                    'data' => [],
                    'errors' => $th->getMessage(),
                    'message' => $th->getMessage()
               ]); 
          }
          
     }

     public function manualPurchaseReturnBulk($request) {   
          $filename = $request->file('csvfile');
               if ($_FILES["csvfile"]["size"] > 0) {
               $file = fopen($filename, "r");
               $i = 0;
               $mushok_no = 'six_two_one';
               DB::beginTransaction();
               while (($product_info = fgetcsv($file, 10000, ",")) !== FALSE) {
                    
                    $i++;
                    if ($i>1) { 
                         // return $product_info;
                         $productInfo = $this->product::where('sku', $product_info[8])->where('company_id', 5)->first();
                         // return $productInfo;
                    
                         $reChallanNo = Helper::purRetChallanNo();
                    
                         $sl_no = explode("-",$reChallanNo);
                         $return_no    =   "FEL-PRE-".$reChallanNo;
                         $user = auth()->user();
                         
                         $purchaseReturn = new $this->purchaseReturn;
                         $purchaseReturn->purchase_id      = 0;
                         $purchaseReturn->company_id      = 5;
                         $purchaseReturn->branch_id      = 21;
                         $purchaseReturn->vendor_id      = 20;
                         $purchaseReturn->sl_no      = (int) $sl_no[2];
                         $purchaseReturn->return_no        = $product_info[1];
                         $purchaseReturn->challan_no        = $product_info[3];
                         $purchaseReturn->challan_date        = $product_info[4];
                         $purchaseReturn->return_reason        = $product_info[9];
                         $purchaseReturn->note        = $product_info[1];
                         $purchaseReturn->created_by       = $user->id;
                         $purchaseReturn->created_at       = date('Y-m-d H:i:s', strtotime($product_info[2]. "09:".$i.":02"));
                         $purchaseReturn->save();  
                         
                         $companyLastMushok = $this->mushok::where(['product_id' => $productInfo->id, 'company_id' => $purchaseReturn->company_id, 'mushok' => 'six_two_one'])
                         ->where('created_at', '<=', $purchaseReturn->created_at)
                         ->orderBy('created_at', 'DESC')
                         ->where('is_transfer', 0)
                         ->first();
                         
                         // Last Branch Mushok
                         $branchLastMushok = $this->mushok::where(['product_id' => $productInfo->id, 'branch_id' => $purchaseReturn->branch_id, 'mushok' => 'six_two_one'])
                         ->where('created_at', '<=',  $purchaseReturn->created_at)
                         ->orderBy('created_at', 'DESC')
                         ->first();


                         $lastAverage = $this->mushok::where(['product_id' => $productInfo->id, 'company_id' => $purchaseReturn->company_id, 'mushok' => 'six_two_one'])
                         ->where('price', '>', 0)
                         ->avg('price');
                              $returnItems = new $this->purchaseReturnItem;
                              $returnItems->purchase_return_id    = (int) $purchaseReturn->id;
                              $returnItems->product_id  = $productInfo->id;
                              $returnItems->challan_item_value = $product_info[5];
                              
                              $returnItems->challan_item_vat = $product_info[6];
                              $returnItems->challan_item_qty = $product_info[7];
                              $returnItems->vat_rate = $product_info[13];
                              $returnItems->vat_amount = $product_info[11];
                              $returnItems->price    = $product_info[10]/$product_info[12];
                              $returnItems->qty      = $product_info[12];
                              $returnItems->total_price = $product_info[10];  
                              // return $returnItems;                  
                              $returnItems->save();
                              
                              // Mushok Insert
                              
                              $vatAmount = $returnItems->vat_amount;
                              $mushokItems = new $this->mushok;
                              $mushokItems->purchase_return_id    = (int) $purchaseReturn->id;
                              $mushokItems->purchase_id    = NULL;
                              $mushokItems->product_id  = $returnItems->product_id;
                              $mushokItems->company_id  = $purchaseReturn->company_id;
                              $mushokItems->branch_id  = $purchaseReturn->branch_id;
                              $mushokItems->type  = 'debit';
                              $mushokItems->mushok  = $mushok_no;
                              $mushokItems->price    = $returnItems->price;
                              $mushokItems->average_price = !empty($lastAverage)? $lastAverage:0;
                              $mushokItems->qty = $returnItems->qty;
                              $mushokItems->purchase_return_qty = $returnItems->qty;
                              $mushokItems->vat_rate =  $returnItems->vat_rate;
                              $mushokItems->vat_amount = $vatAmount;
                              // Branch Balance
                              if (!empty($branchLastMushok)) {
                                   $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                                   $mushokItems->branch_closing = $mushokItems->branch_opening-$mushokItems->qty;
                              }else{
                                   $mushokItems->branch_opening = 0;
                                   $mushokItems->branch_closing = $mushokItems->qty;
                              }

                              // Company Balance
                              $mushokItems->opening_qty = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
                              $mushokItems->closing_qty = $mushokItems->opening_qty-$mushokItems->qty;

                              $mushokItems->created_by = (int) auth()->user()->id;
                              $mushokItems->created_at = $purchaseReturn->created_at;
                              // return $mushokItems;
                              // return $mushokItems;
                              $mushokItems->save();

                              // Update Stock
                              $this->stockUpdate(array('id' => $mushokItems->product_id, 'qty' => $mushokItems->qty, 'company_id' => $mushokItems->company_id, 'branch_id' => $mushokItems->branch_id), 'deduction');
                              $product = ['id' => $mushokItems->product_id, 'qty' => $mushokItems->qty];
                              Helper::postDataUpdate($product, $mushokItems->branch_id, $purchaseReturn->created_at, $mushokItems->type);
                         
                         }
                    }
               }
               DB::commit();
               return response()->json([
                    'status' => true,
                    'data' => [],
                    'errors' => '', 
                    'message' => "Debit note upload successfully completed",
               ]);
     }

     function updateReturn($request) {
          $returnArray = explode("-",$request->return_no);
          $issueDate = date('Y-m-d H:i:s', strtotime($request->issue_date));
          $purchaseReturn = $this->purchaseReturn::find($request->return_id);
          $purchaseReturn->return_no = $request->return_no;
          $purchaseReturn->sl_no = (int) $returnArray[4];
          $purchaseReturn->challan_no = $request->challan_no !=""? $request->challan_no: $purchaseReturn->challan_no;
          $purchaseReturn->challan_date = $request->challan_date !=""? $request->challan_date: $purchaseReturn->challan_date;
          $purchaseReturn->return_reason = $request->return_reason;
          $purchaseReturn->created_at = $issueDate;
          $purchaseReturn->save();
          return response()->json([
               'status' => true,
               'data' => $purchaseReturn,
               'errors' => '', 
               'message' => $purchaseReturn->return_no." debit note no. has been changed",
          ]);
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
          $purchase = $this->getById($id);
          $purchase->created_at = date('Y-m-d H:i:s', strtotime($request->created_at));
          if($purchase->save()){
               return $this->correctionMushok($purchase);
          }
          
     } 

     function correctionMushok($purchase) {
          $purchaseItems = $purchase->purchaseItems;
          foreach ($purchaseItems as $key => $item) {
               $purItem = PurchaseItem::find($item->id);
               $purItem->created_at = $purchase->created_at;
               $purItem->save();
               $data['start_date'] = date("2023-09-01");
               $data['end_date'] = date("Y-m-d");
               
               $productInfo = $this->product::find($item->product_id);      
               $mushok = MushokSix::where('product_id', $item->product_id)->where('purchase_id', $purchase->id)->first();
               $mushok->created_at = $purchase->created_at;
               $mushok->save();
               Helper::updateMushokCompany($productInfo, $data);
          }
          return true;
          
     }
        
     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function delete($id){
     //   return $this->getById($id)->delete();
          try {
               $purchase = $this->getById($id);
               if ($purchase->company_id != auth()->user()->company_id) {
                    return response()->json([
                         'status' => false,
                         'data' => [],
                         'errors' => '', 
                         'message' => "You have no access to delete this purchase",
                    ]);
               }

               DB::beginTransaction();
               $this->stockUpdateMultiple($purchase);
               
               if (!empty($purchase)) {
                    $purchase->delete();
               }    
               DB::commit();
               return response()->json([
                    'status' => true,
                    'data' => [],
                    'errors' => '', 
                    'message' => "Your purchase has been deleted"
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

     protected function stockUpdateMultiple($purchase){    
          foreach ($purchase->purchaseItems as $key => $item) {
               
               $stock = new $this->stock;
               $itemStock = $stock::where(['branch_id'=> $purchase->branch_id, 'product_id' => $item->product_id])
               ->orderBy('stock', 'desc')
               ->first();
               $itemStock->stock = ($itemStock->stock-$item->qty);
               $itemStock->update();
               $product = ['id' => $item->product_id, 'qty' => $item->qty];
               
               Helper::postDataUpdateOnDelete($product, $purchase->branch_id, date('Y-m-d H:i:s', strtotime($purchase->created_at)), 'credit');
          }
          $this->mushok::where('purchase_id', $purchase->id)->delete();
          $this->purchaseItem::where('purchase_id', $purchase->id)->delete();
          return true;
     }

     public function guard()
     {
          return Auth::guard('api');
     }

     protected function stockUpdate($item, $type = "addition"){
          $stock = new $this->stock;                 
          $stockInfo = $stock::where(['product_id'=> $item['id'], 'company_id'=> $item['company_id'], 'branch_id'=> $item['branch_id']])->first();
          if (!empty($stockInfo)) {
               if ($type == 'deduction') {
                    $stockInfo->stock -= $item['qty'];
                    $stockInfo->update();
                    return true;
               }else{
                    $stockInfo->stock += $item['qty'];
                    $stockInfo->update();
                    return true;
               }
          }else{
               $stock->product_id = $item['id'];
               $stock->company_id = $item['company_id'];
               $stock->branch_id = $item['branch_id'];
               $stock->stock = $item['qty'];
               $stock->save();
               return true;
          }
     }

     // Mushok 6.1

     public function mushok_six_one($request)
     {
          $user = auth()->user();
          
          $query = $this->mushok::query();
          $query->with('purchase.vendor', 'purchase.company', 'finished', 'info.category');
          if (!empty($user->company_id)) {
               $query->where('company_id', $user->company_id);
          }
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
          }
          
          $query->where('product_id', $request->product_id);
          $query->where('is_transfer', 0);
          // $query->orderBy('created_at', 'asc');
          return $query->get();
     }

     // Mushok 6.2
     public function mushok_six_two($request)
     { 
          $data['start_date'] = date('Y-m-01 00:00:00');
          $data['end_date'] = date('Y-m-d 23:59:59');
          
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
               $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
          }

          $user = auth()->user();   
          $query = $this->mushok::query();

          $query->with('finished', 'sales.company', 'sales.branch', 'info.category', 'sales.customer');
          if (!empty($user->company_id)) {
               $query->where('company_id', $user->company_id);
          }
          
          $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
          
          $query->where('product_id', $request->product_id);  
          $query->where('is_transfer', 0);          
          $query->orderBy('created_at', 'asc');
          return $query->get();
     }

     public function search($request, $user)
     {
          $query = $this->model::query();
          $query->with('company', 'vendor', 'branch', 'user');
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $query->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
          }
          if ((isset($request->purchase_no) && $request->purchase_no !="")) {
               // $query->where('sales_no', $request->sales_no);
               $query->where('purchase_no', 'LIKE', '%' . $request->purchase_no . '%');
          }
          if ((isset($request->challan_no) && $request->challan_no !="")) {
               // $query->where('sales_no', $request->sales_no);
               $query->where('challan_no', 'LIKE', '%' . $request->challan_no . '%');
          }
          if ((isset($request->type) && $request->type !="")) {
               $query->where('type', $request->type);
          }
          $query->where('company_id', $user->company_id);
          $purchases = $query->latest()->paginate(20);
          return response()->json([
               'status' => true,
               'data' => $purchases,
               'errors' => '', 
               'message' => "Purchase List Loaded",
           ]);
     }

     public function comparisonReport($request, $company_id) {
          $previous_year_start = date("Y-m-d", strtotime("$request->start_date -1 year"));
          $previous_year_end = date("Y-m-d", strtotime("$request->end_date -1 year"));
         
          $data['previous_year'] = $this->purchaseItem::with('purchase')
          ->select(DB::raw('sum(total_price) as total_value'),  DB::raw('sum(sd) as total_sd'), DB::raw('sum(at) as total_at'), DB::raw('sum(vat_amount) as total_vat'), DB::raw("DATE_FORMAT(created_at, '%m-%Y') month_year"), DB::raw("DATE_FORMAT(created_at, '%Y') year"))
          ->whereHas('purchase', function($query) use ($company_id, $request) {
               $query->where('company_id', $company_id);
               if($request->type !='') {
                    $query->where('type', $request->type);
               }
          })
          ->whereBetween('created_at', ["{$previous_year_start}", "{$previous_year_end}-12-31"])
          ->groupBy('month_year')
          ->get();

          $data['current_year'] = $this->purchaseItem::with('purchase')
          ->select(DB::raw('sum(total_price) as total_value'),  DB::raw('sum(sd) as total_sd'), DB::raw('sum(at) as total_at'), DB::raw('sum(vat_amount) as total_vat'), DB::raw("DATE_FORMAT(created_at, '%m-%Y') month_year"), DB::raw("DATE_FORMAT(created_at, '%Y') year"))
          ->whereHas('purchase', function($query) use ($company_id, $request) {
               $query->where('company_id', $company_id);
               if($request->type !='') {
                    $query->where('type', $request->type);
               }
          })
          ->whereBetween('created_at', ["{$request->start_date}", "{$request->end_date}-12-31"])
          ->groupBy('month_year')
          ->get();
          return response()->json([
               'status' => true,
               'data' => $data,                    
               'message' => "Report has been loaded"
          ]);
     
    }

    public function vendorStatement($request, $company_id) {
          $data['start_date'] = date('Y-m-01 00:00:00');
          $data['end_date'] = date('Y-m-d 23:59:59');
          
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
               $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
          }
          $query = $this->vendor::query();
          $query->join("purchases", "vendors.id","=","purchases.vendor_id")
          ->join('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
          ->join('products', 'purchase_items.product_id', '=', 'products.id')
          ->select('vendors.id','vendors.company_id','vendors.name', 'vendors.vendor_bin', 'purchases.branch_id', 'purchases.vendor_id', 'purchases.created_at')
          ->selectRaw('sum(purchase_items.qty) as total_qty')
          ->selectRaw('sum(purchase_items.total_price) as total_value')
          ->selectRaw('sum(purchase_items.at) as total_at')
          ->selectRaw('sum(purchase_items.sd) as total_sd')
          ->selectRaw('sum(purchase_items.vat_amount) as total_vat')
          ->where('purchases.company_id', $company_id);
          if($request->has('branch_id') && $request->branch_id != '')
          {
               $query->where('purchases.branch_id', $request->branch_id);
          }
          if($request->has('vendor_id') && $request->vendor_id != '')
          {
               $query->where('purchases.vendor_id', $request->vendor_id);
          }
          if($request->has('product_id') && $request->product_id != '')
          {
               $query->where('purchase_items.product_id', $request->product_id);
          }
          $query->whereBetween('purchases.created_at', [$data['start_date'], $data['end_date']]);
          $query->groupBy('purchases.vendor_id');
          $query->orderBy('total_vat', 'desc');
          $purchaseStatement = $query->get();
          return response()->json([
               'status' => true,
               'data' => $purchaseStatement,                    
               'message' => "Report has been loaded"
          ]);
     }

     public function productPurchaseStatement($request, $company_id) {
          $data['start_date'] = date('Y-m-01 00:00:00');
          $data['end_date'] = date('Y-m-d 23:59:59');
          
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
               $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
          }
          $query = $this->product::query();
          $query->join("purchase_items", "products.id","=","purchase_items.product_id")
          ->join("purchases", "purchase_items.purchase_id","=","purchases.id")
          ->join("vendors", "purchases.vendor_id","=","vendors.id")          
          ->select('products.id','products.title','products.sku', 'products.price', 'purchases.branch_id', 'purchases.vendor_id')
          ->selectRaw('sum(purchase_items.qty) as total_qty')
          ->selectRaw('sum(purchase_items.total_price) as total_value')
          ->selectRaw('sum(purchase_items.at) as total_at')
          ->selectRaw('sum(purchase_items.sd) as total_sd')
          ->selectRaw('sum(purchase_items.vat_amount) as total_vat')
          ->where('purchases.company_id', $company_id);
          if($request->has('branch_id') && $request->branch_id != '')
          {
               $query->where('purchases.branch_id', $request->branch_id);
          }
          if($request->has('vendor_id') && $request->vendor_id != '')
          {
               $query->where('purchases.vendor_id', $request->vendor_id);
          }
          if($request->has('product_id') && $request->product_id != '')
          {
               $query->where('purchase_items.product_id', $request->product_id);
          }
          $query->whereBetween('purchases.created_at', [$data['start_date'], $data['end_date']]);
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
