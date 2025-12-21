<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'contact_name',
        'phone',
        'email',
        'address',
        'status'
    ];

    // A supplier can have many purchases
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
