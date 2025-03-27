<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user', 'orderDetails.book')->get();
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric',
            'status' => 'nullable|in:pending,processing,shipped,delivered,cancelled',
            'shipping_address' => 'nullable|string',
            'payment_method' => 'nullable|string|max:50',
            'order_details' => 'required|array',
            'order_details.*.book_id' => 'required|exists:books,id',
            'order_details.*.quantity' => 'required|integer|min:1',
            'order_details.*.price' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            $order = Order::create([
                'user_id' => $request->user_id,
                'total_amount' => $request->total_amount,
                'status' => $request->status ?? 'pending',
                'shipping_address' => $request->shipping_address,
                'payment_method' => $request->payment_method
            ]);
            
            foreach ($request->order_details as $detail) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'book_id' => $detail['book_id'],
                    'quantity' => $detail['quantity'],
                    'price' => $detail['price']
                ]);
                
                // Update book stock quantity
                $book = \App\Models\Book::find($detail['book_id']);
                $book->stock_quantity -= $detail['quantity'];
                $book->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'data' => $order->load('orderDetails'),
                'message' => 'Order created successfully'
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $order = Order::with('user', 'orderDetails.book')->find($id);
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|in:pending,processing,shipped,delivered,cancelled',
            'shipping_address' => 'nullable|string',
            'payment_method' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $order->update($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $order = Order::find($id);
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        DB::beginTransaction();
        
        try {
            // Restore book stock quantities
            foreach ($order->orderDetails as $detail) {
                $book = \App\Models\Book::find($detail->book_id);
                $book->stock_quantity += $detail->quantity;
                $book->save();
            }
            
            // Delete order details first
            $order->orderDetails()->delete();
            
            // Then delete the order
            $order->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order: ' . $e->getMessage()
            ], 500);
        }
    }
}