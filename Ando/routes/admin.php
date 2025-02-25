<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AdminController::class, 'login']);
Route::post('/register', [AdminController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AdminController::class, 'logout']);
    
    // Dashboard routes
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
});