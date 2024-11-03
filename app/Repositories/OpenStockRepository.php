<?php
namespace App\Repositories;

use App\Classes\Helper;
use App\Jobs\StockBulkUploadJob;
use App\Models\Company;
use App\Models\ItemStock;
use App\Models\MushokSix;
use App\Models\OpenStock;
use App\Models\OpenStockItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class OpenStockRepository implements BaseRepository{

     protected  $model;
     protected  $openStockItems;
     protected  $product;
     protected  $stock;
     protected  $mushok;
     protected  $user;
     protected  $company;
     
     public function __construct(OpenStock $model, OpenStockItem $openStockItems, Product $product, ItemStock $stock, MushokSix $mushok, Company $company)
     {
        $this->model = $model;
        $this->openStockItems = $openStockItems;
        $this->product = $product;
        $this->stock = $stock;
        $this->mushok = $mushok;
        $this->company = $company;
        $this->user = auth()->user();
     }

     /**
      * all resource get
      * @return Collection
      */
      public function getAll($company_id = NULL){ 
          $user = auth()->user();         
          if (!empty($user->company_id)) {
               return $this->model::with('company', 'branch')
               ->where('company_id', $user->company_id)
               ->latest()->paginate(20);
          }else{
               return $this->model::with('company', 'branch')->latest()->paginate(20);
          }
     }

     /**
      * all resource get
      * @return Collection
      */
      public function getFull($company_id = NULL){
        if (!empty($company_id)) {
            return $this->model::where('company_id', $company_id)
            ->with('company')
            ->latest()->get();
        }else{
            return $this->model::with('company')->latest()->get();
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
          return $this->model::with('company', 'branch', 'stockItems.info.hscode')
          ->where('id', $id)->first();
     }

     public function download(){
          $user = auth()->user();
          if (!empty($user->company_id)) {
               return $this->model::with('company', 'branch', 'stockItems.info')
               ->where('company_id', $user->company_id)
               ->lazy();
          }else{
               return $this->model::with('company', 'branch', 'stockItems.info')
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
          return $this->model::with('company', 'branch', 'stockItems.info')
          ->where('id', $id)->first();
     }

     /**
     * resource create
     * @param  $request
     * @return \Illuminate\Http\Response
     */

     public function create222($request){     
          try {       
          DB::beginTransaction();
          
          $company = $this->company::where('id', $this->guard()->user()->company_id)->first();
          $mushok_no = 'six_two';
          if ($company->business_type == 2) {
               $mushok_no = 'six_two_one';
          }
          $stock_no    =   "STO".date('ymdHis');
          $openStock = $this->model;
          $openStock->company_id       = (int) $this->guard()->user()->company_id? (int) $this->guard()->user()->company_id: 1;
          $openStock->branch_id = $request->stock_branch;
          $openStock->admin_id = $this->guard()->user()->id;
          $openStock->open_stock_no      = $stock_no;
          
          $openStock->save();

          foreach ($request->stockItems as $item) {
               // $checkStockExist = $this->stock::where(['product_id' => $item['item_id'], 'company_id' => $company->id])->first();
               // if ($checkStockExist) {
               //      DB::rollback();
               //      return "Product already stock";
               // }

               $productInfo = $this->product::select('id', 'hs_code_id', 'hs_code', 'sku', 'title', 'model', 'type', 'price', 'status')
                    ->where('id', $item['item_id'])->first();

               $mushok_no = (!empty($productInfo) && $productInfo->type == 1)? 'six_two':'six_one';
               if ($company->business_type == 2) {
                    $mushok_no = 'six_two_one';
               }

               $companyLastMushok = $this->mushok::where(['product_id' => $item['item_id'], 'company_id' => $company->id, 'mushok' => $mushok_no]) 
               ->latest('id')
               ->first();

               $branchLastMushok = $this->mushok::where(['product_id' => $item['item_id'], 'branch_id' => $request->stock_branch, 'mushok' => $mushok_no]) 
               ->latest('id')
               ->first();

               $lastAverage = $this->mushok::where(['product_id' => $item['item_id'], 'company_id' => $company->id, 'mushok' => $mushok_no])->avg('price');
                    
               if (!empty($productInfo)) {
                    $openStockItems = new $this->openStockItems;
                    $openStockItems->open_stock_id    = (int) $openStock->id;
                    $openStockItems->product_id  = $item['item_id'];
                    $openStockItems->item_info   = json_encode($productInfo);
                    $openStockItems->price    = $item['price'];
                    $openStockItems->qty      = $item['qty'];
                    $openStockItems->save();
                    
                    // Mushok Insert
                    $mushokItems = new $this->mushok;
                    $mushokItems->open_stock_id = (int) $openStock->id;
                    $mushokItems->product_id = $item['item_id'];
                    $mushokItems->company_id = $openStock->company_id;
                    $mushokItems->branch_id = $request->stock_branch;
                    $mushokItems->type = 'credit';
                    $mushokItems->mushok = $mushok_no;
                    $mushokItems->price = $item['price'];
                    $mushokItems->average_price = !empty($lastAverage)? $lastAverage:$item['price'];
                    $mushokItems->qty = $item['qty'];
                    $mushokItems->vat_rate = 0;
                    $mushokItems->vds_receive_amount = 0;
                    $mushokItems->vat_rebetable_amount = 0; 
                    $mushokItems->vat_amount = 0;

                    if (!empty($companyLastMushok)) {
                         $mushokItems->qty = $item['qty'];
                         $mushokItems->opening_qty = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
                         $mushokItems->closing_qty = $mushokItems->opening_qty+$mushokItems->qty;
                    }else{
                         $mushokItems->qty = 0;
                         $mushokItems->opening_qty = $item['qty'];
                         $mushokItems->closing_qty = $item['qty'];
                    }

                    if (!empty($branchLastMushok)) {
                         $mushokItems->nature = "StockIn";
                         $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                         $mushokItems->branch_closing = $mushokItems->branch_opening+$mushokItems->qty;
                    }else{
                         $mushokItems->nature = "OpeningStock";
                         $mushokItems->branch_opening = $item['qty'];
                         $mushokItems->branch_closing = $item['qty'];
                    }
                    
                    $mushokItems->created_by = (int) $this->guard()->user()->id;
                    $mushokItems->save();
                    $this->stockUpdate(array('id' => $item['item_id'], 'qty' => $item['qty'], 'company_id' => $openStock->company_id, 'branch_id' => $request->stock_branch));
               }
          }
               DB::commit();
               return $this->getById($openStock->id);
          
          }catch (\Throwable $th) {
               return $th->getMessage();
          }
     }

     public function create($request){     
          try {  
               DB::beginTransaction();
               $time = date('H:i:s');
               $stockDate = date('Y-m-d', strtotime($request->stock_date));
               $dataTime  =$stockDate.' '.$time;
               
               $company = $this->company::where('id', $this->guard()->user()->company_id)->first();
               
               $stock_no    =   "STO".date('ymdHis');
               $openStock = $this->model;
               $openStock->company_id = auth()->user()->company_id;
               $openStock->branch_id = $request->stock_branch;
               $openStock->admin_id = auth()->user()->id;
               $openStock->open_stock_no = $stock_no;
               $openStock->created_at = $dataTime;

               $openStock->save();

               foreach ($request->stockItems as $item) {
                    // $checkStockExist = $this->stock::where(['product_id' => $item['item_id'], 'company_id' => $company->id])->first();
                    // if ($checkStockExist) {
                    //      DB::rollback();
                    //      return "Product already stock";
                    // }


                    $productInfo = $this->product::select('id', 'hs_code_id', 'hs_code', 'sku', 'title', 'model', 'type', 'price', 'status')
                         ->where('id', $item['item_id'])->first();

                    $mushok_no = (!empty($productInfo) && $productInfo->type == 1)? 'six_two':'six_one';
                    if ($company->business_type == 2) {
                         $mushok_no = 'six_two_one';
                    }

                    $companyLastMushok = $this->mushok::where(['product_id' => $item['item_id'], 'company_id' => $company->id, 'mushok' => $mushok_no])
                    ->where('created_at', '<=', $openStock->created_at)
                    ->orderBy('created_at', 'desc')
                    ->first();

                    

                    $branchLastMushok = $this->mushok::where(['product_id' => $item['item_id'], 'branch_id' => $request->stock_branch, 'mushok' => $mushok_no]) 
                    ->where('created_at', '<=', $openStock->created_at)
                    ->orderBy('created_at', 'desc')
                    ->first();

                    // return $branchLastMushok;

                    $lastAverage = $this->mushok::where(['product_id' => $item['item_id'], 'company_id' => $company->id, 'mushok' => $mushok_no])->avg('price');
                         
                    if (!empty($productInfo)) {
                         $openStockItems = new $this->openStockItems;
                         $openStockItems->open_stock_id    = (int) $openStock->id;
                         $openStockItems->product_id  = $item['item_id'];
                         $openStockItems->item_info   = json_encode($productInfo);
                         $openStockItems->price    = $item['price'];
                         $openStockItems->qty      = $item['qty'];
                         $openStockItems->save();
                         
                         // Mushok Insert
                         $mushokItems = new $this->mushok;
                         $mushokItems->open_stock_id = (int) $openStock->id;
                         $mushokItems->product_id =  $openStockItems->product_id;
                         $mushokItems->company_id = $openStock->company_id;
                         $mushokItems->branch_id = $request->stock_branch;
                         $mushokItems->type = 'credit';
                         $mushokItems->mushok = $mushok_no;
                         $mushokItems->price = $item['price'];
                         $mushokItems->average_price = !empty($lastAverage)? $lastAverage:$item['price'];
                         $mushokItems->qty = $item['qty'];
                         $mushokItems->vat_rate = 0;
                         $mushokItems->vds_receive_amount = 0;
                         $mushokItems->vat_rebetable_amount = 0; 
                         $mushokItems->vat_amount = 0;

                         if (!empty($companyLastMushok)) {
                              $mushokItems->qty = $item['qty'];
                              $mushokItems->opening_qty = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
                              $mushokItems->closing_qty = $mushokItems->opening_qty+$mushokItems->qty;
                         }else{
                              $mushokItems->qty = $item['qty'];
                              $mushokItems->opening_qty = 0;
                              $mushokItems->closing_qty = $item['qty'];
                         }

                         if (!empty($branchLastMushok)) {
                              $mushokItems->nature = "StockIn";
                              $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                              $mushokItems->branch_closing = $mushokItems->branch_opening+$mushokItems->qty;
                         }else{
                              $mushokItems->nature = "OpeningStock";
                              $mushokItems->branch_opening = 0;
                              $mushokItems->branch_closing = $item['qty'];
                         }
                         
                         $mushokItems->created_by = auth()->user()->id;
                         $mushokItems->created_at = $openStock->created_at;
                         
                         
                         $mushokItems->save();
                         $this->stockUpdate(array('id' => $item['item_id'], 'qty' => $item['qty'], 'company_id' => $openStock->company_id, 'branch_id' => $request->stock_branch));
                         $product = ['id' => $productInfo->id, 'qty' => $mushokItems->qty];
                         
                         Helper::postDataUpdate($product, $mushokItems->branch_id, $mushokItems->created_at, $mushokItems->type);
                    }
               }
               DB::commit();
               return $this->getById($openStock->id);
          
          }catch (\Throwable $th) {
               DB::rollBack();
               return $th->getMessage();
          }
     }

     function postDataUpdate($product, $branch_id, $date) {
          $mushokUpdates = new $this->mushok;
          $companyMushokItems = $mushokUpdates::where('product_id', $product['item_id'])
               ->whereDate('created_at', '>', date('Y-m-d', strtotime($date)))
               ->orderBy('id', 'asc')
               ->get();

          $mushokBrObj = new $this->mushok;
          $branchMushokItems = $mushokBrObj::where(['product_id' => $product['item_id'], 'branch_id' =>$branch_id])
          ->whereDate('created_at', '>', date('Y-m-d', strtotime($date)))
          ->orderBy('id', 'asc')
          ->get();
          foreach ($companyMushokItems as $key => $item) {
               $update = new $this->mushok;
               $update = $update::find($item['id']);
               $update->opening_qty = ($update->opening_qty + $product['qty']);
               $update->closing_qty = ($update->closing_qty + $product['qty']);
               $update->update();
          }

          foreach ($branchMushokItems as $key => $item) {
               $updateObj = new $this->mushok;
               $updateBr = $updateObj::find($item['id']);
               $updateBr->branch_opening = ($updateBr->branch_opening + $product['qty']);
               $updateBr->branch_closing = ($updateBr->branch_closing + $product['qty']);
               $updateBr->update();
          }
          
     }

     protected function stockUpdate($item, $type = "addition"){
          $stock = new $this->stock;                 
          $stockInfo = $stock::where(['product_id'=> $item['id'], 'company_id'=> $item['company_id'], 'branch_id'=> $item['branch_id']])->first();
          if(!empty($stockInfo)){
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

      /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function delete($id){
          return $this->getById($id)->delete();
     }

     public function bulkUpload($request){
          $user = auth()->user(); 
          $filename = $request->file('csvfile');
          if($_FILES["csvfile"]["size"] > 0)
          {
               // DB::beginTransaction();
               $file = fopen($filename, "r");
               $i = 0;
               $msg = "";
               $invalidSku = "";
               
               

               $i = 0;
               while (($item_info = fgetcsv($file, 10000, ",")) !== FALSE)
               {
                    $company = $this->company::where('id', auth()->user()->company_id)->first();
                    $mushok_no = 'six_one';
                    if ($company->business_type == 2) {
                         $mushok_no = 'six_two_one';
                    }
                    
                    
                    $i++;
                    if ($i>1) {
                         $stock_no    =   "OST".date('ymdHis');
                         // Adding Opening Stock
                         $openStock = new $this->model;
                         $openStock->company_id = 5;
                         $openStock->branch_id = (int) $item_info[0];
                         $openStock->admin_id = auth()->user()->id;
                         $openStock->open_stock_no      = $stock_no;
                         $openStock->created_at = date("Y-m-d 02:$i:00", strtotime($item_info[4]));
                         // return $openStock;
                         $openStock->save();
                         
                         $productInfo = $this->product::select('id', 'hs_code_id', 'hs_code', 'sku', 'title', 'model', 'type', 'price', 'status')->where('id', $item_info[1])->first();
                              // return $productInfo;
                         
                         if (!empty($productInfo)) { 
                              
                              // $time = date('H:i:s', strtotime("+$i minutes" , time()));
                              // $challanDate = $item_info[5]? date("Y-m-d", strtotime($item_info[5])):date("Y-m-d");
                              // $challanDate  = $challanDate.' '.$time;
                              
                              $companyLastMushok = $this->mushok::where(['product_id' => $item_info[1], 'company_id' => $openStock->company_id, 'mushok' => $mushok_no]) 
                              ->where('created_at', '<=', $openStock->created_at)
                              ->latest('created_at')
                              ->first();

                              $branchLastMushok = $this->mushok::where(['product_id' => $item_info[1], 'branch_id' => $item_info[0], 'mushok' => $mushok_no]) 
                              ->where('created_at', '<=', $openStock->created_at)
                              ->latest('created_at')
                              ->first();

                              $lastAverage = $this->mushok::where(['product_id' => $item_info[1], 'company_id' => $openStock->company_id, 'mushok' => $mushok_no])
                              ->where('price', '>', 0)
                              ->avg('price');
                              
                              $openStockItems = new $this->openStockItems;
                              $openStockItems->open_stock_id = $openStock->id;
                              $openStockItems->product_id = $item_info[1];
                              $openStockItems->item_info = json_encode($productInfo);
                              $openStockItems->price = $item_info[2];
                              $openStockItems->qty = $item_info[3];
                              // $openStockItems->created_at = '2023-05-01';
                              // return $openStockItems;
                              $openStockItems->save();
                              
                              // Mushok Insert
                              $mushokItems = new $this->mushok;
                              $mushokItems->open_stock_id = (int) $openStock->id;
                              $mushokItems->product_id = $productInfo->id;
                              $mushokItems->company_id = $openStock->company_id;
                              $mushokItems->branch_id = $item_info[0];
                              $mushokItems->type = 'credit';
                              $mushokItems->mushok = $mushok_no;
                              $mushokItems->price = $openStockItems->price;
                              $mushokItems->average_price = !empty($lastAverage)? $lastAverage:$openStockItems->price;
                              
                              $mushokItems->vat_rate = 0;
                              $mushokItems->vds_receive_amount = 0;
                              $mushokItems->vat_rebetable_amount = 0; 
                              $mushokItems->vat_amount = 0;

                              if (!empty($companyLastMushok)) {
                                   $mushokItems->qty = $openStockItems->qty;
                                   $mushokItems->opening_qty = !empty($companyLastMushok)? $companyLastMushok->closing_qty:0;
                                   $mushokItems->closing_qty = $mushokItems->opening_qty+$mushokItems->qty;
                              }else{
                                   $mushokItems->qty = 0;
                                   $mushokItems->opening_qty = $openStockItems->qty;
                                   $mushokItems->closing_qty = $openStockItems->qty;
                              }

                              if (!empty($branchLastMushok)) {
                                   $mushokItems->nature = "StockIn";
                                   $mushokItems->branch_opening = !empty($branchLastMushok)? $branchLastMushok->branch_closing:0;
                                   $mushokItems->branch_closing = $mushokItems->branch_opening+$mushokItems->qty;
                              }else{
                                   $mushokItems->nature = "OpeningStock";
                                   $mushokItems->branch_opening = $openStockItems->qty;
                                   $mushokItems->branch_closing = $openStockItems->qty;
                              }

                              $mushokItems->created_by = (int) $this->guard()->user()->id;
                              $mushokItems->created_at = $openStock->created_at;
                              $mushokItems->save();
                              $this->stockUpdate(array('id' => $productInfo->id, 'qty' => $openStockItems->qty, 'company_id' => $openStock->company_id, 'branch_id' => $mushokItems->branch_id));
                              
                              $product = ['id' => $item_info[1], 'qty' => $openStockItems->qty];
                              Helper::postDataUpdate($product, $mushokItems->branch_id, $mushokItems->created_at, $mushokItems->type);                              
                         }else{
                              $invalidSku .= $item_info[0]." And ".$item_info[1];
                              DB::rollback();
                         }
                         
                    }
                    
                         
               }
               if($invalidSku != ""){
                    $invalidSku .= "SKU's are invalid, please check  and ";
               }
               DB::commit();
               return $invalidSku."An Opening stock has been created";
               // $msg .= 'BOM has been successfully created';
               // return response()->json($msg);
          }
     }

     public function BulkQueueUpload($request)
     {
          if( $request->has('csvfile')) {
            $csv    = file($request->csvfile);
            $chunks = array_chunk($csv,200);
            $header = [
                'name',
                'company_id',
                'code', 
                'phone', 
                'email', 
                'address', 
                'shipping_address', 
                'type', 
                'nid', 
                'tin',
                'bin'
            ];
            foreach ($chunks as $key => $chunk) {
               
            $data = array_map('str_getcsv', $chunk);
                if($key == 0){
                    unset($data[0]);
                }
                try {
                    StockBulkUploadJob::dispatch($header, $data);
                } catch (\Throwable $th) {
                    return $th->getMessage();
                }
            }
        }
     }

     public function clearData()
     {
          try {
               DB::statement('SET FOREIGN_KEY_CHECKS=0;');
               $this->model::truncate();
               $this->mushok::truncate();
               $this->openStockItems::truncate();
               $this->stock::truncate();
               DB::statement('SET FOREIGN_KEY_CHECKS=1;');
               
               return "Done";
               // 
               
          } catch (\Throwable $th) {
               return $th->getMessage();
          }
          
     }

     public function guard()
     {
          return Auth::guard('api');
     }
     
}