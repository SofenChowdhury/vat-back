<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesReturn extends Model
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

    public function items()
    {
        return $this->hasMany(MushokSix::class);
    }

    public function sales()
    {
        return $this->belongsTo(Sales::class, 'sales_id', 'id');
    }
    public function returnItems()
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function company() {
        return $this->belongsTo(Company::class);
    }
}
