<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Label - {{ $labelData['invoice_number'] }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .page-break { page-break-after: always; }
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
            font-size: 12px;
            line-height: 1.4;
        }

        .invoice-container {
            max-width: 15cm;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 15px;
            background: white;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .invoice-title {
            font-size: 16px;
            color: #007bff;
            font-weight: bold;
        }

        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .invoice-details {
            flex: 1;
        }

        .qr-section {
            text-align: center;
            flex: 0 0 100px;
        }

        .qr-code {
            width: 80px;
            height: 80px;
            border: 1px solid #ccc;
        }

        .qr-label {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
        }

        .customer-info {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            background: #f8f9fa;
        }

        .customer-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #007bff;
        }

        .customer-name {
            font-weight: bold;
            font-size: 14px;
        }

        .amount-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 2px solid #007bff;
            background: #f0f8ff;
            margin-bottom: 15px;
        }

        .amount-label {
            font-size: 14px;
            color: #007bff;
            font-weight: bold;
        }

        .amount-value {
            font-size: 18px;
            font-weight: bold;
            color: #000;
        }

        .status-section {
            text-align: center;
            margin-bottom: 15px;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-draft { background: #ffc107; color: #000; }
        .status-sent { background: #17a2b8; color: #fff; }
        .status-paid { background: #28a745; color: #fff; }
        .status-overdue { background: #dc3545; color: #fff; }

        .dates-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .date-item {
            text-align: center;
            flex: 1;
        }

        .date-label {
            color: #666;
            margin-bottom: 3px;
        }

        .date-value {
            font-weight: bold;
        }

        .barcode-section {
            text-align: center;
            margin-bottom: 15px;
        }

        .barcode {
            width: 150px;
            height: 40px;
            border: 1px solid #ccc;
        }

        .footer {
            border-top: 1px solid #ccc;
            padding-top: 10px;
            font-size: 10px;
            color: #666;
            text-align: center;
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
        <button class="btn" onclick="window.print()">🖨️ Print Invoice Label</button>
        <button class="btn btn-secondary" onclick="window.close()">❌ Close</button>
        <button class="btn btn-secondary" onclick="window.history.back()">⬅️ Back</button>
    </div>

    <div class="invoice-container">
        <div class="header">
            <div class="company-name">{{ config('app.name', 'Sweet Store') }}</div>
            <div class="invoice-title">INVOICE LABEL</div>
        </div>

        <div class="invoice-info">
            <div class="invoice-details">
                <div><strong>Invoice #:</strong> {{ $labelData['invoice_number'] }}</div>
                <div><strong>Order #:</strong> {{ $labelData['order_number'] }}</div>
                <div><strong>Generated:</strong> {{ $labelData['generated_at'] }}</div>
            </div>

            <div class="qr-section">
                @if(isset($qrPath) && $qrPath)
                    <img src="{{ asset('storage/' . $qrPath) }}" alt="Invoice QR Code" class="qr-code">
                    <div class="qr-label">Scan for Details</div>
                @endif
            </div>
        </div>

        <div class="customer-info">
            <div class="customer-title">📧 Customer Information</div>
            <div class="customer-name">{{ $labelData['customer_name'] }}</div>
            @if($labelData['customer_email'])
                <div>{{ $labelData['customer_email'] }}</div>
            @endif

            @if(isset($labelData['billing_address']) && $labelData['billing_address'])
                @php
                    $billing = $labelData['billing_address'];
                @endphp
                @if(is_array($billing))
                    <div style="margin-top: 8px; font-size: 11px;">
                        <div>{{ $billing['address1'] ?? '' }}</div>
                        @if(!empty($billing['address2']))
                            <div>{{ $billing['address2'] }}</div>
                        @endif
                        <div>{{ $billing['city'] ?? '' }} {{ $billing['zip'] ?? '' }}</div>
                        <div>{{ $billing['province'] ?? '' }}, {{ $billing['country'] ?? '' }}</div>
                    </div>
                @endif
            @endif
        </div>

        <div class="amount-section">
            <div class="amount-label">Total Amount:</div>
            <div class="amount-value">{{ $labelData['total_amount'] }}</div>
        </div>

        <div class="status-section">
            <div class="status-badge status-{{ $labelData['status'] }}">
                {{ ucfirst($labelData['status']) }}
            </div>
        </div>

        <div class="dates-section">
            <div class="date-item">
                <div class="date-label">Invoice Date</div>
                <div class="date-value">{{ $labelData['invoice_date'] }}</div>
            </div>
            @if(isset($labelData['due_date']) && $labelData['due_date'])
            <div class="date-item">
                <div class="date-label">Due Date</div>
                <div class="date-value">{{ $labelData['due_date'] }}</div>
            </div>
            @endif
        </div>

        @if(isset($barcodePath) && $barcodePath)
        <div class="barcode-section">
            <img src="{{ asset('storage/' . $barcodePath) }}" alt="Invoice Barcode" class="barcode">
            <div style="font-size: 9px; color: #666; margin-top: 3px;">
                {{ $labelData['invoice_number'] }}
            </div>
        </div>
        @endif

        <div class="footer">
            <div>Scan QR code to view full invoice details and payment status</div>
            <div style="margin-top: 5px;">
                For questions, contact: {{ $labelData['customer_email'] ?? 'support@' . request()->getHost() }}
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto_print') === '1') {
                setTimeout(() => window.print(), 500);
            }
        });
    </script>
</body>
</html>
