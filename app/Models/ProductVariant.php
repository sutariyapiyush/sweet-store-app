<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'variant_name',
        'variant_type',
        'price_modifier',
        'sku_suffix',
        'weight_modifier',
        'additional_attributes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'weight_modifier' => 'decimal:3',
        'additional_attributes' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the product this variant belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the calculated price for this variant
     */
    public function getCalculatedPrice(): float
    {
        $basePrice = $this->product->selling_price ?? 0;
        return $basePrice + $this->price_modifier;
    }

    /**
     * Get the calculated weight for this variant
     */
    public function getCalculatedWeight(): float
    {
        $baseWeight = $this->product->weight ?? 0;
        return $baseWeight + $this->weight_modifier;
    }

    /**
     * Get the full SKU including suffix
     */
    public function getFullSku(): string
    {
        $baseSku = $this->product->sku ?? '';
        return $baseSku . ($this->sku_suffix ?? '');
    }

    /**
     * Get the display name (Product Name - Variant Name)
     */
    public function getDisplayName(): string
    {
        return $this->product->name . ' - ' . $this->variant_name;
    }

    /**
     * Scope to get only active variants
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('variant_name');
    }

    /**
     * Scope to filter by variant type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('variant_type', $type);
    }
}
