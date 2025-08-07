<?php

namespace App\Services;

use Shopify\Context;
use Shopify\Auth\Session;
use Shopify\Rest\Admin2024_01\Product;
use Shopify\Rest\Admin2024_01\Order;
use Shopify\Rest\Admin2024_01\Fulfillment;
use Shopify\Clients\Rest;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class ShopifyService
{
    protected Session $session;
    protected Rest $client;
    protected int $maxRetries;
    protected int $baseDelay; // milliseconds
    protected int $maxDelay; // milliseconds

    public function __construct()
    {
        // Load configuration values
        $this->maxRetries = config('shopify.connection.max_retries', 3);
        $this->baseDelay = config('shopify.connection.base_delay_ms', 1000);
        $this->maxDelay = config('shopify.connection.max_delay_ms', 30000);

        $this->initializeShopify();
        $this->createSession();
        $this->client = new Rest($this->session->getShop(), $this->session->getAccessToken());
        $this->configureHttpClient();
    }

    /**
     * Initialize Shopify Context
     */
    private function initializeShopify(): void
    {
        Context::initialize(
            apiKey: config('shopify.api_key'),
            apiSecretKey: config('shopify.api_secret'),
            scopes: config('shopify.app.scopes'),
            hostName: parse_url(config('app.url'), PHP_URL_HOST),
            sessionStorage: new \Shopify\Auth\FileSessionStorage('/tmp/shopify_sessions'),
            apiVersion: config('shopify.api_version'),
            isEmbeddedApp: false,
            isPrivateApp: false,
        );
    }

    /**
     * Create Shopify session
     */
    private function createSession(): void
    {
        $this->session = new Session(
            id: 'offline_' . config('shopify.shop_domain'),
            shop: config('shopify.shop_domain'),
            isOnline: false,
            state: ''
        );

        $this->session->setAccessToken(config('shopify.access_token'));
    }

    /**
     * Configure HTTP client with connection settings
     */
    private function configureHttpClient(): void
    {
        // Log configuration for debugging
        Log::debug('Configuring Shopify HTTP client with enhanced connection settings');

        // Note: The Shopify Rest client doesn't expose direct HTTP client configuration
        // The retry mechanism and connection handling will be implemented at the method level
        // This method serves as a placeholder for future enhancements
    }

    /**
     * Execute API request with retry mechanism
     */
    private function executeWithRetry(callable $apiCall, string $operation = 'API call'): mixed
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                Log::debug("Shopify API attempt {$attempt}/{$this->maxRetries} for: {$operation}");

                $result = $apiCall();

                if ($attempt > 1) {
                    Log::info("Shopify API call succeeded on attempt {$attempt} for: {$operation}");
                }

                return $result;

            } catch (\Exception $e) {
                $lastException = $e;
                $errorMessage = $e->getMessage();

                Log::warning("Shopify API attempt {$attempt}/{$this->maxRetries} failed for {$operation}: {$errorMessage}");

                // Check if this is a retryable error
                if (!$this->isRetryableError($e)) {
                    Log::error("Non-retryable error encountered for {$operation}: {$errorMessage}");
                    throw $e;
                }

                // Don't wait after the last attempt
                if ($attempt < $this->maxRetries) {
                    $delay = $this->calculateDelay($attempt);
                    Log::debug("Waiting {$delay}ms before retry attempt " . ($attempt + 1));
                    usleep($delay * 1000); // Convert to microseconds
                }
            }
        }

        Log::error("All {$this->maxRetries} attempts failed for {$operation}. Last error: " . $lastException->getMessage());
        throw $lastException;
    }

    /**
     * Check if an error is retryable
     */
    private function isRetryableError(\Exception $e): bool
    {
        $message = strtolower($e->getMessage());

        // Connection-related errors that should be retried
        $retryableErrors = [
            'connection reset by peer',
            'connection timed out',
            'connection refused',
            'network is unreachable',
            'temporary failure in name resolution',
            'could not resolve host',
            'operation timed out',
            'recv failure',
            'send failure',
            'ssl connection error',
            'empty reply from server',
            'transfer closed with outstanding read data remaining',
        ];

        foreach ($retryableErrors as $retryableError) {
            if (strpos($message, $retryableError) !== false) {
                return true;
            }
        }

        // HTTP status codes that should be retried
        if ($e instanceof RequestException && $e->hasResponse()) {
            $statusCode = $e->getResponse()->getStatusCode();
            $retryableStatusCodes = [429, 500, 502, 503, 504, 520, 521, 522, 523, 524];

            if (in_array($statusCode, $retryableStatusCodes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate delay for exponential backoff with jitter
     */
    private function calculateDelay(int $attempt): int
    {
        // Exponential backoff: baseDelay * (2 ^ (attempt - 1))
        $delay = $this->baseDelay * pow(2, $attempt - 1);

        // Add jitter (random variation) to prevent thundering herd
        $jitter = rand(0, (int)($delay * 0.1)); // 10% jitter
        $delay += $jitter;

        // Cap at maximum delay
        return min($delay, $this->maxDelay);
    }

    /**
     * Get all products from Shopify
     */
    public function getProducts(array $params = []): array
    {
        return $this->executeWithRetry(function () use ($params) {
            $defaultParams = [
                'limit' => config('shopify.sync.batch_size', 50),
                'status' => 'active'
            ];

            $params = array_merge($defaultParams, $params);

            $response = $this->client->get('products', [], $params);

            return $response->getDecodedBody()['products'] ?? [];
        }, 'Get products');
    }

    /**
     * Get single product from Shopify
     */
    public function getProduct(string $productId): ?array
    {
        try {
            return $this->executeWithRetry(function () use ($productId) {
                $response = $this->client->get("products/{$productId}");
                return $response->getDecodedBody()['product'] ?? null;
            }, "Get product {$productId}");
        } catch (\Exception $e) {
            Log::error("Failed to fetch product {$productId} from Shopify: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all orders from Shopify
     */
    public function getOrders(array $params = []): array
    {
        return $this->executeWithRetry(function () use ($params) {
            $defaultParams = [
                'limit' => config('shopify.sync.batch_size', 50),
                'status' => 'any',
                'financial_status' => 'any',
                'fulfillment_status' => 'any'
            ];

            $params = array_merge($defaultParams, $params);

            $response = $this->client->get('orders', [], $params);
            return $response->getDecodedBody()['orders'] ?? [];
        }, 'Get orders');
    }

    /**
     * Get single order from Shopify
     */
    public function getOrder(string $orderId): ?array
    {
        try {
            return $this->executeWithRetry(function () use ($orderId) {
                $response = $this->client->get("orders/{$orderId}");
                return $response->getDecodedBody()['order'] ?? null;
            }, "Get order {$orderId}");
        } catch (\Exception $e) {
            Log::error("Failed to fetch order {$orderId} from Shopify: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update order in Shopify
     */
    public function updateOrder(string $orderId, array $data): ?array
    {
        try {
            return $this->executeWithRetry(function () use ($orderId, $data) {
                $response = $this->client->put("orders/{$orderId}", ['order' => $data]);
                return $response->getDecodedBody()['order'] ?? null;
            }, "Update order {$orderId}");
        } catch (\Exception $e) {
            Log::error("Failed to update order {$orderId} in Shopify: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create fulfillment for order
     */
    public function createFulfillment(string $orderId, array $fulfillmentData): ?array
    {
        try {
            Log::info("Creating fulfillment for order {$orderId}", ['fulfillment_data' => $fulfillmentData]);

            return $this->executeWithRetry(function () use ($orderId, $fulfillmentData) {
                try {
                    $response = $this->client->post("orders/{$orderId}/fulfillments", [
                        'fulfillment' => $fulfillmentData
                    ]);
                    $body = $response->getDecodedBody();

                    Log::info("Fulfillment creation response", [
                        'status_code' => $response->getStatusCode(),
                        'response' => $body,
                        'headers' => $response->getHeaders()
                    ]);

                    // Check for errors in response
                    if ($response->getStatusCode() >= 400) {
                        Log::error("Shopify API returned error status", [
                            'status_code' => $response->getStatusCode(),
                            'response_body' => $body,
                            'order_id' => $orderId,
                            'fulfillment_data' => $fulfillmentData
                        ]);
                        return null;
                    }

                    return $body['fulfillment'] ?? null;
                } catch (\Exception $e) {
                    Log::error("HTTP request failed during fulfillment creation", [
                        'error' => $e->getMessage(),
                        'order_id' => $orderId,
                        'fulfillment_data' => $fulfillmentData,
                        'exception_class' => get_class($e)
                    ]);
                    throw $e;
                }
            }, "Create fulfillment for order {$orderId}");
        } catch (\Shopify\Exception\HttpRequestException $e) {
            Log::error("Shopify API error creating fulfillment for order {$orderId}", [
                'status_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'fulfillment_data' => $fulfillmentData
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error("Failed to create fulfillment for order {$orderId}: " . $e->getMessage(), [
                'fulfillment_data' => $fulfillmentData,
                'exception_class' => get_class($e),
                'exception' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Update fulfillment
     */
    public function updateFulfillment(string $orderId, string $fulfillmentId, array $data): ?array
    {
        try {
            return $this->executeWithRetry(function () use ($orderId, $fulfillmentId, $data) {
                $response = $this->client->put("orders/{$orderId}/fulfillments/{$fulfillmentId}", ['fulfillment' => $data]);
                return $response->getDecodedBody()['fulfillment'] ?? null;
            }, "Update fulfillment {$fulfillmentId} for order {$orderId}");
        } catch (\Exception $e) {
            Log::error("Failed to update fulfillment {$fulfillmentId} for order {$orderId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get webhooks
     */
    public function getWebhooks(): array
    {
        try {
            return $this->executeWithRetry(function () {
                $response = $this->client->get('webhooks');
                return $response->getDecodedBody()['webhooks'] ?? [];
            }, 'Get webhooks');
        } catch (\Exception $e) {
            Log::error('Failed to fetch webhooks from Shopify: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create webhook
     */
    public function createWebhook(array $webhookData): ?array
    {
        try {
            return $this->executeWithRetry(function () use ($webhookData) {
                $response = $this->client->post('webhooks', ['webhook' => $webhookData]);
                return $response->getDecodedBody()['webhook'] ?? null;
            }, 'Create webhook');
        } catch (\Exception $e) {
            Log::error('Failed to create webhook in Shopify: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(string $webhookId): bool
    {
        try {
            $this->executeWithRetry(function () use ($webhookId) {
                $this->client->delete("webhooks/{$webhookId}");
                return true;
            }, "Delete webhook {$webhookId}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete webhook {$webhookId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Test connection to Shopify
     */
    public function testConnection(): bool
    {
        try {
            $result = $this->executeWithRetry(function () {
                $response = $this->client->get('shop');
                return isset($response->getDecodedBody()['shop']);
            }, 'Test connection');
            return $result;
        } catch (\Exception $e) {
            Log::error('Shopify connection test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get shop information
     */
    public function getShopInfo(): ?array
    {
        try {
            return $this->executeWithRetry(function () {
                $response = $this->client->get('shop');
                return $response->getDecodedBody()['shop'] ?? null;
            }, 'Get shop info');
        } catch (\Exception $e) {
            Log::error('Failed to fetch shop info from Shopify: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get shop locations
     */
    public function getLocations(): array
    {
        try {
            return $this->executeWithRetry(function () {
                $response = $this->client->get('locations');
                return $response->getDecodedBody()['locations'] ?? [];
            }, 'Get locations');
        } catch (\Exception $e) {
            Log::error('Failed to fetch locations from Shopify: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get primary location ID (first active location)
     */
    public function getPrimaryLocationId(): ?int
    {
        try {
            $locations = $this->getLocations();

            // Find the first active location
            foreach ($locations as $location) {
                if ($location['active'] ?? false) {
                    return (int) $location['id'];
                }
            }

            // If no active location found, return the first location
            if (!empty($locations)) {
                return (int) $locations[0]['id'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get primary location ID: ' . $e->getMessage());
            return null;
        }
    }
}
