<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use Auditable;

    protected $fillable = [
        'supplier_id',
        'purchase_date',
        'total_amount',
        'purchase_number',
        'invoice_number',
        'purchase_status_code',
        'payment_status',
        'subtotal',
        'tax',
        'discount',
        'note'
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // Add this relationship
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseStatus()
    {
        return $this->belongsTo(PurchaseStatus::class, 'purchase_status_code', 'code');
    }
}
