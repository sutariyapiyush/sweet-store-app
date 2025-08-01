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
    ];

    protected $casts = [
        'quantity_in_stock' => 'decimal:2',
    ];

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
}
