<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Repositories\FinishedGoodsRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FinishedGoods extends Model
{
    use HasFactory, LogsActivity;

    // protected static $recordEvents = ['deleted', 'updated'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll()
        ->logOnlyDirty();
    }

    public function item()
    {
        return $this->belongsTo(Product::class, 'product_id','id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function materials()
    {
        return $this->hasMany(FinishedGoodsRawMaterial::class);
    }

    public function sixOne()
    {
        return $this->hasMany(MushokSix::class, 'finished_id', 'id');
    }
}
