<?php
namespace App\Repositories;

use App\Models\Sales;
use App\Classes\Helper;
use App\Models\Company;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\ItemStock;
use App\Models\MushokSix;
use App\Models\SalesItem;
use App\Models\VatPayment;
use App\Models\PurchaseItem;
use App\Models\VatAdjustment;
use App\Models\PurchaseReturn;
use App\Models\SalesReturnItem;
use App\Models\PurchaseReturnItem;
use App\Models\ReturnSummary;
use Illuminate\Support\Facades\DB;
use App\Models\TreasuryChallanCode;
use Illuminate\Support\Facades\Auth;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class MushokRepository implements BaseRepository{

    protected  $model;
    protected  $purchaseItem;
    protected  $sales;
    protected  $salesItems;
    protected  $product;
    protected  $stock;
    protected  $mushok;
    protected  $return;
    protected  $returnItems;
    protected  $company;
    protected  $payment;
    protected  $vatAdjustment;
    protected  $salesReturnItem;
    protected  $purchaseReturnItem;
    protected  $treasuryChallanCode;
    protected  $mushok_return;
    
    public function __construct(Purchase $model, PurchaseItem $purchaseItem, Sales $sales, SalesItem $salesItems, Product $product, ItemStock $stock, MushokSix $mushok, PurchaseReturn $return, PurchaseReturnItem $returnItems, Company $company, VatPayment $payment, VatAdjustment $vatAdjustment, SalesReturnItem $salesReturnItem, PurchaseReturnItem $purchaseReturnItem, TreasuryChallanCode $treasuryChallanCode, ReturnSummeryRepository $mushok_return)
    {
        $this->model = $model;
        $this->purchaseItem = $purchaseItem;
        $this->sales = $sales;
        $this->salesItems = $salesItems;
        $this->product = $product;
        $this->stock = $stock;
        $this->mushok = $mushok;
        $this->return = $return;
        $this->returnItems = $returnItems;
        $this->company = $company;
        $this->payment = $payment;
        $this->vatAdjustment = $vatAdjustment;
        $this->salesReturnItem = $salesReturnItem;
        $this->purchaseReturnItem = $purchaseReturnItem;
        $this->treasuryChallanCode = $treasuryChallanCode;
        $this->mushok_return = $mushok_return;
    }

    // Mushok 6.1
    public function nineOne($request)
    {
        $mushokNineOne = [];
        
        $mushokNineOne['company']   =  $this->company::where('id', auth()->user()->company_id)->first();
        $mushokNineOne['account_code']   =  $this->treasuryChallanCode::where('company_id', auth()->user()->company_id)->get();
        $mushokNineOne['supplies']  =  $this->nineOneSupplies($request, auth()->user()->company_id);
        $mushokNineOne['exempted_supplies']  =  $this->nineOneExemptedSupplies($request, auth()->user()->company_id);
        // Local Rebatable 
        $mushokNineOne['purchase_local_rebatable_amount']  =  $this->nineOneRebateable($request, auth()->user()->company_id, "Local");
        $mushokNineOne['purchase_local_rebatable_vat']  =  $this->nineOneRebateable($request, auth()->user()->company_id, "Local", "VAT");
        //  Imported Rebatable 
        $mushokNineOne['purchase_imported_rebatable_amount']  =  $this->nineOneRebateable($request, auth()->user()->company_id, "Imported");
        $mushokNineOne['purchase_imported_rebatable_vat']  =  $this->nineOneRebateable($request, auth()->user()->company_id, "Imported", "VAT");
        
        // Exempted Purchase
        $mushokNineOne['local_exempted_amount']  = $this->nineOnePurchaseExempted($request, auth()->user()->company_id, 'Local');
        $mushokNineOne['imported_exempted_amount']  = $this->nineOnePurchaseExempted($request, auth()->user()->company_id, 'Imported');
        // Nonrebatable

        $mushokNineOne['local_nonrebateable_amount']  = $this->nineOnePurchaseNonRebatableTotalPrice($request, auth()->user()->company_id, 'Local');
        $mushokNineOne['local_nonrebateable_vat']  = $this->nineOnePurchaseNonRebatableVAT($request, auth()->user()->company_id, 'Local');

        $mushokNineOne['imported_nonrebateable_amount'] = $this->nineOnePurchaseNonRebatableTotalPrice($request, auth()->user()->company_id, 'Imported');
        $mushokNineOne['imported_nonrebateable_vat'] = $this->nineOnePurchaseNonRebatableVAT($request, auth()->user()->company_id, 'Imported');
        

        // Adjustments

        $mushokNineOne['vds_increasing_amount_24']  =  $this->vatAdjustment->adjustmentTotal($request, 24, auth()->user()->company_id, 'increasing');
        $mushokNineOne['other_increasing_amount_27']  =  $this->vatAdjustment->adjustmentTotal($request, 27, auth()->user()->company_id, 'increasing');
        $mushokNineOne['vds_decreasing_amount_29']  =  $this->vatAdjustment->adjustmentTotal($request, 29, auth()->user()->company_id, 'decreasing');
        $mushokNineOne['advance_tax_amount_30']  =  $this->getAdvanceTax($request, auth()->user()->company_id);
        $mushokNineOne['credit_note_amount_31']  =  $this->creditNoteTotal($request, auth()->user()->company_id);
        $mushokNineOne['other_decreasing_amount_32']  =  $this->vatAdjustment->adjustmentTotal($request, 32, auth()->user()->company_id, 'decreasing');
        $mushokNineOne['debit_note_amount_26']  =  $this->debitNoteTotal($request, auth()->user()->company_id);
        $mushokNineOne['last_mushok_return']  =  $this->mushok_return->mushokReturn($request, auth()->user()->company_id);
        
        $mushokNineOne['paid_vat']  =  $this->payment->vatPaid($request);        
        $mushokNineOne['return_summery']  =  $this->returnSummery($request, auth()->user()->company_id);        
        $mushokNineOne['generated_by'] = auth()->user();
        
        // return $mushokNineOne;
        return response()->json([
            'status' => true,
            'data' => $mushokNineOne,
            'errors' => '', 
            'message' => "Mushok Nine One has been loaded",
        ]);
    }

    function returnSummery($request, $company_id = NULL) {
        $return = $this->mushok_return->mushokReturn($request, $company_id);
        return $return;
    }


    function nineOneRebateable($request, $company_id = null, $type, $vat = NULL) {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        
        $result = $this->purchaseItem::whereHas('purchase', function($q) use ($data, $company_id, $type){
            $q->where('company_id', $company_id);
            $q->where('type', $type);
            $q->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        })
        
        ->where('vat_rebetable_amount', '>', 0)
        ->groupBy('vat_rate');
        if ($vat) {
            $result->selectRaw('sum(vat_rebetable_amount) as totalAmount, vat_rate');
        }else{
            $result->selectRaw('sum(total_price+cd+sd+rd) as totalAmount, vat_rate');
        }
        
        $mushok = $result->pluck('totalAmount', 'vat_rate');
        return $mushok;
    }

    function nineOnePurchaseExempted($request, $company_id = null, $type = "Local") {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        
        $result = $this->purchaseItem::whereHas('purchase', function($q) use ($data, $company_id, $type){
            $q->where('company_id', $company_id);
            $q->where('type', $type);
            $q->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        })
        // return $query->get();
        // ->where('vat_rebetable_amount', 0)
        ->where('vat_rate', 0)
        ->selectRaw('sum(total_price+cd+sd+rd) as totalAmount')->first();
        if ($result['totalAmount']>0) {
            return $result['totalAmount'];
        }else{
            return 0;
        }
    }

    function nineOnePurchaseNonRebatableTotalPrice($request, $company_id = null, $type = "Local") {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        
        $result = $this->purchaseItem::whereHas('purchase', function($q) use ($data, $company_id, $type){
            $q->where('company_id', $company_id);
            $q->where('type', $type);
            $q->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        })
        // return $query->get();
        ->where('vat_rebetable_amount', 0)
        ->where('vat_amount', '>', 0)
        // ->groupBy('type')
        ->selectRaw('sum(total_price+cd+sd+rd) as totalAmount')->first();
        if ($result['totalAmount']>0) {
            return $result['totalAmount'];
        }else{
            return 0;
        }
    }

    function nineOnePurchaseNonRebatableVAT($request, $company_id = null, $type = "Local") {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        
        $result = $this->purchaseItem::whereHas('purchase', function($q) use ($data, $company_id, $type){
            $q->where('company_id', $company_id);
            $q->where('type', $type);
            $q->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        })
        // return $query->get();
        ->where('vat_rebetable_amount', 0)
        ->where('vat_amount', '>', 0)
        // ->groupBy('type')
        ->selectRaw('sum(vat_amount) as totalVat')->first();
        if ($result['totalVat']>0) {
            return $result['totalVat'];
        }else{
            return 0;
        }
    }

    public function nineOneSupplies($request = NULL, $company_id = NULL)
    {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        // DB::enableQueryLog(); // Enable query log
        return $this->salesItems::whereHas('sales', function($q) use ($data, $company_id){
            $q->where('company_id', $company_id);
            // stop tracking for wrong entry for FEL Mushok
            $q->where('id', '!=', 1258);
            $q->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        })
        // ->where('is_vat_exempted', '!=', 1)
        // ->where('vat_rate', 15)
        ->groupBy('vat_rate')
        ->selectRaw('sum(total_price) as totalAmount, vat_rate')
        ->pluck('totalAmount', 'vat_rate');
        // return DB::getQueryLog();
    }

    public function nineOneExemptedSupplies($request = NULL, $company_id = NULL)
    {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        
        return $this->salesItems::whereHas('sales', function($q) use ($data, $company_id){
            $q->where('company_id', $company_id);
            $q->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        })
        ->where('is_vat_exempted', '!=', 0)
        ->where('vat_rate', '=', 0)
        ->groupBy('vat_rate')
        ->selectRaw('sum(total_price) as totalAmount, vat_rate')
        ->pluck('totalAmount', 'vat_rate');
    }


    

    function getAdvanceTax($request, $company_id = null) {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        
        return $this->purchaseItem::whereHas('purchase', function($q) use ($data, $company_id){
            $q->where('company_id', $company_id);
            $q->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        })
        ->selectRaw('sum(at) as advanceTax')->first()->advanceTax;
    }

    function creditNoteTotal($request, $company_id = null) {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        
        return $this->salesReturnItem::whereHas('return', function($q) use ($data, $company_id){
            $q->where('company_id', $company_id);
            $q->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        })
        ->sum('vat_amount');
        // ->selectRaw('sum(vat_amount) as vat_amount')->first()->vat_amount;
    }

    function debitNoteTotal($request, $company_id = null) {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        
        return $this->purchaseReturnItem::whereHas('purchaseReturn', function($q) use ($data, $company_id){
            $q->where('company_id', $company_id);
            $q->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        })
        ->sum('vat_amount');
        // ->selectRaw('sum(vat_amount) as vat_amount')->first()->vat_amount;
    }
    
    // Mushok 6.1
    public function sixOne($request)
    {
        // return $request->all();
        // $data['start_date'] = date('Y-m-d H:i:s', strtotime('-30 days'));
        // $data['end_date'] = date('Y-m-d 23:59:59');
        $user = auth()->user();
        $query = $this->mushok::query();
        $query->with('purchase.vendor', 'purchase.company', 'finished', 'info.category', 'opening');
        $query->where('mushok', 'six_one');
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
        }
        
        // $query->groupBy(DB::raw("DATE_FORMAT(created_at, '%d-%m-%Y')"));
        $query->where('product_id', $request->product_id);
        if (!empty($user->company_id)) {
            $query->where('company_id', $user->company_id);
        }
        $query->where('is_transfer', 0);
        $query->orderBy('created_at', 'asc');
        return $query->lazy();
    }

    // Mushok 6.2
    public function sixTwo($request)
    {    
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }

        $query = $this->mushok::query();
        $query->with('finished', 'sales.company', 'sales.branch', 'info.category', 'sales.customer');
        $query->where('mushok', 'six_two');
        $query->whereBetween('created_at', [$data['start_date'], $data['end_date']]);
        $query->where('product_id', $request->product_id);
        $query->where('company_id', auth()->user()->company_id);
        $query->where('is_transfer', 0);
        $query->orderBy('created_at', 'asc');
        return $query->get();
    }

     // Mushok 6.2.1
     public function sixTwoOne($request)
     {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        $query = $this->mushok::query();
        $query->where('mushok', 'six_two_one');
        $query->with('finished', 'sales.company', 'purchase.vendor', 'sales.branch', 'transfer.fromBranch', 'transfer.toBranch', 'info.category', 'sales.customer');
        $query->where('company_id', auth()->user()->company_id);
        
        $query->whereBetween('created_at', [$data['start_date'], $data['end_date']]);
        
        
        // $query->groupBy(DB::raw("DATE_FORMAT(created_at, '%d-%m-%Y')"));
        $query->where('product_id', $request->product_id);
        if (!empty($request->branch_id) && $request->branch_id !="") {
            $query->where('branch_id', $request->branch_id);
        }else{
            $query->where('is_transfer', 0);
        }
        $query->orderBy('created_at', 'asc');
        return $query->get();
     }

    /**
     * all resource get
    * @return Collection
    */
    public function getAll($company_id = NULL){          
        if (!empty($company_id)) {
            return $this->model::with('company', 'vendor', 'items.info')
            ->where('company_id', $company_id)
            ->latest()->paginate(20);
        }else{
            return $this->model::with('company', 'vendor', 'items.info')->latest()->paginate(20);
        }                
    }

    /**
     * all resource get
    * @return Collection
    */
    public function getFull($company_id = NULL){
        if (!empty($company_id)) {
            return $this->model::where(['company_id'=> $company_id, 'is_transfer' => 0])
            ->with('company', 'vendor')
            ->latest()->get(); 
        }else{
            return $this->model::with('company', 'vendor')->latest()->get();
        }
    }

    /**
     * all resource get
    * @return Collection
    */
    //  Sub-form for local Supply (for note 3,4,5,7,10,12,14,18,19,20 and 21)  							
    public function salesSubForm($request){
        return $this->mushok->salesSubForm($request, auth()->user()->company_id);
        $company = NULL;
        
        if (auth()->user()->company_id != NULL) {
            $company = Company::where('id', auth()->user()->company_id)->first();
        }
        $mushok_no = 'six_two';
        if ($company->business_type == 2) {
            $mushok_no = 'six_two_one';
        }
        DB::enableQueryLog(); // Enable query log
        $query = $this->mushok::query();
        $query->groupBy('vat_rate');
        
        if (!empty(auth()->user()->company_id)) {
            $query->where('company_id', auth()->user()->company_id);
        }        
        
        // $query->with('finished', 'sales.company', 'sales.branch', 'info.category', 'info.hscode', 'sales.customer');
        if ($request->start_date !="" && $request->end_date !="") {
            $query->whereBetween('created_at', [date('Y-m-d',  strtotime($request->start_date)), date('Y-m-d', strtotime($request->end_date))]);
        }          
        $query->where(['mushok'=> $mushok_no, 'type' => 'debit', 'is_transfer' => 0]);
        $query->where('vat_rate', $request->percentage);
        $query->get();
        return DB::getQueryLog(); // Show results of log
        return $query->count();
    }    

    /**
     * all resource get
    * @return Collection
    */
    //  Sub-form for local Purchase (for note 10, 12, 14, 16, and 21)
    public function purchaseSubForm($request){
        $company = NULL;
        if (auth()->user()->company_id != NULL) {
            $company = Company::where('id', auth()->user()->company_id)->first();
        }
        $mushok_no = 'six_one';
        if ($company->business_type == 2) {
            $mushok_no = 'six_two_one';
        }
        
        $query = $this->mushok::query();
        $query->with('purchase.vendor', 'purchase.company', 'info.category', 'info.hscode');
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
        }          
        // if ($request->type == 'import') {
        //     $query->where('nature', 'Imported');
        // }

        // if ($request->type == 'local') {
        //     $query->where('nature', 'Local');
        // }
        if ($request->note !="") {
            if ($request->note == 21) {
                $query->where(['mushok'=> $mushok_no, 'type' => 'credit', 'nature' => "Local", 'is_transfer' => 0]);
            }elseif ($request->note == 15) {
                $query->where(['mushok'=> $mushok_no, 'type' => 'credit', 'nature' => "Imported", 'vat_rate' => 15,  'is_transfer' => 0]);
            }elseif ($request->note == 11) {
                $query->where(['mushok'=> $mushok_no, 'type' => 'credit', 'nature' => "Imported", 'vat_rate' => 0,  'is_transfer' => 0]);
            }
        }
        $query->where('company_id', auth()->user()->company_id);
        $data['company'] = $company;
        $data['purchase'] = $query->get();
        return $data;
    }

    /**
     * all resource get
    * @return Collection
    */
    //  Sub-form for local Purchase (for note 10, 12, 14, 16, and 21)
    public function report($request){
       
        $query = $this->mushok::query();
        $query->with('purchase.vendor', 'purchase.company', 'info', 'info.hscode', 'sales.customer', 'finished', 'opening', 'company', 'branch');
        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->company_id) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
        }  
        // $query->take(100);
        // $query->orderBy('id', 'desc');
        return $query->lazy();
    }


    /**
     * all resource get
    * @return Collection
    */
    public function getLatest($company_id = NULL){
        return $this->model::take(20)->get();
    }
     
     /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function getById($id, $company_id = NULL){
          return $this->model::with('company', 'vendor', 'purchaseItems.info')
          ->where('id', $id)->first();
     }  

    public function getReturnById($id)
    {
        return $this->return::with('purchase.company', 'purchase.vendor', 'purchase.purchaseItems', 'returnItems.info')
        ->where('id', $id)->first();
    }

     /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function getShowById(int $id, $company_id = NULL){
        return $this->model::with('company', 'vendor')
        ->where('id', $id)->first();
    }

    public function create($request)
    {
        return false;
    }

    public function update( int $id,  $request)
    {
        return false;
    }

    public function delete($id)
    {
        return false;
    }

    public function upload2($request) {
        $user = auth()->user(); 
        $filename = $request->file('csvfile');
        if($_FILES["csvfile"]["size"] > 0)
        {
            // DB::beginTransaction();
            $file = fopen($filename, "r");
            $i = 0;   
            $previousId =0;
            $previousData = [];
            $data = [];
            $allMushok = [];
                while (($item_info = fgetcsv($file, 10000, ",")) !== FALSE)
                {
                    $i++;
                    if ($i>1) {
                        $dataDate = date('Y-m-d H:i:s', strtotime($item_info[6].' '.$item_info[7]));
                        // $allMushok[] = $mushok;
                        $openingQty = 0;
                        
                        $lastMushok = new $this->mushok;
                        $lastData = $lastMushok->where(['product_id' => $item_info[1]])
                        ->where('created_at', '<', $dataDate)
                        ->orderBy('created_at','desc')
                        ->first();


                        if (!empty($lastData)) {
                            $data['lastData'] =  $lastData;
                            $data['currentData'] =  $item_info;
                            $data['index'] =  $i;
                            return $data;
                            $openingQty = $lastData->closing_qty;
                        }
                            
                        $mushok = new $this->mushok;
                        $mushok = $mushok::find($item_info[0]);
                        $closingQty = $item_info[2] == 'credit'? ($openingQty+$item_info[3]): ($openingQty-$item_info[5]);
                                                                        
                        $product_id = $item_info[1];
                        // return $openingQty;
                        // $mushok->qty = $item_info[5];
                        $mushok->opening_qty = $openingQty;
                        $mushok->closing_qty = $closingQty;
                        $mushok->branch_opening = $mushok->opening_qty;
                        $mushok->branch_closing = $mushok->closing_qty;
                        // return $mushok;
                        $mushok->update();
                       
                        $item['product_id'] = $item_info[1];
                        $item['qty'] =  $mushok->qty;
                        $item['opening_qty'] =  $mushok->opening_qty;
                        $item['closing_qty'] =  $closingQty;
                        $item['branch_opening'] =  $mushok->opening_qty;
                        $item['branch_closing'] =  $mushok->closing_qty;
                    }

                }
                return $data;
            
        }
    }

    public function upload($request) {
        $filename = $request->file('csvfile');
        if($_FILES["csvfile"]["size"] > 0)
        {
            // DB::beginTransaction();
            $file = fopen($filename, "r");
            $i = 0;   
            $previousId =0;
            $previousData = [];
            $data = [];
            try {
                $openingQty = 0;
                while (($item_info = fgetcsv($file, 10000, ",")) !== FALSE)
                {
                    $i++;
                    if ($i>1) {
                        
                        if (empty($previousData)) {
                            $openingQty = 0;
                            $previousData = [];
                        }else{
                            $openingQty = ($previousData['closing_qty']);
                        }
                        $mushok = $this->mushok::find($item_info[0]);
                        $closingQty = $item_info[1] == 'credit'? ($openingQty+$item_info[2]): ($openingQty-$item_info[2]);
                        if ($mushok->nature == "OpeningStock" && $mushok->qty == 0) {
                            $mushok->qty = $item_info[2];
                            $mushok->opening_qty = 0;
                        }else{
                            // $closingQty = $item_info[1] == 'credit'? ($openingQty+$item_info[2]): ($openingQty-$item_info[2]);
                            $mushok->opening_qty = $openingQty;
                        }
                        
                        // if ($item_info[4] == 'Transfer') {
                        //     $closingQty = $openingQty;
                        // }
                        
                        // $mushok->opening_qty = $openingQty;
                        $mushok->closing_qty = $closingQty;
                        $mushok->branch_opening = $openingQty;
                        $mushok->branch_closing = $mushok->closing_qty;
                        // return $mushok;
                        $mushok->update();
                        $item['closing_qty'] =  $mushok->closing_qty;
                        $previousData = $item;
                        $data[] = $mushok;
                        
                    }

                }
                return $data;
            } catch (\Throwable $th) {
                //throw $th;
                return $th->getMessage();
            }
            
        }
    }

    public function sixTen($request, $company_id) {

        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }

        // Sales
        $sales = $this->sales::with('salesItems', 'customer')
        ->whereHas('salesItems', function($query){
            $query->select(DB::raw("SUM(total_price+vat_amount) as orderTotal"));
            $query->having('orderTotal', '>', 200000);
            $query->groupBy('sales_id');
        })
        ->whereBetween('created_at', [$data['start_date'], $data['end_date']])
        ->where('company_id', $company_id)
        ->get();

        $salesData = [];
        $slData = [];   
              
        foreach ($sales as $key => $sale) {
            $slData['id'] = $sale->id;
            $slData['sales_no'] = $sale->sales_no;
            $slData['issue_date'] = date('d-M-Y', strtotime($sale->created_at));
            $slData['total_amount'] = 0;
            foreach ($sale->salesItems as $key => $salesItem) {
                $slData['total_amount'] += ($salesItem->total_price+$salesItem->vat_amount);
            }
            $slData['total_amount'] = number_format(round($slData['total_amount']));
            $slData['customer_name'] = $sale->customer_name;
            $slData['customer_address'] = $sale->customer_address;
            $slData['customer_bin'] = !empty($sale->customer)? $sale->customer->bin: "";
            $slData['sale'] = $sale;
            $salesData[] = $slData;
            
        }

        $data['sales'] = $salesData;

        // Purchase

        $purchases = $this->model::with('purchaseItems', 'vendor')
        ->whereHas('purchaseItems', function($query){
            $query->select(DB::raw("SUM(total_price+vat_amount+cd+rd+sd) as purchaseTotal"));
            $query->having('purchaseTotal', '>', 200000);
            $query->groupBy('purchase_id');
        })
        ->whereBetween('created_at', [$data['start_date'], $data['end_date']])
        ->where('company_id', $company_id)
        ->get();
        
        
        $purchaseData = [];
        $purData = [];       
        foreach ($purchases as $key => $purchase) {
            // 
            $purData['id'] = $purchase->id;
            $purData['challan_no'] = $purchase->challan_no;
            $purData['issue_date'] = date('d-M-Y', strtotime($purchase->created_at));
            $purData['total_amount'] = 0;
            foreach ($purchase->purchaseItems as $key => $purchase_item) {
                $purData['total_amount'] += ($purchase_item->total_price+$purchase_item->vat_amount+$purchase_item->cd+$purchase_item->rd+$purchase_item->sd);
            }
            $purData['total_amount'] = number_format(round($purData['total_amount']));
            $purData['vendor_name'] = $purchase->vendor->name;
            $purData['vendor_address'] = $purchase->vendor->contact_address;
            $purData['vendor_bin'] = $purchase->vendor->vendor_bin;
            $purData['purchase'] = $purchase;
            $purchaseData[] = $purData;
        }
        $data['purchase'] = $purchaseData;
        return $data;
    }

    function correction($request, $company_id) {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        $productInfo = $this->product::where(['sku'=> $request->sku, 'company_id' => $company_id])->first();
        
        return Helper::updateMushokCompany($productInfo, $data);
    }

    function sixTwoOneSummery($request, $company_id) {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
          
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }

        $query = $this->stock::query()
            ->join("products", "stock.product_id","=","products.id")   
            ->join("mushok_sixes", "products.id","=","mushok_sixes.product_id")
            ->join("sales_items", "sales_items.product_id","=","products.id")
            ->join("sales_items", "sales_items.product_id","=","products.id")
            ->select('products.id','products.title','products.sku', 'products.price', 'mushok_sixes.opening_qty', 'mushok_sixes.closing_qty', 'mushok_sixes.branch_opening', 'mushok_sixes.branch_closing')
        //   ->whereRaw('products.price > IF(state = "TX", ?, 100)', [200])
        //   ->selectRaw('select sum(mushok_sixes.qty) as totalSalesQty where mushok_sixes.sales_id != NULL')
          ->selectRaw('sum(mushok_sixes.qty) as allQty');
        $query->whereBetween('mushok_sixes.created_at', [$data['start_date'], $data['end_date']]);
        $query->groupBy('products.id');
        $query->orderBy('qty', 'desc');
        return $query->get();
    }

    public function guard()
    {
        return Auth::guard('api');
    }    
}
