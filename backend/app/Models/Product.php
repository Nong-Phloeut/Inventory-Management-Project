<?php

namespace App\Models;

use App\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    use Auditable;

    protected $fillable = [
        'name',
        'description',
        'price',
        'sku',
        'barcode',
        'status',
        'low_stock_threshold',
        'category_id',
        'unit_id'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
