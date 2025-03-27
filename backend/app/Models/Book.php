<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;
    
    // Set the actual table name
    protected $table = 'tbBook';
    
    // Set the primary key
    protected $primaryKey = 'BookID';
    
    // Disable Laravel's default timestamps
    public $timestamps = false;
    
    // Custom timestamp column
    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = null; // No UpdatedAt field
    
    // Map your database columns
    protected $fillable = [
        'CategoryID',
        'Title',
        'Author',
        'Price',
        'StockQuantity',
        'Image'
    ];
    
    // Define relationships
    public function category()
    {
        return $this->belongsTo(Category::class, 'CategoryID', 'CategoryID');
    }
    
    public function bookDetail()
    {
        return $this->hasOne(BookDetail::class, 'BookID', 'BookID');
    }
    
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'BookID', 'BookID');
    }
    
    public function reviews()
    {
        return $this->hasMany(Review::class, 'BookID', 'BookID');
    }
    
    // Scopes for filtering
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('CategoryID', $categoryId);
    }
    
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('Title', 'like', "%{$search}%")
              ->orWhere('Author', 'like', "%{$search}%");
        });
    }
}