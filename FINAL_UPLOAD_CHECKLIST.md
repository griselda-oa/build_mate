# FINAL UPLOAD CHECKLIST - BuildMate Production Deployment

## Current Issues on Production:
1. ❌ Checkout redirects to /orders instead of /payment
2. ❌ Order "View Details" shows 404
3. ❌ Product pages may show errors

## Root Cause:
PHP 8 strict type checking + string IDs from router = Type mismatch errors

---

## CRITICAL FILES TO UPLOAD (7 files)

### 1. controllers/OrderController.php
**What it fixes:**
- Order view details (404 error)
- Track delivery
- Confirm delivery
- Invoice download
- Dispute order

**Verify locally:**
```bash
grep -n "public function show(\$id): void" controllers/OrderController.php
# Should show line with: public function show($id): void
# Next line should have: $id = (int)$id;
```

---

### 2. controllers/PaymentController.php ⚠️ CRITICAL FOR CHECKOUT
**What it fixes:**
- Payment page loading
- Checkout redirect to payment (currently goes to /orders)
- Payment success page

**Verify locally:**
```bash
grep -n "public function show(\$orderId): void" controllers/PaymentController.php
# Should show line with: public function show($orderId): void
# Next line should have: $orderId = (int)$orderId;
```

**THIS IS THE FILE CAUSING YOUR CURRENT ISSUE!**

---

### 3. controllers/ProductController.php
**What it fixes:**
- Product detail pages
- Reviews display
- Wishlist functionality

**Verify locally:**
```bash
grep -n "\$productId = (int)\$product" controllers/ProductController.php
# Should show line with: $productId = (int)$product['id'];
```

---

### 4. controllers/SupplierController.php
**What it fixes:**
- Supplier dashboard
- Product management
- Order management for suppliers

**Verify locally:**
```bash
grep -n "\$supplierId = (int)\$supplier" controllers/SupplierController.php
# Should show multiple lines with supplier ID casting
```

---

### 5. classes/View.php
**What it fixes:**
- URL generation with APP_BASE_PATH
- Better production URL handling

**Verify locally:**
```bash
grep -n "APP_BASE_PATH" classes/View.php
# Should show lines checking for APP_BASE_PATH from .env
```

---

### 6. views/Home/dashboard.php
**What it fixes:**
- Order links in customer dashboard
- View Details button

**Verify locally:**
```bash
grep -n "relUrl('/orders/" views/Home/dashboard.php
# Should show relUrl (not url) for order links
```

---

### 7. views/Home/index-new.php
**What it fixes:**
- Role-based sidebar navigation
- Admin/Orders/Supplier dashboard links

---

## UPLOAD INSTRUCTIONS

### Method 1: FTP/SFTP (Recommended)
```
Server: hc.cetplus.com
Path: /home/xxxafmdz/hc.cetplus.com/build_mate/

Upload these files:
1. controllers/OrderController.php
2. controllers/PaymentController.php      ← MOST CRITICAL
3. controllers/ProductController.php
4. controllers/SupplierController.php
5. classes/View.php
6. views/Home/dashboard.php
7. views/Home/index-new.php
```

### Method 2: Command Line (if you have SSH)
```bash
# From your local machine
cd /Applications/XAMPP/xamppfiles/htdocs/build_mate

# Upload all at once
scp controllers/OrderController.php \
    controllers/PaymentController.php \
    controllers/ProductController.php \
    controllers/SupplierController.php \
    classes/View.php \
    views/Home/dashboard.php \
    views/Home/index-new.php \
    user@hc.cetplus.com:/home/xxxafmdz/hc.cetplus.com/build_mate/
```

---

## AFTER UPLOAD - VERIFICATION

### Step 1: Upload verify_upload.php
Upload the verification script to your server

### Step 2: Run Verification
Visit: https://hc.cetplus.com/build_mate/verify_upload.php

**Expected Results:**
- ✅ All files show "FIXED" in green
- If OpCache enabled, click "Clear OpCache Now"

### Step 3: Test Checkout Flow
1. Add items to cart
2. Go to checkout: https://hc.cetplus.com/build_mate/checkout
3. Fill delivery form
4. Click "Continue to Payment"
5. **Expected**: Should redirect to https://hc.cetplus.com/build_mate/payment/[ORDER_ID]
6. **Current Bug**: Redirects to https://hc.cetplus.com/build_mate/orders

### Step 4: Test Order View
1. Go to: https://hc.cetplus.com/build_mate/orders
2. Click "View Details" on any order
3. **Expected**: Should show order details page
4. **Current Bug**: Shows 404 error

---

## TROUBLESHOOTING

### If still getting errors after upload:

**1. Clear PHP OpCache**
```php
<?php opcache_reset(); ?>
```

**2. Check file permissions**
```bash
chmod 644 controllers/*.php
chmod 644 classes/*.php
chmod 644 views/Home/*.php
```

**3. Verify files uploaded to correct location**
```bash
ls -la /home/xxxafmdz/hc.cetplus.com/build_mate/controllers/
# Should show recent modification dates
```

**4. Check server error logs**
```bash
tail -f /home/xxxafmdz/hc.cetplus.com/build_mate/error_log
# OR
tail -f /var/log/apache2/error.log
```

**5. Clear browser cache**
- Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
- Or use Incognito/Private mode

---

## PRIORITY ORDER

If you can only upload one file at a time, do this order:

1. **PaymentController.php** ← Fixes checkout redirect issue
2. **OrderController.php** ← Fixes order view 404
3. **ProductController.php** ← Fixes product pages
4. **SupplierController.php** ← Fixes supplier dashboard
5. **View.php** ← Improves URL handling
6. **dashboard.php** ← Fixes dashboard links
7. **index-new.php** ← Adds navigation features

---

## SUCCESS CRITERIA

✅ Checkout redirects to payment page (not /orders)
✅ Payment page loads without errors
✅ Order "View Details" shows order page (not 404)
✅ Product pages load with reviews
✅ Supplier dashboard loads without errors
✅ All order tracking features work

---

## IMPORTANT NOTES

- All fixes are tested and ready on local machine
- No database changes required
- No .env changes required
- Files must be uploaded to exact paths shown
- OpCache must be cleared after upload
- Browser cache should be cleared for testing

---

**Last Updated**: December 3, 2025, 9:42 PM
**Status**: Ready for deployment
**Critical File**: PaymentController.php (fixes checkout issue)
