<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\OrderController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */

    Route::apiResource('users', UserController::class);

    /*
    |--------------------------------------------------------------------------
    | Category Management
    |--------------------------------------------------------------------------
    */

    Route::apiResource('categories', CategoryController::class);

    /*
    |--------------------------------------------------------------------------
    | Product Management
    |--------------------------------------------------------------------------
    */

    Route::apiResource('products', ProductController::class);

    /*
    |--------------------------------------------------------------------------
    | Inventory Management
    |--------------------------------------------------------------------------
    */

    Route::apiResource('inventory', InventoryController::class);

    /*
    |--------------------------------------------------------------------------
    | Supplier Management
    |--------------------------------------------------------------------------
    */

    Route::apiResource('suppliers', SupplierController::class);

    /*
    |--------------------------------------------------------------------------
    | Purchase Management
    |--------------------------------------------------------------------------
    */

    Route::apiResource('purchases', PurchaseController::class);

    /*
    |--------------------------------------------------------------------------
    | Customer Management
    |--------------------------------------------------------------------------
    */

    Route::apiResource('customers', CustomerController::class);

    /*
    |--------------------------------------------------------------------------
    | Order Management
    |--------------------------------------------------------------------------
    */

    Route::apiResource('orders', OrderController::class);

    /*
    |--------------------------------------------------------------------------
    | Sales Management
    |--------------------------------------------------------------------------
    */

    Route::apiResource('sales', SaleController::class);

    /*
    |--------------------------------------------------------------------------
    | Payment Management
    |--------------------------------------------------------------------------
    */

    Route::apiResource('payments', PaymentController::class);

    /*
    |--------------------------------------------------------------------------
    | Delivery Management
    |--------------------------------------------------------------------------
    */

    Route::apiResource('deliveries', DeliveryController::class);

});