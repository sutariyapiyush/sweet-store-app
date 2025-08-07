<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Sticker - {{ $stickerData['order_number'] }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .page-break { page-break-after: always; }
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            background: white;
        }

        .sticker-container {
            width: 10cm;
            height: 7cm;
            border: 2px solid #000;
            padding: 8px;
            box-sizing: border-box;
            position: relative;
            background: white;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }

        .company-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .order-info {
            font-size: 10px;
            color: #666;
        }

        .main-content {
            display: flex;
            height: calc(100% - 40px);
        }

        .left-section {
            flex: 1;
            padding-right: 8px;
        }

        .right-section {
            width: 60px;
            text-align: center;
        }

        .customer-name {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .shipping-address {
            font-size: 9px;
            line-height: 1.3;
            margin-bottom: 6px;
        }

        .order-details {
            font-size: 8px;
            color: #666;
        }

        .qr-code {
            width: 55px;
            height: 55px;
            border: 1px solid #ddd;
        }

        .qr-label {
            font-size: 7px;
            margin-top: 2px;
            color: #666;
        }

        .footer {
            position: absolute;
            bottom: 5px;
            left: 8px;
            right: 8px;
            font-size: 7px;
            color: #999;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 2px;
        }

        .print-controls {
            margin-bottom: 20px;
            text-align: center;
        }

        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 5px;
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
    </style>
</head>
<body>
    <div class="print-controls no-print">
        <button class="btn" onclick="window.print()">🖨️ Print Sticker</button>
        <button class="btn btn-secondary" onclick="window.close()">❌ Close</button>
        <button class="btn btn-secondary" onclick="window.history.back()">⬅️ Back</button>
    </div>

    <div class="sticker-container">
        <div class="header">
            <div class="company-name">{{ config('app.name', 'Sweet Store') }}</div>
            <div class="order-info">
                Order: {{ $stickerData['order_number'] }} |
                Invoice: {{ $stickerData['invoice_number'] }}
            </div>
        </div>

        <div class="main-content">
            <div class="left-section">
                <div class="customer-name">
                    {{ $stickerData['customer_name'] }}
                </div>

                <div class="shipping-address">
                    @if(isset($stickerData['shipping_address']))
                        @php
                            $address = $stickerData['shipping_address'];
                        @endphp
                        @if(is_array($address))
                            {{ $address['address1'] ?? '' }}
                            @if(!empty($address['address2']))
                                <br>{{ $address['address2'] }}
                            @endif
                            <br>{{ $address['city'] ?? '' }} {{ $address['zip'] ?? '' }}
                            <br>{{ $address['province'] ?? '' }}, {{ $address['country'] ?? '' }}
                        @else
                            {{ $address }}
                        @endif
                    @endif
                </div>

                <div class="order-details">
                    <strong>Total: {{ $stickerData['total_amount'] }}</strong><br>
                    Generated: {{ $stickerData['generated_at'] }}
                </div>
            </div>

            <div class="right-section">
                @if(isset($qrPath) && $qrPath)
                    <img src="{{ asset('storage/' . $qrPath) }}" alt="QR Code" class="qr-code">
                    <div class="qr-label">Scan to Track</div>
                @else
                    <div style="width: 55px; height: 55px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; font-size: 8px; color: #999;">
                        QR Code
                    </div>
                @endif
            </div>
        </div>

        <div class="footer">
            Scan QR code to track order status and delivery
        </div>
    </div>

    <script>
        // Auto-focus for printing
        window.addEventListener('load', function() {
            // Auto-print if requested
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto_print') === '1') {
                setTimeout(() => window.print(), 500);
            }
        });
    </script>
</body>
</html>
