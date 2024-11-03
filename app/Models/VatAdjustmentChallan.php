<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VatAdjustmentChallan extends Model
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

    public function purchase(){
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');    
    }

    public function sales(){
        return $this->belongsTo(Sales::class, 'sales_id', 'id');    
    }

    public function adjustment(){
        return $this->belongsTo(VatAdjustment::class, 'vat_adjustment_id', 'id');    
    }

}
