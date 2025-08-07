<?php

namespace App\Services;

use App\Models\ShopifyOrder;
use App\Models\Invoice;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ShippingLabelService
{
    /**
     * Generate shipping label for an order
     */
    public function generateShippingLabel(ShopifyOrder $order): array
    {
        try {
            // Ensure invoice exists
            $invoice = $order->invoice ?: $this->createInvoiceForOrder($order);

            // Generate tracking QR code only
            $trackingQrData = [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'tracking_number' => $order->tracking_number,
                'shipping_partner' => $order->shipping_partner,
                'tracking_url' => route('shopify.orders.track', $order),
                'customer' => $order->customer_name,
                'status' => $order->internal_status,
            ];

            $trackingQrPath = $this->generateTrackingQrCode($trackingQrData, "tracking_order_{$order->id}");

            // Generate shipping label data
            $labelData = $this->prepareLabelData($order, $invoice);

            // Generate barcode for tracking
            $trackingBarcode = $this->generateTrackingBarcode($order);

            return [
                'success' => true,
                'label_data' => $labelData,
                'tracking_qr_path' => $trackingQrPath,
                'tracking_barcode' => $trackingBarcode,
                'print_url' => route('shopify.orders.print-label', $order->id),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate shipping label: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to generate shipping label: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate invoice label for parcel
     */
    public function generateInvoiceLabel(Invoice $invoice): array
    {
        try {
            $order = $invoice->shopifyOrder;

            // Generate invoice QR code
            $invoiceQrPath = $invoice->generateQrCode();

            // Generate invoice barcode
            $invoiceBarcode = $this->generateInvoiceBarcode($invoice);

            $labelData = [
                'invoice_number' => $invoice->invoice_number,
                'order_number' => $order->order_number,
                'customer_name' => $invoice->customer_name,
                'customer_email' => $invoice->customer_email,
                'billing_address' => $invoice->billing_address,
                'shipping_address' => $invoice->shipping_address,
                'total_amount' => $invoice->formatted_total,
                'invoice_date' => $invoice->invoice_date->format('d/m/Y'),
                'due_date' => $invoice->due_date?->format('d/m/Y'),
                'status' => $invoice->status,
                'qr_code_path' => $invoiceQrPath,
                'barcode_path' => $invoiceBarcode,
                'generated_at' => now()->format('d/m/Y H:i'),
            ];

            return [
                'success' => true,
                'label_data' => $labelData,
                'qr_path' => $invoiceQrPath,
                'barcode_path' => $invoiceBarcode,
                'print_url' => route('shopify.invoices.print-label', $invoice->id),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate invoice label: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to generate invoice label: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Create invoice for order if it doesn't exist
     */
    protected function createInvoiceForOrder(ShopifyOrder $order): Invoice
    {
        // Extract customer name with multiple fallback strategies
        $customerName = $order->customer_name;

        // Try customer_data first
        if (!$customerName && $order->customer_data && is_array($order->customer_data)) {
            $customer = $order->customer_data;
            $firstName = $customer['first_name'] ?? '';
            $lastName = $customer['last_name'] ?? '';
            $customerName = trim($firstName . ' ' . $lastName);
        }

        // Try billing address
        if (!$customerName && $order->billing_address && is_array($order->billing_address)) {
            $billing = $order->billing_address;
            $firstName = $billing['first_name'] ?? '';
            $lastName = $billing['last_name'] ?? '';
            $customerName = trim($firstName . ' ' . $lastName);
        }

        // Try shipping address
        if (!$customerName && $order->shipping_address && is_array($order->shipping_address)) {
            $shipping = $order->shipping_address;
            $firstName = $shipping['first_name'] ?? '';
            $lastName = $shipping['last_name'] ?? '';
            $customerName = trim($firstName . ' ' . $lastName);
        }

        // Use email as fallback
        if (!$customerName && $order->customer_email) {
            $customerName = explode('@', $order->customer_email)[0];
        }

        // Final fallback
        if (!$customerName || trim($customerName) === '') {
            $customerName = 'Customer #' . $order->id;
        }

        return Invoice::create([
            'shopify_order_id' => $order->id,
            'invoice_number' => $order->generateInvoiceNumber(),
            'status' => 'draft',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'customer_name' => $customerName,
            'customer_email' => $order->customer_email ?: 'customer@example.com',
            'customer_phone' => $order->customer_phone,
            'billing_address' => $order->billing_address,
            'shipping_address' => $order->shipping_address,
            'subtotal' => $order->subtotal_price ?: 0,
            'tax_amount' => $order->total_tax ?: 0,
            'discount_amount' => $order->total_discounts ?: 0,
            'shipping_amount' => $order->shipping_price ?: 0,
            'total_amount' => $order->total_price ?: 0,
            'currency' => $order->currency ?: 'USD',
            'balance_due' => $order->total_price ?: 0,
        ]);
    }

    /**
     * Prepare comprehensive label data
     */
    protected function prepareLabelData(ShopifyOrder $order, Invoice $invoice): array
    {
        return [
            'order_number' => $order->order_number,
            'invoice_number' => $invoice->invoice_number,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'billing_address' => $order->billing_address,
            'shipping_address' => $order->shipping_address,
            'order_items' => $order->orderItems->map(function ($item) {
                return [
                    'title' => $item->title,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->quantity * $item->price,
                ];
            }),
            'subtotal' => $order->subtotal_price,
            'tax_amount' => $order->total_tax,
            'shipping_amount' => $order->shipping_price,
            'total_amount' => $order->total_price,
            'currency' => $order->currency,
            'order_date' => $order->created_at->format('d/m/Y'),
            'invoice_date' => $invoice->invoice_date->format('d/m/Y'),
            'status' => $order->internal_status,
            'financial_status' => $order->financial_status,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];
    }

    /**
     * Generate tracking QR code
     */
    protected function generateTrackingQrCode(array $data, string $filename): string
    {
        $qrCodeContent = json_encode($data);
        $fileName = "qr_codes/{$filename}.svg";

        // Generate QR code for tracking
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($qrCodeContent);

        Storage::disk('public')->put($fileName, $qrCode);

        return $fileName;
    }

    /**
     * Generate tracking barcode
     */
    protected function generateTrackingBarcode(ShopifyOrder $order): string
    {
        $trackingNumber = "TRK" . str_pad($order->id, 8, '0', STR_PAD_LEFT);
        $fileName = "barcodes/tracking_{$order->id}.svg";

        // Generate barcode using QR code as fallback
        $barcode = QrCode::format('svg')
            ->size(300)
            ->margin(1)
            ->generate($trackingNumber);

        Storage::disk('public')->put($fileName, $barcode);

        return $fileName;
    }

    /**
     * Generate invoice barcode
     */
    protected function generateInvoiceBarcode(Invoice $invoice): string
    {
        $fileName = "barcodes/invoice_{$invoice->id}.svg";

        // Generate barcode for invoice number
        $barcode = QrCode::format('svg')
            ->size(250)
            ->margin(1)
            ->generate($invoice->invoice_number);

        Storage::disk('public')->put($fileName, $barcode);

        return $fileName;
    }

    /**
     * Get label templates
     */
    public function getLabelTemplates(): array
    {
        return [
            'shipping_label' => [
                'name' => 'Full Shipping Label',
                'description' => 'Complete shipping label with order details, customer info, and tracking QR code',
                'size' => 'A4',
                'template' => 'shopify.labels.shipping-label',
            ],
            'invoice_label' => [
                'name' => 'Invoice Label',
                'description' => 'Invoice details with payment info and QR code',
                'size' => 'A5',
                'template' => 'shopify.labels.invoice-label',
            ],
        ];
    }

    /**
     * Bulk generate labels for multiple orders
     */
    public function bulkGenerateLabels(array $orderIds, string $labelType = 'shipping_label'): array
    {
        $results = [];
        $errors = [];

        foreach ($orderIds as $orderId) {
            try {
                $order = ShopifyOrder::findOrFail($orderId);

                switch ($labelType) {
                    case 'shipping_label':
                        $result = $this->generateShippingLabel($order);
                        break;
                    case 'invoice_label':
                        $invoice = $order->invoice ?: $this->createInvoiceForOrder($order);
                        $result = $this->generateInvoiceLabel($invoice);
                        break;
                    default:
                        throw new \Exception("Unknown label type: {$labelType}");
                }

                if ($result['success']) {
                    $results[] = $result;
                } else {
                    $errors[] = "Order {$orderId}: " . $result['error'];
                }

            } catch (\Exception $e) {
                $errors[] = "Order {$orderId}: " . $e->getMessage();
            }
        }

        return [
            'success' => count($results) > 0,
            'generated' => count($results),
            'errors' => count($errors),
            'results' => $results,
            'error_messages' => $errors,
        ];
    }
}
