<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\PurchaseItem;
use Carbon\Carbon;
use DB;

class PurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from;
        $to   = $request->to;
        $categoryId = $request->category;

        // Base query on purchase items
        $query = PurchaseItem::with(['purchase', 'product.category', 'purchase.supplier'])
            ->whereHas('purchase', function ($q) use ($from, $to) {
                $q->whereBetween('purchase_date', [$from, $to])
                    ->where('status', '!=', 'cancelled');
            });

        if ($categoryId) {
            $query->whereHas('product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        $items = $query->get();

        // KPIs
        $totalPurchases = $items->sum('total');             // total cost
        $totalOrders    = $items->pluck('purchase_id')->unique()->count(); // distinct purchase orders
        $totalQuantity  = $items->sum('quantity');          // total quantity purchased
        $avgPurchaseCost = $totalOrders ? $totalPurchases / $totalOrders : 0;

        // Purchase trend by date
        $trend = $items->groupBy(function ($item) {
            return \Carbon\Carbon::parse($item->purchase->purchase_date)->format('Y-m-d');
        })->map(function ($group) {
            return $group->sum('total');
        });


        // Purchases by category
        $byCategory = $items->groupBy(function ($item) {
            return $item->product->category->name;
        })->map(function ($group) {
            return $group->sum('total');
        });

        // Table data (all items)
        $tableData = $items->map(function ($item) {
            return [
                'date' => \Carbon\Carbon::parse($item->purchase->purchase_date)->format('Y-m-d'),
                'purchase_no' => $item->purchase->purchase_number,
                'supplier' => $item->purchase->supplier->name,
                'category' => $item->product->category->name,
                'qty' => $item->quantity,
                'total' => $item->total
            ];
        });

        $perPage = (int) $request->query('per_page', 10);
        $tableData = $query
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'kpis' => [
                ['title' => 'Total Purchases', 'value' => number_format($totalPurchases, 2)],
                ['title' => 'Purchase Orders', 'value' => $totalOrders],
                ['title' => 'Total Quantity', 'value' => $totalQuantity],
                ['title' => 'Avg Purchase Cost', 'value' => number_format($avgPurchaseCost, 2)],
            ],
            'trend' => $trend,
            'byCategory' => $byCategory,
            'table' => $tableData
        ]);
    }
}
