<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with('user', 'book')->get();
        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user already reviewed this book
        $existingReview = Review::where([
            'user_id' => $request->user_id,
            'book_id' => $request->book_id
        ])->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this book'
            ], 422);
        }

        $review = Review::create($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $review,
            'message' => 'Review added successfully'
        ], 201);
    }

    public function show($id)
    {
        $review = Review::with('user', 'book')->find($id);
        
        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $review
        ]);
    }

    public function update(Request $request, $id)
    {
        $review = Review::find($id);
        
        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $review->update($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $review,
            'message' => 'Review updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $review = Review::find($id);
        
        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }
        
        $review->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);
    }

    // Get book reviews
    public function getBookReviews($bookId)
    {
        $reviews = Review::with('user')
            ->where('book_id', $bookId)
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }
}