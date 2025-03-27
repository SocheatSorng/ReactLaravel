<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with('book')->get();
        return response()->json([
            'success' => true,
            'data' => $purchases
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric',
            'payment_method' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            $purchase = Purchase::create($request->all());
            
            // Update book stock quantity
            $book = Book::find($request->book_id);
            $book->stock_quantity += $request->quantity;
            $book->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'data' => $purchase,
                'message' => 'Purchase recorded successfully'
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $purchase = Purchase::with('book')->find($id);
        
        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $purchase
        ]);
    }

    public function update(Request $request, $id)
    {
        $purchase = Purchase::find($id);
        
        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'book_id' => 'sometimes|required|exists:books,id',
            'quantity' => 'sometimes|required|integer|min:1',
            'unit_price' => 'sometimes|required|numeric',
            'payment_method' => 'sometimes|required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            // If book ID or quantity changed, update stock quantities
            if (
                ($request->has('book_id') && $request->book_id != $purchase->book_id) ||
                ($request->has('quantity') && $request->quantity != $purchase->quantity)
            ) {
                // Revert old stock quantity
                $oldBook = Book::find($purchase->book_id);
                $oldBook->stock_quantity -= $purchase->quantity;
                $oldBook->save();
                
                // If book ID changed, update new book stock
                if ($request->has('book_id') && $request->book_id != $purchase->book_id) {
                    $newBook = Book::find($request->book_id);
                    $newBook->stock_quantity += $request->quantity;
                    $newBook->save();
                } else {
                    // Just update the quantity for the same book
                    $oldBook->stock_quantity += $request->quantity;
                    $oldBook->save();
                }
            }
            
            $purchase->update($request->all());
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'data' => $purchase,
                'message' => 'Purchase updated successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $purchase = Purchase::find($id);
        
        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase not found'
            ], 404);
        }
        
        DB::beginTransaction();
        
        try {
            // Revert book stock quantity
            $book = Book::find($purchase->book_id);
            $book->stock_quantity -= $purchase->quantity;
            $book->save();
            
            $purchase->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Purchase deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete purchase: ' . $e->getMessage()
            ], 500);
        }
    }
}