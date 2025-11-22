<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StockService;

class ReturnController extends Controller
{
    public function returnStock(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1',
            'note' => 'nullable|string',
        ]);

        $stockMovement = app(\App\Services\StockService::class)
            ->returnStock($data['product_id'], $data['quantity'], $data['note'] ?? null);

        return response()->json(['message' => 'Stock returned successfully', 'data' => $stockMovement], 201);
    }
}
