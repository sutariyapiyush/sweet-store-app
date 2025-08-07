<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ProductionSchedule extends Model
{
    protected $fillable = [
        'product_id',
        'scheduled_date',
        'scheduled_time',
        'planned_quantity',
        'priority',
        'status',
        'assigned_user_id',
        'notes',
        'required_materials',
        'estimated_duration_minutes',
        'due_date',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime:H:i',
        'due_date' => 'date',
        'planned_quantity' => 'decimal:2',
        'required_materials' => 'array',
        'estimated_duration_minutes' => 'integer',
    ];

    /**
     * Get the product to be produced
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the assigned user
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Check if the schedule is overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        return $this->due_date->isPast() && !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Check if the schedule is due today
     */
    public function isDueToday(): bool
    {
        return $this->scheduled_date->isToday();
    }

    /**
     * Check if the schedule is urgent
     */
    public function isUrgent(): bool
    {
        return $this->priority === 'urgent' || $this->isOverdue();
    }

    /**
     * Get the estimated completion time
     */
    public function getEstimatedCompletionTime(): ?Carbon
    {
        if (!$this->scheduled_time || !$this->estimated_duration_minutes) {
            return null;
        }

        return $this->scheduled_time->addMinutes($this->estimated_duration_minutes);
    }

    /**
     * Calculate required materials based on planned quantity
     */
    public function calculateRequiredMaterials(): array
    {
        $materials = [];

        foreach ($this->product->rawMaterials as $rawMaterial) {
            $requiredQuantity = $rawMaterial->pivot->quantity_required * $this->planned_quantity;
            $materials[] = [
                'raw_material_id' => $rawMaterial->id,
                'name' => $rawMaterial->name,
                'required_quantity' => $requiredQuantity,
                'available_quantity' => $rawMaterial->quantity_in_stock,
                'unit' => $rawMaterial->unit,
                'sufficient' => $rawMaterial->quantity_in_stock >= $requiredQuantity,
            ];
        }

        return $materials;
    }

    /**
     * Check if all required materials are available
     */
    public function hasSufficientMaterials(): bool
    {
        foreach ($this->calculateRequiredMaterials() as $material) {
            if (!$material['sufficient']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Scope for today's schedules
     */
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', today());
    }

    /**
     * Scope for upcoming schedules
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_date', '>=', today())
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope for overdue schedules
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', today())
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope ordered by priority and date
     */
    public function scopeOrdered($query)
    {
        return $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
                    ->orderBy('scheduled_date')
                    ->orderBy('scheduled_time');
    }
}
