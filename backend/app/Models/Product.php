<?php

namespace App\Models;

use App\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        'unit_id',
        'supplier_id',
        'image'
    ];

    /**
     * Accessor to get the full image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            // $disk = Storage::disk('public');
            return asset('storage/' . $this->image);
        }

        return asset('images/placeholder.png');
    }

    public function scopeStatus($query, $status)
    {
        if ($status !== null) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeCategory($query, $categoryIds)
    {
        if ($categoryIds) {
            $ids = is_array($categoryIds) ? $categoryIds : explode(',', $categoryIds);
            return $query->whereIn('category_id', $ids);
        }
        return $query;
    }

    public function scopeKeyword($query, $keyword)
    {
        if ($keyword) {
            return $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('sku', 'like', "%{$keyword}%")
                    ->orWhere('barcode', 'like', "%{$keyword}%");
            });
        }
        return $query;
    }

    public function scopePriceRange($query, $min, $max)
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
        return $query;
    }

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

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
