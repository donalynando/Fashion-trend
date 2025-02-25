<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\AddressController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\UserOrderController;
use App\Http\Controllers\User\ProductController as UserProductController;
use App\Http\Controllers\User\WishlistController;

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

// User API routes
Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);

// Protected user routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/verify', [UserAuthController::class, 'verify']);
    Route::post('/logout', [UserAuthController::class, 'logout']);
    Route::get('/user', [UserAuthController::class, 'user']);
    Route::get('/user/dashboard', [UserDashboardController::class, 'index']);

    // Cart routes
    Route::prefix('user/cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::patch('/{id}', [CartController::class, 'update']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
        Route::delete('/', [CartController::class, 'clear']);
        Route::get('/count', [CartController::class, 'count']);
    });

    // Wishlist routes
    Route::prefix('user/wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/add', [WishlistController::class, 'add']);
        Route::delete('/{productId}', [WishlistController::class, 'remove']);
        Route::get('/count', [WishlistController::class, 'count']);
    });

    // Product routes
    Route::prefix('user/products')->group(function () {
        Route::get('/', [UserProductController::class, 'index']);
        Route::get('/{product}', [UserProductController::class, 'show']);
    });

    // User Address Routes
    Route::get('/user/address/default', [AddressController::class, 'getDefault']);
    Route::apiResource('/user/address', AddressController::class);

    // User Order Routes
    Route::prefix('user/orders')->group(function () {
        Route::post('/', [UserOrderController::class, 'store']);
        Route::get('/recent', [UserOrderController::class, 'getRecentOrders']);
        Route::get('/{id}', [UserOrderController::class, 'show']);
        Route::post('/{id}/cancel', [UserOrderController::class, 'cancel']);
    });
});

// Admin API routes
Route::prefix('admin')->group(function () {
    // Public routes
    Route::post('/login', [AdminAuthController::class, 'login']);
    
    // Protected routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/verify', [AdminAuthController::class, 'verify']);
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        
        // Data routes
        Route::get('/notifications', [AdminAuthController::class, 'notifications']);
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
        Route::put('/orders/{order}', [AdminOrderController::class, 'update']);
        Route::apiResource('/products', AdminProductController::class);
        Route::apiResource('/customers', CustomerController::class);
        Route::apiResource('/refunds', RefundController::class);
    });
});