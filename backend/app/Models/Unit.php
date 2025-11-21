<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    // Define fillable attributes
    protected $fillable = [
        'name',
        'abbreviation',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
