<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\BookController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;

// Public routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Books public endpoints
Route::get('books', [BookController::class, 'index']);
Route::get('books/{id}', [BookController::class, 'show']);
Route::get('categories', [CategoryController::class, 'index']);

Route::get('/test', function() {
    return response()->json([
        'message' => 'API routes are working!',
        'status' => 200
    ]);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Books protected endpoints
    Route::post('books', [BookController::class, 'store']);
    Route::put('books/{id}', [BookController::class, 'update']);
    Route::delete('books/{id}', [BookController::class, 'destroy']);
    
});