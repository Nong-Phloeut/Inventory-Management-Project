<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Table name (optional if default 'roles')
    protected $table = 'roles';

    // Fillable attributes for mass assignment
    protected $fillable = [
        'name',        // Full role name, e.g., Administrator
        'slug',        // Role code, e.g., admin
        'status',
        'description', // Optional description
    ];

    public static function store($request, $id = null)
    {
        $roles = $request->only(
            'name',
            'slug',
            'status',
            'description',
        );

        if ($id) {
            $record = self::find($id);
            if (!$record) {
                return response()->json(['error' => 'Record not found'], 404);
            }
            $record->update($roles);
        } else {
    
            $record = self::create($roles);
            $id = $record->$id;
        }

        return  response()->json(['success' => true, 'data' => $roles], 201);
    }
    // Relationship: Role has many users
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
