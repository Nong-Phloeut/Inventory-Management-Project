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
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'required|numeric|min:0',
            'items.*.item_discount' => 'nullable|numeric|min:0',
            'items.*.item_tax' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data) {
            // Calculate totals
            $totals = app(\App\Services\PurchaseService::class)->calculate(
                $data['items'],
                $data['discount'] ?? 0,
                $data['tax'] ?? 0
            );

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['total_discount'],
                'tax' => $totals['total_tax'],
                'total_amount' => $totals['total_amount'],
                'purchase_number' => $this->generatePurchaseNumber(),
                'note' => $data['note'] ?? null,
            ]);

            // Optional invoice number
            $purchase->invoice_number = 'INV-' . date('Ymd') . '-' . $purchase->id;
            $purchase->save();

            foreach ($data['items'] as $item) {
                $itemSubtotal = $item['quantity'] * $item['cost_price'];
                $itemDiscountAmount = $itemSubtotal * (($item['item_discount'] ?? 0) / 100);
                $itemTax = ($itemSubtotal - $itemDiscountAmount) * ($item['item_tax'] / 100);

                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'item_discount' => $itemDiscountAmount,
                    'item_tax' => $itemTax,
                    'total' => $itemSubtotal - $itemDiscountAmount + $itemTax,
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
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'required|numeric|min:0',
            'items.*.cost_price' => 'required|numeric|min:0',
            'items.*.item_discount' => 'nullable|numeric|min:0',
            'items.*.item_tax' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($purchase, $data) {
            $totals = app(\App\Services\PurchaseService::class)->calculate(
                $data['items'],
                $data['discount'] ?? 0,
                $data['tax'] ?? 0
            );

            // rollback previous stock
            foreach ($purchase->items as $old) {
                DB::table('stocks')
                    ->where('product_id', $old->product_id)
                    ->decrement('quantity', $old->quantity);
            }

            $purchase->items()->delete();

            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['total_discount'],
                'tax' => $totals['total_tax'],
                'total_amount' => $totals['total_amount'],
                'note' => $data['note'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $itemSubtotal = $item['quantity'] * $item['cost_price'];
                $itemDiscount = $itemSubtotal * (($item['item_discount'] ?? 0) / 100);
                // $itemTax = $itemSubtotal * (($item['item_tax'] ?? 0) / 100);
                $itemTax = ($itemSubtotal - $itemDiscount) * ($item['item_tax'] / 100);

                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'item_discount' => $itemDiscount,
                    'item_tax' => $itemTax,
                    'total' => $itemSubtotal - $itemDiscount + $itemTax,
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
