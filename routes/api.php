<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\InventoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Xác thực (Auth)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Upload API
use App\Http\Controllers\UploadController;
Route::post('/upload', [UploadController::class, 'upload'])->middleware('auth:api');

// Public (Cho người dùng chưa đăng nhập)
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/chatbot', [ChatbotController::class, 'chat']);

// Yêu cầu đăng nhập (Customer Routes)
Route::middleware(['auth:api'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('customer')->group(function () {
        Route::get('/cart', [CartController::class, 'getCart']);
        Route::post('/cart', [CartController::class, 'addToCart']);
        Route::put('/cart/{id}', [CartController::class, 'updateCart']);
        Route::delete('/cart/{id}', [CartController::class, 'removeFromCart']);
        
        Route::post('/checkout', [OrderController::class, 'checkout']);
        Route::get('/orders', [OrderController::class, 'myOrders']);
    });
});

// Admin Routes (Yêu cầu đăng nhập & quyền Admin)
Route::middleware(['auth:api', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'getStats']);
    
    // Quản lý Danh mục
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    
    // Quản lý Sản phẩm
    Route::get('/products', [ProductController::class, 'adminIndex']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    
    // Quản lý Đơn hàng
    Route::get('/orders', [OrderController::class, 'adminIndex']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    
    // Quản lý Tồn kho
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::put('/inventory/{id}', [InventoryController::class, 'update']);
});
