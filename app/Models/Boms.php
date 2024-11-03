<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Boms extends Model
{
    use HasFactory, LogsActivity;

    // protected static $recordEvents = ['deleted', 'updated'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll()
        ->logOnlyDirty();
    }


    public function rawMaterials()
    {
        return $this->hasMany(BomItem::class, 'bom_id', 'id')->select(['id', 'bom_id', 'product_id', 'item_info', 'unit', 'actual_qty', 'qty_with_wastage', 'price', 'status']);
    }

    public function services()
    {
        return $this->hasMany(BomService::class, 'bom_id', 'id')->select(['id', 'bom_id', 'product_id', 'amount', 'amount as price']);
    }

    public function finishGoods()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function bomValueAdditions()
    {
        return $this->hasMany(BomValueAddition::class, 'bom_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
}
