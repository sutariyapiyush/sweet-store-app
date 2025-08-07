<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Label - Order #{{ $order->order_number }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
        }

        .label-container {
            width: 210mm;
            min-height: 297mm;
            border: 1px solid #000;
            padding: 10mm;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .section {
            margin-bottom: 15px;
            padding: 8px;
            border: 1px solid #ccc;
        }

        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
            color: #333;
        }

        .two-column {
            display: flex;
            gap: 20px;
        }

        .column {
            flex: 1;
        }

        .qr-section {
            text-align: center;
            margin: 15px 0;
        }

        .qr-code {
            width: 150px;
            height: 150px;
            margin: 10px;
        }

        .barcode {
            width: 200px;
            height: 60px;
            margin: 10px auto;
        }

        .order-items table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .order-items th,
        .order-items td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        .order-items th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .totals {
            margin-top: 15px;
            text-align: right;
        }

        .totals table {
            margin-left: auto;
            border-collapse: collapse;
        }

        .totals td {
            padding: 4px 8px;
            border-bottom: 1px solid #ddd;
        }

        .total-final {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #000 !important;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007cba;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .tracking-info {
            background-color: #f0f8ff;
            border: 2px solid #007cba;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">Print Label</button>

    <div class="label-container">
        <!-- Header -->
        <div class="header">
            <h1>SHIPPING LABEL</h1>
            <h2>Order #{{ $order->order_number }}</h2>
            <p>Generated: {{ $labelData['generated_at'] }}</p>
        </div>

        <!-- Tracking Information -->
        @if($order->tracking_number)
        <div class="tracking-info">
            <div class="section-title">TRACKING INFORMATION</div>
            <div><strong>Tracking Number:</strong> {{ $order->tracking_number }}</div>
            <div><strong>Shipping Partner:</strong> {{ $order->shipping_partner }}</div>
            @if($order->shipped_at)
            <div><strong>Shipped Date:</strong> {{ $order->shipped_at->format('d/m/Y H:i') }}</div>
            @endif
        </div>
        @endif

        <!-- Order Information -->
        <div class="section">
            <div class="section-title">Order Information</div>
            <div class="two-column">
                <div class="column">
                    <strong>Order Number:</strong> {{ $labelData['order_number'] }}<br>
                    <strong>Invoice Number:</strong> {{ $labelData['invoice_number'] }}<br>
                    <strong>Order Date:</strong> {{ $labelData['order_date'] }}<br>
                    <strong>Status:</strong> {{ ucfirst($labelData['status']) }}
                </div>
                <div class="column">
                    <strong>Financial Status:</strong> {{ ucfirst($labelData['financial_status']) }}<br>
                    <strong>Currency:</strong> {{ $labelData['currency'] }}<br>
                    <strong>Total Amount:</strong> {{ $labelData['currency'] }} {{ number_format($labelData['total_amount'], 2) }}
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="section">
            <div class="section-title">Customer Information</div>
            <div class="two-column">
                <div class="column">
                    <strong>Name:</strong> {{ $labelData['customer_name'] ?: 'N/A' }}<br>
                    <strong>Email:</strong> {{ $labelData['customer_email'] ?: 'N/A' }}<br>
                    <strong>Phone:</strong> {{ $labelData['customer_phone'] ?: 'N/A' }}
                </div>
            </div>
        </div>

        <!-- Addresses -->
        <div class="two-column">
            <!-- Billing Address -->
            @if($labelData['billing_address'])
            <div class="section">
                <div class="section-title">Billing Address</div>
                @php $billing = $labelData['billing_address']; @endphp
                @if(is_array($billing))
                    {{ $billing['first_name'] ?? '' }} {{ $billing['last_name'] ?? '' }}<br>
                    {{ $billing['address1'] ?? '' }}<br>
                    @if(!empty($billing['address2']))
                        {{ $billing['address2'] }}<br>
                    @endif
                    {{ $billing['city'] ?? '' }} {{ $billing['zip'] ?? '' }}<br>
                    {{ $billing['province'] ?? '' }}, {{ $billing['country'] ?? '' }}<br>
                    @if(!empty($billing['phone']))
                        Phone: {{ $billing['phone'] }}
                    @endif
                @endif
            </div>
            @endif

            <!-- Shipping Address -->
            @if($labelData['shipping_address'])
            <div class="section">
                <div class="section-title">Shipping Address</div>
                @php $shipping = $labelData['shipping_address']; @endphp
                @if(is_array($shipping))
                    {{ $shipping['first_name'] ?? '' }} {{ $shipping['last_name'] ?? '' }}<br>
                    {{ $shipping['address1'] ?? '' }}<br>
                    @if(!empty($shipping['address2']))
                        {{ $shipping['address2'] }}<br>
                    @endif
                    {{ $shipping['city'] ?? '' }} {{ $shipping['zip'] ?? '' }}<br>
                    {{ $shipping['province'] ?? '' }}, {{ $shipping['country'] ?? '' }}<br>
                    @if(!empty($shipping['phone']))
                        Phone: {{ $shipping['phone'] }}
                    @endif
                @endif
            </div>
            @endif
        </div>

        <!-- Order Items -->
        @if($labelData['order_items'] && count($labelData['order_items']) > 0)
        <div class="section order-items">
            <div class="section-title">Order Items</div>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($labelData['order_items'] as $item)
                    <tr>
                        <td>{{ $item['title'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ $labelData['currency'] }} {{ number_format($item['price'], 2) }}</td>
                        <td>{{ $labelData['currency'] }} {{ number_format($item['total'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Order Totals -->
        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td>{{ $labelData['currency'] }} {{ number_format($labelData['subtotal'], 2) }}</td>
                </tr>
                @if($labelData['tax_amount'] > 0)
                <tr>
                    <td>Tax:</td>
                    <td>{{ $labelData['currency'] }} {{ number_format($labelData['tax_amount'], 2) }}</td>
                </tr>
                @endif
                @if($labelData['shipping_amount'] > 0)
                <tr>
                    <td>Shipping:</td>
                    <td>{{ $labelData['currency'] }} {{ number_format($labelData['shipping_amount'], 2) }}</td>
                </tr>
                @endif
                <tr class="total-final">
                    <td><strong>Total:</strong></td>
                    <td><strong>{{ $labelData['currency'] }} {{ number_format($labelData['total_amount'], 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Tracking QR Code Only -->
        <div class="qr-section">
            <div class="section-title">Tracking QR Code</div>
            @if(isset($trackingQrPath))
            <div style="text-align: center;">
                <img src="{{ asset('storage/' . $trackingQrPath) }}" alt="Tracking QR Code" class="qr-code">
                <div><strong>Scan for Order Tracking</strong></div>
                @if($order->tracking_number)
                <div>Tracking: {{ $order->tracking_number }}</div>
                @endif
            </div>
            @endif

            <!-- Tracking Barcode -->
            @if(isset($trackingBarcode))
            <div style="text-align: center; margin-top: 20px;">
                <div><strong>Tracking Barcode</strong></div>
                <img src="{{ asset('storage/' . $trackingBarcode) }}" alt="Tracking Barcode" class="barcode">
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div style="text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #ccc;">
            <p><strong>Thank you for your order!</strong></p>
            <p>For tracking and support, visit: {{ route('shopify.orders.track', $order) }}</p>
        </div>
    </div>
</body>
</html>
