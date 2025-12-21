<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'icon',
        'color',
        'is_read',
        'data',
        'channel',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
