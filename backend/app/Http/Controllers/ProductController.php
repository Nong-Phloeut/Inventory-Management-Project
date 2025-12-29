<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category:id,name', 'unit:id,name,abbreviation'])
            ->status($request->status)
            ->category($request->category_id)
            ->keyword($request->keyword)
            ->priceRange($request->min_price, $request->max_price);

        $perPage = (int) $request->query('per_page', 10);

        if ($perPage === -1) {
            $items = $query->orderByDesc('id')->get();
            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully.',
                'data' => [
                    'current_page' => 1,
                    'data' => ProductResource::collection($items),
                    'per_page' => $items->count(),
                    'total' => $items->count(),
                    'last_page' => 1
                ]
            ]);
        }

        $products = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully.',
            'data' => [
                'current_page' => $products->currentPage(),
                'data' => ProductResource::collection($products),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'unit_id' => 'nullable|exists:units,id',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sku' => 'required',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('products', 'public');
        }

        $product = Product::create($validated);

        // Automatically create stock record with qty 0 and draft_qty 0
        $product->stock()->create([
            'quantity' => 0,
            'draft_qty' => 0,
        ]);

        return response()->json($product, 201);
    }

    public function generateSku($categoryCode, $productCode)
    {
        // Get last SKU in this category & product set
        $last = Product::where('sku', 'like', "{$categoryCode}-{$productCode}-%")
            ->orderBy('sku', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->sku, -3);
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = "001";
        }

        return "{$categoryCode}-{$productCode}-{$nextNumber}";
    }


    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return $product;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'status' => 'required|in:active,inactive',
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'unit_id' => 'nullable|exists:units,id',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'sku' => 'sometimes|required|string|max:100|unique:products,sku,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Check if user tries to set inactive while stock exists
        if (isset($validated['status']) && $validated['status'] === 'inactive') {
            $stockQty = $product->stock()->sum('quantity'); // assuming relationship 'stock' exists
            if ($stockQty > 0) {
                return response()->json([
                    'message' => 'Cannot set product as inactive while stock is available.'
                ], 400);
            }
        }

        // ✅ Handle image replacement
        if ($request->hasFile('image')) {

            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $validated['image'] = $request->file('image')
                ->store('products', 'public');
        }

        $product->update($validated);

        return $product;
    }


    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(Product $product)
    // {
    //     $product->delete();

    //     return response()->json([
    //         'message' => 'Product deleted successfully.'
    //     ], 200);
    // }

    public function destroy(Product $product)
    {
        // Check stock in related table
        if ($product->stock && $product->stock->quantity > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this product because stock is available.',
            ], 400);
        }

        // Safe to delete
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ], 200);
    }
}
