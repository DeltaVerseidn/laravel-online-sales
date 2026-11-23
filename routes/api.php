<?php 
 
use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController; 
use App\Http\Controllers\Api\V1\AuthController; 
use App\Http\Controllers\Api\V1\CheckoutController; 
use App\Http\Controllers\Api\V1\CustomerOrderController; 
use App\Http\Controllers\Api\V1\ProductController; 
use Illuminate\Support\Facades\Route; 
 
Route::prefix('v1')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/categories', [ProductController::class, 'categories']);
    Route::get('/shipping-services', [ProductController::class, 'shippingServices']);
    
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1'); 
 
    Route::middleware('auth:sanctum')->group(function () { 
        Route::get('/auth/me', [AuthController::class, 'me']); 
        Route::post('/auth/logout', [AuthController::class, 'logout']); 
 
        Route::middleware('role:customer')->group(function () { 
            Route::post('/checkout', [CheckoutController::class, 'store']); 
            Route::get('/orders', [CustomerOrderController::class, 'index']); 
            Route::get('/orders/{order}', [CustomerOrderController::class, 'show']); 
        }); 
 
        Route::prefix('admin')->middleware('role:admin')->group(function () { 
            Route::get('/orders', [AdminOrderController::class, 'index']); 
            Route::get('/orders/{order}', [AdminOrderController::class, 'show']); 
            Route::patch('/orders/{order}/confirm', [AdminOrderController::class, 'confirm']); 
            Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']); 
            Route::patch('/orders/{order}/cancel', [AdminOrderController::class, 'cancel']); 
        }); 
    }); 
}); 