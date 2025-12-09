<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortBy = $request->get('sortBy', 'id');        // default sort by id
        $sortDir = $request->get('sortDir', 'desc');    // default: newest first
        $perPage = $request->query('per_page', 10);        // default page size

        $query = Stock::with([
            'product',
            'product.category',
            'product.unit'
        ])->orderBy($sortBy, $sortDir);

        // 🔍 Search by product name or SKU
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->whereHas('product', function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                ->orWhere('sku', 'like', "%$keyword%");
            });
        }

        // Filter by category (supports comma-separated IDs)
        if ($request->filled('category_id')) {
            $ids = explode(',', $request->category_id);

            $query->whereHas('product', function ($q) use ($ids) {
                $q->whereIn('category_id', $ids);
            });
        }

        // Filter by unit
        if ($request->filled('unit_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('unit_id', $request->unit_id);
            });
        }

        // Filter by quantity range
        if ($request->filled('min_qty')) {
            $query->where('quantity', '>=', $request->min_qty);
        }

        if ($request->filled('max_qty')) {
            $query->where('quantity', '<=', $request->max_qty);
        }

        // Stock level filter
        if ($request->filled('stock_level')) {
            if ($request->stock_level === 'out_of_stock') {
                $query->where('quantity', '=', 0);
            }

            if ($request->stock_level === 'low_stock') {
                $query->whereBetween('quantity', [1, 5]); // adjust threshold
            }

            if ($request->stock_level === 'in_stock') {
                $query->where('quantity', '>', 5);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Units retrieved successfully',
            'data' => $query->paginate($perPage)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:0',
        ]);
        return Stock::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(Stock $stock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Stock $stock)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);
        $stock->update($validated);
        return $stock;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stock $stock)
    {
        $stock->delete();
        return response()->noContent();
    }
}
