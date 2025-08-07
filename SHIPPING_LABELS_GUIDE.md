# Shipping Labels & QR Code Usage Guide

## Overview
Your Sweet Store application now has a complete shipping label and QR code generation system that integrates with Shopify orders. This guide explains how to use all the features.

## ✅ Current Status
- **QR Code Generation**: ✅ Working perfectly
- **Shipping Labels**: ✅ Fully functional
- **Routes**: ✅ All routes properly configured
- **Storage**: ✅ Permissions working correctly

## 🏷️ Available Label Types

### 1. Shipping Sticker (Compact - 10cm x 7cm)
- **Purpose**: Small sticker for parcels
- **Contains**: Customer info, address, QR code, order details
- **Perfect for**: Small packages, envelopes

### 2. Full Shipping Label (A4 Size)
- **Purpose**: Complete shipping documentation
- **Contains**: Full order details, customer info, items, QR codes, barcodes
- **Perfect for**: Large packages, detailed shipping

### 3. Invoice Label (A5 Size)
- **Purpose**: Invoice documentation with QR code
- **Contains**: Invoice details, payment info, QR code
- **Perfect for**: Billing documentation

## 🚀 How to Use Shipping Labels

### Step 1: Access Orders
1. Login to your application
2. Navigate to **Shopify → Orders**
3. Click on any order to view details

### Step 2: Generate Labels
From the order details page, you have several options:

#### Quick Actions:
- **🏷️ Generate Sticker**: Creates a compact shipping sticker
- **📄 Full Label**: Creates a complete shipping label
- **🖨️ Print Sticker**: Directly opens printable sticker
- **🖨️ Print Label**: Directly opens printable full label

#### API Endpoints:
```bash
# Generate shipping sticker
POST /shopify/orders/{order_id}/generate-shipping-sticker

# Generate full shipping label  
POST /shopify/orders/{order_id}/generate-shipping-label

# Generate invoice label
POST /shopify/invoices/{invoice_id}/generate-label
```

### Step 3: Print Labels
- Click any "Print" button to open a print-ready page
- The page will auto-format for printing
- Use your browser's print function (Ctrl+P)

## 📱 QR Code Features

### What's in the QR Code?
Each QR code contains:
```json
{
  "order_id": 1,
  "order_number": "1001", 
  "invoice_number": "INV-20250802-0001",
  "customer": "Customer Name",
  "total": "99.99",
  "status": "processing",
  "tracking_url": "https://yoursite.com/shopify/track/1"
}
```

### QR Code Scanning
- **Customer Use**: Customers can scan to track their order
- **Internal Use**: Staff can scan to update order status
- **Warehouse Use**: Quick access to order details

### Public Tracking
- Each QR code links to: `/shopify/track/{order_id}`
- No login required for customers
- Shows order status, tracking info, delivery updates

## 🔧 Available Routes

### Label Generation Routes:
```php
// Generate labels (AJAX)
POST /shopify/orders/{order}/generate-shipping-sticker
POST /shopify/orders/{order}/generate-shipping-label  
POST /shopify/invoices/{invoice}/generate-label

// Print labels (Direct view)
GET /shopify/orders/{order}/print-sticker
GET /shopify/orders/{order}/print-label
GET /shopify/invoices/{invoice}/print-label

// Bulk operations
POST /shopify/bulk-generate-labels
GET /shopify/label-templates
```

### QR Code & Tracking Routes:
```php
// QR code generation
POST /shopify/orders/{order}/generate-qr

// Public tracking (no auth required)
GET /shopify/track/{order}

// QR scanner interface
GET /shopify/qr-scanner
POST /shopify/process-scan
POST /shopify/execute-action
```

## 💡 Usage Examples

### Example 1: Generate Shipping Sticker via JavaScript
```javascript
function generateShippingSticker(orderId) {
    fetch(`/shopify/orders/${orderId}/generate-shipping-sticker`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Open print window
            window.open(data.print_url, '_blank');
        }
    });
}
```

### Example 2: Bulk Generate Labels
```javascript
fetch('/shopify/bulk-generate-labels', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
        order_ids: [1, 2, 3, 4, 5],
        label_type: 'shipping_sticker'
    })
})
.then(response => response.json())
.then(data => {
    console.log(`Generated ${data.generated} labels`);
});
```

### Example 3: Test QR Generation
```bash
# Run the built-in test command
php artisan test:qr-generation
```

## 🎯 Workflow Recommendations

### For Small Orders (< $50):
1. Use **Shipping Sticker** (compact)
2. Generate QR code for tracking
3. Print on adhesive label paper

### For Large Orders (> $50):
1. Use **Full Shipping Label** (detailed)
2. Generate both order and invoice QR codes
3. Print on standard paper
4. Include invoice label if needed

### For Bulk Processing:
1. Select multiple orders
2. Use bulk generation API
3. Print all labels at once
4. Sort by shipping method/destination

## 📋 Label Templates

### Available Templates:
```php
$templates = [
    'shipping_label' => [
        'name' => 'Full Shipping Label',
        'size' => 'A4',
        'template' => 'shopify.labels.shipping-label'
    ],
    'shipping_sticker' => [
        'name' => 'Compact Shipping Sticker', 
        'size' => '10cm x 7cm',
        'template' => 'shopify.labels.shipping-sticker'
    ],
    'invoice_label' => [
        'name' => 'Invoice Label',
        'size' => 'A5', 
        'template' => 'shopify.labels.invoice-label'
    ]
];
```

## 🔍 Troubleshooting

### QR Code Not Generating?
1. Check storage permissions: `php artisan storage:link`
2. Verify QR code package: `composer require simplesoftwareio/simple-qrcode`
3. Run test: `php artisan test:qr-generation`

### Labels Not Printing Correctly?
1. Check browser print settings
2. Ensure correct paper size selected
3. Use "Print backgrounds" option for better quality

### Routes Not Working?
1. Clear route cache: `php artisan route:clear`
2. Check route list: `php artisan route:list | grep shopify`

## 📁 File Locations

### Generated Files:
- **QR Codes**: `storage/app/qr_codes/`
- **Barcodes**: `storage/app/barcodes/`
- **Public Access**: `storage/app/public/`

### Templates:
- **Shipping Sticker**: `resources/views/shopify/labels/shipping-sticker.blade.php`
- **Shipping Label**: `resources/views/shopify/labels/shipping-label.blade.php`
- **Invoice Label**: `resources/views/shopify/labels/invoice-label.blade.php`

### Services:
- **Main Service**: `app/Services/ShippingLabelService.php`
- **QR Scanner**: `app/Services/QrCodeScannerService.php`

## 🎉 Success! Your System is Ready

Your shipping label and QR code system is now fully functional. You can:

✅ Generate shipping stickers and labels  
✅ Create QR codes for order tracking  
✅ Print professional-looking labels  
✅ Enable customer self-service tracking  
✅ Scan QR codes for order management  
✅ Bulk process multiple orders  

## 📞 Next Steps

1. **Test the system**: Visit `/shopify/orders` and try generating labels
2. **Train your team**: Show them how to use the print buttons
3. **Set up printers**: Configure label printers for best results
4. **Customer communication**: Share tracking URLs with customers

Your shipping workflow is now streamlined and professional! 🚀
