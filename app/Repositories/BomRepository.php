<?php
namespace App\Repositories;

use App\Models\BomItem;
use App\Models\Boms;
use App\Models\BomService;
use App\Models\BomValueAddition;
use App\Models\Company;
use App\Models\Product;
use App\Models\ValueAddition;
use Illuminate\Support\Facades\DB;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class BomRepository implements BaseRepository{

     protected  $model;
     protected  $bomItem;
     protected  $product;
     protected  $bomValue;
     protected  $service;
     protected  $valueAddition;
     protected  $company;
     
     public function __construct(Boms $model, BomItem $bomItem, Product $product, BomValueAddition $bomValue, BomService $service, ValueAddition $valueAddition, Company $company)
     {
        $this->model = $model;
        $this->bomItem = $bomItem;
        $this->product = $product;
        $this->bomValue = $bomValue;
        $this->service = $service;
        $this->valueAddition = $valueAddition;
        $this->company = $company;
     }

     /**
      * all resource get
      * @return Collection
      */
    public function getAll($company_id = NULL){          
        if (!empty($company_id)) {
            return $this->model::with('finishGoods', 'rawMaterials', 'company', 'bomValueAdditions.valueInfo', 'services.info')
            ->where('company_id', $company_id)
            ->latest()->paginate(20);
        }else{
            return $this->model::with('finishGoods', 'rawMaterials', 'company', 'bomValueAdditions.valueInfo', 'services.info')
            ->latest()->paginate(20);
        }               
        
    }

     public function search($request) {               

               return $this->model::with(['finishGoods'])
               ->whereHas('finishGoods', function($q) use($request) {
                    $q->where('sku', $request->keyword)
                    ->orWhere('title', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('details', 'LIKE', '%' . $request->keyword . '%');
               })
               // ->orWhere('bom_number', 'LIKE', '%' . $request->keyword . '%')
               ->where('company_id', auth()->user()->company_id)
               ->latest()
               ->paginate(20);
     }

     
     /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function getById(int $id){
          return $this->model::with('finishGoods.hscode', 'rawMaterials.product.hscode', 'company', 'bomValueAdditions.valueInfo', 'services.info')
               ->where('id', $id)->first();
     }  

     /**
     * resource create
     * @param  $request
     * @return \Illuminate\Http\Response
     */

     public function create($request){
          try {
               $data = [];
               $company = $this->company::where('id', auth()->user()->company_id)->first();
               if ($this->model::where(['product_id'=> $request->product_id, 'status' => 1])->exists()) {
                    $data['status'] = false;
                    $data['data'] = [];
                    $data['message'] = "An active BOM already exist on this product";
                    $data['errors'] = [];
                    return $data;
               }
               DB::beginTransaction(); 
               $bom_no    =   $company->order_prefix."-BOM".date('YmdHi');
               $bom = $this->model;
               $bom->product_id    = $request->product_id;
               $bom->price   = $request->price;
               $bom->company_id    = $company->id;
               $bom->bom_number    = $bom_no;
               $bom->status    = $request->status;
               $bom->start_date    = date('Y-m-d', strtotime($request->start_date));
               $bom->end_date      = date('Y-m-d', strtotime($request->end_date));
               $bom->created_by        = (int) auth()->user()->id;
               
               $bom->save();
               $items = [];
               $services = [];
               $values = [];
               foreach ($request->bomItems as $item) {          
                    $productInfo = $this->product::select('id', 'title', 'sku','model', 'type', 'price', 'status')
                              ->where('id', $item['id'])->first();
                    if ($productInfo) {
                         $items[] = [
                              'bom_id' => $bom->id,
                              'product_id' => $item['id'],
                              'item_info' => json_encode($productInfo),
                              'unit' => $item['unit'],
                              'price' => $item['price'],
                              'actual_qty' => $item['actual_qty'],
                              'qty_with_wastage' => $item['qty_with_wastage']
                         ];
                    }               
               }

               foreach ($request->valueAdditions as $key => $valueAddition) {
                    $values [] = [
                         'bom_id' => $bom->id,
                         'value_addition_id' => $valueAddition['id'],
                         'amount' => $valueAddition['amount']
                    ];
               }
               foreach ($request->bomServices as $key => $service) {
                    $services [] =[
                         'bom_id' => $bom->id,
                         'product_id' => $service['id'],
                         'amount' => $service['price'],
                    ];
               }
               $this->bomItem::insert($items);
               $this->bomValue::insert($values);
               $this->service::insert($services);
               DB::commit();
               $data['status'] = true;
               $data['data'] = $this->getById((int) $bom->id);
               $data['message'] = "BOM has been successfully created";
               $data['errors'] = [];
               return $data;
          } catch (\Throwable $th) {
               $data['status'] = false;
               $data['data'] = [];
               $data['message'] = $th->getMessage();
               $data['errors'] = [];
               return $data;
          }
     } 

     
     public function bulkUploadCreate($request){
          $user = auth()->user();  
          $filename = $request->file('csvfile');
          if($_FILES["csvfile"]["size"] > 0)
          {
               $file = fopen($filename, "r");
               $i = 0;
               
               while (($item_info = fgetcsv($file, 10000, ",")) !== FALSE)
               {
                    if ($i>0) {
                         
                         $productInfo = $this->product::select('id', 'title', 'sku','model', 'type', 'price', 'status', 'unit_type')
                                   ->where('sku', $item_info[0])
                                   ->where('company_id', $user->company_id)
                                   ->first();
                         
                         if (!empty($productInfo)) {
                              $exist = $this->model::where('product_id', $productInfo->id)->first();
                              if (empty($exist)) {
                                   $bom_no    =   "BOM".date('YmdHi').rand(100, 999);
                                   $bom = new $this->model;
                                   $bom->product_id    = $productInfo->id;
                                   $bom->price   = $productInfo->price;
                                   $bom->company_id    = (int) auth()->user()->company_id;
                                   $bom->bom_number    = $bom_no;
                                   $bom->start_date    = date('Y-m-d');
                                   $bom->end_date      = date('Y-m-d');
                                   $bom->created_by    = auth()->user()->id;
                                   $bom->save(); 
                              }                              
                         }                         
                    }                    
                    $i++;                   
               }
               $data['status'] = true;
               $data['message'] = "Uploaded Successfully";
               $data['data'] = [];
               return $data;
          }
     }

     public function bulkUploadRmCreate($request){
          $user = auth()->user();  
          $filename = $request->file('csvfile');
          if($_FILES["csvfile"]["size"] > 0)
          {
               $file = fopen($filename, "r");
               $i = 0;               
               while (($item_info = fgetcsv($file, 10000, ",")) !== FALSE)
               {
                    if ($i>0) {                         
                         $productInfo = $this->product::select('id', 'title', 'sku','model', 'type', 'price', 'status', 'unit_type')
                                   ->where('sku', $item_info[0])
                                   ->where('company_id', $user->company_id)
                                   ->first();
                         
                         if (!empty($productInfo)) {
                              $exist = $this->model::where('product_id', $productInfo->id)->first();
                              if (empty($exist)) {
                                   $bom_no    =   "BOM".date('YmdHi').rand(100, 999);
                                   $bom = new $this->model;
                                   $bom->product_id    = $productInfo->id;
                                   $bom->price   = $productInfo->price;
                                   $bom->company_id    = (int) auth()->user()->company_id;
                                   $bom->bom_number    = $bom_no;
                                   $bom->start_date    = date('Y-m-d');
                                   $bom->end_date      = date('Y-m-d');
                                   $bom->created_by    = auth()->user()->id;
                                   $bom->save(); 
                              }
                         }
                    }
                    $i++;
               }
               $data['status'] = true;
               $data['message'] = "Uploaded Successfully";
               $data['data'] = [];
               return $data;
          }
     }

     public function bulkUpload($request){
          $user = auth()->user();          

          $filename = $request->file('csvfile');
          if($_FILES["csvfile"]["size"] > 0)
          {
               $file = fopen($filename, "r");
               $i = 0;
               $msg = "";
               $invalidSku = "";
               DB::beginTransaction(); 
               $bom_no    =   "BOM".date('YmdHi');    
               $bom = $this->model;
               $bom->product_id    = $request->product_id;
               $bom->price   = $request->price;
               $bom->company_id    = (int) auth()->user()->company_id;
               $bom->bom_number    = $bom_no;
               $bom->start_date    = date('Y-m-d');
               $bom->end_date      = date('Y-m-d');
               $bom->created_by    = (int) auth()->user()->id;
               $bom->save();
               while (($item_info = fgetcsv($file, 10000, ",")) !== FALSE)
               {
                    
                    if (count($item_info) == 7) {
                         $i++;
                         if($i > 2){
                              
                              $productInfo = $this->product::select('id', 'title', 'sku','model', 'type', 'price', 'status', 'unit_type')
                              ->where('sku', $item_info[1])
                              ->where('company_id', auth()->user()->company_id)
                              ->first();
                              if ($productInfo) {
                                   if($item_info[6] == 'Material'){
                                        $bomItem = new  $this->bomItem;
                                        $bomItem->bom_id     = (int) $bom->id;
                                        $bomItem->product_id   = $productInfo->id;
                                        $bomItem->item_info    = json_encode($productInfo);
                                        $bomItem->unit   = $productInfo->unit_type;
                                        $bomItem->price   = $item_info[5];
                                        $bomItem->actual_qty   = $item_info[3];
                                        $bomItem->qty_with_wastage = $item_info[4];
                                        $bomItem->save();
                                   }elseif($item_info[6] == 'Service'){
                                        $bomService = new  $this->service;
                                        $bomService->bom_id     = (int) $bom->id;
                                        $bomService->product_id   = $productInfo->id;
                                        $bomService->amount   = $item_info[5];
                                        $bomService->save();
                                   }                                        
                              
                              }else{
                                   $invalidSku .= $item_info[1].", ";
                              }

                              if($item_info[6] == 'ValueAddition'){
                                   $bomValueInfo = $this->valueAddition::where('serial', $item_info[1])
                                   ->first();
                                   if(!empty($bomValueInfo)){
                                        $bomValue = new  $this->bomValue;
                                        $bomValue->bom_id     = (int) $bom->id;
                                        $bomValue->value_addition_id = $bomValueInfo->id;
                                        $bomValue->amount   = $item_info[5];
                                        $bomValue->save();
                                   }else{
                                        $invalidSku .= $item_info[1]." - SL, ";
                                   }
                                   
                              }
                                   
                         }

                    }
                         
               }
               if($invalidSku != ""){
                    $invalidSku .= "SKU's are invalid, please check";
               }
               DB::commit();
               
               $msg .= 'BOM has been successfully created';
               return response()->json($msg);
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
          try {
               $data = [];
               $items = [];
               $services = [];
               $values = [];
               // $this->bomItem::where('bom_id', $id)->delete();
               DB::beginTransaction(); 
               
               $bom = $this->model::find($id);
               $this->model::where('product_id', $bom->product_id)->where('id', '!=', $id)->update(['status' => 0]);
               // $previousBom->status = $request->status == 1? 0:$previousBom->status;
               // $previousBom->update();

               // $bom_no    =   "BOM".date('YmdHi');    
               // $bom = $this->model;
               $bom->price   = $request->price;
               $bom->created_by    = (int) auth()->user()->id;
               $bom->save();
               
               if ($request->bomItems) {
                    $this->bomItem::where('bom_id', $id)->delete();
                    foreach ($request->bomItems as $item) {  
                         // $this->bomItem::where('bom_id', $id)->delete();
                         $query = new $this->product;        
                         $productInfo = $query->select('id', 'title', 'sku','model', 'type', 'price', 'status')
                              ->where('id', $item['id'])->first();
                         
                         if ($productInfo) {
                              $items[] = [
                                   'bom_id' => $bom->id,
                                   'product_id' => $item['id'],
                                   'item_info' => json_encode($productInfo),
                                   'unit' => $item['unit'],
                                   'price' => $item['price'],
                                   'status' => $item['status'],
                                   'actual_qty' => $item['actual_qty'],
                                   'qty_with_wastage' => $item['qty_with_wastage']
                              ];
                         }           
                    }
               }
               
               if ($request->valueAdditions) {
                    $this->bomValue::where('bom_id', $bom->id)->delete();
                    foreach ($request->valueAdditions as $key => $valueAddition) {
                         $values [] = [
                              'bom_id' => $bom->id,
                              'value_addition_id' => $valueAddition['id'],
                              'amount' => $valueAddition['amount']
                         ];
                    }
               }

               if ($request->bomServices) {
                    $this->service::where('bom_id', $bom->id)->delete();
                    foreach ($request->bomServices as $key => $service) {
                         $services [] =[
                              'bom_id' => $bom->id,
                              'product_id' => $service['id'],
                              'amount' => $service['price'],
                         ];
                    }
               }
               $this->bomItem::insert($items);
               $this->bomValue::insert($values);
               $this->service::insert($services);
               DB::commit();
               $data['status'] = true;
               $data['data'] = $this->getById((int) $bom->id);
               $data['message'] = "BOM has been successfully created";
               $data['errors'] = [];
               return $data;
          } catch (\Throwable $th) {
               $data['status'] = false;
               $data['data'] = [];
               $data['message'] = $th->getMessage();
               $data['errors'] = [];
               return $data;
          }
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

     protected function stockUpdate($item){                 
          $stockInfo = $this->stock::where(['product_id'=> $item['id'], 'company_id'=> $item['company_id'], 'branch_id'=> $item['branch_id']])->first();
           if(!empty($stockInfo) && $stockInfo->stock>0){
               $stockInfo->stock -= (int) $item['qty'];
               $stockInfo->update();
               return true;
           }
           return false;
     }

     public function reportHistory($request, $company_id) {
          $history = $this->model::with('finishGoods.hscode', 'rawMaterials.product.hscode', 'company', 'bomValueAdditions.valueInfo', 'services.info')
               ->where('company_id', $company_id)
               ->where('product_id', $request->product_id)
               ->latest()
               ->get();
          $data['status'] = true;
          $data['data'] = $history;
          $data['message'] = "BOM has been successfully loaded";
          $data['errors'] = [];
          return $data;
     }

     
}
