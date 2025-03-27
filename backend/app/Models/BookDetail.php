<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookDetail extends Model
{
    protected $fillable = [
        'book_id',
        'isbn10',
        'isbn13',
        'publisher',
        'publish_year',
        'edition',
        'page_count',
        'language',
        'format',
        'dimensions',
        'weight',
        'description'
    ];
}
