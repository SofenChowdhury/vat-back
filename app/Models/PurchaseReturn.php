<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseReturn extends Model
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

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }
    public function returnItems()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function user()
    {
        return $this->belongsTo(Admin::class, 'created_by', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function vendor() {
            return $this->belongsTo(Vendor::class);
    }
}
