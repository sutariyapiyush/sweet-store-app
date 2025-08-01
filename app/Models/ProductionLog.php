<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionLog extends Model
{
    protected $fillable = [
        'product_id',
        'quantity_produced',
    ];

    protected $casts = [
        'quantity_produced' => 'decimal:2',
    ];

    /**
     * Get the product that was produced
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
