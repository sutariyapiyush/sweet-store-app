<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class Invoice extends Model
{
    protected $fillable = [
        'shopify_order_id',
        'invoice_number',
        'status',
        'invoice_date',
        'due_date',
        'customer_name',
        'customer_email',
        'customer_phone',
        'billing_address',
        'shipping_address',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_amount',
        'total_amount',
        'currency',
        'paid_amount',
        'balance_due',
        'paid_at',
        'payment_method',
        'payment_reference',
        'pdf_path',
        'qr_code_path',
        'qr_code_data',
        'notes',
        'terms_and_conditions',
        'is_printed',
        'printed_at',
        'is_emailed',
        'emailed_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'paid_at' => 'datetime',
        'qr_code_data' => 'array',
        'is_printed' => 'boolean',
        'printed_at' => 'datetime',
        'is_emailed' => 'boolean',
        'emailed_at' => 'datetime',
    ];

    /**
     * Get the order that owns this invoice
     */
    public function shopifyOrder(): BelongsTo
    {
        return $this->belongsTo(ShopifyOrder::class);
    }

    /**
     * Generate QR code for the invoice
     */
    public function generateQrCode(): string
    {
        if ($this->qr_code_path && Storage::disk('public')->exists($this->qr_code_path)) {
            return $this->qr_code_path;
        }

        $qrData = [
            'invoice_id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'order_id' => $this->shopify_order_id,
            'customer_name' => $this->customer_name,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'invoice_date' => $this->invoice_date->format('Y-m-d'),
        ];

        $qrCodeContent = json_encode($qrData);
        $fileName = "qr_codes/invoice_{$this->id}_{$this->invoice_number}.svg";

        // Generate QR code as SVG (no imagick required)
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($qrCodeContent);

        // Store QR code in public disk
        Storage::disk('public')->put($fileName, $qrCode);

        $this->update([
            'qr_code_path' => $fileName,
            'qr_code_data' => $qrData
        ]);

        return $fileName;
    }

    /**
     * Calculate balance due
     */
    public function calculateBalanceDue(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(float $amount = null, string $paymentMethod = null, string $reference = null): bool
    {
        $amount = $amount ?: $this->total_amount;

        $this->update([
            'paid_amount' => $amount,
            'balance_due' => $this->total_amount - $amount,
            'status' => $amount >= $this->total_amount ? 'paid' : 'partially_paid',
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $reference,
        ]);

        return true;
    }

    /**
     * Mark invoice as sent
     */
    public function markAsSent(): bool
    {
        return $this->update([
            'status' => 'sent',
            'is_emailed' => true,
            'emailed_at' => now(),
        ]);
    }

    /**
     * Mark invoice as printed
     */
    public function markAsPrinted(): bool
    {
        return $this->update([
            'is_printed' => true,
            'printed_at' => now(),
        ]);
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date &&
               $this->due_date->isPast() &&
               $this->status !== 'paid' &&
               $this->status !== 'cancelled';
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted balance due
     */
    public function getFormattedBalanceDueAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->balance_due, 2);
    }

    /**
     * Get days until due
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Scope for invoices by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', ['paid', 'cancelled']);
    }

    /**
     * Scope for paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for unpaid invoices
     */
    public function scopeUnpaid($query)
    {
        return $query->whereNotIn('status', ['paid', 'cancelled']);
    }

    /**
     * Scope for recent invoices
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
