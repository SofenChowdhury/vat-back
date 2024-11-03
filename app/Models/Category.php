<?php

namespace App\Models;

use App\Models\Product;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
   use HasFactory, LogsActivity;

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
   // protected $fillable = [ 'name'];



   public function products()
   {
      return $this->hasMany(Product::class ,'category_id','id');
   }

   public function salesItems()
   {
       return $this->hasManyThrough(SalesItem::class, Product::class);
   }
}
