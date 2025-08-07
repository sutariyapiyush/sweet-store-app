<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'shopify_order_id',
        'product_id',
        'shopify_line_item_id',
        'shopify_product_id',
        'shopify_variant_id',
        'product_title',
        'variant_title',
        'sku',
        'vendor',
        'quantity',
        'price',
        'total_discount',
        'line_total',
        'properties',
        'variant_options',
        'fulfillable_quantity',
        'fulfilled_quantity',
        'fulfillment_status',
        'taxable',
        'tax_lines',
        'grams',
        'requires_shipping',
        'gift_card',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'properties' => 'array',
        'variant_options' => 'array',
        'fulfillable_quantity' => 'integer',
        'fulfilled_quantity' => 'integer',
        'taxable' => 'boolean',
        'tax_lines' => 'array',
        'grams' => 'decimal:2',
        'requires_shipping' => 'boolean',
        'gift_card' => 'boolean',
    ];

    /**
     * Get the order that owns this item
     */
    public function shopifyOrder(): BelongsTo
    {
        return $this->belongsTo(ShopifyOrder::class);
    }

    /**
     * Get the local product if mapped
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate line total
     */
    public function calculateLineTotal(): float
    {
        return ($this->price * $this->quantity) - $this->total_discount;
    }

    /**
     * Get remaining quantity to fulfill
     */
    public function getRemainingQuantityAttribute(): int
    {
        return $this->fulfillable_quantity - $this->fulfilled_quantity;
    }

    /**
     * Check if item is fully fulfilled
     */
    public function isFullyFulfilled(): bool
    {
        return $this->fulfilled_quantity >= $this->fulfillable_quantity;
    }

    /**
     * Mark quantity as fulfilled
     */
    public function fulfill(int $quantity): bool
    {
        $newFulfilledQuantity = min(
            $this->fulfilled_quantity + $quantity,
            $this->fulfillable_quantity
        );

        $this->update([
            'fulfilled_quantity' => $newFulfilledQuantity,
            'fulfillment_status' => $this->isFullyFulfilled() ? 'shipped' : 'processing'
        ]);

        return true;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₹ ' . number_format($this->price, 2);
    }

    /**
     * Get formatted line total
     */
    public function getFormattedLineTotalAttribute(): string
    {
        return '₹ ' . number_format($this->line_total, 2);
    }

    /**
     * Scope for items by fulfillment status
     */
    public function scopeByFulfillmentStatus($query, string $status)
    {
        return $query->where('fulfillment_status', $status);
    }

    /**
     * Scope for pending fulfillment
     */
    public function scopePendingFulfillment($query)
    {
        return $query->where('fulfillment_status', 'pending')
                    ->where('fulfillable_quantity', '>', 'fulfilled_quantity');
    }

    /**
     * Scope for items requiring shipping
     */
    public function scopeRequiresShipping($query)
    {
        return $query->where('requires_shipping', true);
    }
}
