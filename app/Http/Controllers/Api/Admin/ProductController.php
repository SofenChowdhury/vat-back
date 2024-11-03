<?php

namespace App\Http\Controllers\Api\Admin;

use Inertia\Inertia;
use App\Models\Gallery;
use App\Models\Product;
use App\Classes\FileUpload;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Models\ItemStock;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Redirect;

class ProductController extends Controller
{
    protected $product;
    protected $file;

    public function __construct(ProductRepository $product, FileUpload $fileUpload)
    {
        $this->product = $product;
        $this->file = $fileUpload;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products =  $this->product->getAll();
        return response()->json([
            'status' => true,
            'data' => $products,
            'errors' => '',
            'message' => "Product List Loaded",
        ]);
    }

    public function listWithSearch(Request $request)
    {
        $products =  $this->product->listWithSearch($request);
        return response()->json([
            'status' => true,
            'data' => $products,
            'errors' => '',
            'message' => "Product List Loaded",
        ]);
    }

    public function finishedGoods()
    {
        $products =  $this->product->finishedGoods();
        return response()->json([
            'status' => true,
            'data' => $products,
            'errors' => '',
            'message' => "Product List Loaded",
        ]);
    }

    public function rawMaterials()
    {
        $products =  $this->product->rawMaterials();
        return response()->json([
            'status' => true,
            'data' => $products,
            'errors' => '',
            'message' => "Product List Loaded",
        ]);
    }

    public function accessories()
    {
        $products =  $this->product->accessories();
        return response()->json([
            'status' => true,
            'data' => $products,
            'errors' => '',
            'message' => "Product List Loaded",
        ]);
    }

    public function services()
    {
        $products =  $this->product->services();
        return response()->json([
            'status' => true,
            'data' => $products,
            'errors' => '',
            'message' => "Product List Loaded",
        ]);
    }

    public function serviceSearch($query)
    {
        $products =  $this->product->serviceSearch($query);
        return response()->json([
            'status' => true,
            'data' => $products,
            'errors' => '',
            'message' => "Product List Loaded",
        ]);
    }



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function stock(Request $request)
    {
        $stocks =  $this->product->stock($request);
        return response()->json([
            'status' => true,
            'data' => $stocks,
            'errors' => '',
            'message' => "Stock List Loaded",
        ]);
    }

    function stockMerge(Request $request)
    {
        $stocks =  $this->product->stockMerge($request);
        return response()->json([
            'status' => true,
            'data' => $stocks,
            'errors' => '',
            'message' => "Stock has been merged",
        ]);
    }

    public function stockDownload(Request $request)
    {
        $stocks =  $this->product->stockDownload($request);
        return response()->json([
            'status' => true,
            'data' => $stocks,
            'errors' => '',
            'message' => "Stock List Loaded",
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function create(ProductRequest $request)
    {

        $product = $this->product->create($request);
        if ($product['status'] == false) {
            return response()->json([
                'status' => false,
                'errors' => $product['errors'],

            ]);
        }
        return response()->json([
            'status' => true,
            'data' => $product['data'],
            'errors' => '',
            'message' => "Product List Loaded",
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show($id = NULL)
    {
        $product = $this->product->getById($id);
        return response()->json([
            'status' => true,
            'data' => $product,
            'errors' => '',
            'message' => "Product List Loaded",
        ]);
    }

    public function showSku($sku)
    {
        $product = $this->product->getBySku($sku);
        return response()->json([
            'status' => true,
            'data' => $product,
            'errors' => '',
            'message' => "Product List Loaded",
        ]);
    }


    public function search(Request $request)
    {
        $product = $this->product->search($request);
        return response()->json([
            'status' => true,
            'data' => $product,
            'errors' => '',
            'message' => "Product List Loaded",
        ]);
    }

    public function multiSearch(Request $request)
    {
        $product = $this->product->multiSearch($request);
        return response()->json([
            'status' => true,
            'data' => $product,
            'errors' => '',
            'message' => "Product List Loaded",
        ]);
    }

    public function download(Request $request)
    {
        $products = $this->product->download($request);
        return response()->json([
            'status' => true,
            'data' => $products,
            'errors' => '',
            'message' => "Product List Loaded",
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(ProductRequest $request)
    {
        if (empty($request->product_id)) {
            return response()->json([
                'status' => false,
                'data' => NULL,
                'errors' => [array('title' => "product id can't be null")],
                'message' => "Validation Error",
            ]);
        }
        $product = $this->product->update($request->product_id, $request);
        if ($product['status'] == false) {
            return response()->json([
                'status' => false,
                'errors' => $product['errors'],

            ]);
        }
        return response()->json([
            'status' => true,
            'data' => $product['data'],
            'errors' => '',
            'message' => "Product has been successfully updated",
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        $this->product->delete($id);
        return response()->json([
            'status' => true,
            'data' => "",
            'errors' => '',
            'message' => "The product successfully deleted",
        ]);
    }

    public function productUpdate(ProductRequest $request)
    {
        // return $request->thumbnail;
        $product = $this->product->update($request->product_id, $request);
        return Redirect::route('products.index');
    }

    public function galleryAdd($id)
    {
        $galleries = Gallery::latest()->where('product_id', $id)->get();
        return Inertia::render('Product/Gallery', compact('id', 'galleries'));
    }


    public function galleryStore(ProductRequest $request)
    {
        if ($request->image != NULL) {
            $gallery = new Gallery();
            $gallery->product_id = $request->product_id;
            $gallery->image = $this->file->base64ImgUpload($request->image, $file = "", $folder = "galleries");
            $gallery->color_code = $request->color_code;
            $gallery->save();

            $title = "Gallery!";
            $message = "Data has been update successfully";
            $type = "success";
        } else {

            $request->validate([
                'image' => 'required'
            ]);
        }

        return redirect()->back()->with('success', 'some message');
    }


    public function  galleryEdit($id)
    {
        $gallery = Gallery::where('id', $id)->first();
        return response()->json($gallery);
    }


    public function galleryUpdate(ProductRequest $request)
    {

        $gallery = Gallery::where('id', $request->gallery_id)->first();
        $data = $request->image;
        $fileName = "";
        if (substr($data, 0, 22) == 'data:image/jpg;base64,'  ||  substr($data, 0, 22) == "data:image/png;base64"  || substr($data, 0, 22) == "data:image/jpeg;base64") {
            if ($request->image != NULL) {
                $fileName = $this->file->base64ImgUpload($request->image, $file = $gallery->image, $folder = "galleries");
            }
        }
        $gallery->image = $fileName ?  $fileName :  $gallery->image;
        $gallery->update();
        $title = "Gallery!";
        $message = "Data has been update successfully";
        $type = "success";
    }

    public function galleryDelete($id)
    {
        $gallery = Gallery::find($id);
        $this->file->fileDelete($folder = "galleries", $file = $gallery->image);
        $gallery->delete();
        return back();
    }

    public function varitantForm($id)
    {
        $product =  Product::with('attributes.attributeOptions', 'varitantProdcts.variantValues.attributeOption')->where('id', $id)->first();
        return Inertia::render('Product/Variant', compact('id', 'product'));
    }


    public function variantUpdate(ProductRequest $request)
    {

        if (isset($request->variants)) {
            foreach ($request->variants as $key => $variant) {

                $product =  Product::where('id', $variant['id'])->first();
                $product->sku =  sku_generate();
                $product->regular_price =  $variant['regular_price'];
                $product->discount_price =  $variant['discount_price'];
                $product->stock =  10;
                $product->save();
            }
        }

        return Redirect::route('products.index');
    }


    public function varitantDelete($id)
    {
        $product = Product::where('id', $id)->first();
        $product->delete();
        return back();
    }

    public function productDownload()
    {
        $products = $this->product->getAllVariant();
        $fileName = "ProductList" . date("ymdhis");
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        $columns = array(
            'SL',
            'SKU',
            'Product Name',
            'MRP (Editable)',
            'Discount Price (Editable)',
            'Stock (Editable)',
            'Status (Editable)'
        );

        $callback = function () use ($products, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            $i = 0;
            foreach ($products as $product) {
                $i++;
                $row['sl'] = $i;
                $row['sku']  = $product->sku;
                $row['name']  = substr($product->title, 0, 120);
                $row['regular_price']  = $product->regular_price;
                $row['discount_price']  = $product->discount_price;
                $row['stock']  = $product->stock;
                $row['status']  = $product->status == 1 ? "Activated" : "Inactivated";
                fputcsv($file, $row);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function bulkUpload(Request $request)
    {
        // $products = $this->product->BulkQueueUpload($request);
        $products = $this->product->bulkUpload($request);
        return response()->json([
            'status' => true,
            'data' => $products,
            'errors' => '',
            'message' => "Products Successfully Uploaded",
        ]);
    }

    function stockSummery(Request $request)
    {
        $user = auth()->user();
        return $this->product->stockSummery($request, $user->company_id);
    }

    // public function stockReport(Request $request)
    // {
    //     return $this->product->stockReport($request);
    // }
    
    public function stockReportDownload(Request $request)
    {
        return $this->product->stockReportDownload($request);
    }

    public function stockReport(Request $request)
    {
        return $this->product->stockSummeryReport($request);
    }
}