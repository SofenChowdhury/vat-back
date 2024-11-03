<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Classes\Helper;
use App\Models\Payment;
use App\Classes\FileUpload;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    //
    protected $payment;
    protected $order;
    protected $file;   

    public function __construct(PaymentRepository $payment, OrderRepository $order, FileUpload $fileUpload)
    {
        $this->payment  = $payment;
        $this->order    = $order;
        $this->file     = $fileUpload;
    }
    public function orderPayment(Request $request)
    {
        try {
            $data = $this->payment->create($request);
            return response()->json(['status'=>true , 'message' => 'Your payment has been successfully submitted']);
        } catch (\Throwable $th) {
            return response()->json(['status'=>false , 'message' => $th->getMessage()]);
        }
    }

    public function userPayments()
    {
        try {
            $payments = $this->payment->userPayments($this->guard()->user()->id);
            return response()->json([
                'status'=>true,
                'payments'=> $payments
            ]);
        } catch (\Throwable $th) {
            return response()->json(['status'=>false , 'message' => $th->getMessage()]);
        }
    }

    public function paymentDetails($payment_id)
    {
        try {
            $payment = $this->payment->getById($payment_id, $this->guard()->user()->company_id);
            return response()->json([
                'status' => true,
                'payment' => $payment
            ]);
        } catch (\Throwable $th) {
            return response()->json(['status'=>false , 'message' => $th->getMessage()]);
        }
    }





     
































    public function guard()
    {
        return Auth::guard('api');
    }
}
