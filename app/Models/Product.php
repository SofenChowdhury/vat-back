<?php

namespace App\Models;

use BinaryCats\Sku\HasSku;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
   use HasSku;
   use HasFactory; 
//    LogsActivity;

    // protected static $recordEvents = ['deleted', 'updated'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll()
        ->logOnlyDirty();
    }
   /**
    * The attributes that are mass assignable.
    *
    * @var string[]
    */
    
    protected $guarded=[];
    

     public function category()
     {
          return $this->belongsTo(Category::class);
     }
     public function unit()
     {
          return $this->belongsTo(MeasurementUnit::class, 'unit_id', 'id');
     }

     public function hscode()
     {
          return $this->belongsTo(HsCode::class, 'hs_code_id','id');
     }

     public function stocks()
     {
          return $this->hasMany(ItemStock::class);
     }

     public function brand()
     {
          return $this->belongsTo(Brands::class);
     }

     public function salesItems()
     {
          return $this->hasMany(SalesItem::class);
     }

     function openStockItems() {
          return $this->hasMany(OpenStockItem::class);
     }

     public function mushokSixes()
     {
         return $this->hasMany(MushokSix::class, 'product_id');
     }
}