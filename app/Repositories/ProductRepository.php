<?php

namespace App\Repositories;

use App\Models\HsCode;
use App\Models\Company;
use App\Models\Product;
use App\Models\Category;
use App\Models\ItemStock;
use App\Classes\FileUpload;
use Illuminate\Support\Str;
use App\Jobs\ProductCSVUploadJob;
use App\Models\MushokSix;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class ProductRepository implements BaseRepository
{

    protected  $model;
    protected $stock;
    protected $file;
    protected $hscode;
    protected $company;
    protected $mushokSix;

    public function __construct(
        Product $model,
        FileUpload $fileUpload,
        ItemStock $stock,
        HsCode $hscode,
        Company $company,
        MushokSix $mushokSix
    ) {
        $this->model = $model;
        $this->file = $fileUpload;
        $this->stock = $stock;
        $this->hscode = $hscode;
        $this->company = $company;
        $this->mushokSix = $mushokSix;
    }

    /**
     * all resource get
     * @return Collection
     */
    public function getAll()
    {
        return $this->model::with('category', 'hscode')
            ->where('company_id', auth()->user()->company_id)
            ->paginate(20);
    }
    /**
     * all resource get
     * @return Collection
     */

    public function listWithSearch($request)
    {
        $query = $this->model::query();
        $query->with('category', 'hscode');
        if ($request->sku != "") {
            $query->where('sku', 'LIKE', '%' . $request->sku . '%');
        }
        if ($request->title != "") {
            $query->orWhere('title', 'LIKE', '%' . $request->title . '%');
        }
        if ($request->model != "") {
            $query->orWhere('model', 'LIKE', '%' . $request->model . '%');
        }
        if ($request->type != "") {
            $query->where('type', $request->type);
        }
        $query->where('company_id', auth()->user()->company_id);
        return $query->paginate(20);
    }


    /**
     * all resource get
     * @return Collection
     */
    public function download($request)
    {
        $query = $this->model::query();
        $query->with('category', 'hscode');
        if ($request->sku != "") {
            $query->where('sku', 'LIKE', '%' . $request->sku . '%');
        }
        if ($request->title != "") {
            $query->orWhere('title', 'LIKE', '%' . $request->title . '%');
        }
        if ($request->model != "") {
            $query->orWhere('model', 'LIKE', '%' . $request->model . '%');
        }
        if ($request->type != "") {
            $query->where('type', $request->type);
        }
        $query->where('company_id', auth()->user()->company_id);
        return $query->lazy();
    }
    /**
     * all resource get
     * @return Collection
     */
    public function finishedGoods()
    {
        return $this->model::with('category', 'hscode')
            ->where('company_id', auth()->user()->company_id)
            ->where('type', 1)->paginate(10);
    }
    /**
     * all resource get
     * @return Collection
     */
    public function rawMaterials()
    {
        return $this->model::with('category', 'hscode')
            ->where('company_id', auth()->user()->company_id)
            ->where('type', 2)->paginate(10);
    }
    /**
     * all resource get
     * @return Collection
     */
    public function accessories()
    {
        return $this->model::with('category', 'hscode')
            ->where('company_id', auth()->user()->company_id)
            ->where('type', 3)->paginate(10);
    }
    /**
     * all resource get
     * @return Collection
     */
    public function services()
    {

        return $this->model::with('category', 'hscode')
            ->where('company_id', auth()->user()->company_id)
            ->where('type', 4)
            ->take(20)
            ->get();
    }

    public function serviceSearch($query)
    {
        return $this->model::with('category', 'hscode')
            ->where('sku', 'LIKE', '%' . $query . '%')
            ->orWhere('title', 'LIKE', '%' . $query . '%')
            ->orWhere('hs_code', 'LIKE', '%' . $query . '%')
            ->orWhere('model', 'LIKE', '%' . $query . '%')
            ->where('type', 4)
            ->where('company_id', auth()->user()->company_id)
            ->take(20)
            ->get();
    }

    /**
     * all resource get
     * @return Collection
     */
    public function stock($request)
    {
        // return $request->branch_id;
        $query = $request->keyword;
        $company_id = auth()->user()->company_id;
        $branch_id = $request->branch_id;
        if (!empty($company_id) && !empty($branch_id)) {
            return $this->stock::with('product.hscode', 'branch')
                ->where('company_id', $company_id)
                ->where('branch_id', $branch_id)
                ->whereHas('product', function ($q) use ($request) {
                    if (!empty($request)) {
                        $q->where('sku', $request->keyword);
                        // $q->where('sku', 'LIKE', '%' . $request->keyword . '%');
                        $q->orWhere('type', $request->type);
                        $q->orWhere('title', 'LIKE', '%' . $request->keyword . '%');
                        $q->orWhere('model', 'LIKE', '%' . $request->keyword . '%');
                    }
                })
                ->orderBy('stock', 'desc')
                ->paginate(20);
        } else {
            return $this->stock::with('product.hscode', 'branch')
                ->where('company_id', $company_id)
                ->whereHas('product', function ($q) use ($request) {
                    if (!empty($request)) {
                        // $q->where('sku', 'LIKE', '%' . $request->keyword . '%');
                        $q->where('sku', $request->keyword);
                        $q->orWhere('type', $request->type);
                        $q->orWhere('title', 'LIKE', '%' . $request->keyword . '%');
                        $q->orWhere('model', 'LIKE', '%' . $request->keyword . '%');
                    }
                })
                ->orderBy('stock', 'desc')
                ->paginate(20);
        }
    }

    function stockMerge($request)
    {
        $user = auth()->user();
        $duplicates = $this->stock::select('product_id', 'company_id', 'branch_id', 'stock')
            ->selectRaw('COUNT(*) as duplicate_count')
            ->selectRaw('SUM(stock) as total_stock')
            ->where('company_id', $user->company_id)
            ->groupBy('product_id', 'branch_id')
            ->having('duplicate_count', '>', 1)
            ->take(100)
            ->get();

        foreach ($duplicates as $key => $duplicate) {
            $productId = $duplicate->product_id;
            $branchId = $duplicate->branch_id;
            $totalStock = $duplicate->total_stock;

            // Find duplicate records
            $duplicateStocks = $this->stock::where('product_id', $productId)
                ->where('branch_id', $branchId)
                ->get();
            // Calculate total stock and delete duplicates
            $mergedStock = $duplicateStocks->first();
            $mergedStock->stock = $totalStock;
            $mergedStock->save();

            // Delete duplicate records except the first one
            $duplicateStocks->slice(1)->each(function ($duplicate) {
                $duplicate->delete();
            });
        }
    }

    public function stockDownload($request)
    {
        // return $request->branch_id;
        $query = $request->keyword;
        $company_id = auth()->user()->company_id;
        $branch_id = $request->branch_id;
        if (!empty($company_id) && !empty($branch_id)) {
            return $this->stock::with('product.hscode', 'branch')
                ->where('company_id', $company_id)
                ->where('branch_id', $branch_id)
                ->whereHas('product', function ($q) use ($query, $request) {
                    if ($query != "") {
                        $q->where('sku', 'LIKE', '%' . $query . '%');
                        $q->orWhere('type', $request->type);
                        $q->orWhere('title', 'LIKE', '%' . $query . '%');
                        $q->orWhere('model', 'LIKE', '%' . $query . '%');
                    }
                })
                ->orderBy('stock', 'desc')
                ->get();
        } elseif (!empty($company_id) && empty($branch_id)) {
            return $this->stock::with('product.hscode', 'branch')
                ->where('company_id', $company_id)
                ->whereHas('product', function ($q) use ($query, $request) {
                    if ($query != "") {
                        $q->where('sku', 'LIKE', '%' . $query . '%');
                        $q->orWhere('type', $request->type);
                        $q->orWhere('title', 'LIKE', '%' . $query . '%');
                        $q->orWhere('model', 'LIKE', '%' . $query . '%');
                    }
                })
                ->get();
        } else {
            return $this->stock::with('product.hscode', 'branch')
                ->whereHas('product', function ($q) use ($query) {
                    if ($query != "") {
                        $q->where('sku', 'LIKE', '%' . $query . '%');
                        $q->orWhere('title', 'LIKE', '%' . $query . '%');
                        $q->orWhere('model', 'LIKE', '%' . $query . '%');
                    }
                })
                ->orderBy('stock', 'desc')
                ->get();
        }
    }

    /**
     * all resource get
     * @return Collection
     */
    public function getAllVariant()
    {
        return $this->model::latest()->paginate(20);
    }

    public function getPageTen()
    {
        return $this->model::where('company_id', auth()->user()->company_id)->paginate(20);
    }

    /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function getById(int $id)
    {
        return $this->model::with('category', 'hscode')
            ->where('company_id', auth()->user()->company_id)
            ->find($id);
    }


    public function getBySku($sku)
    {
        return $this->model::with('category', 'hscode')->where('sku', $sku)
            ->where('company_id', auth()->user()->company_id)
            ->first();
    }

    public function search($request)
    {
        $query = $request->keyword;

        if ($request->type != "") {
            $company = $this->company::find(auth()->user()->company_id);
            if ($company->business_type == 2 && $request->type == 2) {
                $request->type = 1;
            }
            return $this->model::with('hscode', 'stocks')
                // ->whereIn('type', [$request->type, '4'])
                ->where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($request) {
                    $query->where('type', $request->type)
                        ->orWhere('type', 4);
                })
                ->where(function ($q) use ($query) {
                    $q->where('sku', 'LIKE', '%' . $query . '%');
                    $q->orWhere('title', 'LIKE', '%' . $query . '%');
                })
                ->take(100)
                ->get();
        } elseif ($request->sales != "") {
            $company = $this->company::where('id', auth()
                ->user()->company_id)
                ->first();
            if ($company->business_type == 1) {
                return $this->model::with('hscode', 'stocks')
                    ->whereIn('type', [1, 4])
                    ->where('company_id', auth()->user()->company_id)
                    ->where(function ($q) use ($query) {
                        $q->where('sku', 'LIKE', '%' . $query . '%');
                        $q->orWhere('title', 'LIKE', '%' . $query . '%');
                        // $q->orWhere('model', 'LIKE', '%' . $query . '%');
                    })
                    ->take(100)
                    ->get();
            } else {
                return $this->model::with('hscode', 'stocks')
                    ->where('company_id', auth()->user()->company_id)
                    ->where(function ($q) use ($query) {
                        $q->where('sku', 'LIKE', '%' . $query . '%');
                        $q->orWhere('title', 'LIKE', '%' . $query . '%');
                        // $q->orWhere('model', 'LIKE', '%' . $query . '%');
                    })
                    ->take(100)
                    ->get();
            }
        } else {
            return $this->model::with('hscode', 'stocks')
                ->where('company_id', auth()->user()->company_id)
                ->where(function ($q) use ($query) {
                    $q->where('sku', 'LIKE', '%' . $query . '%');
                    $q->orWhere('title', 'LIKE', '%' . $query . '%');
                    // $q->orWhere('model', 'LIKE', '%' . $query . '%');
                })

                ->take(100)
                ->get();
        }
    }


    public function multiSearch($request)
    {
        if ($request->type != "") {
            return $this->model::with('hscode', 'stocks')
                ->where('company_id', auth()->user()->company_id)
                // ->where('type', $request->type)
                ->whereIn('sku', $request->skus)
                ->get();
        } else {
            return $this->model::with('hscode', 'stocks')
                ->where('company_id', auth()->user()->company_id)
                ->whereIn('sku', $request->skus)
                ->get();
        }
    }

    /**
     * resource create
     * @param $request
     * @return \Illuminate\Http\Response
     */

    public function create($request)
    {
        $product = $this->model;
        $validator = Validator::make($request->all(), [
            "title"    => "required",
            "price"    => "required",
            "details" => "required",
            // "hs_code" => "unique:products"
        ]);

        if ($validator->fails()) {
            return ['status' => false, 'errors' => $validator->errors()];
        }

        if ($request->photo == NULL) {
            $request->validate([
                "photo"    => "image:mime:jpg,png,jpeg,webp",
            ]);
        }
        $product  =  new Product();
        $product->title = $request->title;
        $product->category_id = $request->category_id;
        $product->company_id = auth()->user()->company_id;
        $product->hs_code_id = $request->hs_code_id;
        $product->sku = $request->sku; //Str::random(3).substr(time(), 6,8).Str::random(3);
        $product->hs_code = $request->hs_code;
        $product->sales_vat_rate = $request->sales_vat_rate ? $request->sales_vat_rate : 0;
        $product->model = $request->model;
        $product->slug = Str::slug($request->title);
        $product->type = $request->type;
        $product->unit_type = $request->unit_type;
        $product->vat_rebatable_percentage = $request->vat_rebatable_percentage;
        $product->vds_percentage = $request->vds_percentage;
        $product->details = $request->details;
        $product->photo = $this->file->base64ImgUpload($request->photo, $file = "", $folder = 'thumbnails');
        $product->price = $request->price;
        $product->status = $request->status;
        $product->origin = $request->origin;
        $product->save();
        return ['status' => true, 'data' => $product];
    }

    /**
     * specified resource update
     *
     * @param int $id
     * @param $request
     * @return \Illuminate\Http\Response
     */

    public function update(int $id, $request)
    {
        $validator = Validator::make($request->all(), [
            "product_id" => "required",
            "title" => "required",
            "category_id" => "required",
            "price" => "required",
            "sku" => "required"
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'errors' => $validator->errors()];
        }

        $product = $this->getById($id);
        // return $product;


        $fileName = NULL;
        if (substr($request->photo, 0, 22) == 'data:image/jpg;base64,'  ||  substr($request->photo, 0, 22) == "data:image/png;base64," || substr($request->photo, 0, 22) == "data:image/webp;base64" || substr($request->photo, 0, 22) == "data:image/jpeg;base64") {
            if ($request->photo != NULL) {
                $fileName = $this->file->base64ImgUpload($request->photo, $file = $product->photo, $folder = "thumbnails");
            }
        } else {
            $product->photo = $fileName ?  $fileName :  $product->photo;
        }

        $product->title = $request->title;
        $product->category_id = $request->category_id;
        $product->hs_code_id = $request->hs_code_id;
        $product->sku = $request->sku; //Str::random(3).substr(time(), 6,8).Str::random(3);
        $product->hs_code = $request->hs_code;
        $product->model = $request->model;
        $product->slug = Str::slug($request->title);
        $product->type = $request->type;
        $product->sales_vat_rate = $request->sales_vat_rate ? $request->sales_vat_rate : 0;
        $product->unit_type = $request->unit_type;
        $product->vat_rebatable_percentage = $request->vat_rebatable_percentage;
        $product->vds_percentage = $request->vds_percentage;
        $product->details = $request->details;
        $product->price = $request->price;
        $product->status = $request->status;
        $product->origin = $request->origin;

        if ($request->photo != "") {
            $product->photo = $this->file->base64ImgUpload($request->photo, $file = "", $folder = 'thumbnails');
        }

        $product->update();
        return ['status' => true, 'data' => $product];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function delete($id)
    {
        return $this->getById($id)->delete();
    }

    public function getProduct($limit = null)
    {
        if ($limit == null) {
            return $this->model::where('status', 1)->where('product_id', '=', null)->select('id', 'title', 'slug', 'regular_price', 'discount_price', 'photo', 'discount_start_time', 'discount_end_time')->latest()->paginate(12);
        } else {
            return $this->model::where('status', 1)->where('product_id', '=', null)->select('id', 'title', 'slug', 'regular_price', 'discount_price', 'photo', 'discount_start_time', 'discount_end_time')->limit($limit)->latest()->get();
        }
    }


    public function getCategory()
    {
        return  Category::with('products')->limit(5)->get();
    }
    public function searchProduct($search)
    {
        return  $this->model::where('status', 1)->where('title', 'LIKE', '%' . $search . '%')->get();
    }

    public function bulkUpload($request)
    {
        $filename = $request->file('csvfile');
        if ($_FILES["csvfile"]["size"] > 0) {
            $file = fopen($filename, "r");
            $i = 0;
            $y = 1;
            $hscode_id = NULL;
            while (($product_info = fgetcsv($file, 10000, ",")) !== FALSE) {
                if ($i > 0) {
                    // return $product_info;
                    $existProduct = $this->getBySku($product_info[1]);
                    // return $existProduct;
                    if (empty($existProduct)) {
                        $y++;
                        $product = new $this->model;


                        $product->company_id = auth()->user()->company_id;
                        $product->sku = $product_info[1];
                        $product->category_id = $product_info[2];
                        $product->type = $product_info[3];
                        $product->title = $product_info[4];


                        $product->price = $product_info[5];
                        $product->unit_type = $product_info[6];
                        $product->hs_code_id = $product_info[7];
                        $product->vat_rebatable_percentage = $product_info[8] > 0 ? 100 : 0;
                        $product->vds_percentage = 0;
                        $product->origin = "Korea/BD";
                        $product->sales_vat_rate = $product_info[9];
                        $product->status = 1;
                        $product->save();
                    }
                }
                $i++;
            }
            return $y;
        }
    }

    public function BulkQueueUpload($request)
    {
        if ($request->has('csvfile')) {

            $csv    = file($request->csvfile);
            $chunks = array_chunk($csv, 200);
            $header = [
                'sku',
                'title',
                'category_id',
                'model',
                'price',
                'brand_id',
                'manufacturing_type',
                'unit_type',
                'hs_code_id',
                'sales_vat_rate',
                'type'
            ];
            foreach ($chunks as $key => $chunk) {

                $data = array_map('str_getcsv', $chunk);
                if ($key == 0) {
                    unset($data[0]);
                }
                // return $data;
                try {
                    ProductCSVUploadJob::dispatch($data, $header);
                } catch (\Throwable $th) {
                    return $th->getMessage();
                }
            }
        }
    }

    function stockSummery($request, $company_id)
    {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');

        if ((isset($request->start_date) && $request->start_date != "") && (isset($request->end_date) && $request->end_date != "")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        $user = auth()->user();
        $query = $this->model::query();
        $query->leftJoin('open_stock_items', 'products.id', '=', 'open_stock_items.product_id')
            ->whereBetween('open_stock_items.created_at', [$data['start_date'], $data['end_date']])
            ->select('products.id', 'products.title')
            ->selectRaw('SUM(open_stock_items.qty) as total_open_stock')
            ->where('products.company_id', $user->company_id)
            ->groupBy('products.id');
        $salesStatement = $query->get();
        return response()->json([
            'status' => true,
            'data' => $salesStatement,
            'message' => "Report has been loaded"
        ]);
    }

    function stockReport($request)
    {

        try {

            $data['start_date'] = date('Y-m-01 00:00:00');
            $data['end_date'] = date('Y-m-d 23:59:59');

            if ((isset($request->start_date) &&
                    $request->start_date != "") &&
                (isset($request->end_date) &&
                    $request->end_date != "")
            ) {
                $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
                $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
            }

            if ($request->product_id != null) {
                $result = $this->mushokSix->where('product_id', $request->product_id)
                    ->whereBetween('created_at', [$data['start_date'], $data['end_date']])
                    ->with('product:id,title,sku')
                    ->selectRaw('product_id,
            SUM(IF(purchase_id IS NOT NULL, qty, 0)) AS total_purchase_qty,
            SUM(IF(sales_id IS NOT NULL, qty, 0)) AS total_sales_qty,
            SUM(IF(finished_id IS NOT NULL, qty, 0)) AS total_finished_qty,
            SUM(IF(purchase_return_id IS NOT NULL, qty, 0)) AS total_purchase_return_qty,
            SUM(IF(sales_return_id IS NOT NULL, qty, 0)) AS total_sales_return_qty,
            SUM(IF(open_stock_id IS NOT NULL, qty, 0)) AS total_open_stock_qty')
                    ->groupBy('product_id')
                    ->latest()
                    ->paginate(20);
            } else {
                $result = $this->mushokSix->whereBetween('created_at',  [$data['start_date'], $data['end_date']])
                    ->with('product:id,title,sku')
                    ->selectRaw('product_id,
            SUM(IF(purchase_id IS NOT NULL, qty, 0)) AS total_purchase_qty,
            SUM(IF(sales_id IS NOT NULL, qty, 0)) AS total_sales_qty,
            SUM(IF(finished_id IS NOT NULL, qty, 0)) AS total_finished_qty,
            SUM(IF(purchase_return_id IS NOT NULL, qty, 0)) AS total_purchase_return_qty,
            SUM(IF(sales_return_id IS NOT NULL, qty, 0)) AS total_sales_return_qty,
            SUM(IF(open_stock_id IS NOT NULL, qty, 0)) AS total_open_stock_qty')
                    ->groupBy('product_id')
                    ->latest()
                    ->paginate(20);
            }



            return response()->json([
                'status' => true,
                'message' => 'Report Loaded Successfully!',
                'data' => $result
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => NULL
            ]);
        }
    }

    function stockReportDownload($request)
    {
        $user = auth()->user();
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');

        if ((isset($request->start_date) &&
                $request->start_date != "") &&
            (isset($request->end_date) &&
                $request->end_date != "")
        ) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        try {
            $query = $this->mushokSix
                ->join('products', 'mushok_sixes.product_id', '=', 'products.id')
                // ->leftJoin('item_stocks', 'products.id', '=', 'item_stocks.product_id')
                ->leftJoin('hs_codes', 'products.hs_code_id', '=', 'hs_codes.id')
                ->whereBetween('mushok_sixes.created_at', [$data['start_date'], $data['end_date']])
                ->select(
                    'products.id as product_id',
                    'products.sku',
                    'products.title',
                    'hs_codes.code',
                    'hs_codes.description',
                    'mushok_sixes.created_at AS startAt',
                    // Total OpeningQty
                    DB::raw('(SELECT opening_qty FROM mushok_sixes AS ms WHERE ms.product_id = products.id ORDER BY ms.created_at ASC LIMIT 1) as opening_qty'),
                    // Total ClosingQty
                    DB::raw('(SELECT closing_qty FROM mushok_sixes AS ms WHERE ms.product_id = products.id ORDER BY ms.created_at DESC LIMIT 1) as closing_qty'),

                    // Total OpeningQty
                    DB::raw('(SELECT branch_opening FROM mushok_sixes AS ms WHERE ms.product_id = products.id ORDER BY ms.created_at ASC LIMIT 1) as branch_opening'),
                    // Total ClosingQty
                    DB::raw('(SELECT branch_closing FROM mushok_sixes AS ms WHERE ms.product_id = products.id ORDER BY ms.created_at DESC LIMIT 1) as branch_closing'),

                    // only TotalStockIn 1st July 2023
                    DB::raw('SUM(CASE WHEN mushok_sixes.open_stock_id IS NOT NULL THEN mushok_sixes.qty ELSE 0 END) AS totalStockIn'),
                    
                    // TotalStockIn and purchase after 1st July 2023
                    DB::raw('SUM(CASE WHEN mushok_sixes.open_stock_id IS NOT NULL OR (mushok_sixes.finished_id IS NOT NULL AND mushok_sixes.created_at > "2023-06-01") THEN mushok_sixes.qty ELSE 0 END) AS totalStockInProduction'),
                    // Total Only Production without Manual StockIn
                    DB::raw('SUM(CASE WHEN mushok_sixes.finished_id IS NOT NULL AND mushok_sixes.nature = "FGReceived" THEN mushok_sixes.qty ELSE 0 END) AS totalProduction'),
                    // Total Sales Return
                    DB::raw('SUM(CASE WHEN mushok_sixes.sales_return_id IS NOT NULL THEN mushok_sixes.qty ELSE 0 END) AS totalSalesReturn'),
                    // Stock Receive (Transfer IN)
                    DB::raw('SUM(CASE WHEN mushok_sixes.transfer_id IS NOT NULL AND mushok_sixes.type = "credit" THEN mushok_sixes.qty ELSE 0 END) AS stockReceived'),
                    // Stock Transfer (Transfer Out)
                    DB::raw('SUM(CASE WHEN mushok_sixes.transfer_id IS NOT NULL AND mushok_sixes.type = "debit" THEN mushok_sixes.qty ELSE 0 END) AS stockTransfer'),
                    // total Raw Materials Consumption on Production
                    DB::raw('SUM(CASE WHEN mushok_sixes.finished_id IS NOT NULL AND mushok_sixes.nature = "Consumption" THEN mushok_sixes.qty ELSE 0 END) AS totalConsumption'),
                    // total Purchase
                    DB::raw('SUM(CASE WHEN mushok_sixes.purchase_id IS NOT NULL THEN mushok_sixes.qty ELSE 0 END) AS totalPurchase'),
                    // Total Sales
                    DB::raw('SUM(CASE WHEN mushok_sixes.sales_id IS NOT NULL AND mushok_sixes.sales_return_id IS NULL THEN mushok_sixes.qty ELSE 0 END) AS totalSales'),
                    // Total Purchase Return
                    DB::raw('SUM(CASE WHEN mushok_sixes.purchase_return_id IS NOT NULL THEN mushok_sixes.qty ELSE 0 END) AS totalPurchaseReturn')
                );

            if ($request->type) {
                $query->where('products.type', $request->type);
            }

            if (!empty($request->product_id)) {
                $query->where('product_id', $request->product_id);
            }
            $query->where('products.company_id', $user->company_id);
            $query->orderBy('mushok_sixes.opening_qty', 'desc');
            $query->groupBy('mushok_sixes.product_id');

            // echo $query->toSql(); // Print the SQL query
            $results = $query->get();



            return response()->json([
                'status' => true,
                'message' => 'Report Loaded Successfully!',
                'data' => $results
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => NULL
            ]);
        }
    }

    function stockSummeryReport($request) {
        $user = auth()->user();
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');

        if ((isset($request->start_date) &&
                $request->start_date != "") &&
            (isset($request->end_date) &&
                $request->end_date != "")
        ) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        try {
            $query = $this->mushokSix
                ->join('products', 'mushok_sixes.product_id', '=', 'products.id')
                // ->leftJoin('item_stocks', 'products.id', '=', 'item_stocks.product_id')
                ->leftJoin('hs_codes', 'products.hs_code_id', '=', 'hs_codes.id')
                ->whereBetween('mushok_sixes.created_at', [$data['start_date'], $data['end_date']])
                ->select(
                    'products.id as product_id',
                    'products.sku',
                    'products.title',
                    'hs_codes.code',                    
                    // Total OpeningQty
                    DB::raw('(SELECT opening_qty FROM mushok_sixes AS ms WHERE ms.product_id = products.id ORDER BY ms.created_at ASC LIMIT 1) as opening_qty'),
                    // Total ClosingQty
                    DB::raw('(SELECT closing_qty FROM mushok_sixes AS ms WHERE ms.product_id = products.id ORDER BY ms.created_at DESC LIMIT 1) as closing_qty'),

                    // Total OpeningQty
                    DB::raw('(SELECT branch_opening FROM mushok_sixes AS ms WHERE ms.product_id = products.id ORDER BY ms.created_at ASC LIMIT 1) as branch_opening'),
                    // Total ClosingQty
                    DB::raw('(SELECT branch_closing FROM mushok_sixes AS ms WHERE ms.product_id = products.id ORDER BY ms.created_at DESC LIMIT 1) as branch_closing'),


                    
                    // TotalStockIn and purchase after 1st July 2023
                    DB::raw('SUM(CASE WHEN mushok_sixes.open_stock_id IS NOT NULL OR (mushok_sixes.finished_id IS NOT NULL AND mushok_sixes.created_at > "2023-06-01") THEN mushok_sixes.qty ELSE 0 END) AS totalStockInProduction'),
                    // Total Only Production without Manual StockIn
                    DB::raw('SUM(CASE WHEN mushok_sixes.finished_id IS NOT NULL AND mushok_sixes.nature = "FGReceived" THEN mushok_sixes.qty ELSE 0 END) AS totalProduction'),
                    // Total Sales Return
                    DB::raw('SUM(CASE WHEN mushok_sixes.sales_return_id IS NOT NULL THEN mushok_sixes.qty ELSE 0 END) AS totalSalesReturn'),
                    // Stock Receive (Transfer IN)
                    DB::raw('SUM(CASE WHEN mushok_sixes.transfer_id IS NOT NULL AND mushok_sixes.type = "credit" THEN mushok_sixes.qty ELSE 0 END) AS stockReceived'),
                    // Stock Transfer (Transfer Out)
                    DB::raw('SUM(CASE WHEN mushok_sixes.transfer_id IS NOT NULL AND mushok_sixes.type = "debit" THEN mushok_sixes.qty ELSE 0 END) AS stockTransfer'),
                    // total Raw Materials Consumption on Production
                    DB::raw('SUM(CASE WHEN mushok_sixes.finished_id IS NOT NULL AND mushok_sixes.nature = "Consumption" THEN mushok_sixes.qty ELSE 0 END) AS totalConsumption'),
                    // total Purchase
                    DB::raw('SUM(CASE WHEN mushok_sixes.purchase_id IS NOT NULL THEN mushok_sixes.qty ELSE 0 END) AS totalPurchase'),
                    // Total Sales
                    DB::raw('SUM(CASE WHEN mushok_sixes.sales_id IS NOT NULL AND mushok_sixes.sales_return_id IS NULL THEN mushok_sixes.qty ELSE 0 END) AS totalSales'),
                    // Total Purchase Return
                    DB::raw('SUM(CASE WHEN mushok_sixes.purchase_return_id IS NOT NULL THEN mushok_sixes.qty ELSE 0 END) AS totalPurchaseReturn')
                );

            if ($request->type) {
                $query->where('products.type', $request->type);
            }

            if (!empty($request->product_id)) {
                $query->where('mushok_sixes.product_id', $request->product_id);
            }
            if (!empty($request->branch_id)) {
                $query->where('mushok_sixes.branch_id', $request->branch_id);
            }
            $query->where('products.company_id', $user->company_id);
            $query->orderBy('mushok_sixes.created_at', 'asc');
            $query->groupBy('mushok_sixes.product_id');

            // echo $query->toSql(); // Print the SQL query
            $results = $query->paginate(20);
            return response()->json([
                'status' => true,
                'message' => 'Report Loaded Successfully!',
                'data' => $results
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => NULL
            ]);
        }
        
    }

    function getOpening($request){
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');

        if ((isset($request->start_date) &&
                $request->start_date != "") &&
            (isset($request->end_date) &&
                $request->end_date != "")
        ) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        // return $data;
        // DB::connection()->enableQueryLog();
        $query = $this->mushokSix::query();
        $query->with(['product' => function($query) {
                    // $query->where('type', 1);
                    $query->select('id', 'sku', 'title');
                }])
                ->whereBetween('created_at',  [$data['start_date'], $data['end_date']]);
                if (!empty($request->product_id)) {
                    $query->where('product_id', $request->product_id);
                }
                // ->where('product_id', $request->product_id)
                $query->selectRaw('product_id, SUM(qty) as productionTotal')
                ->where(function($q){
                    return $q->whereNotNull('open_stock_id')
                        ->orWhereNotNull('finished_id');
                    })
                    ->groupBy('product_id')
                    ->limit(10);
                return  $query->first();
                    
           
    }

    function getSalesReturn($request){
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');

        if ((isset($request->start_date) &&
                $request->start_date != "") &&
            (isset($request->end_date) &&
                $request->end_date != "")
        ) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        // return $data;
        // DB::connection()->enableQueryLog();
        $query = $this->mushokSix::query();
        $query->with(['product' => function($query) {
                    // $query->where('type', 1);
                    $query->select('id', 'sku', 'title');
                }])
                ->whereBetween('created_at',  [$data['start_date'], $data['end_date']]);
                if (!empty($request->product_id)) {
                    $query->where('product_id', $request->product_id);
                }
                // ->where('product_id', $request->product_id)
                $query->selectRaw('product_id, SUM(qty) as productionTotal')
                ->where(function($q){
                    return $q->whereNotNull('open_stock_id')
                        ->orWhereNotNull('finished_id');
                    })
                    ->groupBy('product_id')
                    ->limit(10);
                return  $query->first();
                    
           
    }
}