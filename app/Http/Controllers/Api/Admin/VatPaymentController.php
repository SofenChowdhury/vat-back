<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\VatPaymentRepository;
use Illuminate\Http\Request;

class VatPaymentController extends Controller
{
    protected $payment;

    public function __construct(VatPaymentRepository $payment)
    {
        $this->payment = $payment;
    }

    public function index()
    {
        $values =  $this->payment->getAll(auth()->user()->company_id);
        return response()->json([
            'status' => true,
            'data' => $values,
            'errors' => '', 
            'message' => "Treasury Challan has been loaded",
        ]);
    }

   
    //  Sub-form for local Supply (for note 3,4,5,7,10,12,14,18,19,20 and 21)  							
    public function vatDeposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "challan_no" => "required",
            "bank" => "required",
            "account_code" => "required",
            "amount" => "required"
        ]);
        if( $validator->fails()){
            return ['status' =>false , 'errors' => $validator->errors()];
        }
        $vat_deposits = $this->payment->create($request);
        return response()->json([
            'status' => true,
            'data' => $vat_deposits,
            'errors' => '', 
            'message' => "Payment has been successfully added",
        ]);
    }

    public function vatPaymentSubForm(Request $request)
    {
        $vat_deposits = $this->payment->vatPaymentSubForm($request);
        return response()->json([
            'status' => true,
            'data' => $vat_deposits,
            'errors' => '', 
            'message' => "Sub-Form note ".$request->note_no." been successfully added",
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "challan_no" => "required",
            "bank" => "required",
            "account_code" => "required",
            "amount" => "required"
        ]);
        if( $validator->fails()){
            return ['status' =>false , 'errors' => $validator->errors()];
        }
        $vat_deposits = $this->payment->update($request->id, $request);
        return response()->json([
            'status' => true,
            'data' => $vat_deposits,
            'errors' => '', 
            'message' => "Payment has been successfully Updated",
        ]);
    }

    public function show($id)
    {
        $goodsDetails = $this->payment->getById($id);
        return response()->json([
            'status' => true,
            'data' => $goodsDetails,
            'errors' => '', 
            'message' => "Purchase list has been loaded",
        ]);
    }
}
