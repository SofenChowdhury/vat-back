<?php

namespace App\Models;

use App\Models\Product;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesItem extends Model
{
    use HasFactory;
    use LogsActivity;

    // protected static $recordEvents = ['deleted', 'updated'];

    protected $fillable = [ 'title'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll()
        ->logOnlyDirty()
        ->setDescriptionForEvent(fn(string $eventName) => "{$eventName}");
    }
    protected $casts = [
        'attribute' => 'array'
    ];

    public function itemInfo()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id')->select('id', 'type', 'category_id', 'brand_id', 'sku', 'title', 'model', 'slug', 'unit_type', 'vds_percentage', 'vat_rebatable_percentage', 'hs_code_id', 'hs_code', 'status');
    }

    public function sale() {
        return $this->belongsTo(Sales::class);
    }

    public function sales() {
        return $this->belongsTo(Sales::class);
    }
}
