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
        try {
            $bookDetails = BookDetail::with('book')->get();
            return response()->json([
                'success' => true,
                'data' => $bookDetails
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'BookID' => 'required|exists:tbBook,BookID',
            'ISBN10' => 'nullable|string|max:10',
            'ISBN13' => 'nullable|string|max:17',
            'Publisher' => 'nullable|string|max:255',
            'PublishYear' => 'nullable|integer|min:1800|max:' . (date('Y') + 1),
            'Edition' => 'nullable|string|max:50',
            'PageCount' => 'nullable|integer|min:1',
            'Language' => 'nullable|string|max:50',
            'Format' => 'nullable|in:Hardcover,Paperback,Ebook,Audiobook',
            'Dimensions' => 'nullable|string|max:100',
            'Weight' => 'nullable|numeric|min:0',
            'Description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if a detail record already exists for this book
            $existingDetail = BookDetail::where('BookID', $request->BookID)->first();
            
            if ($existingDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book detail already exists for this book. Please use update instead.'
                ], 422);
            }
            
            $bookDetail = BookDetail::create($request->all());
            
            return response()->json([
                'success' => true,
                'data' => $bookDetail->load('book'),
                'message' => 'Book detail created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'BookID' => 'sometimes|required|exists:tbBook,BookID',
            'ISBN10' => 'nullable|string|max:10',
            'ISBN13' => 'nullable|string|max:17',
            'Publisher' => 'nullable|string|max:255',
            'PublishYear' => 'nullable|integer|min:1800|max:' . (date('Y') + 1),
            'Edition' => 'nullable|string|max:50',
            'PageCount' => 'nullable|integer|min:1',
            'Language' => 'nullable|string|max:50',
            'Format' => 'nullable|in:Hardcover,Paperback,Ebook,Audiobook',
            'Dimensions' => 'nullable|string|max:100',
            'Weight' => 'nullable|numeric|min:0',
            'Description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $bookDetail = BookDetail::find($id);
            
            if (!$bookDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book detail not found'
                ], 404);
            }
            
            $bookDetail->update($request->all());
            
            return response()->json([
                'success' => true,
                'data' => $bookDetail->fresh()->load('book'),
                'message' => 'Book detail updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getByBookId($bookId)
    {
        try {
            $bookDetail = BookDetail::where('BookID', $bookId)->first();
            
            if (!$bookDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book detail not found for this book'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $bookDetail
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}