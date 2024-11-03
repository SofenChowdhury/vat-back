<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HsCode extends Model
{
    use HasFactory;

    public function products()
    {
        return $this->hasMany(Product::class, 'id', 'hs_code_id');
    }
}
