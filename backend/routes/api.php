<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\BookController;
use App\Http\Controllers\API\BookDetailController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\OrderDetailController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\PurchaseController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Books and categories (public access)
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{id}', [BookController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/categories/{id}/books', [CategoryController::class, 'books']);

// Book reviews (public access)
Route::get('/books/{id}/reviews', [ReviewController::class, 'getBookReviews']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Users
    Route::apiResource('users', UserController::class);
    
    // Categories (admin only)
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    
    // Books (admin only)
    Route::post('/books', [BookController::class, 'store']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);
    
    // Book Details
    Route::apiResource('book-details', BookDetailController::class);
    
    // Orders
    Route::apiResource('orders', OrderController::class);
    Route::get('/user/orders', [OrderController::class, 'userOrders']);
    
    // Cart
    Route::get('/cart', [CartController::class, 'getUserCart']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clearCart']);
    
    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::get('/user/reviews', [ReviewController::class, 'userReviews']);
    
    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'getUserWishlist']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);
    Route::post('/wishlist/check', [WishlistController::class, 'checkInWishlist']);
    
    // Purchases (admin only)
    Route::apiResource('purchases', PurchaseController::class);
    
    // Dashboard Stats (admin only)
    Route::get('/stats', [DashboardController::class, 'stats']);
});