<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopifyProduct extends Model
{
    protected $fillable = [
        'shopify_product_id',
        'local_product_id',
        'title',
        'body_html',
        'vendor',
        'product_type',
        'handle',
        'status',
        'published_scope',
        'published_at',
        'seo_title',
        'seo_description',
        'options',
        'images',
        'featured_image',
        'is_synced_to_local',
        'last_synced_at',
        'sync_errors',
        'shopify_created_at',
        'shopify_updated_at',
        'tags',
        'template_suffix',
        'metafields',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'options' => 'array',
        'images' => 'array',
        'is_synced_to_local' => 'boolean',
        'last_synced_at' => 'datetime',
        'sync_errors' => 'array',
        'shopify_created_at' => 'datetime',
        'shopify_updated_at' => 'datetime',
        'tags' => 'array',
        'metafields' => 'array',
    ];

    /**
     * Get the local product if mapped
     */
    public function localProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'local_product_id');
    }

    /**
     * Sync this Shopify product to local product
     */
    public function syncToLocal(): ?Product
    {
        try {
            // Check if already mapped to a local product
            if ($this->local_product_id && $this->localProduct) {
                $product = $this->localProduct;
            } else {
                // Create new local product
                $product = new Product();
            }

            // Map Shopify product data to local product
            $product->fill([
                'name' => $this->title,
                'description' => strip_tags($this->body_html),
                'unit' => 'pieces', // Default unit
                'status' => $this->status === 'active' ? 'active' : 'inactive',
                'sku' => $this->handle, // Use handle as SKU if no specific SKU
            ]);

            // Extract price from first variant if available
            if ($this->images && count($this->images) > 0) {
                $product->image_path = $this->featured_image ?: $this->images[0]['src'] ?? null;
            }

            $product->save();

            // Update the mapping
            $this->update([
                'local_product_id' => $product->id,
                'is_synced_to_local' => true,
                'last_synced_at' => now(),
                'sync_errors' => null,
            ]);

            return $product;

        } catch (\Exception $e) {
            $this->update([
                'sync_errors' => ['error' => $e->getMessage(), 'timestamp' => now()],
                'is_synced_to_local' => false,
            ]);

            return null;
        }
    }

    /**
     * Check if product needs sync
     */
    public function needsSync(): bool
    {
        if (!$this->is_synced_to_local) {
            return true;
        }

        if (!$this->last_synced_at) {
            return true;
        }

        // Check if Shopify product was updated after last sync
        if ($this->shopify_updated_at && $this->shopify_updated_at > $this->last_synced_at) {
            return true;
        }

        return false;
    }

    /**
     * Get product URL on Shopify
     */
    public function getShopifyUrlAttribute(): string
    {
        $shopDomain = config('shopify.shop_domain');
        return "https://{$shopDomain}/products/{$this->handle}";
    }

    /**
     * Get featured image URL
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if ($this->featured_image) {
            return $this->featured_image;
        }

        if ($this->images && count($this->images) > 0) {
            return $this->images[0]['src'] ?? null;
        }

        return null;
    }

    /**
     * Get product tags as string
     */
    public function getTagsStringAttribute(): string
    {
        return $this->tags ? implode(', ', $this->tags) : '';
    }

    /**
     * Check if product is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'active' && $this->published_at !== null;
    }

    /**
     * Check if product has sync errors
     */
    public function hasSyncErrors(): bool
    {
        return $this->sync_errors !== null && !empty($this->sync_errors);
    }

    /**
     * Scope for active products
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for published products
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'active')
                    ->whereNotNull('published_at');
    }

    /**
     * Scope for products that need sync
     */
    public function scopeNeedsSync($query)
    {
        return $query->where(function ($q) {
            $q->where('is_synced_to_local', false)
              ->orWhereNull('last_synced_at')
              ->orWhereRaw('shopify_updated_at > last_synced_at');
        });
    }

    /**
     * Scope for synced products
     */
    public function scopeSynced($query)
    {
        return $query->where('is_synced_to_local', true)
                    ->whereNotNull('local_product_id');
    }

    /**
     * Scope for products with sync errors
     */
    public function scopeWithSyncErrors($query)
    {
        return $query->whereNotNull('sync_errors');
    }

    /**
     * Scope by product type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('product_type', $type);
    }

    /**
     * Scope by vendor
     */
    public function scopeByVendor($query, string $vendor)
    {
        return $query->where('vendor', $vendor);
    }
}
