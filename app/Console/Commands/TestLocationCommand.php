<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ShopifyService;
use Illuminate\Support\Facades\Log;

class TestLocationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:test-location';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Shopify location retrieval';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Shopify location retrieval...');

        try {
            $shopifyService = app(ShopifyService::class);

            // Test getting all locations
            $this->info('Fetching all locations...');
            $locations = $shopifyService->getLocations();

            if (empty($locations)) {
                $this->error('No locations found!');
                return 1;
            }

            $this->info('Found ' . count($locations) . ' location(s):');
            foreach ($locations as $location) {
                $this->line("- ID: {$location['id']}, Name: {$location['name']}, Active: " . ($location['active'] ? 'Yes' : 'No'));
            }

            // Test getting primary location ID
            $this->info('Getting primary location ID...');
            $primaryLocationId = $shopifyService->getPrimaryLocationId();

            if ($primaryLocationId) {
                $this->info("Primary location ID: {$primaryLocationId}");
            } else {
                $this->error('No primary location ID found!');
                return 1;
            }

            $this->info('Location test completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Location test failed: ' . $e->getMessage());
            Log::error('Location test failed: ' . $e->getMessage());
            return 1;
        }
    }
}
