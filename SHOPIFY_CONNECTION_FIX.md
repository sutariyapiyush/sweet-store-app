# Shopify Connection Fix - cURL Error 56 Resolution

## Problem
The application was experiencing `cURL error 56: Recv failure: Connection reset by peer` when trying to sync orders from Shopify API. This error typically occurs due to:
- Network connectivity issues
- Connection timeouts
- Server-side connection drops
- Lack of retry mechanisms

## Solution Implemented

### 1. Retry Mechanism with Exponential Backoff
- Added configurable retry mechanism (default: 3 attempts)
- Implemented exponential backoff with jitter to prevent thundering herd
- Base delay: 1000ms, Max delay: 30000ms
- Added intelligent error detection for retryable vs non-retryable errors

### 2. Enhanced Error Handling
- Comprehensive list of retryable connection errors
- HTTP status code-based retry logic (429, 5xx errors)
- Detailed logging for debugging connection issues
- Graceful degradation for non-retryable errors

### 3. Connection Configuration
- Added configurable timeout settings
- Connection timeout: 30s
- Read timeout: 45s
- Total request timeout: 60s

### 4. Improved Logging
- Debug-level logging for retry attempts
- Warning-level logging for failed attempts
- Info-level logging for successful retries
- Error-level logging for final failures

## Configuration

### Environment Variables (.env)
```env
# Shopify Connection Settings
SHOPIFY_MAX_RETRIES=3
SHOPIFY_BASE_DELAY_MS=1000
SHOPIFY_MAX_DELAY_MS=30000
SHOPIFY_TIMEOUT=60
SHOPIFY_CONNECT_TIMEOUT=30
SHOPIFY_READ_TIMEOUT=45
```

### Config File (config/shopify.php)
```php
'connection' => [
    'max_retries' => env('SHOPIFY_MAX_RETRIES', 3),
    'base_delay_ms' => env('SHOPIFY_BASE_DELAY_MS', 1000),
    'max_delay_ms' => env('SHOPIFY_MAX_DELAY_MS', 30000),
    'timeout' => env('SHOPIFY_TIMEOUT', 60),
    'connect_timeout' => env('SHOPIFY_CONNECT_TIMEOUT', 30),
    'read_timeout' => env('SHOPIFY_READ_TIMEOUT', 45),
],
```

## Files Modified

1. **app/Services/ShopifyService.php**
   - Added retry mechanism with exponential backoff
   - Enhanced error detection and handling
   - Wrapped all API calls with retry logic

2. **config/shopify.php**
   - Added connection configuration section

3. **/.env**
   - Added connection timeout and retry settings

## Testing

Run the following commands to test the fix:

```bash
# Test connection only
php artisan shopify:test connection

# Test order sync specifically
php artisan shopify:test orders

# Test all Shopify functionality
php artisan shopify:test all
```

## Retryable Errors Handled

- Connection reset by peer
- Connection timed out
- Connection refused
- Network is unreachable
- Temporary failure in name resolution
- Could not resolve host
- Operation timed out
- Recv failure
- Send failure
- SSL connection error
- Empty reply from server
- Transfer closed with outstanding read data remaining

## HTTP Status Codes Retried

- 429 (Too Many Requests)
- 500 (Internal Server Error)
- 502 (Bad Gateway)
- 503 (Service Unavailable)
- 504 (Gateway Timeout)
- 520-524 (Cloudflare errors)

## Benefits

1. **Improved Reliability**: Automatic retry on transient network issues
2. **Better User Experience**: Reduced failed sync operations
3. **Configurable**: Easy to adjust retry behavior based on needs
4. **Comprehensive Logging**: Better visibility into connection issues
5. **Intelligent Backoff**: Prevents overwhelming the API with rapid retries
