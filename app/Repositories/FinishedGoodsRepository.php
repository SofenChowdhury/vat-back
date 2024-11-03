<?php

namespace App\Repositories;

use App\Classes\Helper;
use App\Models\Boms;
use App\Models\BomItem;
use App\Models\Company;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\ItemStock;
use App\Models\PurchaseItem;
use App\Models\FinishedGoods;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\FinishedGoodsRawMaterial;
use App\Models\MushokSix;
use AWS\CRT\HTTP\Request;
use Illuminate\Support\Facades\Validator;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class FinishedGoodsRepository implements BaseRepository
{

    protected  $model;
    protected  $product;
    protected  $stock;
    protected  $boms;
    protected  $bomItem;
    protected  $finishedItems;
    protected  $mushok;
    protected $company;

    public function __construct(FinishedGoods $model, Product $product, ItemStock $stock, Boms $boms, BomItem $bomItem, FinishedGoodsRawMaterial $finishedItems, MushokSix $mushok, Company $company)
    {
        $this->model = $model;
        $this->product = $product;
        $this->stock = $stock;
        $this->boms = $boms;
        $this->bomItem = $bomItem;
        $this->finishedItems = $finishedItems;
        $this->mushok = $mushok;
        $this->company = $company;
    }

    /**
     * all resource get
     * @return Collection
     */
    public function getAll()
    {
        $user = auth()->user();
        if ($user->company_id > 0) {
            return $this->model::with('company', 'branch', 'item')
                ->where('company_id', $user->company_id)
                ->latest()->paginate(20);
        } else {
            return $this->model::with('company', 'branch', 'item')->latest()->paginate(20);
        }
    }

    public function search($request)
    {
        $user = auth()->user();
        $query = $this->model::query();
        $query->with('company', 'branch', 'item');

        if ((isset($request->sku))){
            $query->whereHas('item', function ($q) use ($request)  {
                $q->where('sku', 'LIKE', '%' . $request->sku . '%');
                $q->where('type', 1);
            });
        }
        
        if ((isset($request->start_date) && $request->start_date != "") && (isset($request->end_date) && $request->end_date != "")) {
            $query->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
        }
        if ((isset($request->goods_no) && $request->goods_no != "")) {
            $query->where('goods_no', 'LIKE', '%' . $request->goods_no . '%');
        }
        
        $query->where('company_id', $user->company_id);
        $finishGoods = $query->latest()->paginate(20);
        return response()->json([
            'status' => true,
            'data' => $finishGoods,
            'errors' => '',
            'message' => "Finished Goods has been loaded",
        ]);
    }

    public function goodsDownload($request)
    {
        $user = auth()->user();
        $query = $this->model::query();
        $query->with('company', 'branch', 'item');

        if ((isset($request->sku))){
            $query->whereHas('item', function ($q) use ($request)  {
                $q->where('sku', 'LIKE', '%' . $request->sku . '%');
                $q->where('type', 1);
            });
        }
        
        if ((isset($request->start_date) && $request->start_date != "") && (isset($request->end_date) && $request->end_date != "")) {
            $query->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
        }
        if ((isset($request->goods_no) && $request->goods_no != "")) {
            $query->where('goods_no', 'LIKE', '%' . $request->goods_no . '%');
        }
        
        $query->where('company_id', $user->company_id);
        $finishGoods = $query->latest()->get();
        return response()->json([
            'status' => true,
            'data' => $finishGoods,
            'errors' => '',
            'message' => "Finished Goods has been loaded",
        ]);
    }

    /**
     * all resource get
     * @return Collection
     */
    public function getFull($request, $company_id = NULL)
    {
        if (!empty($company_id)) {
            $query = $this->model::query();
            $query->with('company', 'branch', 'item');

            if ((isset($request->sku))){
                $query->whereHas('item', function ($q) use ($request)  {
                    $q->where('sku', 'LIKE', '%' . $request->sku . '%');
                    $q->where('type', 1);
                });
            }
            
            if ((isset($request->start_date) && $request->start_date != "") && (isset($request->end_date) && $request->end_date != "")) {
                $query->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
            }
            if ((isset($request->goods_no) && $request->goods_no != "")) {
                $query->where('goods_no', 'LIKE', '%' . $request->goods_no . '%');
            }
            
            $query->where('company_id', $company_id);
            $finishGoods = $query->latest()->get();
            return $finishGoods;
        } else {
            $query = $this->model::query();
            $query->with('company', 'branch', 'item');

            if ((isset($request->sku))){
                $query->whereHas('item', function ($q) use ($request)  {
                    $q->where('sku', 'LIKE', '%' . $request->sku . '%');
                    $q->where('type', 1);
                });
            }
            
            if ((isset($request->start_date) && $request->start_date != "") && (isset($request->end_date) && $request->end_date != "")) {
                $query->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
            }
            if ((isset($request->goods_no) && $request->goods_no != "")) {
                $query->where('goods_no', 'LIKE', '%' . $request->goods_no . '%');
            }
            
            $finishGoods = $query->latest()->get();
            return $finishGoods;
        }
    }

    function report($request, $company_id) {
        if (!empty($company_id)) {
            return $this->model::with('item')
                ->select(DB::raw('SUM(qty) as total_qty'))
                ->withCount('materials') 
                ->where('company_id', $company_id)
                ->groupBy('product_id')
                ->latest()->get();



                // $category = $this->category::withCount(['salesItems' => function($query) {
                //     // $query->selectRaw('sales_items.id, sales_items.product_id,sales_items.price');
                //      $query->select(DB::raw('SUM(sales_items.price) as totalPrice'));  
                // }])
                // ->withCount('salesItems')
                // ->where('company_id', 5)
                // ->get();
        } else {
            return $this->model::with('company', 'branch', 'item')->latest()->get();
        }
    }

    /**
     * all resource get
     * @return Collection
     */
    public function getLatest($company_id = NULL)
    {
        return $this->model::where('company_id', $company_id)->take(20)->get();
    }




    /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function getById(int $id)
    {
        return $this->model::with('item', 'materials.info', 'company', 'branch', 'sixOne.info')
            //   ->with(['sixOne' => function ($query) {
            //         $query->select('finished_id', 'company_id', 'product_id', 'type', 'mushok', 'nature', 'qty', 'price', 'opening_qty', 'closing_qty')
            //         ->where('type', 'debit');
            //     }])
            ->where('id', $id)->first();
    }

    /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function getShowById(int $id, $company_id = NULL)
    {
        return $this->model::with('company', 'branch', 'item')
            ->where('id', $id)->first();
    }

    /**
     * resource create
     * @param  $request
     * @return \Illuminate\Http\Response
     */

    public function create($request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required',
                'branch_id' => 'required',
                'qty' => 'required',
                'production_date' => 'required'
            ]);

            if ($validator->fails()) {
                return ['status' => false, 'errors' => $validator->errors()];
            }
            $company_id    = (int) $this->guard()->user()->company_id ? (int) $this->guard()->user()->company_id : 1;
            $company = $this->company::where('id', auth()->user()->company_id)->first();
            $fg_sl = 1;
            $goosFinYear = Helper::getFinYear();
            $goodsLastSl = $this->model::where('company_id', $company->id)
                ->orderBy('id', 'desc')
                ->first();
            if (!empty($goodsLastSl)) {
                $fg_sl = $goodsLastSl->sl_no > 1 ? $goodsLastSl->sl_no + 1 : 1;
            }
            $fg_sl = str_pad($fg_sl, 4, '0', STR_PAD_LEFT);
            $goods_no    =   $company->order_prefix . "-FGR-" . $goosFinYear . "-" . $fg_sl;

            $wrongSkus = [];
            // Posting Date
            $time = date('H:i:s');
            $productionDate = $request->production_date != "" ? date('Y-m-d', strtotime($request->production_date)) : date('Y-m-d');
            $productionDate  = $productionDate . ' ' . $time;



            $bomInfo = $this->boms::with('item', 'rawMaterials')
                ->where(['product_id' => $request->product_id, 'company_id' => $company_id, 'status' => 1])
                ->first();
            if (empty($bomInfo)) {
                return response()->json([
                    'status' => false,
                    'data' => [],
                    'errors' => "",
                    'message' => "Active BOM is not found for this production, please check in Manage BOM"
                ]);
            }
            DB::beginTransaction();
            $goods = $this->model;
            $goods->company_id    = (int) $this->guard()->user()->company_id ? (int) $this->guard()->user()->company_id : 1;
            $goods->product_id    = $request->product_id;
            $goods->branch_id     = $request->branch_id? $request->branch_id:auth()->user()->branch_id;
            $goods->goods_no      = $goods_no;
            $goods->sl_no         = (int) $fg_sl;
            $goods->price         = $bomInfo->price;
            $goods->qty           = $request->qty;
            $goods->vat_rate      = $request->vat_rate;
            $goods->production_date = date('Y-m-d', strtotime($productionDate));
            $goods->challan_no    = $request->challan_no;
            $goods->created_by    = (int) $this->guard()->user()->id;
            $goods->created_at    = date('Y-m-d H:i:s', strtotime($productionDate));
            $itemInfo['id'] =  $goods->product_id;
            $itemInfo['company_id'] =  $goods->company_id;
            $itemInfo['branch_id'] =  $goods->branch_id;
            $itemInfo['qty'] =  $goods->qty;
            $itemInfo['price'] =  $goods->price;
            $stockIssue = $this->stockUpdate($itemInfo);
            if ($stockIssue === true) {
                $goods->save();
                // Finished Goods Raw Materials add
                if (!empty($bomInfo)) {
                    foreach ($bomInfo->rawMaterials as $key => $raw) {
                        if ($raw->status == 1) {
                            $requiredStock = ($goods->qty * $raw->qty_with_wastage);

                            // Last Balance
                            $mushokBoj = new $this->mushok;

                            $lastMushok = $mushokBoj::where('product_id', $raw->product_id)
                                ->where('created_at', '<=', $goods->created_at)
                                ->orderBy('created_at', 'desc')
                                ->first();


                            $lastAverage = $this->mushok::where('product_id', $raw->product_id)
                                ->where('company_id', auth()->user()->company_id)
                                ->where('price', '>', 0)
                                ->avg('price');

                            $finishedItems = new $this->finishedItems;
                            $finishedItems->finished_goods_id = $goods->id;
                            $finishedItems->finished_item_id = $goods->product_id;
                            $finishedItems->raw_item_id = $raw->product_id;
                            $finishedItems->qty = $requiredStock;
                            $finishedItems->price = $raw->price;
                            $finishedItems->save();

                            // Mushok Insert

                            $mushokItems = new $this->mushok;
                            $mushokItems->finished_id   =  $goods->id;
                            $mushokItems->product_id    = $raw->product_id;
                            $mushokItems->company_id    = $goods->company_id;
                            $mushokItems->branch_id     = $goods->branch_id;
                            $mushokItems->type          = 'debit';
                            $mushokItems->mushok        = 'six_one';
                            $mushokItems->price         = ($raw->price * $requiredStock);
                            $mushokItems->average_price = !empty($lastAverage) ? $lastAverage : 0;
                            $mushokItems->qty           = $requiredStock;
                            $mushokItems->opening_qty   = !empty($lastMushok) ? $lastMushok->closing_qty : 0;
                            $mushokItems->closing_qty   = $mushokItems->opening_qty - $finishedItems->qty;

                            $mushokItems->branch_opening   = !empty($lastMushok) ? $lastMushok->closing_qty : 0;
                            $mushokItems->branch_closing   = $mushokItems->opening_qty - $finishedItems->qty;
                            $mushokItems->nature   = "Consumption";
                            $mushokItems->created_by    = auth()->user()->id;
                            $mushokItems->created_at    = $productionDate;
                            $product = ['id' => $raw->product_id, 'qty' => $mushokItems->qty];

                            Helper::postDataUpdate($product, $mushokItems->branch_id, $productionDate, $mushokItems->type);

                            $mushokItems->save();
                        }
                    }
                }
                // Mushok Insert
                $lastAverage = $this->mushok::where('product_id', $raw->product_id)->avg('price');
                $mushokBoj = new $this->mushok;
                $lastMushok = $mushokBoj::where('product_id', $goods->product_id)
                    ->whereDate('created_at', '<=', date('Y-m-d H:i:s', strtotime($productionDate)))
                    ->latest('id')->first();
                $lastAverage = $mushokBoj::where('product_id', $goods->product_id)
                    ->where('company_id', auth()->user()->company_id)
                    ->where('price', '>', 0)
                    ->avg('price');

                $mushokItems = new $this->mushok;
                $mushokItems->finished_id   = $goods->id;
                $mushokItems->product_id    = $goods->product_id;
                $mushokItems->company_id    = $goods->company_id;
                $mushokItems->branch_id    = $goods->branch_id;
                $mushokItems->type          = 'credit';
                $mushokItems->mushok        = 'six_two';
                $mushokItems->price         = $bomInfo->price;
                $mushokItems->average_price = !empty($lastAverage) ? $lastAverage : 0;
                $mushokItems->qty           = $goods->qty;
                $mushokItems->nature   = "FGReceived";
                $mushokItems->opening_qty   = !empty($lastMushok) ? $lastMushok->closing_qty : 0;
                $mushokItems->closing_qty   = $mushokItems->opening_qty + $goods->qty;

                $mushokItems->branch_opening   = !empty($lastMushok) ? $lastMushok->closing_qty : 0;
                $mushokItems->branch_closing   = $mushokItems->opening_qty + $goods->qty;


                $mushokItems->created_by    = auth()->user()->id;
                $mushokItems->created_at    = $productionDate;
                $product = ['id' => $goods->product_id, 'qty' => $mushokItems->qty];

                Helper::postDataUpdate($product, $mushokItems->branch_id, $productionDate, $mushokItems->type);
                $mushokItems->save();
                DB::commit();
                return response()->json([
                    'status' => true,
                    'data' => $this->getShowById($goods->id),
                    'errors' => "",
                    'message' => "A finished goods has been successfully received"
                ]);
            } else {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'data' => $stockIssue->product,
                    'errors' => "",
                    'message' => $stockIssue->product->title . " - " . $stockIssue->product->sku . " materials stock is not available as per BOM settings"
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'data' => [],
                'errors' => '',
                'message' => $th->getMessage()
            ]);
        }
    }

    // Stock Update

    protected function stockUpdate($item)
    {
        $boms = new $this->boms;
        $bomInfo = $boms::with('item', 'rawMaterials.product')
            ->where(['product_id' => $item['id'], 'company_id' => $item['company_id'], 'status' => 1])
            ->first();
        if (!empty($bomInfo)) {
            foreach ($bomInfo->rawMaterials as $key => $raw) {
                // return $raw;
                if ($raw->status == 1) {
                    $requiredStock = $item['qty'] * $raw->qty_with_wastage;
                    $stock = new $this->stock;
                    $stockInfo = $stock::with('product')->where(['product_id' => $raw->product_id, 'company_id' => $item['company_id'], 'branch_id' => $item['branch_id']])->first();

                    if (empty($stockInfo) || $requiredStock > $stockInfo->stock) {
                        // return false;
                        return $raw;
                    } else {
                        $stockInfo->stock -= $requiredStock;
                        $stockInfo->update();
                    }
                }
            }
            $finishedStock = new $this->stock;
            $finishedStockInfo = $finishedStock::where(['product_id' => $item['id'], 'company_id' => $item['company_id'], 'branch_id' => $item['branch_id']])->first();
            if (!empty($finishedStockInfo)) {
                $finishedStockInfo->stock += $item['qty'];
                $finishedStockInfo->update();
            } else {
                $finishedStock->product_id = $item['id'];
                $finishedStock->company_id = $item['company_id'];
                $finishedStock->branch_id = $item['branch_id'];
                $finishedStock->stock = $item['qty'];
                $finishedStock->save();
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * specified resource update
     *
     * @param int $id
     * @param  $request
     * @return \Illuminate\Http\Response
     */

    public function update(int $id,  $request)
    {
        $order = $this->model->find($id);
        if ($order->order_status == 'delivered') {
            return false;
        } elseif ($order->order_status == 'declined') {
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

    public function delete($id)
    {
        try {
            $finishedGoods = $this->getById($id);
            if (!empty($finishedGoods)) {
                // return $finishedGoods;
                if ($finishedGoods->company_id != auth()->user()->company_id) {
                    return response()->json([
                        'status' => false,
                        'data' => [],
                        'errors' => '', 
                        'message' => "You have no access to delete this received",
                    ]);
                }
                DB::beginTransaction();
                $this->finishedItems::where('finished_goods_id', $id)->delete();
                $this->mushok::where('finished_id', $id)->delete();
                $this->stockUpdateMultiple($finishedGoods);
                
                if (!empty($finishedGoods)) {
                    $stock = new $this->stock;
                    $itemStock = $stock::where(['branch_id'=> $finishedGoods->branch_id, 'product_id' => $finishedGoods->product_id])
                    ->orderBy('stock', 'desc')
                    ->first();
                    $itemStock->stock = ($itemStock->stock-$finishedGoods->qty);
                    $itemStock->update();
                    $product = ['id' => $finishedGoods->product_id, 'qty' => $finishedGoods->qty];
                
                    Helper::postDataUpdateOnDelete($product, $finishedGoods->branch_id, date('Y-m-d H:i:s', strtotime($finishedGoods->created_at)), 'debit');
                    $finishedGoods->delete();
                }    
                DB::commit();
                return response()->json([
                        'status' => true,
                        'data' => [],
                        'errors' => '', 
                        'message' => "Your finished goods received has been deleted"
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'data' => [],
                    'errors' => '', 
                    'message' => "Your finished goods received information is wrong"
                ]);
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

    protected function stockUpdateMultiple($goods){    
        foreach ($goods->materials as $key => $item) {
             $stock = new $this->stock;
             $itemStock = $stock::where(['branch_id'=> $goods->branch_id, 'product_id' => $item->raw_item_id])
             ->orderBy('stock', 'desc')
             ->first();
             $itemStock->stock = ($itemStock->stock+$item->qty);
             $itemStock->update();
             $product = ['id' => $item->raw_item_id, 'qty' => $item->qty];
        
             Helper::postDataUpdateOnDelete($product, $goods->branch_id, date('Y-m-d H:i:s', strtotime($goods->created_at)), 'credit');
        }
        return true;
    }

    function removeConsumptionItem($request) {
        try {
            $fgMaterialsConsumption = $this->finishedItems::with('finishedGoods.item', 'info')->find($request->material_id);
            if (!empty($fgMaterialsConsumption)) {
                DB::beginTransaction();
                $stock = new $this->stock;
                $itemStock = $stock::where(['branch_id'=> $fgMaterialsConsumption->finishedGoods->branch_id, 'product_id' => $fgMaterialsConsumption->raw_item_id])->first();
                $itemStock->stock = ($itemStock->stock+$fgMaterialsConsumption->qty);
                $itemStock->update();
                
                $fgMaterialsConsumption->delete();
                // Delete Mushok
                $this->mushok::where(['finished_id' => $fgMaterialsConsumption->finishedGoods->id, 'product_id' => $fgMaterialsConsumption->raw_item_id])->delete();

                // Update Mushok 6.2
                $product = ['id' => $fgMaterialsConsumption->raw_item_id, 'qty' => $fgMaterialsConsumption->qty];
                Helper::postDataUpdateOnDelete($product, $fgMaterialsConsumption->finishedGoods->branch_id, date('Y-m-d H:i:s', strtotime($fgMaterialsConsumption->finishedGoods->production_date)), 'credit');
                DB::commit();
                return response()->json([
                    'status' => true,
                    'data' => $this->getById($fgMaterialsConsumption->finishedGoods->id),
                    'message' => "Consumption Item has been successfully removed from FG"
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'data' => [],
                    'message' => "Your materials information is wrong!"
                ]);
            }
            
       } catch (\Throwable $th) {
            return response()->json([
                 'status' => false,
                 'data' => [],
                 'message' => $th->getMessage()
            ]);
       }
    }

    public function guard()
    {
        return Auth::guard('api');
    }
}
