<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $table = 'stock_movements';

    public $timestamps = false; // only created_at exists

    protected $fillable = [
        'product_id',
        'movement_type',
        'qty',
        'cost',
        'related_id',
        'note',
        'created_by',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
