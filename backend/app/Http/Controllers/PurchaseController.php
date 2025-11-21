<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Stock;
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
            'status' => 'required|in:draft,ordered,received,cancelled',
            'payment_status' => 'required|in:paid,unpaid,partial',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data) {

            $totalAmount = collect($data['items'])->sum(
                fn($i) => $i['quantity'] * $i['cost_price']
            );

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'status' => $data['status'],                   // FIXED
                'payment_status' => $data['payment_status'],   // FIXED
                'total_amount' => $totalAmount,
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
                    'total' => $item['quantity'] * $item['cost_price'],  // FIXED
                ]);

                // Update stock
                DB::table('stocks')->updateOrInsert(
                    ['product_id' => $item['product_id']],
                    ['quantity' => DB::raw("COALESCE(quantity,0) + {$item['quantity']}")]
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
            'status' => 'required|in:draft,ordered,received,cancelled',
            'payment_status' => 'required|in:paid,unpaid,partial',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($purchase, $data) {

            // 1. Rollback previous stock
            foreach ($purchase->items as $old) {
                DB::table('stocks')
                    ->where('product_id', $old->product_id)
                    ->decrement('quantity', $old->quantity);
            }

            // 2. Delete old purchase items
            $purchase->items()->delete();

            // 3. Recalculate purchase total
            $totalAmount = collect($data['items'])->sum(
                fn($i) => $i['quantity'] * $i['cost_price']
            );

            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'status' => $data['status'],                  // FIXED
                'payment_status' => $data['payment_status'],  // FIXED
                'total_amount' => $totalAmount,
            ]);

            // 4. Re-create items + update stock
            foreach ($data['items'] as $item) {

                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'total' => $item['quantity'] * $item['cost_price'],  // FIXED
                ]);

                DB::table('stocks')->updateOrInsert(
                    ['product_id' => $item['product_id']],
                    ['quantity' => DB::raw("COALESCE(quantity,0) + {$item['quantity']}")]
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
