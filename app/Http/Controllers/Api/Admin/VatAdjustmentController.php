<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\VatAdjustment;
use App\Repositories\VatAdjustmentRepository;
use Illuminate\Support\Facades\Validator;
use App\Repositories\VatPaymentRepository;
use Illuminate\Http\Request;

class VatAdjustmentController extends Controller
{
    protected $adjustment;

    public function __construct(VatAdjustmentRepository $adjustment)
    {
        $this->adjustment = $adjustment;
    }

    public function index()
    {
        $user = auth()->user();
        $values =  $this->adjustment->getAll($user->company_id);
        return response()->json([
            'status' => true,
            'data' => $values,
            'errors' => '', 
            'message' => "Value Addition Heads has been loaded",
        ]);
    }

    function download(Request $request) {
        $user = auth()->user();
        $adjustments =  $this->adjustment->download($request, $user->company_id);
        return response()->json([
            'status' => true,
            'data' => $adjustments,
            'errors' => '', 
            'message' => "Challan for adjustment has been loaded",
        ]);
    }

    public function challanSearch(Request $request)
    {
        $challans =  $this->adjustment->challanSearch($request);
        return response()->json([
            'status' => true,
            'data' => $challans,
            'errors' => '', 
            'message' => "Challan for adjustment has been loaded",
        ]);
    }

    

   
    //  Sub-form for local Supply (for note 3,4,5,7,10,12,14,18,19,20 and 21)  							
    public function payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "amount" => "required"
        ]);
        if( $validator->fails()){
            return ['status' =>false , 'errors' => $validator->errors()];
        }
        
        $vat_deposits = $this->adjustment->create($request);
        return response()->json([
            'status' => true,
            'data' => $vat_deposits,
            'errors' => '', 
            'message' => "adjustment has been successfully added",
        ]);
        
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "bank" => "required",
            "account_code" => "required",
            "amount" => "required"
        ]);
        if( $validator->fails()){
            return ['status' =>false , 'errors' => $validator->errors()];
        }
        $vat_deposits = $this->adjustment->update($request->id, $request);
        return response()->json([
            'status' => true,
            'data' => $vat_deposits,
            'errors' => '', 
            'message' => "adjustment has been successfully Updated",
        ]);
    }

    public function show($id)
    {
        $adjustmentDetails = $this->adjustment->getById($id);
        return response()->json([
            'status' => true,
            'data' => $adjustmentDetails,
            'errors' => '', 
            'message' => "Adjustment details has been loaded",
        ]);
    }

    public function vdsAdjustmentList(Request $request)
    {
        $vat_adjustments = $this->adjustment->vdsAdjustmentList($request);
        return response()->json([
            'status' => true,
            'data' => $vat_adjustments,
            'errors' => '', 
            'message' => "Adjustment has been successfully loaded",
        ]);
    }

    public function upload(Request $request){
        return $this->adjustment->upload($request);
    }
}
