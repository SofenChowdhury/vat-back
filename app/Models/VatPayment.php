<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VatPayment extends Model
{
    use HasFactory, LogsActivity;

    // protected static $recordEvents = ['deleted', 'updated'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll()
        ->logOnlyDirty();
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'created_by', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function vatPaid($request)
    {
        $data['start_date'] = date('Y-m-01');
        $data['end_date'] = date('Y-m-d');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d', strtotime($request->end_date));
        }

        $query = $this->query();
        $query->groupBy('type');
        if (!empty(auth()->user()->company_id)) {
            $query->where('company_id', auth()->user()->company_id);
        }

        $query->whereBetween('ledger_month', [$data['start_date'], $data['end_date']]);
        
        $query->selectRaw('sum(amount) as totalAmount, type');
        return $query->pluck('totalAmount', 'type');
    }
}
