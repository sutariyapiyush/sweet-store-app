<?php

namespace App\Services;

use App\Models\ShopifyOrder;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class QrCodeScannerService
{
    /**
     * Process scanned QR code data
     */
    public function processScannedData(string $qrData): array
    {
        try {
            $data = json_decode($qrData, true);

            if (!$data) {
                return [
                    'success' => false,
                    'error' => 'Invalid QR code format',
                ];
            }

            // Determine QR code type and process accordingly
            if (isset($data['order_id'])) {
                return $this->processOrderQrCode($data);
            } elseif (isset($data['invoice_id'])) {
                return $this->processInvoiceQrCode($data);
            } else {
                return [
                    'success' => false,
                    'error' => 'Unknown QR code type',
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error processing QR code: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to process QR code',
            ];
        }
    }

    /**
     * Process order QR code
     */
    protected function processOrderQrCode(array $data): array
    {
        try {
            $order = ShopifyOrder::find($data['order_id']);

            if (!$order) {
                return [
                    'success' => false,
                    'error' => 'Order not found',
                ];
            }

            // Verify QR code data matches order
            if ($order->shopify_order_id !== $data['shopify_order_id'] ||
                $order->order_number !== $data['order_number']) {
                return [
                    'success' => false,
                    'error' => 'QR code data mismatch',
                ];
            }

            return [
                'success' => true,
                'type' => 'order',
                'order' => $order,
                'data' => $data,
                'actions' => $this->getAvailableOrderActions($order),
            ];

        } catch (\Exception $e) {
            Log::error('Error processing order QR code: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to process order QR code',
            ];
        }
    }

    /**
     * Process invoice QR code
     */
    protected function processInvoiceQrCode(array $data): array
    {
        try {
            $invoice = Invoice::find($data['invoice_id']);

            if (!$invoice) {
                return [
                    'success' => false,
                    'error' => 'Invoice not found',
                ];
            }

            // Verify QR code data matches invoice
            if ($invoice->invoice_number !== $data['invoice_number']) {
                return [
                    'success' => false,
                    'error' => 'QR code data mismatch',
                ];
            }

            return [
                'success' => true,
                'type' => 'invoice',
                'invoice' => $invoice,
                'order' => $invoice->shopifyOrder,
                'data' => $data,
                'actions' => $this->getAvailableInvoiceActions($invoice),
            ];

        } catch (\Exception $e) {
            Log::error('Error processing invoice QR code: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to process invoice QR code',
            ];
        }
    }

    /**
     * Get available actions for order based on current status
     */
    protected function getAvailableOrderActions(ShopifyOrder $order): array
    {
        $actions = [];

        switch ($order->internal_status) {
            case 'pending':
                $actions[] = [
                    'action' => 'mark_processing',
                    'label' => 'Mark as Processing',
                    'description' => 'Start processing this order',
                ];
                break;

            case 'processing':
                $actions[] = [
                    'action' => 'mark_shipped',
                    'label' => 'Mark as Shipped',
                    'description' => 'Mark order as shipped and notify customer',
                ];
                $actions[] = [
                    'action' => 'generate_shipping_label',
                    'label' => 'Generate Shipping Label',
                    'description' => 'Create shipping label for this order',
                ];
                break;

            case 'shipped':
                $actions[] = [
                    'action' => 'mark_delivered',
                    'label' => 'Mark as Delivered',
                    'description' => 'Confirm order delivery',
                ];
                $actions[] = [
                    'action' => 'track_shipment',
                    'label' => 'Track Shipment',
                    'description' => 'View shipment tracking information',
                ];
                break;

            case 'delivered':
                $actions[] = [
                    'action' => 'view_details',
                    'label' => 'View Order Details',
                    'description' => 'View complete order information',
                ];
                break;
        }

        // Common actions available for all statuses
        $actions[] = [
            'action' => 'view_invoice',
            'label' => 'View Invoice',
            'description' => 'View or download invoice',
        ];

        $actions[] = [
            'action' => 'contact_customer',
            'label' => 'Contact Customer',
            'description' => 'Send message to customer',
        ];

        return $actions;
    }

    /**
     * Get available actions for invoice
     */
    protected function getAvailableInvoiceActions(Invoice $invoice): array
    {
        $actions = [];

        switch ($invoice->status) {
            case 'draft':
                $actions[] = [
                    'action' => 'send_invoice',
                    'label' => 'Send Invoice',
                    'description' => 'Email invoice to customer',
                ];
                break;

            case 'sent':
                $actions[] = [
                    'action' => 'mark_paid',
                    'label' => 'Mark as Paid',
                    'description' => 'Record payment for this invoice',
                ];
                $actions[] = [
                    'action' => 'send_reminder',
                    'label' => 'Send Reminder',
                    'description' => 'Send payment reminder to customer',
                ];
                break;

            case 'paid':
                $actions[] = [
                    'action' => 'view_payment',
                    'label' => 'View Payment Details',
                    'description' => 'View payment information',
                ];
                break;
        }

        // Common actions
        $actions[] = [
            'action' => 'download_pdf',
            'label' => 'Download PDF',
            'description' => 'Download invoice as PDF',
        ];

        $actions[] = [
            'action' => 'print_invoice',
            'label' => 'Print Invoice',
            'description' => 'Print invoice',
        ];

        return $actions;
    }

    /**
     * Execute action based on QR code scan
     */
    public function executeAction(string $qrData, string $action, array $params = []): array
    {
        try {
            $scanResult = $this->processScannedData($qrData);

            if (!$scanResult['success']) {
                return $scanResult;
            }

            switch ($action) {
                case 'mark_processing':
                    return $this->markOrderAsProcessing($scanResult['order']);

                case 'mark_shipped':
                    return $this->markOrderAsShipped($scanResult['order'], $params);

                case 'mark_delivered':
                    return $this->markOrderAsDelivered($scanResult['order']);

                case 'mark_paid':
                    return $this->markInvoiceAsPaid($scanResult['invoice'], $params);

                case 'send_invoice':
                    return $this->sendInvoice($scanResult['invoice']);

                default:
                    return [
                        'success' => false,
                        'error' => 'Unknown action',
                    ];
            }

        } catch (\Exception $e) {
            Log::error("Error executing action {$action}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to execute action',
            ];
        }
    }

    /**
     * Mark order as processing
     */
    protected function markOrderAsProcessing(ShopifyOrder $order): array
    {
        try {
            $order->updateStatus('processing');

            return [
                'success' => true,
                'message' => 'Order marked as processing',
                'order' => $order->fresh(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to update order status',
            ];
        }
    }

    /**
     * Mark order as shipped
     */
    protected function markOrderAsShipped(ShopifyOrder $order, array $params = []): array
    {
        try {
            $order->markAsShipped();

            // Update fulfillment in Shopify
            $shopifyOrderSyncService = app(ShopifyOrderSyncService::class);
            $shopifyOrderSyncService->updateOrderStatusInShopify($order, 'shipped');

            return [
                'success' => true,
                'message' => 'Order marked as shipped',
                'order' => $order->fresh(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to mark order as shipped',
            ];
        }
    }

    /**
     * Mark order as delivered
     */
    protected function markOrderAsDelivered(ShopifyOrder $order): array
    {
        try {
            $order->markAsDelivered();

            return [
                'success' => true,
                'message' => 'Order marked as delivered',
                'order' => $order->fresh(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to mark order as delivered',
            ];
        }
    }

    /**
     * Mark invoice as paid
     */
    protected function markInvoiceAsPaid(Invoice $invoice, array $params = []): array
    {
        try {
            $amount = $params['amount'] ?? $invoice->total_amount;
            $paymentMethod = $params['payment_method'] ?? null;
            $reference = $params['reference'] ?? null;

            $invoice->markAsPaid($amount, $paymentMethod, $reference);

            return [
                'success' => true,
                'message' => 'Invoice marked as paid',
                'invoice' => $invoice->fresh(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to mark invoice as paid',
            ];
        }
    }

    /**
     * Send invoice to customer
     */
    protected function sendInvoice(Invoice $invoice): array
    {
        try {
            // This would integrate with email service
            $invoice->markAsSent();

            return [
                'success' => true,
                'message' => 'Invoice sent to customer',
                'invoice' => $invoice->fresh(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to send invoice',
            ];
        }
    }

    /**
     * Get QR code scan history
     */
    public function getScanHistory(int $limit = 50): array
    {
        // This would be implemented with a scan_logs table
        // For now, return empty array
        return [];
    }
}
