<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $start = $request->start_date ?? now()->startOfMonth()->toDateString();
        $end   = $request->end_date ?? now()->endOfMonth()->toDateString();
        // 1. Total products
        $totalProducts = Product::count();
        $totalProductsLast = Product::whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->count();
        $trendProducts = $totalProducts - $totalProductsLast;

        // 2. Total stock quantity (sum of all stock movements per product)
        $inStock = DB::table('stocks')->sum('quantity');
        $inStockLast = DB::table('stocks')
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('quantity');
        $trendInStock = $inStock - $inStockLast;

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

        $outOfStockProducts = Product::with('category')
            ->select('products.*')
            ->selectSub(function ($query) {
                $query->from('stocks')
                    ->selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('stocks.product_id', 'products.id');
            }, 'total_quantity') // alias for the subquery
            ->get();

        // Filter products with zero total quantity
        $outOfStock = $outOfStockProducts->filter(function ($product) {
            return $product->total_quantity <= 0;
        })->count();

        // 4. Supplier count
        $suppliers = DB::table('suppliers')->count() ?? 0;
        $suppliersLast = DB::table('suppliers')
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count();
        $trendSuppliers = $suppliers - $suppliersLast;

        // Inventory value
        $inventoryValue = DB::table('stocks')
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->select(DB::raw('SUM(stocks.quantity * products.price) as total_value'))
            ->value('total_value');

        $inventoryValueLast = DB::table('stocks')
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->whereBetween('stocks.created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->selectRaw('SUM(stocks.quantity * products.price) as total_value')
            ->value('total_value');

        $trendInventory = $inventoryValue - $inventoryValueLast;

        // 5. Stock grouped by category //Cart
        $stockByCategory = DB::table('categories')
            ->join('products', 'products.category_id', '=', 'categories.id')
            ->leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
            ->select('categories.name as category', DB::raw('COALESCE(SUM(stocks.quantity), 0) as total'))
            ->groupBy('categories.name')
            ->get();

        /* ----------------------------------------------------
        1. Monthly Purchases & Sales for Chart
        -----------------------------------------------------*/
        // purchases table must contain: id, total_amount, created_at
        $monthlyPurchases = DB::table('purchases')
            ->selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // sales table must contain: id, total_amount, created_at
        $monthlySales = DB::table('sales')
            ->selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Fill missing months (Jan–Dec)
        $months = collect(range(1, 12))->map(function ($m) use ($monthlyPurchases, $monthlySales) {
            return [
                'month' => $m,
                'purchases' => $monthlyPurchases[$m] ?? 0,
                'sales'     => $monthlySales[$m] ?? 0,
            ];
        });

        // 6. Return aggregated data as JSON
        return response()->json([
            'totalProducts' => $totalProducts,
            'inStock' => $inStock,
            'lowStock' => $lowStock,
            'suppliers' => $suppliers,
            'outOfStock' => $outOfStock,
            'inventoryValue'  => $inventoryValue,
            'lowStockItems' => $lowStockProducts->map(fn($p) => [
                'name' => $p->name,
                'stock' => $p->current_stock,
                'category' => $p->category->name ?? 'Uncategorized'
            ]),
            'trend' => [
                'products' => $trendProducts,
                'inStock' => $trendInStock,
                'lowStock' => 0, // optional: compute if needed
                'suppliers' => $trendSuppliers,
                'inventoryValue' => $trendInventory
            ],
            'stockByCategory' => [
                'labels' => $stockByCategory->pluck('category'),
                'datasets' => [[
                    'label' => 'Stock',
                    'data' => $stockByCategory->pluck('total'),
                    'backgroundColor' => ['#42A5F5', '#66BB6A', '#FFA726'],
                    'borderColor' => ['#42A5F5', '#66BB6A', '#FFA726'],
                    'borderWidth' => 1
                ]]
            ],
            'monthsData' => $months,
        ]);
    }

    public function getMonthlyPurchases(Request $request)
    {
        $year = $request->year ?? date('Y'); // default current year

        $monthlyData = Purchase::select(
            DB::raw('MONTH(purchase_date) as month'),
            DB::raw('SUM(total_amount) as total')
        )
            ->whereYear('purchase_date', $year)
            ->groupBy(DB::raw('MONTH(purchase_date)'))
            ->orderBy('month')
            ->get();

        // Prepare data for chart (12 months)
        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthData = $monthlyData->firstWhere('month', $i);
            $chartData[] = $monthData ? $monthData->total : 0;
        }

        return response()->json([
            'year' => $year,
            'data' => $chartData
        ]);
    }
}
