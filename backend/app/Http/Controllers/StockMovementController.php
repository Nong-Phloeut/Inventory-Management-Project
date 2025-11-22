<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
        ]);

        $query = StockMovement::with(['product', 'user'])
            ->where('product_id', $request->product_id)
            ->orderBy('created_at', 'desc');

        return response()->json([
            'success' => true,
            'message' => 'Stock movements fetched successfully',
            'data' => $query->get(),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
