<?php

namespace App\Services;

use App\Models\ShopifyOrder;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ShopifyOrderSyncService
{
    protected ShopifyService $shopifyService;

    public function __construct(ShopifyService $shopifyService)
    {
        $this->shopifyService = $shopifyService;
    }

    /**
     * Sync all orders from Shopify
     */
    public function syncAllOrders(): array
    {
        $results = [
            'total' => 0,
            'synced' => 0,
            'errors' => 0,
            'created' => 0,
            'updated' => 0,
        ];

        try {
            $orders = $this->shopifyService->getOrders();
            $results['total'] = count($orders);

            foreach ($orders as $orderData) {
                try {
                    $result = $this->syncSingleOrder($orderData);

                    if ($result['success']) {
                        $results['synced']++;
                        if ($result['action'] === 'created') {
                            $results['created']++;
                        } else {
                            $results['updated']++;
                        }
                    } else {
                        $results['errors']++;
                    }
                } catch (\Exception $e) {
                    $results['errors']++;
                    Log::error("Error syncing order {$orderData['id']}: " . $e->getMessage());
                }
            }

            Log::info('Order sync completed', $results);
            return $results;

        } catch (\Exception $e) {
            Log::error('Failed to sync orders from Shopify: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync single order from Shopify
     */
    public function syncSingleOrder(array $orderData): array
    {
        try {
            DB::beginTransaction();

            $shopifyOrder = ShopifyOrder::where('shopify_order_id', $orderData['id'])->first();
            $isNew = !$shopifyOrder;

            if (!$shopifyOrder) {
                $shopifyOrder = new ShopifyOrder();
            }

            // Map Shopify order data
            $shopifyOrder->fill([
                'shopify_order_id' => $orderData['id'],
                'order_number' => $orderData['order_number'],
                'name' => $orderData['name'],
                'customer_email' => $orderData['customer']['email'] ?? null,
                'customer_phone' => $orderData['customer']['phone'] ?? null,
                'customer_data' => $orderData['customer'] ?? [],
                'financial_status' => $orderData['financial_status'],
                'fulfillment_status' => $orderData['fulfillment_status'],
                'total_price' => $orderData['total_price'],
                'subtotal_price' => $orderData['subtotal_price'],
                'total_tax' => $orderData['total_tax'],
                'total_discounts' => $orderData['total_discounts'],
                'shipping_price' => $orderData['total_shipping_price_set']['shop_money']['amount'] ?? 0,
                'currency' => $orderData['currency'],
                'billing_address' => $orderData['billing_address'],
                'shipping_address' => $orderData['shipping_address'],
                'internal_status' => $this->mapToInternalStatus($orderData['financial_status'], $orderData['fulfillment_status']),
                'shopify_created_at' => \Carbon\Carbon::parse($orderData['created_at']),
                'shopify_updated_at' => \Carbon\Carbon::parse($orderData['updated_at']),
                'processed_at' => $orderData['processed_at'] ? \Carbon\Carbon::parse($orderData['processed_at']) : null,
                'tags' => !empty($orderData['tags']) ? explode(', ', $orderData['tags']) : [],
                'note' => $orderData['note'],
                'note_attributes' => $orderData['note_attributes'] ?? [],
                'discount_codes' => $orderData['discount_codes'] ?? [],
                'tax_lines' => $orderData['tax_lines'] ?? [],
                'shipping_lines' => $orderData['shipping_lines'] ?? [],
            ]);

            $shopifyOrder->save();

            // Sync order items
            $this->syncOrderItems($shopifyOrder, $orderData['line_items'] ?? []);

            // Generate invoice if order is paid and new
            if ($isNew && $orderData['financial_status'] === 'paid') {
                $this->generateInvoice($shopifyOrder);
            }

            // Mark as synced
            $shopifyOrder->update([
                'is_synced' => true,
                'synced_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'action' => $isNew ? 'created' : 'updated',
                'order' => $shopifyOrder,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to sync order {$orderData['id']}: " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sync order items
     */
    protected function syncOrderItems(ShopifyOrder $shopifyOrder, array $lineItems): void
    {
        // Clear existing items
        $shopifyOrder->orderItems()->delete();

        foreach ($lineItems as $lineItem) {
            // Try to find matching local product
            $localProduct = null;
            if (!empty($lineItem['sku'])) {
                $localProduct = Product::where('sku', $lineItem['sku'])->first();
            }

            $orderItem = new OrderItem([
                'shopify_order_id' => $shopifyOrder->id,
                'product_id' => $localProduct?->id,
                'shopify_line_item_id' => $lineItem['id'],
                'shopify_product_id' => $lineItem['product_id'],
                'shopify_variant_id' => $lineItem['variant_id'],
                'product_title' => $lineItem['title'],
                'variant_title' => $lineItem['variant_title'],
                'sku' => $lineItem['sku'],
                'vendor' => $lineItem['vendor'],
                'quantity' => $lineItem['quantity'],
                'price' => $lineItem['price'],
                'total_discount' => $lineItem['total_discount'],
                'line_total' => ($lineItem['price'] * $lineItem['quantity']) - $lineItem['total_discount'],
                'properties' => $lineItem['properties'] ?? [],
                'variant_options' => $lineItem['variant_options'] ?? [],
                'fulfillable_quantity' => $lineItem['fulfillable_quantity'],
                'fulfilled_quantity' => $lineItem['fulfilled_quantity'] ?? 0,
                'fulfillment_status' => 'pending',
                'taxable' => $lineItem['taxable'],
                'tax_lines' => $lineItem['tax_lines'] ?? [],
                'grams' => $lineItem['grams'],
                'requires_shipping' => $lineItem['requires_shipping'],
                'gift_card' => $lineItem['gift_card'],
            ]);

            $orderItem->save();
        }
    }

    /**
     * Generate invoice for order
     */
    protected function generateInvoice(ShopifyOrder $shopifyOrder): Invoice
    {
        // Extract customer name with multiple fallback strategies
        $customerName = $shopifyOrder->customer_name;

        // Try customer_data first
        if (!$customerName && $shopifyOrder->customer_data && is_array($shopifyOrder->customer_data)) {
            $customer = $shopifyOrder->customer_data;
            $firstName = $customer['first_name'] ?? '';
            $lastName = $customer['last_name'] ?? '';
            $customerName = trim($firstName . ' ' . $lastName);
        }

        // Try billing address
        if (!$customerName && $shopifyOrder->billing_address && is_array($shopifyOrder->billing_address)) {
            $billing = $shopifyOrder->billing_address;
            $firstName = $billing['first_name'] ?? '';
            $lastName = $billing['last_name'] ?? '';
            $customerName = trim($firstName . ' ' . $lastName);
        }

        // Try shipping address
        if (!$customerName && $shopifyOrder->shipping_address && is_array($shopifyOrder->shipping_address)) {
            $shipping = $shopifyOrder->shipping_address;
            $firstName = $shipping['first_name'] ?? '';
            $lastName = $shipping['last_name'] ?? '';
            $customerName = trim($firstName . ' ' . $lastName);
        }

        // Use email as fallback
        if (!$customerName && $shopifyOrder->customer_email) {
            $customerName = explode('@', $shopifyOrder->customer_email)[0];
        }

        // Final fallback
        if (!$customerName || trim($customerName) === '') {
            $customerName = 'Customer #' . $shopifyOrder->id;
        }

        $invoice = new Invoice([
            'shopify_order_id' => $shopifyOrder->id,
            'invoice_number' => $shopifyOrder->generateInvoiceNumber(),
            'status' => 'draft',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'customer_name' => $customerName,
            'customer_email' => $shopifyOrder->customer_email ?: 'customer@example.com',
            'customer_phone' => $shopifyOrder->customer_phone,
            'billing_address' => $shopifyOrder->billing_address,
            'shipping_address' => $shopifyOrder->shipping_address,
            'subtotal' => $shopifyOrder->subtotal_price ?: 0,
            'tax_amount' => $shopifyOrder->total_tax ?: 0,
            'discount_amount' => $shopifyOrder->total_discounts ?: 0,
            'shipping_amount' => $shopifyOrder->shipping_price ?: 0,
            'total_amount' => $shopifyOrder->total_price ?: 0,
            'currency' => $shopifyOrder->currency ?: 'USD',
            'balance_due' => $shopifyOrder->total_price ?: 0,
        ]);

        $invoice->save();

        // Generate QR code for invoice
        $invoice->generateQrCode();

        // Update order with invoice info
        $shopifyOrder->update([
            'invoice_generated_at' => now(),
        ]);

        return $invoice;
    }

    /**
     * Map Shopify status to internal status
     */
    protected function mapToInternalStatus(string $financialStatus, ?string $fulfillmentStatus): string
    {
        if ($financialStatus === 'paid') {
            if ($fulfillmentStatus === 'fulfilled') {
                return 'delivered';
            } elseif ($fulfillmentStatus === 'partial') {
                return 'shipped';
            } else {
                return 'processing';
            }
        } elseif ($financialStatus === 'pending') {
            return 'pending';
        } elseif ($financialStatus === 'refunded') {
            return 'refunded';
        } elseif ($financialStatus === 'voided') {
            return 'cancelled';
        }

        return 'pending';
    }

    /**
     * Update order status in Shopify
     */
    public function updateOrderStatusInShopify(ShopifyOrder $order, string $status): bool
    {
        try {
            $shopifyStatus = $this->mapToShopifyStatus($status);

            if ($status === 'shipped') {
                // Create fulfillment with tracking information
                $fulfillmentData = [
                    'location_id' => $this->shopifyService->getPrimaryLocationId(),
                    'tracking_number' => $order->tracking_number,
                    'tracking_company' => $order->shipping_partner ?: 'Other',
                    'tracking_url' => $this->generateTrackingUrl($order),
                    'notify_customer' => true,
                    'line_items' => $order->orderItems->map(function ($item) {
                        return [
                            'id' => (int)$item->shopify_line_item_id,
                            'quantity' => $item->quantity,
                        ];
                    })->toArray(),
                ];

                $fulfillment = $this->shopifyService->createFulfillment(
                    $order->shopify_order_id,
                    $fulfillmentData
                );

                if ($fulfillment) {
                    Log::info("Created fulfillment for order {$order->shopify_order_id}", [
                        'tracking_number' => $order->tracking_number,
                        'tracking_company' => $order->shipping_partner,
                        'tracking_url' => $fulfillmentData['tracking_url']
                    ]);
                    return true;
                }
            }

            return false;

        } catch (\Exception $e) {
            Log::error("Failed to update order status in Shopify for order {$order->shopify_order_id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create fulfillment with tracking information
     */
    public function createFulfillmentWithTracking(ShopifyOrder $order, array $trackingData): array
    {
        try {
            // Update local order with tracking information
            $order->update([
                'tracking_number' => $trackingData['tracking_number'] ?? null,
                'shipping_partner' => $trackingData['shipping_carrier'] ?? 'Other',
                'shipped_at' => now(),
                'internal_status' => 'shipped'
            ]);

            // Prepare fulfillment data for Shopify
            $fulfillmentData = [
                'location_id' => $this->shopifyService->getPrimaryLocationId(),
                // 'tracking_number' => $trackingData['tracking_number'] ?? null,
                // 'tracking_company' => $trackingData['shipping_carrier'] ?? 'Other',
                // 'tracking_url' => $trackingData['tracking_url'] ?? $this->generateTrackingUrl($order),
                // 'notify_customer' => $trackingData['notify_customer'] ?? true,
                'line_items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => (int)$item->shopify_line_item_id,
                        'quantity' => $item->quantity,
                    ];
                })->toArray(),
            ];

            // Create fulfillment in Shopify
            $fulfillment = $this->shopifyService->createFulfillment(
                $order->shopify_order_id,
                $fulfillmentData
            );

            if ($fulfillment) {
                Log::info("Successfully created fulfillment with tracking", [
                    'order_id' => $order->shopify_order_id,
                    'tracking_number' => $trackingData['tracking_number'],
                    'shipping_carrier' => $trackingData['shipping_carrier'],
                    'tracking_url' => $fulfillmentData['tracking_url'],
                    'fulfillment_id' => $fulfillment['id'] ?? null
                ]);

                return [
                    'success' => true,
                    'fulfillment' => $fulfillment,
                    'message' => 'Order fulfilled successfully with tracking information'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to create fulfillment in Shopify'
                ];
            }

        } catch (\Exception $e) {
            Log::error("Failed to create fulfillment with tracking for order {$order->shopify_order_id}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to create fulfillment: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update existing fulfillment with tracking information
     */
    public function updateFulfillmentTracking(ShopifyOrder $order, string $fulfillmentId, array $trackingData): array
    {
        try {
            // Update local order
            $order->update([
                'tracking_number' => $trackingData['tracking_number'] ?? $order->tracking_number,
                'shipping_partner' => $trackingData['shipping_carrier'] ?? $order->shipping_partner,
            ]);

            // Prepare update data
            $updateData = [
                'tracking_number' => $trackingData['tracking_number'] ?? null,
                'tracking_company' => $trackingData['shipping_carrier'] ?? 'Other',
                'tracking_url' => $trackingData['tracking_url'] ?? $this->generateTrackingUrl($order),
                'notify_customer' => $trackingData['notify_customer'] ?? true,
            ];

            // Update fulfillment in Shopify
            $fulfillment = $this->shopifyService->updateFulfillment(
                $order->shopify_order_id,
                $fulfillmentId,
                $updateData
            );

            if ($fulfillment) {
                Log::info("Successfully updated fulfillment tracking", [
                    'order_id' => $order->shopify_order_id,
                    'fulfillment_id' => $fulfillmentId,
                    'tracking_number' => $trackingData['tracking_number'],
                    'shipping_carrier' => $trackingData['shipping_carrier']
                ]);

                return [
                    'success' => true,
                    'fulfillment' => $fulfillment,
                    'message' => 'Tracking information updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to update fulfillment in Shopify'
                ];
            }

        } catch (\Exception $e) {
            Log::error("Failed to update fulfillment tracking for order {$order->shopify_order_id}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to update tracking: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate tracking URL for order
     */
    protected function generateTrackingUrl(ShopifyOrder $order): ?string
    {
        if (!$order->tracking_number) {
            return route('shopify.orders.track', $order);
        }

        // Generate carrier-specific tracking URLs
        $carrier = strtolower($order->shipping_partner ?? '');
        $trackingNumber = $order->tracking_number;

        $carrierUrls = [
            'fedex' => "https://www.fedex.com/fedextrack/?trknbr={$trackingNumber}",
            'ups' => "https://www.ups.com/track?tracknum={$trackingNumber}",
            'dhl' => "https://www.dhl.com/en/express/tracking.html?AWB={$trackingNumber}",
            'usps' => "https://tools.usps.com/go/TrackConfirmAction?tLabels={$trackingNumber}",
            'aramex' => "https://www.aramex.com/track/results?ShipmentNumber={$trackingNumber}",
            'bluedart' => "https://www.bluedart.com/web/guest/trackdartresult?trackFor=0&trackNo={$trackingNumber}",
            'dtdc' => "https://www.dtdc.in/tracking/tracking_results.asp?Ttype=awb_no&strTrkNo={$trackingNumber}",
        ];

        return $carrierUrls[$carrier] ?? route('shopify.orders.track', $order);
    }

    /**
     * Map internal status to Shopify status
     */
    protected function mapToShopifyStatus(string $internalStatus): array
    {
        $mapping = config('shopify.order_status_mapping', []);

        return [
            'financial_status' => $mapping[$internalStatus] ?? $internalStatus,
            'fulfillment_status' => $internalStatus === 'shipped' ? 'fulfilled' : null,
        ];
    }

    /**
     * Get sync statistics
     */
    public function getSyncStats(): array
    {
        return [
            'total_orders' => ShopifyOrder::count(),
            'pending_orders' => ShopifyOrder::byStatus('pending')->count(),
            'processing_orders' => ShopifyOrder::byStatus('processing')->count(),
            'shipped_orders' => ShopifyOrder::byStatus('shipped')->count(),
            'delivered_orders' => ShopifyOrder::byStatus('delivered')->count(),
            'paid_orders' => ShopifyOrder::paid()->count(),
            'recent_orders' => ShopifyOrder::recent()->count(),
            'needs_sync' => ShopifyOrder::needsSync()->count(),
        ];
    }

    /**
     * Sync recent orders (last 24 hours)
     */
    public function syncRecentOrders(): array
    {
        try {
            $since = now()->subDay()->toISOString();
            $orders = $this->shopifyService->getOrders(['created_at_min' => $since]);

            $results = [
                'total' => count($orders),
                'synced' => 0,
                'errors' => 0,
            ];

            foreach ($orders as $orderData) {
                try {
                    $result = $this->syncSingleOrder($orderData);
                    if ($result['success']) {
                        $results['synced']++;
                    } else {
                        $results['errors']++;
                    }
                } catch (\Exception $e) {
                    $results['errors']++;
                    Log::error("Error syncing recent order {$orderData['id']}: " . $e->getMessage());
                }
            }

            return $results;

        } catch (\Exception $e) {
            Log::error('Failed to sync recent orders: ' . $e->getMessage());
            throw $e;
        }
    }
}
