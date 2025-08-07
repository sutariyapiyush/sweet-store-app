<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductionScheduleController;
use App\Http\Controllers\QualityCheckController;
use App\Http\Controllers\ShopifyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Product Categories Routes
    Route::resource('product-categories', ProductCategoryController::class);

    // Raw Materials Routes
    Route::resource('raw-materials', RawMaterialController::class);

    // Products Routes
    Route::resource('products', ProductController::class);

    // Production Logs Routes
    Route::resource('production-logs', ProductionLogController::class);
    Route::get('production-logs-dashboard', [ProductionLogController::class, 'dashboard'])->name('production-logs.dashboard');
    Route::patch('production-logs/{productionLog}/update-status', [ProductionLogController::class, 'updateStatus'])->name('production-logs.update-status');
    Route::get('production-logs-export', [ProductionLogController::class, 'export'])->name('production-logs.export');
    Route::get('products/{product}/production-logs', [ProductionLogController::class, 'forProduct'])->name('production-logs.for-product');

    // Production Schedules Routes
    Route::resource('production-schedules', ProductionScheduleController::class);
    Route::patch('production-schedules/{productionSchedule}/update-status', [ProductionScheduleController::class, 'updateStatus'])->name('production-schedules.update-status');

    // Quality Checks Routes
    Route::resource('quality-checks', QualityCheckController::class);
    Route::get('quality-checks-dashboard', [QualityCheckController::class, 'dashboard'])->name('quality-checks.dashboard');
    Route::get('quality-checks-failed', [QualityCheckController::class, 'failed'])->name('quality-checks.failed');
    Route::get('production-logs/{productionLog}/quality-checks', [QualityCheckController::class, 'forProductionLog'])->name('quality-checks.for-production-log');

    // Sellers Routes
    Route::resource('sellers', SellerController::class);
    Route::patch('sellers/{seller}/toggle-status', [SellerController::class, 'toggleStatus'])->name('sellers.toggle-status');

    // Users Routes (Admin only)
    Route::resource('users', UserController::class)->middleware('admin');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Shopify Integration Routes
    Route::prefix('shopify')->name('shopify.')->group(function () {
        Route::get('dashboard', [ShopifyController::class, 'dashboard'])->name('dashboard');
        Route::get('test-connection', [ShopifyController::class, 'testConnection'])->name('test-connection');
        Route::post('setup-webhooks', [ShopifyController::class, 'setupWebhooks'])->name('setup-webhooks');

        // Product API Routes (Direct Shopify API)
        Route::get('api/products', [ShopifyController::class, 'getProducts'])->name('api.products');
        Route::get('products', [ShopifyController::class, 'products'])->name('products.index');
        Route::get('products/{product}', [ShopifyController::class, 'showProduct'])->name('products.show');
        Route::post('products/{product}/sync-to-local', [ShopifyController::class, 'syncProductToLocal'])->name('products.sync-to-local');

        // Order API Routes (Direct Shopify API)
        Route::get('api/orders', [ShopifyController::class, 'getOrders'])->name('api.orders');
        Route::get('orders', [ShopifyController::class, 'orders'])->name('orders.index');
        Route::get('orders/{orderId}', [ShopifyController::class, 'showOrder'])->name('orders.show');
        Route::patch('orders/{orderId}/status', [ShopifyController::class, 'updateOrderStatus'])->name('orders.update-status');
        Route::post('orders/{orderId}/ship', [ShopifyController::class, 'shipOrder'])->name('orders.ship');
        Route::post('orders/{orderId}/fulfill', [ShopifyController::class, 'fulfillOrder'])->name('orders.fulfill');
        Route::patch('orders/{orderId}/fulfillments/{fulfillmentId}/tracking', [ShopifyController::class, 'updateFulfillmentTracking'])->name('orders.update-fulfillment-tracking');
        Route::post('orders/{orderId}/generate-qr', [ShopifyController::class, 'generateOrderQr'])->name('orders.generate-qr');

        // Shipping Label Routes (Only full label)
        Route::post('orders/{orderId}/generate-shipping-label', [ShopifyController::class, 'generateShippingLabel'])->name('orders.generate-shipping-label');
        Route::get('orders/{orderId}/print-label', [ShopifyController::class, 'printShippingLabel'])->name('orders.print-label');

        // Invoice Label Routes
        Route::post('invoices/{invoice}/generate-label', [ShopifyController::class, 'generateInvoiceLabel'])->name('invoices.generate-label');
        Route::get('invoices/{invoice}/print-label', [ShopifyController::class, 'printInvoiceLabel'])->name('invoices.print-label');

        // Bulk Label Generation (Updated)
        Route::post('bulk-generate-labels', [ShopifyController::class, 'bulkGenerateLabels'])->name('bulk-generate-labels');
        Route::get('label-templates', [ShopifyController::class, 'getLabelTemplates'])->name('label-templates');

        // QR Code Scanner Routes
        Route::get('qr-scanner', [ShopifyController::class, 'qrScanner'])->name('qr-scanner');
        Route::post('process-scan', [ShopifyController::class, 'processScan'])->name('process-scan');
        Route::post('execute-action', [ShopifyController::class, 'executeAction'])->name('execute-action');
    });
});

// Public tracking route (accessible via QR code)
Route::get('shopify/track/{orderId}', [ShopifyController::class, 'trackOrder'])->name('shopify.orders.track');

// Shopify Webhooks (outside auth middleware)
Route::post('shopify/webhook', [ShopifyController::class, 'webhook'])->name('shopify.webhook');

// Test routes for debugging (remove in production)
Route::get('test/shopify-orders', function() {
    $orders = \App\Models\ShopifyOrder::all();
    return response()->json([
        'total_orders' => $orders->count(),
        'orders' => $orders->take(5)->map(function($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'internal_status' => $order->internal_status,
                'created_at' => $order->created_at
            ];
        })
    ]);
});

Route::patch('test/shopify/orders/{order}/status', [ShopifyController::class, 'updateOrderStatus'])->name('test.shopify.orders.update-status');

require __DIR__.'/auth.php';
