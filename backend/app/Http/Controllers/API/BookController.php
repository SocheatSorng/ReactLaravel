<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\Book;
use Illuminate\Http\Request;
use App\Http\Resources\BookResource;
use Illuminate\Support\Facades\Validator;

class BookController extends BaseController
{
    public function index()
    {
        $books = Book::all();
        return $this->sendResponse(BookResource::collection($books), 'Books retrieved successfully.');
    }
    
    public function show($id)
    {
        $book = Book::find($id);
  
        if (is_null($book)) {
            return $this->sendError('Book not found.');
        }
   
        return $this->sendResponse(new BookResource($book), 'Book retrieved successfully.');
    }
    
}