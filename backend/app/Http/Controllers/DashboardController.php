<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        // 1. Total products
        $totalProducts = Product::count();

        // 2. Total stock quantity (sum of all stock movements per product)
        $inStock = DB::table('stocks')->sum('quantity');

        // 3. Low stock items (threshold < 5)
        $lowStockProducts = Product::with('category')
            ->select('products.*')
            ->selectSub(function ($query) {
                $query->from('stocks')
                    ->selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('stocks.product_id', 'products.id');
            }, 'current_stock')
            ->having('current_stock', '<', 5)
            ->get();

        $lowStock = $lowStockProducts->count();

        // 4. Supplier count
        $suppliers = DB::table('suppliers')->count() ?? 0;

        // 5. Stock grouped by category
        $stockByCategory = DB::table('categories')
            ->join('products', 'products.category_id', '=', 'categories.id')
            ->leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
            ->select('categories.name as category', DB::raw('COALESCE(SUM(stocks.quantity), 0) as total'))
            ->groupBy('categories.name')
            ->get();

        // 6. Return aggregated data as JSON
        return response()->json([
            'totalProducts' => $totalProducts,
            'inStock' => $inStock,
            'lowStock' => $lowStock,
            'suppliers' => $suppliers,
            'lowStockItems' => $lowStockProducts->map(fn($p) => [
                'name' => $p->name,
                'stock' => $p->current_stock,
                'category' => $p->category->name ?? 'Uncategorized'
            ]),
            'stockByCategory' => [
                'labels' => $stockByCategory->pluck('category'),
                'datasets' => [[
                    'label' => 'Stock',
                    'data' => $stockByCategory->pluck('total'),
                    'backgroundColor' => ['#42A5F5', '#66BB6A', '#FFA726'],
                    'borderColor' => ['#42A5F5', '#66BB6A', '#FFA726'],
                    'borderWidth' => 1
                ]]
            ]
        ]);
    }
}
