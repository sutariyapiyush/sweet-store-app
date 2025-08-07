<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityCheck extends Model
{
    protected $fillable = [
        'production_log_id',
        'check_type',
        'result',
        'measured_value',
        'expected_value',
        'tolerance',
        'notes',
        'checklist_items',
        'checked_by',
        'checked_at',
        'corrective_action',
    ];

    protected $casts = [
        'measured_value' => 'decimal:3',
        'expected_value' => 'decimal:3',
        'tolerance' => 'decimal:3',
        'checklist_items' => 'array',
        'checked_at' => 'datetime',
    ];

    /**
     * Get the production log this check belongs to
     */
    public function productionLog(): BelongsTo
    {
        return $this->belongsTo(ProductionLog::class);
    }

    /**
     * Get the user who performed the check
     */
    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    /**
     * Check if the measured value is within tolerance
     */
    public function isWithinTolerance(): bool
    {
        if (!$this->measured_value || !$this->expected_value || !$this->tolerance) {
            return true; // If no values set, assume it's within tolerance
        }

        $difference = abs($this->measured_value - $this->expected_value);
        return $difference <= $this->tolerance;
    }

    /**
     * Get the variance from expected value
     */
    public function getVariance(): ?float
    {
        if (!$this->measured_value || !$this->expected_value) {
            return null;
        }

        return $this->measured_value - $this->expected_value;
    }

    /**
     * Get the variance percentage
     */
    public function getVariancePercentage(): ?float
    {
        if (!$this->measured_value || !$this->expected_value || $this->expected_value == 0) {
            return null;
        }

        return (($this->measured_value - $this->expected_value) / $this->expected_value) * 100;
    }

    /**
     * Check if this is a critical failure
     */
    public function isCriticalFailure(): bool
    {
        return $this->result === 'fail' && in_array($this->check_type, ['taste', 'safety', 'contamination']);
    }

    /**
     * Get the overall quality score for checklist items
     */
    public function getChecklistScore(): ?float
    {
        if (!$this->checklist_items || empty($this->checklist_items)) {
            return null;
        }

        $totalItems = count($this->checklist_items);
        $passedItems = 0;

        foreach ($this->checklist_items as $item) {
            if (isset($item['passed']) && $item['passed']) {
                $passedItems++;
            }
        }

        return ($passedItems / $totalItems) * 100;
    }

    /**
     * Scope for failed checks
     */
    public function scopeFailed($query)
    {
        return $query->where('result', 'fail');
    }

    /**
     * Scope for passed checks
     */
    public function scopePassed($query)
    {
        return $query->where('result', 'pass');
    }

    /**
     * Scope for conditional passes
     */
    public function scopeConditionalPass($query)
    {
        return $query->where('result', 'conditional_pass');
    }

    /**
     * Scope by check type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('check_type', $type);
    }

    /**
     * Scope for recent checks
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('checked_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for checks by specific checker
     */
    public function scopeByChecker($query, $userId)
    {
        return $query->where('checked_by', $userId);
    }

    /**
     * Scope ordered by check date
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('checked_at', 'desc');
    }
}
