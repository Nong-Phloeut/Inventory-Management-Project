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
        $perPage = $request->get('perPage', 10);        // default page size

        $query = Stock::with([
            'product',
            'product.category',
            'product.unit'
        ])->orderBy($sortBy, $sortDir);


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
