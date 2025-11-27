<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query();

        if ($request->filled('user_id'))
            $query->where('user_id', $request->user_id);

        if ($request->filled('action_type'))
            $query->where('action_type', $request->action_type);

        if ($request->filled('module'))
            $query->where('module', $request->module);

        if ($request->filled('date_from'))
            $query->where('created_at', '>=', Carbon::parse($request->date_from));

        if ($request->filled('date_to'))
            $query->where('created_at', '<=', Carbon::parse($request->date_to));

        return response()->json(
            $query->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 20))
        );
    }


    public function show($id)
    {
        return response()->json(AuditLog::with('user')->findOrFail($id));
    }
}
