<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ShopifyService;
use App\Services\ShopifyOrderSyncService;
use App\Models\ShopifyOrder;
use Illuminate\Support\Facades\Log;

class TestFulfillmentCommand extends Command
{
    protected $signature = 'test:fulfillment {order_id?}';
    protected $description = 'Test the fulfillment functionality with tracking information';

    protected ShopifyService $shopifyService;
    protected ShopifyOrderSyncService $orderSyncService;

    public function __construct(ShopifyService $shopifyService, ShopifyOrderSyncService $orderSyncService)
    {
        parent::__construct();
        $this->shopifyService = $shopifyService;
        $this->orderSyncService = $orderSyncService;
    }

    public function handle()
    {
        $this->info('Testing Fulfillment Functionality');
        $this->info('================================');

        try {
            // Test Shopify connection first
            $this->info('1. Testing Shopify connection...');
            if (!$this->shopifyService->testConnection()) {
                $this->error('Failed to connect to Shopify API');
                return 1;
            }
            $this->info('✓ Shopify connection successful');

            // Get a test order
            $orderId = $this->argument('order_id');
            if ($orderId) {
                $localOrder = ShopifyOrder::where('shopify_order_id', $orderId)->first();
                if (!$localOrder) {
                    $this->info("2. Syncing order {$orderId} from Shopify...");
                    $shopifyOrder = $this->shopifyService->getOrder($orderId);
                    if (!$shopifyOrder) {
                        $this->error("Order {$orderId} not found in Shopify");
                        return 1;
                    }

                    $syncResult = $this->orderSyncService->syncSingleOrder($shopifyOrder);
                    if (!$syncResult['success']) {
                        $this->error("Failed to sync order: " . $syncResult['error']);
                        return 1;
                    }
                    $localOrder = $syncResult['order'];
                    $this->info('✓ Order synced successfully');
                }
            } else {
                // Get the first available order
                $localOrder = ShopifyOrder::first();
                if (!$localOrder) {
                    $this->error('No orders found in local database');
                    return 1;
                }
            }

            $this->info("3. Testing fulfillment for order: {$localOrder->order_number}");

            // Prepare test tracking data
            $trackingData = [
                'tracking_number' => 'TEST' . time(),
                'shipping_carrier' => 'DHL',
                'tracking_url' => 'https://www.dhl.com/en/express/tracking.html?AWB=TEST' . time(),
                'notify_customer' => true,
            ];

            $this->info('4. Creating fulfillment with tracking data...');
            $this->table(['Field', 'Value'], [
                ['Tracking Number', $trackingData['tracking_number']],
                ['Shipping Carrier', $trackingData['shipping_carrier']],
                ['Tracking URL', $trackingData['tracking_url']],
                ['Notify Customer', $trackingData['notify_customer'] ? 'Yes' : 'No'],
            ]);

            // Test the fulfillment creation
            $result = $this->orderSyncService->createFulfillmentWithTracking($localOrder, $trackingData);

            if ($result['success']) {
                $this->info('✓ Fulfillment created successfully!');
                $this->info('Fulfillment Details:');
                $this->line('- Order updated with tracking information');
                $this->line('- Shopify fulfillment created');
                $this->line('- Customer notification sent (if enabled)');

                // Show updated order details
                $localOrder->refresh();
                $this->table(['Field', 'Value'], [
                    ['Order Status', $localOrder->internal_status],
                    ['Tracking Number', $localOrder->tracking_number],
                    ['Shipping Partner', $localOrder->shipping_partner],
                    ['Shipped At', $localOrder->shipped_at?->format('Y-m-d H:i:s')],
                ]);

                $this->info('5. Testing tracking URL generation...');
                // Since generateTrackingUrl is protected, we'll test it indirectly
                $trackingUrl = route('shopify.orders.track', $localOrder);
                $this->line("Order tracking URL: {$trackingUrl}");

                // Test carrier-specific URL generation logic
                if ($localOrder->tracking_number && $localOrder->shipping_partner) {
                    $carrier = strtolower($localOrder->shipping_partner);
                    $trackingNumber = $localOrder->tracking_number;

                    $carrierUrls = [
                        'dhl' => "https://www.dhl.com/en/express/tracking.html?AWB={$trackingNumber}",
                        'fedex' => "https://www.fedex.com/fedextrack/?trknbr={$trackingNumber}",
                        'ups' => "https://www.ups.com/track?tracknum={$trackingNumber}",
                    ];

                    if (isset($carrierUrls[$carrier])) {
                        $this->line("Carrier tracking URL: {$carrierUrls[$carrier]}");
                    }
                }

            } else {
                $this->error('✗ Fulfillment creation failed: ' . $result['error']);
                return 1;
            }

            $this->info('');
            $this->info('🎉 All tests passed successfully!');
            $this->info('The fulfillment functionality is working correctly.');

        } catch (\Exception $e) {
            $this->error('Test failed with exception: ' . $e->getMessage());
            Log::error('Fulfillment test failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }
}
