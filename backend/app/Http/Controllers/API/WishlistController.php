<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = Wishlist::with('user', 'book')->get();
        return response()->json([
            'success' => true,
            'data' => $wishlists
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if the book is already in the wishlist
        $exists = Wishlist::where([
            'user_id' => $request->user_id,
            'book_id' => $request->book_id
        ])->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Book is already in wishlist'
            ], 422);
        }

        $wishlist = Wishlist::create($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $wishlist,
            'message' => 'Book added to wishlist successfully'
        ], 201);
    }

    public function show($id)
    {
        $wishlist = Wishlist::with('user', 'book')->find($id);
        
        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Wishlist item not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $wishlist
        ]);
    }

    public function destroy($id)
    {
        $wishlist = Wishlist::find($id);
        
        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Wishlist item not found'
            ], 404);
        }
        
        $wishlist->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Item removed from wishlist successfully'
        ]);
    }

    // Get user's wishlist
    public function getUserWishlist($userId)
    {
        $wishlistItems = Wishlist::with('book')
            ->where('user_id', $userId)
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $wishlistItems
        ]);
    }

    // Check if a book is in user's wishlist
    public function checkInWishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $exists = Wishlist::where([
            'user_id' => $request->user_id,
            'book_id' => $request->book_id
        ])->exists();

        return response()->json([
            'success' => true,
            'inWishlist' => $exists
        ]);
    }
}