<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StockService;

class AdjustmentController extends Controller
{
    public function adjustStock(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric', // can be + or - adjustment
            'note' => 'nullable|string',
        ]);

        $stockMovement = app(\App\Services\StockService::class)
            ->adjustStock($data['product_id'], $data['quantity'], $data['note'] ?? null);

        return response()->json(['message' => 'Stock adjusted successfully', 'data' => $stockMovement], 201);
    }
}
