<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with([
            'category:id,name',
            'unit:id,name,abbreviation'
        ]);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->orderBy('id', 'desc')->paginate(10);

        return response()->json($products);
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
            'category_id' => 'nullable|exists:categories,id'
        ]);

        // Get category code
        // Get category
        // $category = Category::find($request->category_id);
        // $categoryCode = $category ? strtoupper(substr($category->name, 0, 3)) : 'GEN';

        // // Generate product short code from name
        // $productCode = strtoupper(substr($request->name, 0, 3));

        // // Generate SKU
        // $sku = $this->generateSku($categoryCode, $productCode);

        // // Add SKU to data
        // $validated['sku'] = $sku;


        $product = Product::create($validated);

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
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'unit_id' => 'nullable|exists:units,id',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'sku' => 'sometimes|required|string|max:100|unique:products,sku,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
        ]);

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
