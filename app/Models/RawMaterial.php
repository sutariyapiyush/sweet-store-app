<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'quantity_in_stock',
    ];

    protected $casts = [
        'quantity_in_stock' => 'decimal:2',
    ];

    /**
     * Get the products that use this raw material
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)
                    ->withPivot('quantity_required')
                    ->withTimestamps();
    }

    /**
     * Check if stock is low (less than 10 units)
     */
    public function isLowStock(): bool
    {
        return $this->quantity_in_stock < 10;
    }
}
