<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Role::query();

        // Filter by status if provided (?status=1 or ?status=0)
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Pagination (default 10 per page)
        $roles = $query->orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'success'   => true,
            'message'   => 'Suppliers retrieved successfully.',
            'data'      => $roles,
        ], 200);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $role = Role::store($request);
        return $role;
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return response()->json($role, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::store($request, $id);
        return response()->json(['success' => true, 'data' => $role], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json(null, 204);
    }
}
