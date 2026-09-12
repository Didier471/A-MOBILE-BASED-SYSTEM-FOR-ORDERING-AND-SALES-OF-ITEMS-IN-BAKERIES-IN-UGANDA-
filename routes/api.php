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
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReportExportController;

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

    Route::middleware('permission:manage users')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Category Management
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:manage categories')->group(function () {
        Route::apiResource('categories', CategoryController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Product Management
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:manage products')->group(function () {
        Route::apiResource('products', ProductController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Inventory Management
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:manage inventory')->group(function () {
        Route::apiResource('inventory', InventoryController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Supplier Management
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:manage suppliers')->group(function () {
        Route::apiResource('suppliers', SupplierController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Purchase Management
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:manage purchases')->group(function () {
        Route::apiResource('purchases', PurchaseController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Customer Management
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:manage customers')->group(function () {
        Route::apiResource('customers', CustomerController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Order Management
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:manage orders')->group(function () {
        Route::apiResource('orders', OrderController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Sales Management
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:manage sales')->group(function () {
        Route::apiResource('sales', SaleController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Payment Management
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:manage payments')->group(function () {
        Route::apiResource('payments', PaymentController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Delivery Management
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:manage deliveries')->group(function () {
        Route::apiResource('deliveries', DeliveryController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:view reports')->group(function () {

        Route::get('/reports', [ReportController::class, 'index']);
        Route::get('/reports/sales', [ReportController::class, 'sales']);
        Route::get('/reports/purchases', [ReportController::class, 'purchases']);
        Route::get('/reports/inventory', [ReportController::class, 'inventory']);
        Route::get('/reports/payments', [ReportController::class, 'payments']);
        Route::get('/reports/orders', [ReportController::class, 'orders']);
        Route::get('/reports/deliveries', [ReportController::class, 'deliveries']);

    });

    /*
    |--------------------------------------------------------------------------
    | Report Exports (xlsx / pdf)
    |--------------------------------------------------------------------------
    | Reuses the 'export reports' permission that was already seeded in
    | RolePermissionSeeder but had no routes pointing at it.
    | ?format=xlsx (default) or ?format=pdf, plus from=/to= where applicable.
    */

    Route::middleware('permission:export reports')->group(function () {

        Route::get('/reports/sales/export', [ReportExportController::class, 'sales']);
        Route::get('/reports/purchases/export', [ReportExportController::class, 'purchases']);
        Route::get('/reports/inventory/export', [ReportExportController::class, 'inventory']);
        Route::get('/reports/payments/export', [ReportExportController::class, 'payments']);

    });

});