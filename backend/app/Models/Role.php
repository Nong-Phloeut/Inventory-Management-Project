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

    // Relationship: Role has many users
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
