<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Employee::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'phone'            => 'nullable|string|max:20',
            'gender'           => 'nullable|string',
            'dob'              => 'nullable|date',
            'job_title'        => 'nullable|string|max:255',
            'department'       => 'nullable|string|max:255',
            'joining_date'     => 'nullable|date',
            'status'           => 'required|in:Active,Inactive',
            'address'          => 'nullable|string',
            'emergency_name'   => 'nullable|string|max:255',
            'emergency_phone'  => 'nullable|string|max:20',
        ]);
        // ✅ Fix date format
        $validated['dob'] = $request->dob ? date('Y-m-d', strtotime($request->dob)) : null;
        $validated['joining_date'] = $request->joining_date ? date('Y-m-d', strtotime($request->joining_date)) : null;

        $employee = Employee::create($validated);

        return response()->json([
            'message' => 'Employee created successfully',
            'employee' => $employee,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        return response()->json($employee);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'first_name'       => 'sometimes|required|string|max:255',
            'last_name'        => 'sometimes|required|string|max:255',
            'phone'            => 'nullable|string|max:20',
            'gender'           => 'nullable|string',
            'dob'              => 'nullable|date',
            'job_title'        => 'nullable|string|max:255',
            'department'       => 'nullable|string|max:255',
            'employee_id'      => 'sometimes|required|string|unique:employees,employee_id,' . $employee->id,
            'joining_date'     => 'nullable|date',
            'status'           => 'required|in:Active,Inactive',
            'address'          => 'nullable|string',
            'emergency_name'   => 'nullable|string|max:255',
            'emergency_phone'  => 'nullable|string|max:20',
        ]);

        $employee->update($data);

        return response()->json($employee);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();
        return response()->json(null, 204);
    }
}
