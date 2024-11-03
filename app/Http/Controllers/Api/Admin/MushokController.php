<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\MushokRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class MushokController extends Controller
{
    protected $mushok;

    public function __construct(MushokRepository $mushok)
    {
        $this->mushok = $mushok;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function nineOne(Request $request)
    {
        return $this->mushok->nineOne($request);
    }



    // Mushok 6.1
    public function sixOne(Request $request)
    {
        $purchases =  $this->mushok->sixOne($request);        
        return response()->json([
            'status' => true,
            'data' => $purchases,
            'errors' => '', 
            'message' => "Item Purchase Loaded",
        ]);
    }

    // Mushok 6.2
    public function sixTwo(Request $request)
    {
        $purchases =  $this->mushok->sixTwo($request);        
        return response()->json([
            'status' => true,
            'data' => $purchases,
            'errors' => '', 
            'message' => "Mushok Six Two Loaded",
        ]);
    }

    // Mushok 6.2
    public function sixTwoOne(Request $request)
    {
        $purchases =  $this->mushok->sixTwoOne($request);        
        return response()->json([
            'status' => true,
            'data' => $purchases,
            'errors' => '', 
            'message' => "Mushok Six Two One Loaded",
        ]);
    }

     
    //  Sub-form for local Supply (for note 3,4,5,7,10,12,14,18,19,20 and 21)  							
    public function salesSubForm(Request $request)
    {
        $purchases =  $this->mushok->salesSubForm($request);             
        return response()->json([
            'status' => true,
            'data' => $purchases,
            'errors' => '', 
            'message' => "Sales Sub-Form has been Loaded",
        ]);
    }

    //  Sub-form for local Supply (for note 3,4,5,7,10,12,14,18,19,20 and 21)  							
    public function purchaseSubForm(Request $request)
    {
        $purchases =  $this->mushok->purchaseSubForm($request);        
        return response()->json([
            'status' => true,
            'data' => $purchases,
            'errors' => '', 
            'message' => "Purchases Sub-Form has been Loaded",
        ]);
    }

    public function collectVds(Request $request)
    {
        $vat_deposits =  $this->mushok->collectVds($request);
        return response()->json([
            'status' => true,
            'data' => $vat_deposits,
            'errors' => '', 
            'message' => "Purchases Sub-Form has been Loaded",
        ]);
    }

    // NOTE 24 LIST
    public function vdsList(Request $request)
    {
        $vds_list =  $this->mushok->vdsList($request);
        return response()->json([
            'status' => true,
            'data' => $vds_list,
            'errors' => '', 
            'message' => "VDS list has been Loaded",
        ]);
    }

    public function report(Request $request)
    {
        $vds_list =  $this->mushok->report($request);
        return response()->json([
            'status' => true,
            'data' => $vds_list,
            'errors' => '', 
            'message' => "Report has been Loaded",
        ]);
    }

    function upload(Request $request) {
        return $this->mushok->upload($request);
    }

    function sixTen(Request $request) {
        $company_id = $request->company_id? $request->company_id:auth()->user()->company_id;
        return $this->mushok->sixTen($request, $company_id);
    }

    function correction(Request $request) {
        // if (auth()->user()->can('update_mushok')) {
            return $this->mushok->correction($request, auth()->user()->company_id);
        // }else{
        //     return response()->json(
        //         [
        //             'status' => false, 
        //             'data' => [],
        //             'errors' => '', 
        //             'message' => "You have no permission to update the mushok six two"
        //         ]);
        // }
    }

    function sixTwoOneSummery(Request $request) {
        $user = auth()->user();
        $report =  $this->mushok->sixTwoOneSummery($request, $user->company_id);
        return response()->json([
            'status' => true,
            'data' => $report,
            'errors' => '', 
            'message' => "Report has been Loaded",
        ]);
    }

    function sixTwoOneSummeryBranch(Request $request) {
        $user = auth()->user();
        $report =  $this->mushok->sixTwoOneSummery($request, $user->company_id);
        return response()->json([
            'status' => true,
            'data' => $report,
            'errors' => '', 
            'message' => "Report has been Loaded",
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
