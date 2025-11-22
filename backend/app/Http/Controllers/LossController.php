<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StockService;

class LossController extends Controller
{
    public function reportLoss(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|numeric|min:1',
            'note'       => 'nullable|string',
        ]);

        $stockMovement = app(\App\Services\StockService::class)
            ->reportLoss($data['product_id'], $data['quantity'], $data['note'] ?? null);

        return response()->json([
            'message' => 'Stock loss reported successfully',
            'data'    => $stockMovement
        ], 201);
    }
}
