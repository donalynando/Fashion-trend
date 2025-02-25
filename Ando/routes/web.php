<?php

use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/login', function () {
    return view('welcome');
});

// User routes
Route::get('/user/dashboard', function () {
    return view('welcome');
});

Route::get('/user/{any}', function () {
    return view('welcome');
})->where('any', '.*');

// Admin routes
Route::get('/admin/login', function () {
    return view('welcome');
});

Route::get('/admin/{any}', function () {
    return view('welcome');
})->where('any', '.*');

// Catch-all route for Vue router
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');