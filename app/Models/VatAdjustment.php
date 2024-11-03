<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VatAdjustment extends Model
{
    use HasFactory, LogsActivity;

    // protected static $recordEvents = ['deleted', 'updated'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll()
        ->logOnlyDirty();
    }
    protected $guarded = ['id'];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'created_by', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }

    public function sales()
    {
        return $this->belongsTo(Sales::class, 'sales_id', 'id');
    }

    public function challans(){
        return $this->hasMany(VatAdjustmentChallan::class, 'vat_adjustment_id', 'id');
    }

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function vendor() {
        return $this->belongsTo(Vendor::class);
    }

    public function adjustmentTotal($request = NULL, $note_no = NULL, $company_id, $type = "increasing")
    {
        
        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d', strtotime($request->end_date));
        }

        $query = $this->query();
        if (!empty($note_no)) {
            $query->where('note_no', $note_no);
        }
        
        $query->where('type', $type);
        
        $query->where('company_id', $company_id);
        
        $query->whereBetween('ledger_month', [$data['start_date'], $data['end_date']]);
        
        $increasings = $query->withSum('challans', 'amount')->get();
        // return $increasings;
        $totalPrice = 0;
        foreach ($increasings as $key => $increasing) {
            $totalPrice += $increasing->challans_sum_amount;
        }
        return $totalPrice;
    }
    
    public function decreasingTotal($request = NULL, $note_no = NULL)
    {

        $data['start_date'] = date('Y-m-01 00:00:00');
        $data['end_date'] = date('Y-m-d 23:59:59');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d', strtotime($request->end_date));
        }
        

        $query = $this->query();
        if (!empty($note_no)) {
            $query->where('note_no', $note_no);
        }
        
        $query->where('type', 'decreasing');
        
        $query->where('company_id', auth()->user()->company_id);
        
        
        
        $query->whereBetween('ledger_month', [date('Y-m-d',  strtotime($request->start_date)), date('Y-m-d', strtotime($request->end_date))]);
        
        
        $increasings = $query->withSum('challans', 'amount')->get();
        
        $totalPrice = 0;
        foreach ($increasings as $key => $increasing) {
            $totalPrice += $increasing->challans_sum_amount;
        }
        return $totalPrice;
    }
}
