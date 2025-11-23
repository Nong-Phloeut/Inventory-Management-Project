<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Stock;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Purchase::with('items.product', 'supplier')->get();
    }

    private function generatePurchaseNumber()
    {
        $year = date('Y');

        $last = Purchase::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextId = $last ? $last->id + 1 : 1;

        return "PUR-{$year}-" . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'subtotal' => 'required',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total_amount' => 'required',
            'status' => 'required|in:draft,ordered,received,cancelled',
            'payment_status' => 'required|in:paid,unpaid,partial',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data) {

            $subtotal = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['cost_price']);

            $discount = $data['discount'] ?? 0;
            $tax = $data['tax'] ?? 0;

            $total_amount = $subtotal - $discount + $tax;

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total_amount' => $total_amount,
                'purchase_number' => $this->generatePurchaseNumber(),
            ]);

            // Optional invoice number
            $purchase->invoice_number = 'INV-' . date('Ymd') . '-' . $purchase->id;
            $purchase->save();

            foreach ($data['items'] as $item) {

                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'total' => $item['quantity'] * $item['cost_price'],
                ]);


                app(\App\Services\StockService::class)->addStock(
                    $item['product_id'],
                    $item['quantity'],
                    $item['cost_price'],
                    'purchase',
                    $purchase->id,
                    "Purchase #{$purchase->id}"
                );
            }

            return response()->json($purchase->load('items.product', 'supplier'), 201);
        });
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $purchase = Purchase::findOrFail($id);

        // Prevent editing used/locked purchase
        if ($purchase->status === 'received') {
            return response()->json(['message' => 'Cannot edit a completed purchase'], 400);
        }

        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'subtotal' => 'required',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total_amount' => 'required',
            'status' => 'required|in:draft,ordered,received,cancelled',
            'payment_status' => 'required|in:paid,unpaid,partial',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($purchase, $data) {

            $subtotal = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['cost_price']);

            $discount = $data['discount'] ?? 0;
            $tax = $data['tax'] ?? 0;

            $total_amount = $subtotal - $discount + $tax;
            // 1. Rollback previous stock
            foreach ($purchase->items as $old) {
                DB::table('stocks')
                    ->where('product_id', $old->product_id)
                    ->decrement('quantity', $old->quantity);
            }

            // 2. Delete old purchase items
            $purchase->items()->delete();

            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total_amount' => $total_amount,
            ]);

            // 4. Re-create items + update stock
            foreach ($data['items'] as $item) {

                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'total' => $item['quantity'] * $item['cost_price'],  // FIXED
                ]);


                app(\App\Services\StockService::class)->addStock(
                    $item['product_id'],
                    $item['quantity'],
                    $item['cost_price'],
                    'purchase',
                    $purchase->id,
                    "Purchase #{$purchase->id}"
                );
            }

            return response()->json($purchase->load('items.product', 'supplier'));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Purchase::with('items.product', 'supplier')->findOrFail($id);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $purchase = Purchase::findOrFail($id);
        $purchase->delete();
        return response()->json(null, 204);
    }
}
