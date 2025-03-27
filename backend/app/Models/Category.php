<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    
    protected $table = 'tbCategory';
    protected $primaryKey = 'CategoryID';
    public $timestamps = false;
    
    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = null;
    
    protected $fillable = [
        'Name',
        'Description',
        'Image'
    ];
    
    public function books()
    {
        return $this->hasMany(Book::class, 'CategoryID', 'CategoryID');
    }
}