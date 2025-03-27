<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('category')->get();
        return response()->json([
            'success' => true,
            'data' => $books
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:100',
            'price' => 'required|numeric',
            'stock_quantity' => 'nullable|integer',
            'image' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $book = Book::create($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $book,
            'message' => 'Book created successfully'
        ], 201);
    }

    public function show($id)
    {
        $book = Book::with('category', 'bookDetail')->find($id);
        
        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $book
        ]);
    }

    public function update(Request $request, $id)
    {
        $book = Book::find($id);
        
        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:100',
            'price' => 'sometimes|required|numeric',
            'stock_quantity' => 'nullable|integer',
            'image' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $book->update($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $book,
            'message' => 'Book updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $book = Book::find($id);
        
        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found'
            ], 404);
        }
        
        $book->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Book deleted successfully'
        ]);
    }
}