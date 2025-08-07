<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shopify API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Shopify API integration.
    | You can configure your Shopify store credentials and API settings here.
    |
    */

    'api_key' => env('SHOPIFY_API_KEY'),
    'api_secret' => env('SHOPIFY_API_SECRET'),
    'access_token' => env('SHOPIFY_ACCESS_TOKEN'),
    'shop_domain' => env('SHOPIFY_SHOP_DOMAIN'),
    'api_version' => env('SHOPIFY_API_VERSION', '2024-10'),
    'webhook_secret' => env('SHOPIFY_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Shopify App Configuration
    |--------------------------------------------------------------------------
    */

    'app' => [
        'name' => env('SHOPIFY_APP_NAME', 'Sweet Store Integration'),
        'redirect_uri' => env('SHOPIFY_REDIRECT_URI', env('APP_URL') . '/shopify/callback'),
        'scopes' => [
            'read_products',
            'write_products',
            'read_orders',
            'write_orders',
            'read_inventory',
            'write_inventory',
            'read_fulfillments',
            'write_fulfillments',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Configuration
    |--------------------------------------------------------------------------
    */

    'sync' => [
        'auto_sync_products' => env('SHOPIFY_AUTO_SYNC_PRODUCTS', true),
        'auto_sync_orders' => env('SHOPIFY_AUTO_SYNC_ORDERS', true),
        'sync_interval_minutes' => env('SHOPIFY_SYNC_INTERVAL', 15),
        'batch_size' => env('SHOPIFY_BATCH_SIZE', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection and Retry Configuration
    |--------------------------------------------------------------------------
    */

    'connection' => [
        'max_retries' => env('SHOPIFY_MAX_RETRIES', 3),
        'base_delay_ms' => env('SHOPIFY_BASE_DELAY_MS', 1000),
        'max_delay_ms' => env('SHOPIFY_MAX_DELAY_MS', 30000),
        'timeout' => env('SHOPIFY_TIMEOUT', 60),
        'connect_timeout' => env('SHOPIFY_CONNECT_TIMEOUT', 30),
        'read_timeout' => env('SHOPIFY_READ_TIMEOUT', 45),
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Status Mapping
    |--------------------------------------------------------------------------
    */

    'order_status_mapping' => [
        'pending' => 'pending',
        'processing' => 'processing',
        'shipped' => 'shipped',
        'delivered' => 'delivered',
        'cancelled' => 'cancelled',
        'refunded' => 'refunded',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fulfillment Status Mapping
    |--------------------------------------------------------------------------
    */

    'fulfillment_status_mapping' => [
        'pending' => 'pending',
        'processing' => 'processing',
        'shipped' => 'shipped',
        'delivered' => 'delivered',
        'cancelled' => 'cancelled',
    ],
];
