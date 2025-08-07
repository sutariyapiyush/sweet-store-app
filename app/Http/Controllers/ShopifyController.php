<?php

namespace App\Http\Controllers;

use App\Services\ShopifyService;
use App\Services\ShopifyProductSyncService;
use App\Services\ShopifyOrderSyncService;
use App\Services\QrCodeScannerService;
use App\Services\ShopifyWebhookService;
use App\Services\ShippingLabelService;
use App\Models\ShopifyOrder;
use App\Models\ShopifyProduct;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopifyController extends Controller
{
    protected ShopifyService $shopifyService;
    protected ShopifyProductSyncService $productSyncService;
    protected ShopifyOrderSyncService $orderSyncService;
    protected QrCodeScannerService $qrScannerService;
    protected ShopifyWebhookService $webhookService;
    protected ShippingLabelService $shippingLabelService;

    public function __construct(
        ShopifyService $shopifyService,
        ShopifyProductSyncService $productSyncService,
        ShopifyOrderSyncService $orderSyncService,
        QrCodeScannerService $qrScannerService,
        ShopifyWebhookService $webhookService,
        ShippingLabelService $shippingLabelService
    ) {
        $this->shopifyService = $shopifyService;
        $this->productSyncService = $productSyncService;
        $this->orderSyncService = $orderSyncService;
        $this->qrScannerService = $qrScannerService;
        $this->webhookService = $webhookService;
        $this->shippingLabelService = $shippingLabelService;
    }

    /**
     * Shopify dashboard
     */
    public function dashboard()
    {
        try {
            $shopInfo = $this->shopifyService->getShopInfo();
            $productStats = $this->productSyncService->getSyncStats();
            $orderStats = $this->orderSyncService->getSyncStats();

            $recentOrders = ShopifyOrder::with('orderItems')
                ->recent(7)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return view('shopify.dashboard', compact(
                'shopInfo',
                'productStats',
                'orderStats',
                'recentOrders'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading Shopify dashboard: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load Shopify dashboard');
        }
    }

    /**
     * Test Shopify connection
     */
    public function testConnection()
    {
        try {
            $isConnected = $this->shopifyService->testConnection();

            if ($isConnected) {
                $shopInfo = $this->shopifyService->getShopInfo();
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully connected to Shopify',
                    'shop_info' => $shopInfo,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to Shopify',
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Shopify connection test failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get products directly from Shopify API
     */
    public function getProducts(Request $request)
    {
        try {
            $params = [];

            if ($request->filled('limit')) {
                $params['limit'] = min($request->get('limit', 50), 250);
            }

            if ($request->filled('status')) {
                $params['status'] = $request->get('status');
            }

            if ($request->filled('since_id')) {
                $params['since_id'] = $request->get('since_id');
            }

            $products = $this->shopifyService->getProducts($params);

            return response()->json([
                'success' => true,
                'products' => $products,
                'count' => count($products),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch products from Shopify: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get orders directly from Shopify API
     */
    public function getOrders(Request $request)
    {
        try {
            $params = [];

            if ($request->filled('limit')) {
                $params['limit'] = min($request->get('limit', 50), 250);
            }

            if ($request->filled('status')) {
                $params['status'] = $request->get('status');
            }

            if ($request->filled('financial_status')) {
                $params['financial_status'] = $request->get('financial_status');
            }

            if ($request->filled('fulfillment_status')) {
                $params['fulfillment_status'] = $request->get('fulfillment_status');
            }

            if ($request->filled('since_id')) {
                $params['since_id'] = $request->get('since_id');
            }

            if ($request->filled('created_at_min')) {
                $params['created_at_min'] = $request->get('created_at_min');
            }

            $orders = $this->shopifyService->getOrders($params);

            return response()->json([
                'success' => true,
                'orders' => $orders,
                'count' => count($orders),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch orders from Shopify: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * QR Code scanner page
     */
    public function qrScanner()
    {
        return view('shopify.qr-scanner');
    }

    /**
     * Process scanned QR code
     */
    public function processScan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        try {
            $result = $this->qrScannerService->processScannedData($request->qr_data);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('QR scan processing failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process QR code',
            ]);
        }
    }

    /**
     * Execute action from QR scan
     */
    public function executeAction(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
            'action' => 'required|string',
            'params' => 'array',
        ]);

        try {
            $result = $this->qrScannerService->executeAction(
                $request->qr_data,
                $request->action,
                $request->params ?? []
            );

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Action execution failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to execute action',
            ]);
        }
    }

    /**
     * Orders management page - Direct from Shopify API
     */
    public function orders(Request $request)
    {
        try {
            // Get orders directly from Shopify API
            $params = [
                'limit' => 250, // Get more orders
                'status' => 'any',
                'financial_status' => 'any',
                'fulfillment_status' => 'any'
            ];

            // Apply filters to Shopify API call
            if ($request->filled('status')) {
                $params['fulfillment_status'] = $request->status;
            }

            if ($request->filled('financial_status')) {
                $params['financial_status'] = $request->financial_status;
            }

            $allOrders = $this->shopifyService->getOrders($params);

            // Apply search filter locally (since Shopify API doesn't support complex search)
            if ($request->filled('search')) {
                $search = strtolower($request->search);
                $allOrders = array_filter($allOrders, function($order) use ($search) {
                    return strpos(strtolower($order['order_number'] ?? ''), $search) !== false ||
                           strpos(strtolower($order['customer']['email'] ?? ''), $search) !== false ||
                           strpos(strtolower($order['name'] ?? ''), $search) !== false;
                });
            }

            // Sort by created date (newest first)
            usort($allOrders, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            // Simple pagination
            $page = $request->get('page', 1);
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            $orders = array_slice($allOrders, $offset, $perPage);

            // Create pagination info
            $pagination = [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => count($allOrders),
                'last_page' => ceil(count($allOrders) / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, count($allOrders))
            ];

            return view('shopify.orders.index', compact('orders', 'pagination'));

        } catch (\Exception $e) {
            Log::error('Failed to fetch orders from Shopify: ' . $e->getMessage());
            return view('shopify.orders.index', ['orders' => [], 'pagination' => null])
                ->with('error', 'Failed to load orders from Shopify');
        }
    }

    /**
     * Show single order - Direct from Shopify API
     */
    public function showOrder($orderId)
    {
        try {
            $order = $this->shopifyService->getOrder($orderId);

            if (!$order) {
                return redirect()->route('shopify.orders.index')
                    ->with('error', 'Order not found');
            }

            return view('shopify.orders.show', compact('order'));

        } catch (\Exception $e) {
            Log::error("Failed to fetch order {$orderId}: " . $e->getMessage());
            return redirect()->route('shopify.orders.index')
                ->with('error', 'Failed to load order details');
        }
    }

    /**
     * Update order status - Direct Shopify API
     */
    public function updateOrderStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
            'tracking_number' => 'nullable|string',
            'shipping_partner' => 'nullable|string',
        ]);

        try {
            $status = $request->status;
            $trackingNumber = $request->tracking_number;
            $shippingPartner = $request->shipping_partner;

            // Get order from Shopify first
            $order = $this->shopifyService->getOrder($orderId);
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ]);
            }

            // Update Shopify directly based on status
            if ($status === 'shipped' && $trackingNumber) {
                // Check if order is already fulfilled
                if (($order['fulfillment_status'] ?? 'unfulfilled') === 'fulfilled') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order is already fulfilled',
                    ]);
                }

                // Prepare line items for fulfillment - only include unfulfilled items
                $lineItems = [];
                foreach ($order['line_items'] as $item) {
                    // Use fulfillable_quantity which is the quantity available for fulfillment
                    $fulfillableQuantity = $item['fulfillable_quantity'] ?? 0;

                    if ($fulfillableQuantity > 0) {
                        $lineItems[] = [
                            'id' => (int)$item['id'],
                            'quantity' => $fulfillableQuantity,
                        ];
                    }
                }

                if (empty($lineItems)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No items available for fulfillment',
                    ]);
                }

                // Create fulfillment in Shopify with tracking info
                $fulfillmentData = [
                    'location_id' => $this->shopifyService->getPrimaryLocationId(),
                    // 'tracking_number' => $trackingNumber,
                    // 'tracking_company' => $shippingPartner ?: 'Other',
                    // 'notify_customer' => true,
                    'line_items' => $lineItems,
                ];

                $fulfillment = $this->shopifyService->createFulfillment($orderId, $fulfillmentData);

                if ($fulfillment) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Order shipped successfully in Shopify',
                        'fulfillment' => $fulfillment,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create fulfillment in Shopify',
                    ]);
                }
            } elseif ($status === 'cancelled') {
                // Cancel order in Shopify
                $cancelData = [
                    'reason' => 'other',
                    'email' => true,
                    'refund' => false,
                ];

                $updatedOrder = $this->shopifyService->updateOrder($orderId, $cancelData);

                if ($updatedOrder) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Order cancelled successfully in Shopify',
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to cancel order in Shopify',
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to update order status: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Ship order with tracking information - Direct Shopify API
     */
    public function shipOrder(Request $request, $orderId)
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'shipping_partner' => 'required|string',
            'tracking_url' => 'nullable|url',
            'notify_customer' => 'boolean',
        ]);

        try {
            // Get order from Shopify first
            $order = $this->shopifyService->getOrder($orderId);
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found in Shopify',
                ]);
            }

            // Check if order is already fulfilled
            if (($order['fulfillment_status'] ?? 'unfulfilled') === 'fulfilled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already fulfilled',
                ]);
            }

            // Find or create local order record
            $localOrder = ShopifyOrder::where('shopify_order_id', $orderId)->first();
            if (!$localOrder) {
                // Sync this order to local database first
                $syncResult = $this->orderSyncService->syncSingleOrder($order);
                if (!$syncResult['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to sync order to local database',
                    ]);
                }
                $localOrder = $syncResult['order'];
            }

            // Prepare tracking data
            $trackingData = [
                'tracking_number' => $request->tracking_number,
                'shipping_carrier' => $request->shipping_partner,
                'tracking_url' => $request->tracking_url,
                'notify_customer' => $request->get('notify_customer', true),
            ];

            // Create fulfillment with tracking information
            $result = $this->orderSyncService->createFulfillmentWithTracking($localOrder, $trackingData);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error("Failed to ship order: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to ship order: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Fulfill order with tracking information (new method for the fulfillment form)
     */
    public function fulfillOrder(Request $request, $orderId)
    {
        $request->validate([
            'tracking_number' => 'nullable|string',
            'shipping_carrier' => 'required|string',
            'tracking_url' => 'nullable|url',
            'notify_customer' => 'boolean',
        ]);

        try {
            // Get order from Shopify first
            $order = $this->shopifyService->getOrder($orderId);
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found in Shopify',
                ]);
            }

            // Check if order is already fulfilled
            if (($order['fulfillment_status'] ?? 'unfulfilled') === 'fulfilled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already fulfilled',
                ]);
            }

            // Find or create local order record
            $localOrder = ShopifyOrder::where('shopify_order_id', $orderId)->first();
            if (!$localOrder) {
                // Sync this order to local database first
                $syncResult = $this->orderSyncService->syncSingleOrder($order);
                if (!$syncResult['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to sync order to local database',
                    ]);
                }
                $localOrder = $syncResult['order'];
            }

            // Prepare tracking data
            $trackingData = [
                'tracking_number' => $request->tracking_number,
                'shipping_carrier' => $request->shipping_carrier,
                'tracking_url' => $request->tracking_url,
                'notify_customer' => $request->get('notify_customer', true),
            ];

            // Create fulfillment with tracking information
            $result = $this->orderSyncService->createFulfillmentWithTracking($localOrder, $trackingData);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order fulfilled successfully with tracking information',
                    'fulfillment' => $result['fulfillment'],
                    'redirect' => route('shopify.orders.show', $orderId)
                ]);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            Log::error("Failed to fulfill order: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fulfill order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update fulfillment tracking information
     */
    public function updateFulfillmentTracking(Request $request, $orderId, $fulfillmentId)
    {
        $request->validate([
            'tracking_number' => 'nullable|string',
            'shipping_carrier' => 'required|string',
            'tracking_url' => 'nullable|url',
            'notify_customer' => 'boolean',
        ]);

        try {
            // Find local order record
            $localOrder = ShopifyOrder::where('shopify_order_id', $orderId)->first();
            if (!$localOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found in local database',
                ]);
            }

            // Prepare tracking data
            $trackingData = [
                'tracking_number' => $request->tracking_number,
                'shipping_carrier' => $request->shipping_carrier,
                'tracking_url' => $request->tracking_url,
                'notify_customer' => $request->get('notify_customer', true),
            ];

            // Update fulfillment tracking
            $result = $this->orderSyncService->updateFulfillmentTracking($localOrder, $fulfillmentId, $trackingData);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error("Failed to update fulfillment tracking: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tracking: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate QR code for order
     */
    public function generateOrderQr(ShopifyOrder $order)
    {
        try {
            $qrPath = $order->generateQrCode();

            return response()->json([
                'success' => true,
                'qr_path' => $qrPath,
                'qr_url' => asset('storage/' . $qrPath),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to generate QR code: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code',
            ]);
        }
    }

    /**
     * Products management page
     */
    public function products(Request $request)
    {
        // Auto-sync all products from Shopify on every page load
        try {
            $this->productSyncService->syncAllProducts();
        } catch (\Exception $e) {
            Log::warning('Failed to auto-sync products: ' . $e->getMessage());
        }

        $query = ShopifyProduct::with('localProduct');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sync_status')) {
            if ($request->sync_status === 'synced') {
                $query->where('is_synced_to_local', true)
                      ->whereNotNull('local_product_id');
            } elseif ($request->sync_status === 'needs_sync') {
                $query->where(function ($q) {
                    $q->where('is_synced_to_local', false)
                      ->orWhereNull('last_synced_at')
                      ->orWhereRaw('shopify_updated_at > last_synced_at');
                });
            } elseif ($request->sync_status === 'errors') {
                $query->whereNotNull('sync_errors');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('handle', 'like', "%{$search}%")
                  ->orWhere('vendor', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('shopify.products.index', compact('products'));
    }

    /**
     * Show single product
     */
    public function showProduct(ShopifyProduct $product)
    {
        $product->load('localProduct');

        return view('shopify.products.show', compact('product'));
    }

    /**
     * Sync single product to local
     */
    public function syncProductToLocal(ShopifyProduct $product)
    {
        try {
            $localProduct = $product->syncToLocal();

            if ($localProduct) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product synced to local successfully',
                    'local_product' => $localProduct,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to sync product to local',
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to sync product to local: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync product to local',
            ]);
        }
    }

    /**
     * Setup webhooks
     */
    public function setupWebhooks()
    {
        try {
            $results = $this->webhookService->setupWebhooks($this->shopifyService);

            return response()->json([
                'success' => true,
                'message' => 'Webhooks setup completed',
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to setup webhooks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to setup webhooks: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Webhooks endpoint
     */
    public function webhook(Request $request)
    {
        $topic = $request->header('X-Shopify-Topic');
        $hmac = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();

        // Verify webhook authenticity
        if (!$this->webhookService->verifyWebhook($data, $hmac)) {
            Log::warning('Invalid webhook signature', ['topic' => $topic]);
            return response('Unauthorized', 401);
        }

        // Parse webhook data
        $webhookData = json_decode($data, true);

        if (!$webhookData) {
            Log::error('Invalid webhook data', ['topic' => $topic]);
            return response('Bad Request', 400);
        }

        // Process webhook
        $success = $this->webhookService->handleWebhook($topic, $webhookData);

        if ($success) {
            return response('OK', 200);
        } else {
            return response('Internal Server Error', 500);
        }
    }

    /**
     * Generate shipping label for order
     */
    public function generateShippingLabel(ShopifyOrder $order)
    {
        try {
            $result = $this->shippingLabelService->generateShippingLabel($order);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            Log::error("Failed to generate shipping label: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate shipping label',
            ], 500);
        }
    }

    /**
     * Print shipping label view (only full label with tracking QR)
     */
    public function printShippingLabel(ShopifyOrder $order)
    {
        try {
            $result = $this->shippingLabelService->generateShippingLabel($order);

            if (!$result['success']) {
                return redirect()->back()->with('error', $result['error']);
            }

            return view('shopify.labels.shipping-label', [
                'order' => $order,
                'labelData' => $result['label_data'],
                'trackingQrPath' => $result['tracking_qr_path'],
                'trackingBarcode' => $result['tracking_barcode'],
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to print shipping label: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate shipping label');
        }
    }

    /**
     * Generate invoice label
     */
    public function generateInvoiceLabel(Invoice $invoice)
    {
        try {
            $result = $this->shippingLabelService->generateInvoiceLabel($invoice);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            Log::error("Failed to generate invoice label: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoice label',
            ], 500);
        }
    }

    /**
     * Print invoice label view
     */
    public function printInvoiceLabel(Invoice $invoice)
    {
        try {
            $result = $this->shippingLabelService->generateInvoiceLabel($invoice);

            if (!$result['success']) {
                return redirect()->back()->with('error', $result['error']);
            }

            return view('shopify.labels.invoice-label', [
                'invoice' => $invoice,
                'labelData' => $result['label_data'],
                'qrPath' => $result['qr_path'],
                'barcodePath' => $result['barcode_path'],
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to print invoice label: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate invoice label');
        }
    }

    /**
     * Bulk generate shipping labels
     */
    public function bulkGenerateLabels(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:shopify_orders,id',
            'label_type' => 'required|in:shipping_label,shipping_sticker,invoice_label',
        ]);

        try {
            $result = $this->shippingLabelService->bulkGenerateLabels(
                $request->order_ids,
                $request->label_type
            );

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error("Failed to bulk generate labels: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate labels',
            ], 500);
        }
    }

    /**
     * Get available label templates
     */
    public function getLabelTemplates()
    {
        try {
            $templates = $this->shippingLabelService->getLabelTemplates();

            return response()->json([
                'success' => true,
                'templates' => $templates,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to get label templates: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get label templates',
            ], 500);
        }
    }

    /**
     * Track order (public endpoint for QR code scanning)
     */
    public function trackOrder(ShopifyOrder $order)
    {
        return view('shopify.tracking.order', [
            'order' => $order->load(['orderItems', 'invoice']),
        ]);
    }
}
