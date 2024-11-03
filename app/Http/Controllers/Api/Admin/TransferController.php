<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TransferRequest;
use Illuminate\Support\Facades\Auth;
use App\Repositories\TransferRepository;

class TransferController extends Controller
{
    protected $transfer;

    public function __construct(TransferRepository $transfer)
    {
        $this->transfer = $transfer;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $transfers =  $this->transfer->getAll();
        return response()->json([
            'status' => true,
            'data' => $transfers,
            'errors' => '', 
            'message' => "Transfer List Loaded",
        ]);
    }

    public function create(TransferRequest $request)
    {
        return $this->transfer->create($request);
        
    }

    public function bulkUpload(Request $request) {
        return $this->transfer->bulkUpload($request);
        
    }

    public function show($id)
    {
        $purchaseDetails = $this->transfer->getById($id);
        return response()->json([
            'status' => true,
            'data' => $purchaseDetails,
            'errors' => '', 
            'message' => "Purchase list has been loaded",
        ]);
    }

    public function returnShow($id)
    {
        $returnedDetails = $this->transfer->getReturnById($id);
        return response()->json([
            'status' => true,
            'data' => $returnedDetails,
            'errors' => '', 
            'message' => "Purchase return has been loaded",
        ]);
    }

    // Purchase search
    public function search(Request $request)
    {
        $purchases = $this->transfer->search($request);
        return response()->json([
            'status' => true,
            'data' => $purchases,
            'errors' => '', 
            'message' => "Purchase Search Loaded",
        ]);
    }

    public function download(Request $request){
        $purchases = $this->transfer->download($request);
        return response()->json([
            'status' => true,
            'data' => $purchases,
            'errors' => '', 
            'message' => "Purchase List Loaded",
        ]);
    }

    public function checkTransfer($challan)
    {
        $transferDetails = $this->transfer->getByChallan($challan);
        return response()->json([
            'status' => true,
            'data' => $transferDetails,
            'errors' => '', 
            'message' => "Transfer details has been loaded",
        ]);
    }

    
     /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard('api');
    }
}
