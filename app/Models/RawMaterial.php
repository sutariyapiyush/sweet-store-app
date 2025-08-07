<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'quantity_in_stock',
        'seller_id',
        'invoice_path',
        'purchase_date',
        'purchase_price',
    ];

    protected $casts = [
        'quantity_in_stock' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'purchase_date' => 'date',
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
     * Get the seller that supplies this raw material
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Check if stock is low (less than 10 units)
     */
    public function isLowStock(): bool
    {
        return $this->quantity_in_stock < 10;
    }

    /**
     * Get the invoice file name
     */
    public function getInvoiceFileNameAttribute()
    {
        if (!$this->invoice_path) {
            return null;
        }

        return basename($this->invoice_path);
    }

    /**
     * Get the full invoice URL
     */
    public function getInvoiceUrlAttribute()
    {
        if (!$this->invoice_path) {
            return null;
        }

        return asset('storage/' . $this->invoice_path);
    }
}
