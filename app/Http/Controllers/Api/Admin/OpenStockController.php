<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PurchaseRequest;
use App\Repositories\OpenStockRepository;

class OpenStockController extends Controller
{
    protected $openStock;

    public function __construct(OpenStockRepository $openStock)
    {
        $this->openStock = $openStock;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $stocks =  $this->openStock->getAll();
        return response()->json([
            'status' => true,
            'data' => $stocks,
            'errors' => '', 
            'message' => "Stocks List Loaded",
        ]);
    }

    public function create(Request $request)
    {
        $stockInfo = $this->openStock->create($request);
        return response()->json([
            'status' => true,
            'data' => $stockInfo,
            'errors' => '', 
            'message' => "A new opening stock has been successfully created",
        ]);
    }


    public function show($id)
    {
        $purchaseDetails = $this->openStock->getById($id);
        return response()->json([
            'status' => true,
            'data' => $purchaseDetails,
            'errors' => '', 
            'message' => "Opening stock list has been loaded",
        ]);
    }


    // Purchase search
    public function search(Request $request)
    {
        $purchases = $this->openStock->search($request);
        return response()->json([
            'status' => true,
            'data' => $purchases,
            'errors' => '', 
            'message' => "Opening stock Search Loaded",
        ]);
    }

    public function upload(Request $request)
    {
        $stockInfo = $this->openStock->bulkUpload($request);
        return response()->json([
            'status' => true,
            'data' => $stockInfo,
            'errors' => '', 
            'message' => "AN opening stock has been successfully created",
        ]);
    }

    public function download(){
        $purchases = $this->openStock->download();
        return response()->json([
            'status' => true,
            'data' => $purchases,
            'errors' => '', 
            'message' => "Opening stock List Loaded",
        ]);
    }

    public function clearData()
    {
        $stock = $this->openStock->clearData();
        return response()->json([
            'status' => true,
            'data' => $stock,
            'errors' => '', 
            'message' => "Opening stock has been cleared",
        ]);
    }
}
