<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserOrderController;
use App\Http\Controllers\User\UserAuthController;
use App\Http\Controllers\User\UserAddressController;
use App\Http\Controllers\User\UserCartController;

Route::middleware(['auth:sanctum'])->group(function () {
    // User profile
    Route::get('/user', [UserAuthController::class, 'user'])->name('user.profile');
    
    // Address routes
    Route::post('/address', [UserAddressController::class, 'store'])->name('user.address.store');
    Route::get('/address', [UserAddressController::class, 'show'])->name('user.address.show');
    
    // Order routes
    Route::post('/orders', [UserOrderController::class, 'store'])->name('user.orders.store');
    Route::get('/orders/recent', [UserOrderController::class, 'getRecentOrders'])->name('user.orders.recent');
    
    // Cart routes
    Route::get('/cart', [UserCartController::class, 'index'])->name('user.cart.index');
    Route::delete('/cart/clear', [UserCartController::class, 'clear'])->name('user.cart.clear');
});
