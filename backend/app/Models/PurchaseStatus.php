<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseStatus extends Model
{
    use HasFactory;
    protected $table = 'purchase_statuses';
    protected $primaryKey = 'code';
    public $incrementing = false; // Because primary key is string
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'label',
        'is_final',
        'affects_stock',
    ];

    protected $casts = [
        'is_final' => 'boolean',
        'affects_stock' => 'boolean',
    ];

    /**
     * A status can be used by many purchases
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'purchase_status_code', 'code');
    }
}
