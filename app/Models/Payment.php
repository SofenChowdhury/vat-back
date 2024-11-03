<?php

namespace App\Models;

use App\Models\Shop;
use App\Models\Company;
use App\Models\OrderPayment;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
   /**
    * The attributes that are mass assignable.
    *
    * @var string[]
    */
    protected $guarded = ['id'];

   public function orderPayments()
   {
      return $this->hasMany(OrderPayment::class);
   }

   public function user()
   {
      return $this->belongsTo(User::class,'user_id', 'id');
   }

   public function company()
   {
      return $this->belongsTo(Company::class,'company_id', 'id');
   }

   public function shop()
   {
      return $this->belongsTo(Shop::class,'shop_id', 'id');
   }

   public function verified()
   {
      return $this->hasOne(Admin::class,'id', 'approval_denied_by');
   }
}
