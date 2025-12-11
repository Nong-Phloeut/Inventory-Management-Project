<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get keyword from query params
        $keyword = $request->query('keyword');
        // Get items per page (default = 10)
        per_page = -1 mena list all
        $perPage = $request->query('per_page', 10);
        // Query builder
        $query = Category::query();

        // Apply keyword filter if provided
        if ($keyword) {
            $query->where('name', 'like', "%{$keyword}%");
        }

            // If per_page = -1 → get all items (no pagination)
        if ($perPage == -1) {
            $categories = $query->orderBy('id', 'desc')->get();
            return response()->json([
                "data" => $categories,
                "total" => $categories->count(),
                "per_page" => -1,
            ]);
        }

        // Paginate results (default 10 per page)
        $categories = $query
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json($categories);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return response()->json($category);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }
}
