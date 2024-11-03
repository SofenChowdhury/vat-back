<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\MushokRepository;
use App\Repositories\ReturnSummeryRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class MushokReturnController extends Controller
{
    protected $return;

    public function __construct(ReturnSummeryRepository $return)
    {
        $this->return = $return;
    }

    
    public function index(Request $request)
    {
        $returnSummery = $this->return->getAll($request);
        return response()->json([
            'status' => true,
            'data' => $returnSummery,
            'errors' => '', 
            'message' => "Return summery has been loaded",
        ]);
    }

    public function getLastTwo(Request $request)
    {
        $returnSummery = $this->return->getByMonth($request, auth()->user()->company_id);
        return response()->json([
            'status' => true,
            'data' => $returnSummery,
            'errors' => '', 
            'message' => "Return summery has been loaded",
        ]);
    }

    function returnSubmit(Request $request) {
        $returnSummery = $this->return->create($request, auth()->user()->company_id);
        return response()->json([
            'status' => true,
            'data' => $returnSummery,
            'errors' => '', 
            'message' => "Return summery has been loaded",
        ]);
        
    }

    public function guard()
    {
        return Auth::guard('api');
    }
}
