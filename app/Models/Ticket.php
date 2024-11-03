<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = ['id'];

    public function assigned()
    {
        return $this->belongsTo(AssignedTicket::class, 'id', 'ticket_id');
    }
    
    public function comment()
    {
        return $this->hasMany(Comment::class);
    }

    public function submittedByUser()
    {
        return $this->belongsTo(Admin::class, 'submitted_by');
    }


    protected static function booted()
    {
        static::deleting(function (Ticket $ticket) {
            $ticket->assigned()->delete();
            $ticket->comment()->delete();
        });
    }
}