<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ShopifyService;
use App\Services\ShopifyProductSyncService;
use App\Services\ShopifyOrderSyncService;
use App\Services\ShopifyWebhookService;

class ShopifyTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:test {action=all : The action to test (connection|products|orders|webhooks|all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Shopify integration functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        $this->info('🛍️  Testing Shopify Integration...');
        $this->newLine();

        switch ($action) {
            case 'connection':
                $this->testConnection();
                break;
            case 'products':
                $this->testProductSync();
                break;
            case 'orders':
                $this->testOrderSync();
                break;
            case 'webhooks':
                $this->testWebhooks();
                break;
            case 'all':
            default:
                $this->testConnection();
                $this->testProductSync();
                $this->testOrderSync();
                $this->testWebhooks();
                break;
        }

        $this->newLine();
        $this->info('✅ Shopify integration test completed!');
    }

    protected function testConnection()
    {
        $this->info('🔗 Testing Shopify Connection...');

        try {
            $shopifyService = app(ShopifyService::class);
            $isConnected = $shopifyService->testConnection();

            if ($isConnected) {
                $shopInfo = $shopifyService->getShopInfo();
                $this->line("✅ Connected to: {$shopInfo['name']} ({$shopInfo['domain']})");
            } else {
                $this->error('❌ Failed to connect to Shopify');
            }
        } catch (\Exception $e) {
            $this->error('❌ Connection error: ' . $e->getMessage());
        }

        $this->newLine();
    }

    protected function testProductSync()
    {
        $this->info('📦 Testing Product Sync...');

        try {
            $productSyncService = app(ShopifyProductSyncService::class);

            // Get sync stats
            $stats = $productSyncService->getSyncStats();
            $this->line("📊 Product Stats:");
            $this->line("   - Total Shopify Products: {$stats['total_shopify_products']}");
            $this->line("   - Synced to Local: {$stats['synced_to_local']}");
            $this->line("   - Needs Sync: {$stats['needs_sync']}");
            $this->line("   - With Errors: {$stats['with_errors']}");

            // Test sync (limit to 5 products for testing)
            if ($this->confirm('Do you want to sync a few products for testing?')) {
                $this->line('🔄 Syncing products...');
                $results = $productSyncService->syncAllProducts();
                $this->line("✅ Sync completed: {$results['synced']}/{$results['total']} products synced");
                if ($results['errors'] > 0) {
                    $this->warn("⚠️  {$results['errors']} errors occurred");
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ Product sync error: ' . $e->getMessage());
        }

        $this->newLine();
    }

    protected function testOrderSync()
    {
        $this->info('🛒 Testing Order Sync...');

        try {
            $orderSyncService = app(ShopifyOrderSyncService::class);

            // Get sync stats
            $stats = $orderSyncService->getSyncStats();
            $this->line("📊 Order Stats:");
            $this->line("   - Total Orders: {$stats['total_orders']}");
            $this->line("   - Pending: {$stats['pending_orders']}");
            $this->line("   - Processing: {$stats['processing_orders']}");
            $this->line("   - Shipped: {$stats['shipped_orders']}");
            $this->line("   - Delivered: {$stats['delivered_orders']}");

            // Test recent sync
            if ($this->confirm('Do you want to sync recent orders for testing?')) {
                $this->line('🔄 Syncing recent orders...');
                $results = $orderSyncService->syncRecentOrders();
                $this->line("✅ Sync completed: {$results['synced']}/{$results['total']} orders synced");
                if ($results['errors'] > 0) {
                    $this->warn("⚠️  {$results['errors']} errors occurred");
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ Order sync error: ' . $e->getMessage());
        }

        $this->newLine();
    }

    protected function testWebhooks()
    {
        $this->info('🔗 Testing Webhooks...');

        try {
            $shopifyService = app(ShopifyService::class);
            $webhookService = app(ShopifyWebhookService::class);

            // List existing webhooks
            $webhooks = $shopifyService->getWebhooks();
            $this->line("📋 Existing webhooks: " . count($webhooks));

            foreach ($webhooks as $webhook) {
                $this->line("   - {$webhook['topic']} -> {$webhook['address']}");
            }

            // Setup webhooks if requested
            if ($this->confirm('Do you want to setup/update webhooks?')) {
                $this->line('🔄 Setting up webhooks...');
                $results = $webhookService->setupWebhooks($shopifyService);

                $successful = collect($results)->where('success', true)->count();
                $failed = collect($results)->where('success', false)->count();

                $this->line("✅ Webhooks setup: {$successful} successful, {$failed} failed");

                if ($failed > 0) {
                    $this->warn('⚠️  Some webhooks failed to setup:');
                    foreach ($results as $result) {
                        if (!$result['success']) {
                            $this->line("   - {$result['topic']}: {$result['error']}");
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ Webhook error: ' . $e->getMessage());
        }

        $this->newLine();
    }
}
