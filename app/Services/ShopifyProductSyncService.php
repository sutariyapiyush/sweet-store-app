<?php

namespace App\Services;

use App\Models\ShopifyProduct;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ShopifyProductSyncService
{
    protected ShopifyService $shopifyService;

    public function __construct(ShopifyService $shopifyService)
    {
        $this->shopifyService = $shopifyService;
    }

    /**
     * Sync all products from Shopify
     */
    public function syncAllProducts(): array
    {
        $results = [
            'total' => 0,
            'synced' => 0,
            'errors' => 0,
            'created' => 0,
            'updated' => 0,
        ];

        try {
            $products = $this->shopifyService->getProducts();
            $results['total'] = count($products);

            foreach ($products as $productData) {
                try {
                    $result = $this->syncSingleProduct($productData);

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
                    Log::error("Error syncing product {$productData['id']}: " . $e->getMessage());
                }
            }

            Log::info('Product sync completed', $results);
            return $results;

        } catch (\Exception $e) {
            Log::error('Failed to sync products from Shopify: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync single product from Shopify
     */
    public function syncSingleProduct(array $productData): array
    {
        try {
            DB::beginTransaction();

            $shopifyProduct = ShopifyProduct::where('shopify_product_id', $productData['id'])->first();
            $isNew = !$shopifyProduct;

            if (!$shopifyProduct) {
                $shopifyProduct = new ShopifyProduct();
            }

            // Map Shopify product data
            $shopifyProduct->fill([
                'shopify_product_id' => $productData['id'],
                'title' => $productData['title'],
                'body_html' => $productData['body_html'],
                'vendor' => $productData['vendor'],
                'product_type' => $productData['product_type'],
                'handle' => $productData['handle'],
                'status' => $productData['status'],
                'published_scope' => $productData['published_scope'],
                'published_at' => $productData['published_at'] ? \Carbon\Carbon::parse($productData['published_at']) : null,
                'seo_title' => $productData['seo_title'] ?? null,
                'seo_description' => $productData['seo_description'] ?? null,
                'options' => $productData['options'] ?? [],
                'images' => $productData['images'] ?? [],
                'featured_image' => $productData['image']['src'] ?? null,
                'shopify_created_at' => \Carbon\Carbon::parse($productData['created_at']),
                'shopify_updated_at' => \Carbon\Carbon::parse($productData['updated_at']),
                'tags' => !empty($productData['tags']) ? explode(', ', $productData['tags']) : [],
                'template_suffix' => $productData['template_suffix'] ?? null,
                'metafields' => $productData['metafields'] ?? [],
            ]);

            $shopifyProduct->save();

            // Auto-sync to local product if enabled
            if (config('shopify.sync.auto_sync_products', true)) {
                $localProduct = $this->syncToLocalProduct($shopifyProduct, $productData);
            }

            DB::commit();

            return [
                'success' => true,
                'action' => $isNew ? 'created' : 'updated',
                'shopify_product' => $shopifyProduct,
                'local_product' => $localProduct ?? null,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to sync product {$productData['id']}: " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sync Shopify product to local product
     */
    public function syncToLocalProduct(ShopifyProduct $shopifyProduct, array $productData = null): ?Product
    {
        try {
            // Get existing local product or create new one
            $localProduct = $shopifyProduct->localProduct;

            if (!$localProduct) {
                $localProduct = new Product();
            }

            // Extract price from variants
            $price = null;
            $sku = null;
            $inventory = 0;

            if (isset($productData['variants']) && !empty($productData['variants'])) {
                $firstVariant = $productData['variants'][0];
                $price = $firstVariant['price'] ?? null;
                $sku = $firstVariant['sku'] ?? $shopifyProduct->handle;
                $inventory = $firstVariant['inventory_quantity'] ?? 0;
            }

            // Map to local product
            $localProduct->fill([
                'name' => $shopifyProduct->title,
                'description' => strip_tags($shopifyProduct->body_html ?? ''),
                'unit' => 'pieces', // Default unit
                'quantity_in_stock' => $inventory,
                'selling_price' => $price,
                'status' => $shopifyProduct->status === 'active' ? 'active' : 'inactive',
                'sku' => $sku ?: $shopifyProduct->handle,
                'image_path' => $shopifyProduct->featured_image,
            ]);

            $localProduct->save();

            // Update the mapping
            $shopifyProduct->update([
                'local_product_id' => $localProduct->id,
                'is_synced_to_local' => true,
                'last_synced_at' => now(),
                'sync_errors' => null,
            ]);

            return $localProduct;

        } catch (\Exception $e) {
            Log::error("Failed to sync Shopify product {$shopifyProduct->id} to local: " . $e->getMessage());

            $shopifyProduct->update([
                'sync_errors' => ['error' => $e->getMessage(), 'timestamp' => now()],
                'is_synced_to_local' => false,
            ]);

            return null;
        }
    }

    /**
     * Sync products that need updating
     */
    public function syncUpdatedProducts(): array
    {
        $results = [
            'total' => 0,
            'synced' => 0,
            'errors' => 0,
        ];

        try {
            // Get products that need sync
            $productsNeedingSync = ShopifyProduct::where(function ($q) {
                $q->where('is_synced_to_local', false)
                  ->orWhereNull('last_synced_at')
                  ->orWhereRaw('shopify_updated_at > last_synced_at');
            })->get();
            $results['total'] = $productsNeedingSync->count();

            foreach ($productsNeedingSync as $shopifyProduct) {
                try {
                    // Fetch latest data from Shopify
                    $productData = $this->shopifyService->getProduct($shopifyProduct->shopify_product_id);

                    if ($productData) {
                        $result = $this->syncSingleProduct($productData);
                        if ($result['success']) {
                            $results['synced']++;
                        } else {
                            $results['errors']++;
                        }
                    } else {
                        $results['errors']++;
                    }
                } catch (\Exception $e) {
                    $results['errors']++;
                    Log::error("Error syncing updated product {$shopifyProduct->shopify_product_id}: " . $e->getMessage());
                }
            }

            return $results;

        } catch (\Exception $e) {
            Log::error('Failed to sync updated products: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get sync statistics
     */
    public function getSyncStats(): array
    {
        return [
            'total_shopify_products' => ShopifyProduct::count(),
            'synced_to_local' => ShopifyProduct::where('is_synced_to_local', true)
                                              ->whereNotNull('local_product_id')->count(),
            'needs_sync' => ShopifyProduct::where(function ($q) {
                $q->where('is_synced_to_local', false)
                  ->orWhereNull('last_synced_at')
                  ->orWhereRaw('shopify_updated_at > last_synced_at');
            })->count(),
            'with_errors' => ShopifyProduct::whereNotNull('sync_errors')->count(),
            'active_products' => ShopifyProduct::where('status', 'active')->count(),
            'published_products' => ShopifyProduct::where('status', 'active')
                                                  ->whereNotNull('published_at')->count(),
        ];
    }

    /**
     * Retry failed syncs
     */
    public function retryFailedSyncs(): array
    {
        $results = [
            'total' => 0,
            'synced' => 0,
            'errors' => 0,
        ];

        try {
            $productsWithErrors = ShopifyProduct::withSyncErrors()->get();
            $results['total'] = $productsWithErrors->count();

            foreach ($productsWithErrors as $shopifyProduct) {
                try {
                    // Clear previous errors
                    $shopifyProduct->update(['sync_errors' => null]);

                    // Retry sync to local
                    $localProduct = $this->syncToLocalProduct($shopifyProduct);

                    if ($localProduct) {
                        $results['synced']++;
                    } else {
                        $results['errors']++;
                    }
                } catch (\Exception $e) {
                    $results['errors']++;
                    Log::error("Error retrying sync for product {$shopifyProduct->shopify_product_id}: " . $e->getMessage());
                }
            }

            return $results;

        } catch (\Exception $e) {
            Log::error('Failed to retry failed syncs: ' . $e->getMessage());
            throw $e;
        }
    }
}
