<?php
namespace App\Repositories;

use App\Classes\Helper;
use App\Classes\FileUpload;
use App\Mail\PaymentPlaced;
use App\Models\Company;
use App\Models\VatPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class VatPaymentRepository implements BaseRepository{

     protected  $model;
     protected  $file;
     protected  $company;
     
     public function __construct(VatPayment $model, FileUpload $file, Company $company)
     {
        $this->model = $model;
        $this->file = $file;
        $this->company = $company;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll($company_id = NULL){
          if (!empty($company_id)) {
               return $this->model::with('admin')
               ->where('company_id', $company_id)
               ->latest()->paginate(20);
          }else{
               return $this->model::with('admin')->latest()->paginate(20);
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
               return $this->model::with('admin')
               ->where('company_id', $company_id)
               ->find($id);
          }else{
               return $this->model::with('admin')->find($id);
          }
     }   

     /**
     * resource create
     * @param $request
     * @return \Illuminate\Http\Response
     */

     public function create($request){
          try {
            $payment  =  $this->model;
            $payment->type = $request->type;
            $payment->treasury_challan_no = $request->challan_no;
            $payment->remarks = $request->note;
            $payment->bank = $request->bank; 
            $payment->branch = $request->branch; //Str::random(3).substr(time(), 6,8).Str::random(3);
            $payment->account_code = $request->account_code;  
            $payment->amount = $request->amount;
            $payment->payment_date = date("Y-m-d", strtotime($request->payment_date));   
            $payment->ledger_month = date("Y-m-d", strtotime($request->ledger_month));
            $payment->created_by = auth()->user()->id;
            $payment->company_id = auth()->user()->company_id;
            $payment->save();
            return $payment;
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
        $payment = $this->getById($id);
        $payment->type = $request->type;
        $payment->treasury_challan_no = $request->challan_no;
        $payment->remarks = $request->note;
        $payment->bank = $request->bank; 
        $payment->branch = $request->branch;
        $payment->account_code = $request->account_code;  
        $payment->amount = $request->amount;
        $payment->payment_date = date("Y-m-d", strtotime($request->payment_date));   
        $payment->ledger_month = date("Y-m-d", strtotime($request->ledger_month));
        $payment->company_id = auth()->user()->company_id;
        $payment->update();
        return $payment;
     }

     /**
     * all resource get
    * @return Collection
    */
    //  Sub-form for local Purchase (for note 58 - 64)
    public function vatPaymentSubForm($request){

          $data['start_date'] = date('Y-m-01');
          $data['end_date'] = date('Y-m-d');
          
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $data['start_date'] = date('Y-m-d',  strtotime($request->start_date));
               $data['end_date'] = date('Y-m-d', strtotime($request->end_date));
          }
          $note_no = 58;
          $data['company'] = $this->company::find(auth()->user()->company_id);
          if (!empty(auth()->user()->company_id)) {
               if (!empty($request->start_date)) {
                    $start_date = date('Y-m-d', strtotime($request->start_date));
               }
               if (!empty($request->end_date)) {
                    $end_date = date('Y-m-d', strtotime($request->end_date));
               }
               if (!empty($request->note_no)) {
                    $note_no = $request->note_no;
               }
               $data['payments'] = $this->model::with('admin', 'company')
               ->where('company_id', auth()->user()->company_id)
               ->where('type', $note_no)
               ->whereBetween('ledger_month', [$data['start_date'], $data['end_date']])
               ->get();
          }else{
               if (!empty($request->start_date)) {
                    $start_date = date('Y-m-d', strtotime($request->start_date));
               }
               if (!empty($request->end_date)) {
                    $end_date = date('Y-m-d', strtotime($request->end_date));
               }
               if (!empty($request->note_no)) {
                    $note_no = $request->note_no;
               }
               $data['payments'] = $this->model::with('admin', 'company')
               ->where('type', $note_no)
               ->whereBetween('ledger_month', [$data['start_date'], $data['end_date']])
               ->get();
          }
          return $data;
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
