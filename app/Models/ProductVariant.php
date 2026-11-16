<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'weight_grams',
        'grind_size',
        'price',
        'stock',
    ];

    protected $casts = [
        'weight_grams' => 'integer',
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getFormattedLabelAttribute(): string
    {
        return "{$this->weight_grams}g - {$this->grind_size}";
    }
}