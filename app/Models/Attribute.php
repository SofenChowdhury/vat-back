<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attribute extends Model
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
   protected $fillable = [ 'name'];



    public function product()
    {
       return $this->belongsTo(Product::class);
    }
     //
    public function attributeOptions()
    {
        return $this->hasMany(AttributeOption::class);
    }
}
