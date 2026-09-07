<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\ProductController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/categories', [CategoryController::class, 'index'])
        ->middleware('role:admin,manager,staff');

    Route::get('/categories/{category}', [CategoryController::class, 'show'])
        ->middleware('role:admin,manager,staff');

    Route::post('/categories', [CategoryController::class, 'store'])
        ->middleware('role:admin');

    Route::put('/categories/{category}', [CategoryController::class, 'update'])
        ->middleware('role:admin');

    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
        ->middleware('role:admin');

    Route::get('/suppliers', [SupplierController::class, 'index'])
        ->middleware('role:admin,manager,staff');

    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])
        ->middleware('role:admin,manager,staff');

    Route::post('/suppliers', [SupplierController::class, 'store'])
        ->middleware('role:admin');

    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])
        ->middleware('role:admin');

    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])
        ->middleware('role:admin');

    Route::get('/products', [ProductController::class, 'index'])
        ->middleware('role:admin,manager,staff');

    Route::get('/products/{product}', [ProductController::class, 'show'])
        ->middleware('role:admin,manager,staff');

    Route::post('/products', [ProductController::class, 'store'])
        ->middleware('role:admin');

    Route::put('/products/{product}', [ProductController::class, 'update'])
        ->middleware('role:admin');

    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->middleware('role:admin');
});
