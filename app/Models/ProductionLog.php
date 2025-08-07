<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionLog extends Model
{
    protected $fillable = [
        'product_id',
        'quantity_produced',
        'batch_number',
        'production_date',
        'expiry_date',
        'user_id',
        'shift',
        'production_time_minutes',
        'yield_percentage',
        'quality_grade',
        'waste_quantity',
        'labor_cost',
        'overhead_cost',
        'total_production_cost',
        'notes',
        'issues_encountered',
        'temperature_log',
        'status',
    ];

    protected $casts = [
        'quantity_produced' => 'decimal:2',
        'production_date' => 'date',
        'expiry_date' => 'date',
        'production_time_minutes' => 'integer',
        'yield_percentage' => 'decimal:2',
        'waste_quantity' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'overhead_cost' => 'decimal:2',
        'total_production_cost' => 'decimal:2',
        'temperature_log' => 'array',
    ];

    /**
     * Get the product that was produced
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who performed the production
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the quality checks for this production log
     */
    public function qualityChecks()
    {
        return $this->hasMany(QualityCheck::class);
    }

    // Efficiency Methods

    /**
     * Get efficiency rate (actual vs expected production time)
     */
    public function getEfficiencyRate(): ?float
    {
        if (!$this->production_time_minutes || !$this->product->production_time_minutes) {
            return null;
        }

        $expectedTime = $this->product->production_time_minutes * $this->quantity_produced;
        if ($expectedTime == 0) {
            return null;
        }

        return ($expectedTime / $this->production_time_minutes) * 100;
    }

    /**
     * Get cost per unit produced
     */
    public function getCostPerUnit(): float
    {
        if ($this->quantity_produced == 0) {
            return 0;
        }

        return ($this->total_production_cost ?? 0) / $this->quantity_produced;
    }

    /**
     * Get waste percentage
     */
    public function getWastePercentage(): float
    {
        $totalProduced = $this->quantity_produced + $this->waste_quantity;
        if ($totalProduced == 0) {
            return 0;
        }

        return ($this->waste_quantity / $totalProduced) * 100;
    }

    /**
     * Get quality score based on grade
     */
    public function getQualityScore(): int
    {
        return match($this->quality_grade) {
            'A' => 100,
            'B' => 80,
            'C' => 60,
            'D' => 40,
            default => 0,
        };
    }

    /**
     * Get yield efficiency (actual vs expected yield)
     */
    public function getYieldEfficiency(): ?float
    {
        return $this->yield_percentage;
    }

    /**
     * Check if production is profitable
     */
    public function isProfitable(): bool
    {
        if (!$this->total_production_cost || !$this->product->selling_price) {
            return false;
        }

        $revenue = $this->quantity_produced * $this->product->selling_price;
        return $revenue > $this->total_production_cost;
    }

    /**
     * Get profit margin for this production
     */
    public function getProfitMargin(): float
    {
        if (!$this->total_production_cost || !$this->product->selling_price) {
            return 0;
        }

        $revenue = $this->quantity_produced * $this->product->selling_price;
        if ($revenue == 0) {
            return 0;
        }

        return (($revenue - $this->total_production_cost) / $revenue) * 100;
    }

    /**
     * Check if production is overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast() && $this->status !== 'completed';
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiry(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    // Scopes

    /**
     * Scope for completed productions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for in-progress productions
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for failed productions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope by shift
     */
    public function scopeByShift($query, $shift)
    {
        return $query->where('shift', $shift);
    }

    /**
     * Scope by quality grade
     */
    public function scopeByQualityGrade($query, $grade)
    {
        return $query->where('quality_grade', $grade);
    }

    /**
     * Scope for high quality productions (A or B grade)
     */
    public function scopeHighQuality($query)
    {
        return $query->whereIn('quality_grade', ['A', 'B']);
    }

    /**
     * Scope for productions within date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('production_date', [$startDate, $endDate]);
    }

    /**
     * Scope for recent productions
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('production_date', '>=', now()->subDays($days));
    }

    /**
     * Scope ordered by production date
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('production_date', 'desc');
    }
}
