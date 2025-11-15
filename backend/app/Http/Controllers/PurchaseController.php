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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data) {
            $total = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['price']);


            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'total_amount' => $total,
            ]);
            // generate invoice number
            $purchase->invoice_number = 'INV-' . date('Ymd') . '-' . $purchase->id;
            $purchase->save();

            foreach ($data['items'] as $item) {
                // Create purchase items
                $purchase->items()->create($item);

                // Update stock
                $stock = Stock::firstOrNew(['product_id' => $item['product_id']]);
                $stock->quantity = ($stock->quantity ?? 0) + $item['quantity'];
                $stock->save();
            }
            return response()->json($purchase->load('items.product'), 201);
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $purchase = Purchase::findOrFail($id);

        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($purchase, $data) {
            // Step 1: Rollback old stock
            foreach ($purchase->items as $oldItem) {
                DB::table('stocks')
                    ->where('product_id', $oldItem->product_id)
                    ->decrement('quantity', $oldItem->quantity);
            }

            // Step 2: Delete old items
            $purchase->items()->delete();

            // Step 3: Update purchase itself
            $total = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['price']);
            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'total_amount' => $total,
            ]);

            // Step 4: Add new items + update stock
            foreach ($data['items'] as $item) {
                $purchase->items()->create($item);

                DB::table('stocks')->updateOrInsert(
                    ['product_id' => $item['product_id']],
                    ['quantity' => DB::raw("quantity + {$item['quantity']}")]
                );
            }

            return response()->json($purchase->load('items.product'));
        });
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
