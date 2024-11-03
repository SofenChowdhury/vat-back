<?php

namespace App\Models;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\Company;
use App\Models\SalesItem;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Sales extends Model
{

   // use LogsActivity;
   
   protected $guarded = ['id'];
   // protected static $recordEvents = ['deleted', 'updated'];
   /**
    * The attributes that are mass assignable.
    *
    * @var string[]
    */   
   // public function getActivitylogOptions(): LogOptions
   // {
   //    return LogOptions::defaults()
   //    ->logAll()
   //    ->logOnlyDirty()
   //    ->setDescriptionForEvent(fn(string $eventName) => "{$eventName}");
   // }
   
   public function salesItems()
   {
      return $this->hasMany(SalesItem::class);
   }
   public function mushokItems()
   {
      return $this->hasMany(MushokSix::class, 'sales_id', 'id');
   }

   public function payments()
   {
      return $this->hasMany(Payment::class);
   }

   public function customer()
   {
      return $this->belongsTo(Customer::class, 'customer_id','id');
   }

   public function company()
   {
      return $this->belongsTo(Company::class,'company_id','id');
   }

   public function branch()
   {
      return $this->belongsTo(Branch::class,'branch_id','id');
   }


   // this is a recommended way to declare event handlers
   protected static function booted () {
      static::deleting(function(Sales $sales) {
         $sales->salesItems()->delete();
         $sales->mushokItems()->delete();
      });
   }
}
