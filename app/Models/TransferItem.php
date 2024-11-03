<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransferItem extends Model
{
    use HasFactory, LogsActivity;

    // protected static $recordEvents = ['deleted', 'updated'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll()
        ->logOnlyDirty();
    }

    public function itemInfo()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id')->select('id', 'sku', 'title', 'slug' , 'vds_percentage', 'vat_rebatable_percentage', 'hs_code_id', 'hs_code', 'status');
    }
}
