<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseReturnItem extends Model
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

    public function info()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    function purchaseReturn() {
          return $this->belongsTo(PurchaseReturn::class);
    }
    
     // Mushok 9.1 Note 26
     public function debitNoteTotal($request)
     { 
          $query = $this->query();
          //  $query->with('finished', 'sales.company', 'sales.branch', 'info.category', 'sales.customer');
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
               $query->whereBetween('created_at', [date('Y-m-d 00:00:00',  strtotime($request->start_date)), date('Y-m-d 23:59:59', strtotime($request->end_date))]);
          }
          return $query->sum('vat_amount');
     }
}
