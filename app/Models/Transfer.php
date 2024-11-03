<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transfer extends Model
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

    public function transferItems()
    {
        return $this->hasMany(TransferItem::class);
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'branch_from_id', 'id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'branch_to_id', 'id');
    }

    public function user() {
        return $this->belongsTo(Admin::class, 'created_by', 'id');
    }

}
