<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function ticket() {
        return $this->belongsTo(Ticket::class);
    }
    
    public function commentedBy()
    {
        return $this->belongsTo(Admin::class, 'commented_by_id');
    }
}