<?php

use App\Http\Controllers\AdjustmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LossController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\RoleController;
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
Route::apiResource('roles', RoleController::class);
Route::get('/dashboard', [DashboardController::class, 'stats']);
Route::apiResource('sales', SaleController::class);
Route::apiResource('employees', EmployeeController::class);
Route::apiResource('/stock-movements', StockMovementController::class);
Route::prefix('stock')->group(function () {
    Route::post('return', [ReturnController::class, 'returnStock']);
    Route::post('adjust', [AdjustmentController::class, 'adjustStock']);
    Route::post('loss', [LossController::class, 'reportLoss']);
});

Route::get('audit-logs', [AuditLogController::class, 'index']);
Route::get('audit-logs/{id}', [AuditLogController::class, 'show']);

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('jwt.auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

Route::get('/units', [UnitController::class, 'index']);
Route::post('/units', [UnitController::class, 'store']);
Route::put('/units/{id}', [UnitController::class, 'update']);
Route::delete('/units/{id}', [UnitController::class, 'destroy']);


