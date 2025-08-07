<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShopifyOrder;
use App\Services\ShippingLabelService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class TestQrCodeGeneration extends Command
{
    protected $signature = 'test:qr-generation';
    protected $description = 'Test QR code generation functionality';

    public function handle()
    {
        $this->info('Testing QR Code Generation...');

        try {
            // Test basic QR code generation
            $this->info('1. Testing basic QR code generation...');
            $testData = ['test' => 'data', 'timestamp' => now()->toISOString()];
            $qrCode = QrCode::format('svg')->size(200)->generate(json_encode($testData));
            $testPath = 'qr_codes/test_qr.svg';
            Storage::put($testPath, $qrCode);

            if (Storage::exists($testPath)) {
                $this->info('✅ Basic QR code generation successful');
                $this->info('   File saved to: storage/app/' . $testPath);
            } else {
                $this->error('❌ Basic QR code generation failed');
                return 1;
            }

            // Test with ShippingLabelService if we have orders
            $this->info('2. Testing with ShippingLabelService...');
            $order = ShopifyOrder::first();

            if ($order) {
                $shippingLabelService = app(ShippingLabelService::class);

                // Test shipping sticker generation
                $this->info('   Testing shipping sticker generation...');
                $stickerResult = $shippingLabelService->generateShippingSticker($order);

                if ($stickerResult['success']) {
                    $this->info('✅ Shipping sticker generation successful');
                    $this->info('   QR Path: ' . $stickerResult['qr_path']);
                    $this->info('   Print URL: ' . $stickerResult['print_url']);
                } else {
                    $this->error('❌ Shipping sticker generation failed: ' . $stickerResult['error']);
                }

                // Test full shipping label generation
                $this->info('   Testing full shipping label generation...');
                $labelResult = $shippingLabelService->generateShippingLabel($order);

                if ($labelResult['success']) {
                    $this->info('✅ Full shipping label generation successful');
                    $this->info('   Order QR Path: ' . $labelResult['order_qr_path']);
                    $this->info('   Invoice QR Path: ' . $labelResult['invoice_qr_path']);
                    $this->info('   Print URL: ' . $labelResult['print_url']);
                } else {
                    $this->error('❌ Full shipping label generation failed: ' . $labelResult['error']);
                }

            } else {
                $this->warn('⚠️  No orders found in database. Skipping ShippingLabelService tests.');
                $this->info('   You can create test orders by running: php artisan db:seed');
            }

            // Test storage permissions
            $this->info('3. Testing storage permissions...');
            $testFile = 'test_permissions.txt';
            Storage::put($testFile, 'test content');

            if (Storage::exists($testFile)) {
                Storage::delete($testFile);
                $this->info('✅ Storage permissions are working correctly');
            } else {
                $this->error('❌ Storage permissions issue detected');
                return 1;
            }

            // Show available routes
            $this->info('4. Available shipping label routes:');
            $routes = [
                'Generate Shipping Sticker' => 'POST /shopify/orders/{order}/generate-shipping-sticker',
                'Generate Shipping Label' => 'POST /shopify/orders/{order}/generate-shipping-label',
                'Print Shipping Sticker' => 'GET /shopify/orders/{order}/print-sticker',
                'Print Shipping Label' => 'GET /shopify/orders/{order}/print-label',
                'Generate Invoice Label' => 'POST /shopify/invoices/{invoice}/generate-label',
                'Print Invoice Label' => 'GET /shopify/invoices/{invoice}/print-label',
                'Public Order Tracking' => 'GET /shopify/track/{order}',
            ];

            foreach ($routes as $name => $route) {
                $this->info("   {$name}: {$route}");
            }

            $this->info('');
            $this->info('🎉 QR Code generation test completed successfully!');
            $this->info('');
            $this->info('Next steps:');
            $this->info('1. Visit your Shopify orders page to test the functionality');
            $this->info('2. Click "Generate Sticker" or "Print Sticker" buttons');
            $this->info('3. The generated labels will be perfect for sticking on parcels');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Test failed with error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}
