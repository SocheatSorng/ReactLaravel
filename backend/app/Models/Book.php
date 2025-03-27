<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    
    protected $table = 'tbBook';
    protected $primaryKey = 'BookID';
    public $timestamps = false;
    
    protected $fillable = [
        'CategoryID', 
        'Title', 
        'Author', 
        'Price', 
        'StockQuantity', 
        'Image'
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class, 'CategoryID');
    }
    
    public function details()
    {
        return $this->hasOne(BookDetail::class, 'BookID');
    }
}