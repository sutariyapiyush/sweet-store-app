<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .order-number {
            font-size: 18px;
            opacity: 0.9;
        }

        .content {
            padding: 30px 20px;
        }

        .status-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .status-badge {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .status-pending { background: #fff3cd; color: #856404; border: 2px solid #ffeaa7; }
        .status-processing { background: #d1ecf1; color: #0c5460; border: 2px solid #bee5eb; }
        .status-shipped { background: #d4edda; color: #155724; border: 2px solid #c3e6cb; }
        .status-delivered { background: #e2e3e5; color: #383d41; border: 2px solid #d6d8db; }

        .status-description {
            color: #666;
            font-size: 14px;
        }

        .progress-bar {
            margin: 30px 0;
            position: relative;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 10px;
        }

        .progress-line {
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e9ecef;
            z-index: 1;
        }

        .progress-line-active {
            background: #28a745;
            height: 2px;
            transition: width 0.3s ease;
            z-index: 2;
        }

        .step {
            background: white;
            border: 3px solid #e9ecef;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            position: relative;
            z-index: 3;
        }

        .step.active {
            border-color: #28a745;
            background: #28a745;
            color: white;
        }

        .step.completed {
            border-color: #28a745;
            background: #28a745;
            color: white;
        }

        .step-labels {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #666;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }

        .info-title {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .info-content {
            font-size: 13px;
            line-height: 1.4;
        }

        .customer-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .order-items {
            margin-bottom: 30px;
        }

        .items-title {
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            font-size: 16px;
        }

        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .item:last-child {
            border-bottom: none;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-weight: 500;
            margin-bottom: 3px;
        }

        .item-details {
            font-size: 12px;
            color: #666;
        }

        .item-total {
            font-weight: bold;
            color: #007bff;
        }

        .total-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .total-row.final {
            font-weight: bold;
            font-size: 18px;
            color: #007bff;
            border-top: 2px solid #dee2e6;
            padding-top: 10px;
            margin-top: 10px;
            margin-bottom: 0;
        }

        .actions {
            text-align: center;
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin: 0 10px;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }

        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .container {
                margin: 10px;
                border-radius: 8px;
            }

            .header {
                padding: 20px 15px;
            }

            .content {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">{{ config('app.name', 'Sweet Store') }}</div>
            <div class="order-number">Order #{{ $order->order_number }}</div>
        </div>

        <div class="content">
            <div class="status-section">
                <div class="status-badge status-{{ $order->internal_status }}">
                    {{ ucfirst($order->internal_status) }}
                </div>
                <div class="status-description">
                    @switch($order->internal_status)
                        @case('pending')
                            Your order has been received and is being prepared.
                            @break
                        @case('processing')
                            Your order is currently being processed and prepared for shipment.
                            @break
                        @case('shipped')
                            Your order has been shipped and is on its way to you.
                            @break
                        @case('delivered')
                            Your order has been successfully delivered.
                            @break
                        @default
                            Order status: {{ ucfirst($order->internal_status) }}
                    @endswitch
                </div>
            </div>

            <div class="progress-bar">
                <div class="progress-steps">
                    <div class="progress-line"></div>
                    @php
                        $progressWidth = '0%';
                        if ($order->internal_status === 'processing') $progressWidth = '33%';
                        elseif ($order->internal_status === 'shipped') $progressWidth = '66%';
                        elseif ($order->internal_status === 'delivered') $progressWidth = '100%';
                    @endphp
                    <div class="progress-line-active" style="width: {{ $progressWidth }};"></div>

                    <div class="step {{ in_array($order->internal_status, ['pending', 'processing', 'shipped', 'delivered']) ? 'completed' : '' }}">1</div>
                    <div class="step {{ in_array($order->internal_status, ['processing', 'shipped', 'delivered']) ? 'completed' : ($order->internal_status === 'processing' ? 'active' : '') }}">2</div>
                    <div class="step {{ in_array($order->internal_status, ['shipped', 'delivered']) ? 'completed' : ($order->internal_status === 'shipped' ? 'active' : '') }}">3</div>
                    <div class="step {{ $order->internal_status === 'delivered' ? 'completed' : ($order->internal_status === 'delivered' ? 'active' : '') }}">4</div>
                </div>
                <div class="step-labels">
                    <span>Pending</span>
                    <span>Processing</span>
                    <span>Shipped</span>
                    <span>Delivered</span>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <div class="info-title">📧 Customer Information</div>
                    <div class="info-content">
                        <div class="customer-name">{{ $order->customer_name }}</div>
                        @if($order->customer_email)
                            <div>{{ $order->customer_email }}</div>
                        @endif
                        @if($order->customer_phone)
                            <div>📞 {{ $order->customer_phone }}</div>
                        @endif
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-title">🚚 Shipping Address</div>
                    <div class="info-content">
                        @if($order->shipping_address)
                            @php
                                $shipping = $order->shipping_address;
                            @endphp
                            @if(is_array($shipping))
                                <div>{{ $shipping['address1'] ?? '' }}</div>
                                @if(!empty($shipping['address2']))
                                    <div>{{ $shipping['address2'] }}</div>
                                @endif
                                <div>{{ $shipping['city'] ?? '' }} {{ $shipping['zip'] ?? '' }}</div>
                                <div>{{ $shipping['province'] ?? '' }}, {{ $shipping['country'] ?? '' }}</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            @if($order->orderItems && count($order->orderItems) > 0)
            <div class="order-items">
                <div class="items-title">📦 Order Items</div>
                @foreach($order->orderItems as $item)
                <div class="item">
                    <div class="item-info">
                        <div class="item-name">{{ $item->title }}</div>
                        <div class="item-details">Qty: {{ $item->quantity }} × {{ $order->currency }} {{ number_format($item->price, 2) }}</div>
                    </div>
                    <div class="item-total">{{ $order->currency }} {{ number_format($item->quantity * $item->price, 2) }}</div>
                </div>
                @endforeach
            </div>
            @endif

            <div class="total-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>{{ $order->currency }} {{ number_format($order->subtotal_price, 2) }}</span>
                </div>
                @if($order->total_tax > 0)
                <div class="total-row">
                    <span>Tax:</span>
                    <span>{{ $order->currency }} {{ number_format($order->total_tax, 2) }}</span>
                </div>
                @endif
                @if($order->shipping_price > 0)
                <div class="total-row">
                    <span>Shipping:</span>
                    <span>{{ $order->currency }} {{ number_format($order->shipping_price, 2) }}</span>
                </div>
                @endif
                <div class="total-row final">
                    <span>Total:</span>
                    <span>{{ $order->currency }} {{ number_format($order->total_price, 2) }}</span>
                </div>
            </div>

            @if($order->invoice)
            <div class="info-card" style="margin-bottom: 20px;">
                <div class="info-title">🧾 Invoice Information</div>
                <div class="info-content">
                    <div><strong>Invoice #:</strong> {{ $order->invoice->invoice_number }}</div>
                    <div><strong>Status:</strong> {{ ucfirst($order->invoice->status) }}</div>
                    <div><strong>Date:</strong> {{ $order->invoice->invoice_date->format('M d, Y') }}</div>
                    @if($order->invoice->due_date)
                        <div><strong>Due Date:</strong> {{ $order->invoice->due_date->format('M d, Y') }}</div>
                    @endif
                </div>
            </div>
            @endif

            <div class="actions">
                @if($order->internal_status === 'shipped' || $order->internal_status === 'delivered')
                    <a href="#" class="btn" onclick="alert('Tracking details would be shown here')">📍 Track Package</a>
                @endif
                <a href="mailto:support@{{ request()->getHost() }}" class="btn btn-secondary">📧 Contact Support</a>
            </div>
        </div>

        <div class="footer">
            <div>Order placed on {{ $order->created_at->format('M d, Y \a\t g:i A') }}</div>
            <div style="margin-top: 5px;">
                Last updated: {{ $order->updated_at->format('M d, Y \a\t g:i A') }}
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh every 30 seconds if order is not delivered
        @if($order->internal_status !== 'delivered')
            setTimeout(function() {
                window.location.reload();
            }, 30000);
        @endif
    </script>
</body>
</html>
