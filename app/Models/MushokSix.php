<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MushokSix extends Model
{
    use HasFactory, LogsActivity;

    // protected static $recordEvents = ['deleted', 'updated'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll()
        ->logOnlyDirty();
    }

    public function info()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }

    public function opening()
    {
        return $this->belongsTo(OpenStock::class, 'open_stock_id', 'id');
    }

    public function sales()
    {
        return $this->belongsTo(Sales::class, 'sales_id', 'id');
    }

    public function transfer()
    {
        return $this->belongsTo(Transfer::class, 'transfer_id', 'id');
    }

    public function finished()
    {
        return $this->belongsTo(FinishedGoods::class, 'finished_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function salesSubForm($request = NULL, $company_id = NULL){
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        $company = NULL;
        if ($company_id != NULL) {
            $company = Company::where('id', $company_id)->first();
        }
        $mushok_no = 'six_two';
        if ($company->business_type == 2) {
            $mushok_no = 'six_two_one';
        }
        
        $query = $this->query();
        $query->with('finished', 'sales.company', 'sales.branch', 'info.category', 'info.hscode', 'sales.customer');
        // $query->groupBy('vat_rate');
        
        if (!empty(auth()->user()->company_id)) {
            $query->where('company_id', auth()->user()->company_id);
        }        
       
        $query->where(['mushok'=> $mushok_no, 'type' => 'debit']);
        // $query->where('vat_rate', '!=', '0.00');
        // $query->where('vat_rebetable_amount', '>', 0);
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
        }else{
            $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        }
        // $query->selectRaw('sum(vat_amount) as totalVat, vat_rate');
        $query->where('vat_rate', $request->percentage);
        return $query->get();
    }

   
    public function nineOnePurchaseLocal($request = NULL, $company_id = NULL)
    {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        $company = NULL;
        if ($company_id != NULL) {
            $company = Company::where('id', $company_id)->first();
        }
        $mushok_no = 'six_one';
        if ($company->business_type == 2) {
            $mushok_no = 'six_two_one';
        }
        $query = $this->query();
        $query->groupBy('vat_rate');
        if (!empty(auth()->user()->company_id)) {
            $query->where('company_id', auth()->user()->company_id);
        }
        $query->where(['mushok'=> $mushok_no, 'type' => 'credit', 'nature' => "Local"]);
        $query->where('vat_rate', '!=', '0.00');
        $query->where('vat_rebetable_amount', '!=', '0.00');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
        }else{
            $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        }
        // return $query->get();
        $query->selectRaw('sum(price*qty) as totalAmount, vat_rate');
        return $query->pluck('totalAmount', 'vat_rate');
    }

    public function nineOnePurchaseLocalVATAmount($request = NULL, $company_id = NULL)
    {
        $data['start_date'] = date('Y-m-01 H:i:s');
        $data['end_date'] = date('Y-m-d 23:59:59');
        $company = NULL;
        if ($company_id != NULL) {
            $company = Company::where('id', $company_id)->first();
        }
        $mushok_no = 'six_one';
        if ($company->business_type == 2) {
            $mushok_no = 'six_two_one';
        }
        $query = $this->query();
        $query->groupBy('vat_rate');
        if (!empty(auth()->user()->company_id)) {
            $query->where('company_id', auth()->user()->company_id);
        }
        $query->where(['mushok'=> $mushok_no, 'type' => 'credit', 'nature' => "Local"]);
        $query->where('vat_rate', '!=', '0.00');
        $query->where('vat_rebetable_amount', '!=', '0.00');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
        }else{
            $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        }
        
        $query->selectRaw('sum(vat_amount) as totalVAT, vat_rate');
        return $query->pluck('totalVAT', 'vat_rate');
    }

    public function nineOnePurchaseLocalNonrebateable($request = NULL, $company_id = NULL)
    {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        $company = NULL;
        if ($company_id != NULL) {
            $company = Company::where('id', $company_id)->first();
        }
        $mushok_no = 'six_one';
        if ($company->business_type == 2) {
            $mushok_no = 'six_two_one';
        }
        $query = $this->query();
        // $query->groupBy('vat_rate');
        if (!empty(auth()->user()->company_id)) {
            $query->where('company_id', auth()->user()->company_id);
        }
        $query->where(['mushok'=> $mushok_no, 'type' => 'credit', 'nature' => "Local"]);
        $query->where('vat_rate', '!=', '0.00');
        $query->where('vat_rebetable_amount', '=', '0.00');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
        }else{
            $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        }
        // return $query->get();
        $totalAmount = $query->selectRaw('sum(price*qty) as totalAmount')->first()->totalAmount;
        return $totalAmount;
        // return $query->pluck('totalAmount','vat_rate');
    }

    public function nineOnePurchaseImported($request = NULL, $company_id)
    {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        $company = NULL;
        if ($company_id != NULL) {
            $company = Company::where('id', $company_id)->first();
        }
        $mushok_no = 'six_one';
        if ($company->business_type == 2) {
            $mushok_no = 'six_two_one';
        }
        $query = $this->query();
        $query->groupBy('vat_rate');
        if (!empty(auth()->user()->company_id)) {
            $query->where('company_id', auth()->user()->company_id);
        }
        
        $query->where(['mushok'=> $mushok_no, 'type' => 'credit', 'nature' => "Imported"]);
        // $query->where('vat_rate', '!=', '0.00');
        // $query->where('vat_rebetable_amount', '!=', '0.00');
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
        }else{
            $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        }
        $query->selectRaw('sum(price*qty) as totalAmount, vat_rate, sum(vat_amount) as vat_amount');
        return $query->pluck('totalAmount', 'vat_rate', 'vat_amount');
    }

    public function nineOnePurchaseImportedVATAmount($request = NULL, $company_id)
    {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        $company = NULL;
        if ($company_id != NULL) {
            $company = Company::where('id', $company_id)->first();
        }
        $mushok_no = 'six_one';
        if ($company->business_type == 2) {
            $mushok_no = 'six_two_one';
        }
        $query = $this->query();
        $query->groupBy('vat_rate');
        if (!empty(auth()->user()->company_id)) {
            $query->where('company_id', auth()->user()->company_id);
        }
        
        $query->where(['mushok'=> $mushok_no, 'type' => 'credit', 'nature' => "Imported"]);
        
        $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        $query->selectRaw('sum(vat_amount) as totalVAT, vat_rate');
        return $query->pluck('totalVAT', 'vat_rate');
    }

    public function nineOnePurchaseImportedNonrebateable($request = NULL, $company_id)
    {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        $company = NULL;
        if ($company_id != NULL) {
            $company = Company::where('id', $company_id)->first();
        }
        $mushok_no = 'six_one';
        if ($company->business_type == 2) {
            $mushok_no = 'six_two_one';
        }
        $query = $this->query();
        // $query->groupBy('vat_rate');
        if (!empty(auth()->user()->company_id)) {
            $query->where('company_id', auth()->user()->company_id);
        }
        
        $query->where(['mushok'=> $mushok_no, 'type' => 'credit', 'nature' => "Imported"]);
        $query->where('vat_rate', '!=', '0.00');
        $query->where('vat_rebetable_amount', '>', 0);
        
        $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
       
        return $query->selectRaw('sum(price*qty) as totalAmount')->first()->totalAmount;
    }

    function advanceTaxTotal($request = NULL, $company_id) {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        $company = NULL;
        if ($company_id != NULL) {
            $company = Company::where('id', $company_id)->first();
        }
        $mushok_no = 'six_one';
        if ($company->business_type == 2) {
            $mushok_no = 'six_two_one';
        }
        $query = $this->query();
        // $query->groupBy('vat_rate');
        if (!empty($company_id)) {
            $query->where('company_id', $company_id); 
        }
        
        $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        
        return $query->sum('at_amount');
    }

    public function creditNoteTotal($request = NULL, $company_id)
    {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        $company = NULL;
        if ($company_id != NULL) {
            $company = Company::where('id', $company_id)->first();
        }
        $mushok_no = 'six_two';
        if ($company->business_type == 2) {
            $mushok_no = 'six_two_one';
        }
        $query = $this->query();
        
        $query->where('company_id', $company_id); 
        
        $query->where(['mushok'=> $mushok_no, 'type' => 'credit']);
        $query->whereNotNull('sales_return_id');
        
        
        $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        
        return $query->sum('vat_amount');
    }

    public function debitNoteTotal($request = NULL, $company_id)
    {
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }
        $company = NULL;
        if ($company_id != NULL) {
            $company = Company::where('id', $company_id)->first();
        }
        $mushok_no = 'six_one';
        if ($company->business_type == 2) {
            $mushok_no = 'six_two_one';
        }
        $query = $this->query();
        // $query->groupBy('vat_rate');
        if (!empty($company_id)) {
            $query->where('company_id', $company_id); 
        }
        
        $query->where(['mushok'=> $mushok_no, 'type' => 'debit']);
        $query->whereNotNull('purchase_return_id');
        
        $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
        
        return $query->sum('vat_amount');
    }

    // public function vdsIncreasing($request = NULL)
    // {
        // $data['start_date'] = date('Y-m-01 00:00:00');
        // $data['end_date'] = date('Y-m-d 23:59:59');
        
        // if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
        //     $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
        //     $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
        // }

    //     $query = $this->query();
    //     // $query->groupBy('vat_rate');
    //     if (!empty(auth()->user()->company_id)) {
    //         $query->where('company_id', auth()->user()->company_id);
    //     }
        
    //     $query->where(['mushok'=> 'six_one', 'type' => 'credit']);
        
    //     if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
    //         $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
    //     }else{
    //         $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
    //     }
    //     return $query->sum('vds_receive_amount');
    // }


    // Total Sales or Purchase Return and Nine one Return - 26 / Sales - 31
    public function totalReturned($request,$note_no = 26)
    {
        if ($note_no == 26) {
            $data['start_date'] = date('Y-m-01 00:00:00');
            $data['end_date'] = date('Y-m-d 23:59:59');
            
            if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
                $data['start_date'] = date('Y-m-d 00:00:00',  strtotime($request->start_date));
                $data['end_date'] = date('Y-m-d 23:59:59', strtotime($request->end_date));
            }
            $query = $this->query();
            // $query->groupBy('vat_rate');
            if (!empty(auth()->user()->company_id)) {
                $query->where('company_id', auth()->user()->company_id);
                
            }
            if ($note_no == 26) {
                $query->where(['mushok'=> 'six_one', 'type' => 'debit']);
                $query->whereNotNull('purchase_return_id');
            }elseif($note_no == 31){
                $query->where(['mushok'=> 'six_one', 'type' => 'debit']);
                $query->whereNotNull('sales_return_id');
            }
            
            $query->whereBetween('created_at', [date('Y-m-d H:i:s',  strtotime($data['start_date'])), date('Y-m-d 23:59:59', strtotime($data['end_date']))]);
            
            return $query->sum('vds_receive_amount');
        }
    }
}