<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BookDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookDetailController extends Controller
{
    public function index()
    {
        $bookDetails = BookDetail::with('book')->get();
        return response()->json([
            'success' => true,
            'data' => $bookDetails
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|exists:books,id|unique:book_details',
            'isbn10' => 'nullable|string|max:10',
            'isbn13' => 'nullable|string|max:17',
            'publisher' => 'nullable|string|max:255',
            'publish_year' => 'nullable|integer',
            'edition' => 'nullable|string|max:50',
            'page_count' => 'nullable|integer',
            'language' => 'nullable|string|max:50',
            'format' => 'nullable|in:Hardcover,Paperback,Ebook,Audiobook',
            'dimensions' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $bookDetail = BookDetail::create($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $bookDetail,
            'message' => 'Book detail created successfully'
        ], 201);
    }

    public function show($id)
    {
        $bookDetail = BookDetail::with('book')->find($id);
        
        if (!$bookDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Book detail not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $bookDetail
        ]);
    }

    public function update(Request $request, $id)
    {
        $bookDetail = BookDetail::find($id);
        
        if (!$bookDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Book detail not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'book_id' => 'sometimes|required|exists:books,id|unique:book_details,book_id,'.$bookDetail->id,
            'isbn10' => 'nullable|string|max:10',
            'isbn13' => 'nullable|string|max:17',
            'publisher' => 'nullable|string|max:255',
            'publish_year' => 'nullable|integer',
            'edition' => 'nullable|string|max:50',
            'page_count' => 'nullable|integer',
            'language' => 'nullable|string|max:50',
            'format' => 'nullable|in:Hardcover,Paperback,Ebook,Audiobook',
            'dimensions' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $bookDetail->update($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $bookDetail,
            'message' => 'Book detail updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $bookDetail = BookDetail::find($id);
        
        if (!$bookDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Book detail not found'
            ], 404);
        }
        
        $bookDetail->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Book detail deleted successfully'
        ]);
    }
}