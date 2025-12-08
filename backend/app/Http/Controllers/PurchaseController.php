<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Stock;
use App\Services\StockService;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Purchase::with([
            'items.product:id,name',
            'supplier:id,name'
        ]);

        // 🔍 Search by important names:
        // purchase_number, invoice_number, supplier name
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('purchase_number', 'like', "%{$keyword}%")
                    ->orWhere('invoice_number', 'like', "%{$keyword}%")
                    ->orWhereHas('supplier', function ($sq) use ($keyword) {
                        $sq->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        // 🔍 Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 🔍 Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // 🔍 Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }

        // 🔍 Filter by supplier_id
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Pagination
        $perPage = $request->query('per_page', 10);

        $purchases = $query
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json($purchases);
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
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
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

            $purchaseSubtotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;

            // create purchase first without totals
            $purchase = Purchase::create([
                'supplier_id'     => $data['supplier_id'],
                'purchase_date'   => $data['purchase_date'],
                'status'          => $data['status'],
                'payment_status'  => $data['payment_status'],
                'subtotal'        => 0,
                'discount'        => 0,
                'tax'             => 0,
                'total_amount'    => 0,
                'purchase_number' => $this->generatePurchaseNumber(),
                'note'            => $data['note'] ?? null,
            ]);

            // optional invoice number
            $purchase->invoice_number = 'INV-' . date('Ymd') . '-' . $purchase->id;
            $purchase->save();

            // calculate and store items
            foreach ($data['items'] as $reqItem) {
                $quantity  = (float) $reqItem['quantity'];
                $costPrice = (float) $reqItem['cost_price'];
                $discount  = (float) ($reqItem['item_discount'] ?? 0);
                $tax       = (float) ($reqItem['item_tax'] ?? 0);

                $subtotal       = $quantity * $costPrice;
                $discountAmount = $subtotal * ($discount / 100);
                $taxAmount      = ($subtotal - $discountAmount) * ($tax / 100);
                $total          = $subtotal - $discountAmount + $taxAmount;

                $purchaseSubtotal += $subtotal;
                $totalDiscount   += $discountAmount;
                $totalTax        += $taxAmount;

                $purchase->items()->create([
                    'product_id'    => $reqItem['product_id'],
                    'quantity'      => $quantity,
                    'cost_price'    => $costPrice,
                    'item_discount' => $discount,   // percent
                    'item_tax'      => $tax,        // percent
                    'total'         => $total,
                ]);

                StockService::addStock(
                    $reqItem['product_id'],
                    $quantity,
                    $costPrice,
                    'purchase',
                    $purchase->id,
                    "Purchase #{$purchase->id}"
                );
            }

            // update purchase totals
            $finalTotal = $purchaseSubtotal - $totalDiscount + $totalTax;

            $purchase->update([
                'subtotal'     => $purchaseSubtotal,
                'discount'     => $totalDiscount,
                'tax'          => $totalTax,
                'total_amount' => $finalTotal,
            ]);

            return response()->json($purchase->load('items.product', 'supplier'), 201);
        });
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $purchase = Purchase::findOrFail($id);

        if ($purchase->status === 'received') {
            return response()->json(['message' => 'Cannot edit a completed purchase'], 400);
        }

        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
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

        return DB::transaction(function () use ($purchase, $data) {

            // rollback stock
            foreach ($purchase->items as $old) {
                DB::table('stocks')
                    ->where('product_id', $old->product_id)
                    ->decrement('quantity', $old->quantity);
            }

            // delete old items
            $purchase->items()->delete();

            // calculate totals like Vue composable
            $purchaseSubtotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;

            foreach ($data['items'] as $reqItem) {
                $quantity  = (float) $reqItem['quantity'];
                $costPrice = (float) $reqItem['cost_price'];
                $discount  = (float) ($reqItem['item_discount'] ?? 0);
                $tax       = (float) ($reqItem['item_tax'] ?? 0);

                $subtotal       = $quantity * $costPrice;
                $discountAmount = $subtotal * ($discount / 100);
                $taxAmount      = ($subtotal - $discountAmount) * ($tax / 100);
                $total          = $subtotal - $discountAmount + $taxAmount;

                $purchaseSubtotal += $subtotal;
                $totalDiscount   += $discountAmount;
                $totalTax        += $taxAmount;

                // save item
                $purchase->items()->create([
                    'product_id'    => $reqItem['product_id'],
                    'quantity'      => $quantity,
                    'cost_price'    => $costPrice,
                    'item_discount' => $discount,   // store percent
                    'item_tax'      => $tax,        // store percent
                    'total'         => $total,
                ]);

                // add stock
                StockService::addStock(
                    $reqItem['product_id'],
                    $quantity,
                    $costPrice,
                    'purchase',
                    $purchase->id,
                    "Purchase #{$purchase->id}"
                );
            }

            // update purchase totals
            $finalTotal = $purchaseSubtotal - $totalDiscount + $totalTax;

            $purchase->update([
                'supplier_id'  => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'status'       => $data['status'],
                'payment_status' => $data['payment_status'],
                'subtotal'     => $purchaseSubtotal,
                'discount'     => $totalDiscount,
                'tax'          => $totalTax,
                'total_amount' => $finalTotal,
                'note'         => $data['note'] ?? null,
            ]);

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
