<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory, LogsActivity;

    // protected static $recordEvents = ['deleted', 'updated'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll()
        ->logOnlyDirty();
    }
    protected $guarded=[];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function sales()
    {
        return $this->hasMany(Sales::class, 'company_id');
    }

    // public function salesItem()
    // {
    //     return $this->hasManyThrough(SalesItem::class, Type::class);
    // }
}
