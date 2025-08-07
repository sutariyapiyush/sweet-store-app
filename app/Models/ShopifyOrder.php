<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class ShopifyOrder extends Model
{
    protected $fillable = [
        'shopify_order_id',
        'order_number',
        'name',
        'customer_email',
        'customer_phone',
        'customer_data',
        'financial_status',
        'fulfillment_status',
        'total_price',
        'subtotal_price',
        'total_tax',
        'total_discounts',
        'shipping_price',
        'currency',
        'billing_address',
        'shipping_address',
        'internal_status',
        'is_synced',
        'synced_at',
        'invoice_number',
        'qr_code_path',
        'invoice_generated_at',
        'tracking_number',
        'shipping_partner',
        'shipped_at',
        'shopify_created_at',
        'shopify_updated_at',
        'processed_at',
        'tags',
        'note',
        'note_attributes',
        'discount_codes',
        'tax_lines',
        'shipping_lines',
    ];

    protected $casts = [
        'customer_data' => 'array',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
        'invoice_generated_at' => 'datetime',
        'shipped_at' => 'datetime',
        'shopify_created_at' => 'datetime',
        'shopify_updated_at' => 'datetime',
        'processed_at' => 'datetime',
        'tags' => 'array',
        'note_attributes' => 'array',
        'discount_codes' => 'array',
        'tax_lines' => 'array',
        'shipping_lines' => 'array',
        'total_price' => 'decimal:2',
        'subtotal_price' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_discounts' => 'decimal:2',
        'shipping_price' => 'decimal:2',
    ];

    /**
     * Get the order items for this order
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the invoice for this order
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Generate invoice number
     */
    public function generateInvoiceNumber(): string
    {
        if ($this->invoice_number) {
            return $this->invoice_number;
        }

        $prefix = 'INV';
        $date = now()->format('Ymd');
        $sequence = str_pad($this->id, 4, '0', STR_PAD_LEFT);

        $invoiceNumber = "{$prefix}-{$date}-{$sequence}";

        $this->update(['invoice_number' => $invoiceNumber]);

        return $invoiceNumber;
    }

    /**
     * Generate QR code for the order
     */
    public function generateQrCode(): string
    {
        if ($this->qr_code_path && Storage::disk('public')->exists($this->qr_code_path)) {
            return $this->qr_code_path;
        }

        $qrData = [
            'order_id' => $this->id,
            'shopify_order_id' => $this->shopify_order_id,
            'order_number' => $this->order_number,
            'invoice_number' => $this->invoice_number ?: $this->generateInvoiceNumber(),
            'total' => $this->total_price,
            'status' => $this->internal_status,
        ];

        $qrCodeContent = json_encode($qrData);
        $fileName = "qr_codes/order_{$this->id}_{$this->order_number}.svg";

        // Generate QR code as SVG (no imagick required)
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($qrCodeContent);

        // Store QR code in public disk
        Storage::disk('public')->put($fileName, $qrCode);

        $this->update(['qr_code_path' => $fileName]);

        return $fileName;
    }

    /**
     * Update internal status and sync to Shopify if needed
     */
    public function updateStatus(string $status, bool $syncToShopify = true): bool
    {
        $this->update(['internal_status' => $status]);

        if ($syncToShopify) {
            // This will be implemented in the sync service
            // ShopifyOrderSyncService::updateOrderStatus($this, $status);
        }

        return true;
    }

    /**
     * Check if order is ready for shipping
     */
    public function isReadyForShipping(): bool
    {
        return $this->internal_status === 'processing' &&
               $this->financial_status === 'paid' &&
               $this->invoice_number !== null;
    }

    /**
     * Mark order as shipped
     */
    public function markAsShipped(): bool
    {
        return $this->updateStatus('shipped');
    }

    /**
     * Mark order as delivered
     */
    public function markAsDelivered(): bool
    {
        return $this->updateStatus('delivered');
    }

    /**
     * Get customer name from customer data or billing address
     */
    public function getCustomerNameAttribute(): ?string
    {
        if ($this->customer_data && isset($this->customer_data['first_name'])) {
            return trim($this->customer_data['first_name'] . ' ' . ($this->customer_data['last_name'] ?? ''));
        }

        if ($this->billing_address && isset($this->billing_address['first_name'])) {
            return trim($this->billing_address['first_name'] . ' ' . ($this->billing_address['last_name'] ?? ''));
        }

        return null;
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->total_price, 2);
    }

    /**
     * Scope for orders by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('internal_status', $status);
    }

    /**
     * Scope for orders that need sync
     */
    public function scopeNeedsSync($query)
    {
        return $query->where('is_synced', false);
    }

    /**
     * Scope for orders by financial status
     */
    public function scopeByFinancialStatus($query, string $status)
    {
        return $query->where('financial_status', $status);
    }

    /**
     * Scope for paid orders
     */
    public function scopePaid($query)
    {
        return $query->where('financial_status', 'paid');
    }

    /**
     * Scope for recent orders
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
