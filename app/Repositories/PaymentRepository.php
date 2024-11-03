<?php
namespace App\Repositories;

use App\Classes\Helper;
use App\Models\Payment;
use App\Mail\OrderPlaced;
use App\Classes\FileUpload;
use App\Mail\PaymentPlaced;
use App\Models\OrderPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Repositories\OrderRepository;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class PaymentRepository implements BaseRepository{

     protected  $model;
     protected  $orderPayment;
     protected  $order;
     protected  $file;
     
     public function __construct(Payment $model, OrderPayment $orderPayment, OrderRepository $order, FileUpload $file)
     {
        $this->model = $model;
        $this->orderPayment = $orderPayment;
        $this->order = $order;
        $this->file = $file;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll($company_id = NULL){
          if (!empty($company_id)) {
               return $this->model::with('orderPayments.orderInfo', 'company', 'shop')
               ->where('company_id', $company_id)
               ->latest()->paginate(20);
          }else{
               return $this->model::with('orderPayments.orderInfo', 'company', 'shop')->latest()->paginate(20);
          }
     }
     
     /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function getById(int $id, $company_id = NULL){
          if (!empty($company_id)) {
               return $this->model::with('orderPayments.orderInfo', 'user', 'company', 'shop', 'verified')
               ->where('company_id', $company_id)
               ->find($id);
          }else{
               return $this->model::with('orderPayments.orderInfo', 'user', 'company', 'shop', 'verified')->find($id);
          }
     }   

     /**
     * resource create
     * @param $request
     * @return \Illuminate\Http\Response
     */

     public function create($request){
          try {
               DB::beginTransaction();
               $total_amount = 0;
               foreach ($request->payment_orders as $key => $order) {
                    $orderInfo = $this->order->getById((int) $order['id']);
                    $total_amount += $orderInfo->total_amount;
               }
               $payment = $this->model;
               $payment->user_id        = (int) $this->guard()->user()->id;
               $payment->company_id        = (int) $this->guard()->user()->company_id;
               $payment->shop_id            = (int) $this->guard()->user()->shop_id;
               $payment->method            = Helper::$bank;
               $payment->transaction_type  = Helper::$credit;
               $payment->amount_credit     = $total_amount;
               $payment->bank              = $request->bank;
               $payment->branch            = $request->branch;
               $payment->account_number    = $request->account_number;
               $payment->payment_status    = 0;
               $payment->payment_number    = "PAY".date('YmdHis').rand(1000, 9999);
               $payment->payment_date      = date('Y-m-d', strtotime($request->payment_date));
               $payment->payment_slip      = $this->file->base64ImgUpload($request->payment_slip, $file = null, $folder="payment_slip");
               $payment->save();
               $orders = [];
               foreach ($request->payment_orders as $key => $order) {
                    $orderInfo = $this->order->getById($order['id']);
                    $payOrder['payment_id']  = $payment->id;
                    $payOrder['order_id']    = $order['id'];
                    $payOrder['amount']      = $orderInfo->total_amount;
                    $orders[] = $payOrder;
               }
               $this->orderPayment::insert($orders);
               DB::commit();
               $payment = $this->getById($payment->id);
               Mail::to($this->guard()->user()->email)->send(new PaymentPlaced($payment));
          } catch (\Throwable $th) {
               return $th->getMessage();
          }
          
     }  

    /**
      * specified resource update
      *
      * @param int $id
      * @param $request
      * @return \Illuminate\Http\Response
      */

     public function update( int $id, $request){
          return $request->note;
          $payment = $this->getById($id);
          $payment->name = $request->name;
          $payment->update();
     }

     /**
      * specified resource update
      *
      * @param int $id
      * @param $request
      * @return \Illuminate\Http\Response
      */

      public function approveDeny( int $id, $request){
          $payment = $this->getById($id);
          $payment->payment_status = $request->status;
          $payment->approval_deny_note = $request->note;
          $payment->approval_denied_by = auth()->user()->id;
          $payment->approval_denied_at = date('Y-m-d H:i:s');
          $payment->update();
          if ($request->status == 1) {
               $data['payment'] = $payment;
               $data['Subject'] = "Your Approved - ".$payment->dg;
               $data['message'] = "Your payment has been approved";
               $data['message'] = "Your payment has been approved";
               Mail::to(auth()->user()->email)->send(new PaymentPlaced($data));
          }
          if ($request->status == 2) {
               $data['payment'] = $payment;
               $data['message'] = "Your payment has been denied, please contact with administrator";
               Mail::to(auth()->user()->email)->send(new PaymentPlaced($data));
          }
     }

     

     public function userPayments($user_id)
     {
          if (!empty($user_id)) {
               return $this->model::with('orderPayments', 'user', 'company', 'shop')
               ->where('user_id', $user_id)
               ->latest()->paginate(20);
          }
          return false;
     }
        
     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function delete($id){
       return $this->getById($id)->delete();
     }

     public function guard()
     {
          return Auth::guard('api');
     }
}
