<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookDetail extends Model
{
    use HasFactory;
    
    // Specify the correct table name from your database
    protected $table = 'tbBookDetail';
    
    // Set the primary key
    protected $primaryKey = 'DetailID';
    
    // Disable timestamps if not used in your table
    public $timestamps = false;
    
    protected $fillable = [
        'BookID',
        'ISBN10',
        'ISBN13',
        'Publisher',
        'PublishYear',
        'Edition',
        'PageCount',
        'Language',
        'Format',
        'Dimensions',
        'Weight',
        'Description'
    ];
    
    public function book()
    {
        return $this->belongsTo(Book::class, 'BookID', 'BookID');
    }
}