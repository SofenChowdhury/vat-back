<?php
namespace App\Repositories;

use App\Classes\FileUpload;
use App\Models\Purchase;
use App\Models\Sales;
use App\Models\VatAdjustment;
use App\Models\VatAdjustmentChallan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class VatAdjustmentRepository implements BaseRepository{

     protected  $model;
     protected  $adjustmentChallan;
     protected  $purchase;
     protected  $sales;
     protected  $file;
     
     public function __construct(VatAdjustment $model, VatAdjustmentChallan $adjustmentChallan, FileUpload $file, Purchase $purchase, Sales $sales)
     {
        $this->model = $model;
        $this->adjustmentChallan = $adjustmentChallan;
        $this->purchase = $purchase;
        $this->sales = $sales;
        $this->file = $file;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll($company_id = NULL){
          if (!empty($company_id)) {
               return $this->model::with('customer', 'vendor', 'challans.purchase.vendor', 'challans.sales.customer', 'admin', 'company')
               ->where('company_id', $company_id)
               ->latest()->paginate(20);
          }else{
               return $this->model::with('customer', 'vendor', 'challans.purchase.vendor', 'challans.sales.customer', 'admin', 'company')->latest()->paginate(20);
          }
     }

     /**
      * all resource get
      * @return Collection
      */
      public function download($request, $company_id = NULL){
          if (!empty($company_id)) {
               return $this->model::with('customer', 'vendor', 'challans.purchase.purchaseItems.info.hscode', 'challans.purchase.vendor', 'admin', 'challans.sales.customer', 'challans.sales.SalesItems', 'company')
               ->where('company_id', $company_id)
               ->latest()
               ->lazy();
          }else{
               return $this->model::with('customer', 'vendor', 'challans.purchase.purchaseItems.info.hscode', 'challans.purchase.vendor', 'admin', 'challans.sales.customer', 'challans.sales.SalesItems', 'company')
               ->latest()
               ->lazy();
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
               return $this->model::with('customer', 'vendor', 'challans.purchase.purchaseItems.info.hscode', 'challans.purchase.vendor', 'admin', 'challans.sales.customer', 'challans.sales.SalesItems', 'company')
               ->where('company_id', $company_id)
               ->find($id);
          }else{
               return $this->model::with('customer', 'vendor', 'challans.purchase.purchaseItems.info.hscode', 'challans.purchase.vendor', 'admin', 'challans.sales.customer', 'challans.sales.SalesItems', 'company')->find($id);
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
               $payment  =  $this->model;
               $payment->type = $request->type;          
               $payment->bank = $request->bank; 
               $payment->branch = $request->branch; //Str::random(3).substr(time(), 6,8).Str::random(3);
               $payment->reference_no = $request->reference_no;
               $payment->account_code = $request->account_code;  
               $payment->certificate_no = $request->certificate_no;  
               $payment->certificate_date = $request->certificate_date;  
               $payment->note_no = $request->note_no;  
               $payment->amount = $request->amount;
               $payment->vat = $request->vat;
               $payment->deposit_date = date("Y-m-d", strtotime($request->deposit_date));   
               $payment->ledger_month = date("Y-m-d", strtotime($request->ledger_month));
               $payment->created_by = auth()->user()->id;
               $payment->company_id = auth()->user()->company_id;
               $payment->remarks = $request->remarks;
               $payment->save();
               foreach ($request->challans as $challan) { 
                    $purchase_id = NULL;
                    $sales_id = NULL;
                    if($request->type == 'increasing'){
                         $purchase_id = $challan['id'];
                    }else{
                         $sales_id =  $challan['id'];
                    }
                    $vatChallan = new VatAdjustmentChallan();

                    $vatChallan->vat_adjustment_id = $payment->id;
                    $vatChallan->purchase_id = $purchase_id;
                    $vatChallan->sales_id = $sales_id;
                    $vatChallan->value = $challan['value'];
                    $vatChallan->amount = $challan['amount'];
                    $vatChallan->save();
               }
               DB::commit();
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
        $payment->purchase_id = $request->purchase_id;
        $payment->sales_id = $request->sales_id;
        $payment->type = $request->type;
        $payment->challan_no = $request->challan_no;            
        $payment->bank = $request->bank; 
        $payment->branch = $request->branch; //Str::random(3).substr(time(), 6,8).Str::random(3);
        $payment->reference_no = $request->reference_no;
        $payment->account_code = $request->account_code;  
        $payment->certificate_no = $request->certificate_no;  
        $payment->certificate_date = $request->certificate_date;  
        $payment->note_no = $request->note_no;  
        $payment->amount = $request->amount;
        $payment->vat = $request->vat;
        $payment->deposit_date = date("Y-m-d", strtotime($request->deposit_date));   
        $payment->ledger_month = date("Y-m-d", strtotime($request->ledger_month));
        $payment->created_by = auth()->user()->id;
        $payment->company_id = auth()->user()->company_id;
        $payment->remarks = $request->remarks;
        $payment->update();
        return $payment;
     }

     public function vdsAdjustmentList($request){
          
          $query = '';
          // $query->groupBy('vat_rate');
          // $query = $this->model;
          if($request->note_no == 24 || $request->note_no == 27){
               $query = $this->model::with('vendor', 'admin', 'challans.purchase.vendor');
               $query->where('type', 'increasing');
          }elseif($request->note_no == 29 || $request->note_no == 32){
               $query = $this->model::with('customer', 'admin', 'challans.sales.customer');
               $query->where('type', 'decreasing');
          }
          
          if (!empty(auth()->user()->company_id)) {
               $query->where('company_id', auth()->user()->company_id);
          }
          

          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $query->whereBetween('ledger_month', [date('Y-m-d',  strtotime($request->start_date)), date('Y-m-d', strtotime($request->end_date))]);
          }
          if (!empty($request->note_no)) {
               $query->where('note_no', $request->note_no);
          }
        
          return $query->get();
     }

     /**
     * all resource get
    * @return Collection
    */
    //  Sub-form for local Purchase (for note 58 - 64)
    public function vatAdjustmentSubForm($request){

          // $month = date('Y-m-1', strtotime('-15 days'));
          
          $note_no = 58;
          if (!empty($request->note_no)) {
               $note_no = $request->note_no;
          }
          if (!empty(auth()->user()->company_id)) {               
              
               if($request->start_date !="" && $request->end_date !=""){
                    return $this->model::with('admin', 'purchase.vendor')
                    ->where('company_id', auth()->user()->company_id)
                    ->where('type', $note_no)
                    ->whereBetween('ledger_month', [date('Y-m-d',  strtotime($request->start_date)), date('Y-m-d', strtotime($request->end_date))])
                    ->get();
               }else{
                    return $this->model::with('admin', 'purchase.vendor')
                    ->where('company_id', auth()->user()->company_id)
                    ->where('type', $note_no)
                    ->get();
               }
               
          }else{
               if($request->start_date !="" && $request->end_date !=""){
                    return $this->model::with('admin', 'purchase.vendor')
                    ->where('type', $note_no)
                    ->whereBetween('ledger_month', [date('Y-m-d',  strtotime($request->start_date)), date('Y-m-d', strtotime($request->end_date))])
                    ->get();
               }else{
                    return $this->model::with('admin', 'purchase.vendor')
                    ->where('type', $note_no)
                    ->get();
               }
          }
          
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

     public function search($request)
     {
          $query = $request->keyword;
          
          return $this->model::where(function($q) use ($query){
                         $q->where('purchase_no','LIKE', $query.'%');
                         $q->orWhere('challan_no', 'LIKE', $query.'%');
                         $q->orWhere('vendor_id', 'LIKE', $query.'%');
                    })
                    ->orderBy("id", "desc")
                    ->take(100)
                    ->get();
     }

     /**
      * all resource get
      * @return Collection
      */
      public function challanSearch($request, $user = NULL){
          
          
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
               $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
          }

          if ($request->type == 'increasing') {
               $query = $this->purchase::query();
               $query->with('company', 'vendor', 'purchaseItems.info');
               if ((isset($request->vendor_id) && $request->vendor_id !="")) {
                    $query->where('vendor_id', $request->vendor_id);
               }
          } else {
               $query = $this->sales::query();
               $query->with('SalesItems.itemInfo', 'customer', 'company');
               if ((isset($request->customer_id) && $request->customer_id !="")) {
                    $query->where('customer_id', $request->customer_id);
               }
          }          
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
          }
                    
          if (!empty($user) && $user->company_id !="") {
               $query->take(100);
               return $query->get();    
          }else{
               $query->take(100);
               return $query->get();    
          }     
           
     }

     public function upload($request) {
          $filename = $request->file('csvfile');
          if($_FILES["csvfile"]["size"] > 0)
          {
               $file = fopen($filename, "r");
               $i = 0;
               try {
                    $previousRef = NULL;
                    DB::beginTransaction();
                    while (($item_info = fgetcsv($file, 10000, ",")) !== FALSE)
                    {
                         $i++;
                         if ($i>1) { 
                              if ($previousRef != $item_info[1]) {
                                   $adjustmentInfo = [
                                        'company_id' => auth()->user()->company_id,
                                        'vendor_id' => $item_info[2] == 'increasing'? (int) $item_info[0]: NULL,
                                        'customer_id' => $item_info[2] == 'decreasing'? $item_info[0]: NULL,
                                        'certificate_no' => $item_info[1],
                                        'ref_no' => $item_info[10],
                                        'note_no' => $item_info[6],
                                        'bank' => $item_info[3],
                                        'branch' => $item_info[4],
                                        'reference_no' => $item_info[13],
                                        'account_code' => $item_info[13],
                                        'type' => $item_info[2],
                                        'sl_no' => (int) $item_info[7],
                                        'certificate_date' => date("Y-m-d", strtotime($item_info[12])),
                                        'date' => date("Y-m-d", strtotime($item_info[11])),
                                        'ledger_month' => date("Y-m-d", strtotime($item_info[5])),
                                        'deposit_date' => date("Y-m-d 09:$i:s", strtotime($item_info[12])),
                                        'created_at' => date("Y-m-d 09:$i:s", strtotime($item_info[12])),
                                        'remarks' => "Manual Bulk Upload",
                                        'created_by' => auth()->user()->id
                                   ];
                                   $adjustment = $this->createNewAdjustment($adjustmentInfo);
                              }
                              $challan = new  $this->adjustmentChallan;
                              $challan->vat_adjustment_id = $adjustment->id;
                              $challan->challan_no = $item_info[10];
                              $challan->challan_date = date("Y-m-d", strtotime($item_info[11]));
                              $challan->value = $item_info[8];
                              $challan->amount = $item_info[9];
                              $challan->save();
                              $previousRef = $item_info[1];
                         }
                    }
                    DB::commit();
                    $returnData['status'] = true;
                    $returnData['data'] = $this->getById($adjustment->id);
                    $returnData['message'] = "VDS certificate has been  successfully uploaded";
                    return $returnData;
               }catch (\Throwable $th) {
                    DB::rollBack();
                    $returnData['status'] = false;
                    $returnData['data'] = [];
                    $returnData['message'] = $th->getMessage();
                    return $returnData;
               }
          }
     }

     public function createNewAdjustment($adjustmentInfo) {
          $adjustment = $this->model::create($adjustmentInfo);
          return $adjustment;
     }

     public function guard()
     {
          return Auth::guard('api');
     }
}
