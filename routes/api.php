<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here you can register API routes for your application.
|
*/

// JWT Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Routes that require authentication
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::get("/products" , [AuthController::class , "products"]);
        Route::get("/product/{id}" , [AuthController::class , "product"]);
        Route::post('/orders', [AuthController::class, 'store']);
        Route::get('/orders', [AuthController::class, 'index']);
        Route::get('/my_orders', [AuthController::class, 'my_orders']);
        Route::get("/order/{id}" , [AuthController::class , "order"]);
        Route::patch("/update_order/{id}" , [AuthController::class , "update_order"]);
        Route::delete("/delete_order/{id}" , [AuthController::class , "delete_order"]);
    });
});
