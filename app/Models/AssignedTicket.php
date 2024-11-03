<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignedTicket extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function ticket() {
        return $this->belongsTo(Ticket::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(Admin::class, 'assigned_to_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(Admin::class, 'assigned_by_id');
    }

}