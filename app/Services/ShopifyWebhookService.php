<?php

namespace App\Services;

use App\Models\ShopifyOrder;
use App\Models\ShopifyProduct;
use Illuminate\Support\Facades\Log;

class ShopifyWebhookService
{
    protected ShopifyOrderSyncService $orderSyncService;
    protected ShopifyProductSyncService $productSyncService;

    public function __construct(
        ShopifyOrderSyncService $orderSyncService,
        ShopifyProductSyncService $productSyncService
    ) {
        $this->orderSyncService = $orderSyncService;
        $this->productSyncService = $productSyncService;
    }

    /**
     * Handle webhook based on topic
     */
    public function handleWebhook(string $topic, array $data): bool
    {
        try {
            Log::info("Processing webhook: {$topic}", ['data' => $data]);

            switch ($topic) {
                case 'orders/create':
                case 'orders/updated':
                case 'orders/paid':
                    return $this->handleOrderWebhook($data);

                case 'orders/cancelled':
                    return $this->handleOrderCancellation($data);

                case 'orders/fulfilled':
                    return $this->handleOrderFulfillment($data);

                case 'products/create':
                case 'products/update':
                    return $this->handleProductWebhook($data);

                case 'products/delete':
                    return $this->handleProductDeletion($data);

                case 'app/uninstalled':
                    return $this->handleAppUninstall($data);

                default:
                    Log::warning("Unhandled webhook topic: {$topic}");
                    return true; // Return true to acknowledge receipt
            }

        } catch (\Exception $e) {
            Log::error("Error processing webhook {$topic}: " . $e->getMessage(), [
                'data' => $data,
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * Handle order webhooks
     */
    protected function handleOrderWebhook(array $orderData): bool
    {
        try {
            $result = $this->orderSyncService->syncSingleOrder($orderData);

            if ($result['success']) {
                Log::info("Order webhook processed successfully", [
                    'order_id' => $orderData['id'],
                    'action' => $result['action']
                ]);
                return true;
            } else {
                Log::error("Failed to process order webhook", [
                    'order_id' => $orderData['id'],
                    'error' => $result['error']
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error("Error handling order webhook: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle order cancellation
     */
    protected function handleOrderCancellation(array $orderData): bool
    {
        try {
            $order = ShopifyOrder::where('shopify_order_id', $orderData['id'])->first();

            if ($order) {
                $order->updateStatus('cancelled', false); // Don't sync back to Shopify
                Log::info("Order cancelled via webhook", ['order_id' => $orderData['id']]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Error handling order cancellation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle order fulfillment
     */
    protected function handleOrderFulfillment(array $orderData): bool
    {
        try {
            $order = ShopifyOrder::where('shopify_order_id', $orderData['id'])->first();

            if ($order) {
                // Check fulfillment status and update accordingly
                if (isset($orderData['fulfillment_status'])) {
                    if ($orderData['fulfillment_status'] === 'fulfilled') {
                        $order->updateStatus('delivered', false);
                    } elseif ($orderData['fulfillment_status'] === 'partial') {
                        $order->updateStatus('shipped', false);
                    }
                }

                Log::info("Order fulfillment updated via webhook", [
                    'order_id' => $orderData['id'],
                    'fulfillment_status' => $orderData['fulfillment_status'] ?? 'unknown'
                ]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Error handling order fulfillment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle product webhooks
     */
    protected function handleProductWebhook(array $productData): bool
    {
        try {
            $result = $this->productSyncService->syncSingleProduct($productData);

            if ($result['success']) {
                Log::info("Product webhook processed successfully", [
                    'product_id' => $productData['id'],
                    'action' => $result['action']
                ]);
                return true;
            } else {
                Log::error("Failed to process product webhook", [
                    'product_id' => $productData['id'],
                    'error' => $result['error']
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error("Error handling product webhook: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle product deletion
     */
    protected function handleProductDeletion(array $productData): bool
    {
        try {
            $product = ShopifyProduct::where('shopify_product_id', $productData['id'])->first();

            if ($product) {
                // Mark as inactive instead of deleting
                $product->update(['status' => 'archived']);
                Log::info("Product archived via webhook", ['product_id' => $productData['id']]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Error handling product deletion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle app uninstall
     */
    protected function handleAppUninstall(array $data): bool
    {
        try {
            // Log the uninstall event
            Log::warning("Shopify app uninstalled", ['shop' => $data['domain'] ?? 'unknown']);

            // Here you could:
            // - Clean up webhooks
            // - Mark shop as inactive
            // - Send notification to admin
            // - Archive data

            return true;

        } catch (\Exception $e) {
            Log::error("Error handling app uninstall: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Setup required webhooks
     */
    public function setupWebhooks(ShopifyService $shopifyService): array
    {
        $webhooks = [
            [
                'topic' => 'orders/create',
                'address' => route('shopify.webhook'),
                'format' => 'json'
            ],
            [
                'topic' => 'orders/updated',
                'address' => route('shopify.webhook'),
                'format' => 'json'
            ],
            [
                'topic' => 'orders/paid',
                'address' => route('shopify.webhook'),
                'format' => 'json'
            ],
            [
                'topic' => 'orders/cancelled',
                'address' => route('shopify.webhook'),
                'format' => 'json'
            ],
            [
                'topic' => 'orders/fulfilled',
                'address' => route('shopify.webhook'),
                'format' => 'json'
            ],
            [
                'topic' => 'products/create',
                'address' => route('shopify.webhook'),
                'format' => 'json'
            ],
            [
                'topic' => 'products/update',
                'address' => route('shopify.webhook'),
                'format' => 'json'
            ],
            [
                'topic' => 'products/delete',
                'address' => route('shopify.webhook'),
                'format' => 'json'
            ],
        ];

        $results = [];

        foreach ($webhooks as $webhook) {
            try {
                $result = $shopifyService->createWebhook($webhook);

                if ($result) {
                    $results[] = [
                        'topic' => $webhook['topic'],
                        'success' => true,
                        'webhook_id' => $result['id']
                    ];
                    Log::info("Webhook created successfully", ['topic' => $webhook['topic']]);
                } else {
                    $results[] = [
                        'topic' => $webhook['topic'],
                        'success' => false,
                        'error' => 'Failed to create webhook'
                    ];
                    Log::error("Failed to create webhook", ['topic' => $webhook['topic']]);
                }

            } catch (\Exception $e) {
                $results[] = [
                    'topic' => $webhook['topic'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
                Log::error("Error creating webhook: " . $e->getMessage(), ['topic' => $webhook['topic']]);
            }
        }

        return $results;
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhook(string $data, string $signature): bool
    {
        $calculatedSignature = base64_encode(
            hash_hmac('sha256', $data, config('shopify.webhook_secret'), true)
        );

        return hash_equals($signature, $calculatedSignature);
    }

    /**
     * Get webhook statistics
     */
    public function getWebhookStats(): array
    {
        // This would be implemented with a webhook_logs table
        // For now, return basic stats
        return [
            'total_webhooks_received' => 0,
            'successful_webhooks' => 0,
            'failed_webhooks' => 0,
            'last_webhook_received' => null,
        ];
    }
}
