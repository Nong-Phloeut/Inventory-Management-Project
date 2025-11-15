<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = ['supplier_id', 'purchase_date', 'total_amount'];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // Add this relationship
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
