<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    // GET /units
    public function index()
    {
        return response()->json(Unit::all());
    }

    // POST /units
    public function store(StoreUnitRequest $request)
    {
        $unit = Unit::create($request->validated());
        return response()->json($unit, 201);
    }

    public function update(UpdateUnitRequest $request, $id)
    {
        $unit = Unit::findOrFail($id);
        $unit->update($request->validated());
        return response()->json($unit);
    }

    // DELETE /units/{id}
    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);

        // Prevent deletion if used by products
        if ($unit->products()->count() > 0) {
            return response()->json(['error' => 'Cannot delete unit used by products'], 400);
        }

        $unit->delete();

        return response()->json(['message' => 'Unit deleted successfully']);
    }
}
