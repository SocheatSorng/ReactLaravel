<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'book_id',
        'quantity',
        'unit_price',
        'payment_method'
    ];
}
