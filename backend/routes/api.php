<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::get('/user', function () {
    return response()->json([
        'message' => 'API is working'
    ]);
});

Route::apiResource('suppliers', SupplierController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('stocks', StockController::class);
Route::apiResource('purchases', PurchaseController::class);
Route::get('/dashboard', [DashboardController::class, 'stats']);
Route::apiResource('sales', SaleController::class);
Route::apiResource('employees', EmployeeController::class);
