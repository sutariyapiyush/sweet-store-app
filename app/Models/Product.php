<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'unit',
        'quantity_in_stock',
        'cost_price',
        'selling_price',
        'wholesale_price',
        'profit_margin',
        'category_id',
        'sku',
        'barcode',
        'weight',
        'dimensions',
        'shelf_life_days',
        'storage_temperature',
        'minimum_stock_level',
        'maximum_stock_level',
        'reorder_quantity',
        'status',
        'is_seasonal',
        'seasonal_months',
        'production_time_minutes',
        'image_path',
        'ingredients_list',
        'nutritional_info',
        'allergens',
    ];

    protected $casts = [
        'quantity_in_stock' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'weight' => 'decimal:3',
        'dimensions' => 'array',
        'shelf_life_days' => 'integer',
        'minimum_stock_level' => 'decimal:2',
        'maximum_stock_level' => 'decimal:2',
        'reorder_quantity' => 'decimal:2',
        'is_seasonal' => 'boolean',
        'seasonal_months' => 'array',
        'production_time_minutes' => 'integer',
        'nutritional_info' => 'array',
        'allergens' => 'array',
    ];

    /**
     * Get the category this product belongs to
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Get the variants of this product
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the production schedules for this product
     */
    public function productionSchedules()
    {
        return $this->hasMany(ProductionSchedule::class);
    }

    /**
     * Get the raw materials required for this product (BOM)
     */
    public function rawMaterials()
    {
        return $this->belongsToMany(RawMaterial::class)
                    ->withPivot('quantity_required')
                    ->withTimestamps();
    }

    /**
     * Get the production logs for this product
     */
    public function productionLogs()
    {
        return $this->hasMany(ProductionLog::class);
    }

    /**
     * Check if stock is low (less than 10 units)
     */
    public function isLowStock(): bool
    {
        return $this->quantity_in_stock < 10;
    }

    /**
     * Check if enough raw materials are available for production
     */
    public function canProduce(float $quantity): bool
    {
        foreach ($this->rawMaterials as $rawMaterial) {
            $requiredQuantity = $rawMaterial->pivot->quantity_required * $quantity;
            if ($rawMaterial->quantity_in_stock < $requiredQuantity) {
                return false;
            }
        }
        return true;
    }

    // Pricing Methods

    /**
     * Get profit margin percentage
     */
    public function getProfitMargin(): float
    {
        if (!$this->cost_price || !$this->selling_price || $this->cost_price == 0) {
            return 0;
        }

        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }

    /**
     * Get markup percentage
     */
    public function getMarkupPercentage(): float
    {
        if (!$this->cost_price || !$this->selling_price || $this->selling_price == 0) {
            return 0;
        }

        return (($this->selling_price - $this->cost_price) / $this->selling_price) * 100;
    }

    // Inventory Management Methods

    /**
     * Check if stock needs reordering
     */
    public function needsReorder(): bool
    {
        return $this->quantity_in_stock <= $this->minimum_stock_level;
    }

    /**
     * Get optimal order quantity
     */
    public function getOptimalOrderQuantity(): float
    {
        if ($this->reorder_quantity) {
            return $this->reorder_quantity;
        }

        // Default to difference between max and current stock
        if ($this->maximum_stock_level) {
            return max(0, $this->maximum_stock_level - $this->quantity_in_stock);
        }

        // Fallback to minimum stock level
        return $this->minimum_stock_level ?? 10;
    }

    /**
     * Get inventory value at cost price
     */
    public function getInventoryValue(): float
    {
        return $this->quantity_in_stock * ($this->cost_price ?? 0);
    }

    /**
     * Get inventory value at selling price
     */
    public function getInventoryValueAtSellingPrice(): float
    {
        return $this->quantity_in_stock * ($this->selling_price ?? 0);
    }

    // Expiry and Freshness Methods

    /**
     * Check if product expires soon
     */
    public function isExpiringSoon(int $days = 3): bool
    {
        if (!$this->shelf_life_days) {
            return false;
        }

        // This would need to be calculated based on production dates
        // For now, return false as we'd need production log data
        return false;
    }

    /**
     * Get remaining shelf life for latest production
     */
    public function getShelfLifeRemaining(): ?int
    {
        $latestProduction = $this->productionLogs()
            ->where('status', 'completed')
            ->latest('production_date')
            ->first();

        if (!$latestProduction || !$this->shelf_life_days) {
            return null;
        }

        $productionDate = $latestProduction->production_date ?? $latestProduction->created_at;
        $expiryDate = $productionDate->addDays($this->shelf_life_days);

        return max(0, now()->diffInDays($expiryDate, false));
    }

    // Production Planning Methods

    /**
     * Estimate production time for given quantity
     */
    public function estimateProductionTime(float $quantity): int
    {
        if (!$this->production_time_minutes) {
            return 0;
        }

        return (int) ($this->production_time_minutes * $quantity);
    }

    /**
     * Check if product is seasonal and currently in season
     */
    public function isInSeason(): bool
    {
        if (!$this->is_seasonal || !$this->seasonal_months) {
            return true;
        }

        $currentMonth = now()->month;
        return in_array($currentMonth, $this->seasonal_months);
    }

    // Scopes

    /**
     * Scope for active products
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for low stock products
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity_in_stock <= minimum_stock_level');
    }

    /**
     * Scope for seasonal products
     */
    public function scopeSeasonal($query)
    {
        return $query->where('is_seasonal', true);
    }

    /**
     * Scope for products in current season
     */
    public function scopeInSeason($query)
    {
        $currentMonth = now()->month;
        return $query->where('is_seasonal', false)
                    ->orWhereJsonContains('seasonal_months', $currentMonth);
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope by storage temperature
     */
    public function scopeByStorageTemp($query, $temp)
    {
        return $query->where('storage_temperature', $temp);
    }
}
