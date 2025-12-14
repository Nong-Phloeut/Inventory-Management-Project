<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Services\InventoryAIService;
use Illuminate\Http\Request;

class InventoryAIController extends Controller
{
    protected $ai;

    public function __construct(InventoryAIService $ai)
    {
        $this->ai = $ai;
    }

    // Low Stock AI report
    public function lowStock()
    {
        $products = Product::select('products.*', 'stocks.quantity', 'products.low_stock_threshold')
            ->join('stocks', 'stocks.product_id', '=', 'products.id')
            ->whereColumn('stocks.quantity', '<=', 'products.low_stock_threshold')
            ->get();

        $recommendations = $this->ai->analyzeLowStock($products);

        return response()->json([
            'low_stock' => $products,
            'recommendations' => $recommendations
        ]);
    }

    // Purchase AI report
    public function purchaseReport(Request $request)
    {
        $start = $request->start_date ?? now()->startOfMonth()->toDateString();
        $end = $request->end_date ?? now()->endOfMonth()->toDateString();

        $purchases = Purchase::whereBetween('created_at', [$start, $end])->get();
        $insights = $this->ai->analyzePurchases($purchases);

        return response()->json([
            'purchases' => $purchases,
            'insights' => $insights
        ]);
    }

    // Stock Movement AI report
    public function stockMovement(Request $request)
    {
        $start = $request->start_date ?? now()->startOfMonth()->toDateString();
        $end = $request->end_date ?? now()->endOfMonth()->toDateString();

        $movements = StockMovement::whereBetween('created_at', [$start, $end])->get();
        $insights = $this->ai->analyzeStockMovement($movements);

        return response()->json([
            'movements' => $movements,
            'insights' => $insights
        ]);
    }
}
