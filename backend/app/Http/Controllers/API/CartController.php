<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index()
    {
        $carts = Cart::with('user', 'book')->get();
        return response()->json([
            'success' => true,
            'data' => $carts
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if the cart item already exists
        $existingCart = Cart::where([
            'user_id' => $request->user_id,
            'book_id' => $request->book_id
        ])->first();

        if ($existingCart) {
            // Update quantity of existing cart item
            $existingCart->quantity += $request->quantity;
            $existingCart->save();
            $cart = $existingCart;
        } else {
            // Create new cart item
            $cart = Cart::create($request->all());
        }
        
        return response()->json([
            'success' => true,
            'data' => $cart,
            'message' => 'Item added to cart successfully'
        ], 201);
    }

    public function show($id)
    {
        $cart = Cart::with('user', 'book')->find($id);
        
        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $cart
        ]);
    }

    public function update(Request $request, $id)
    {
        $cart = Cart::find($id);
        
        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cart->update($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $cart,
            'message' => 'Cart item updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $cart = Cart::find($id);
        
        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }
        
        $cart->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Cart item removed successfully'
        ]);
    }

    // Get user's cart items
    public function getUserCart($userId)
    {
        $cartItems = Cart::with('book')
            ->where('user_id', $userId)
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $cartItems
        ]);
    }

    // Clear user's cart
    public function clearCart($userId)
    {
        Cart::where('user_id', $userId)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }
}