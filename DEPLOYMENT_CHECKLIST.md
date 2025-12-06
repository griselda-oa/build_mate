# Deployment Checklist for Production Server

## Files That MUST Be Uploaded to Fix Errors

### Critical Files (Fix Type Casting Errors):

1. **controllers/OrderController.php**
   - Fixed: `show()`, `getStatus()`, `trackDelivery()`, `confirmDelivery()`, `dispute()`, `invoice()`
   - Issue: All methods now accept string IDs from router and cast to int
   - Error Fixed: 404 errors when viewing orders

2. **controllers/SupplierController.php**
   - Fixed: Multiple methods with supplier ID type casting
   - Issue: `getRecentProducts()`, `getRecentOrders()`, etc. now handle string IDs
   - Error Fixed: Supplier dashboard errors

3. **controllers/ProductController.php**
   - Fixed: `show()` method with product, user, and supplier ID casting
   - Issue: Review and wishlist methods now handle string IDs
   - Error Fixed: Product detail page errors

4. **classes/View.php**
   - Added: Support for `APP_BASE_PATH` from .env
   - Issue: Prioritizes .env setting over auto-detection
   - Feature: Better URL generation for production

5. **views/Home/dashboard.php**
   - Fixed: Changed `url()` to `relUrl()` for order links
   - Issue: Order detail links now use proper base path
   - Error Fixed: 404 on customer dashboard order links

6. **views/Home/index-new.php**
   - Added: Role-based navigation (Admin Dashboard, My Orders, Supplier Dashboard)
   - Feature: Dynamic sidebar menu based on user role

## How to Deploy

### Option 1: FTP/SFTP Upload
```bash
# Upload these files to your server:
/controllers/OrderController.php
/controllers/SupplierController.php
/controllers/ProductController.php
/classes/View.php
/views/Home/dashboard.php
/views/Home/index-new.php
```

### Option 2: Git Deployment
```bash
# On your local machine:
git add controllers/OrderController.php
git add controllers/SupplierController.php
git add controllers/ProductController.php
git add classes/View.php
git add views/Home/dashboard.php
git add views/Home/index-new.php
git commit -m "Fix type casting errors for PHP 8 strict types and update navigation"
git push

# On your production server:
cd /home/xxxafmdz/hc.cetplus.com/build_mate
git pull
```

### Option 3: Direct Copy (if you have SSH access)
```bash
# From your local machine:
scp controllers/OrderController.php user@hc.cetplus.com:/home/xxxafmdz/hc.cetplus.com/build_mate/controllers/
scp controllers/SupplierController.php user@hc.cetplus.com:/home/xxxafmdz/hc.cetplus.com/build_mate/controllers/
scp controllers/ProductController.php user@hc.cetplus.com:/home/xxxafmdz/hc.cetplus.com/build_mate/controllers/
scp classes/View.php user@hc.cetplus.com:/home/xxxafmdz/hc.cetplus.com/build_mate/classes/
scp views/Home/dashboard.php user@hc.cetplus.com:/home/xxxafmdz/hc.cetplus.com/build_mate/views/Home/
scp views/Home/index-new.php user@hc.cetplus.com:/home/xxxafmdz/hc.cetplus.com/build_mate/views/Home/
```

## Verification Steps

After uploading, test these URLs:

1. **Order Details**: `https://hc.cetplus.com/build_mate/orders/2`
   - Should show order details page (not 404)

2. **Product Details**: `https://hc.cetplus.com/build_mate/product/any-product-slug`
   - Should show product page with reviews

3. **Supplier Dashboard**: `https://hc.cetplus.com/build_mate/supplier/dashboard`
   - Should load without errors

4. **Customer Dashboard**: `https://hc.cetplus.com/build_mate/dashboard`
   - Order "View Details" buttons should work

5. **Homepage Sidebar**: `https://hc.cetplus.com/build_mate/`
   - Should show role-based menu items

## Root Cause

**Problem**: PHP's PDO returns all database values as strings by default, but the controller methods had strict `int` type hints.

**Solution**: Removed strict type hints and added explicit `(int)` casting inside each method.

**Example**:
```php
// Before (causes error):
public function show(int $id): void

// After (works):
public function show($id): void
{
    $id = (int)$id; // Cast to int
    // ... rest of code
}
```

## Important Notes

- ✅ All changes are backward compatible
- ✅ No database changes required
- ✅ No .env changes required (unless migrating domains)
- ✅ Works with both PHP 7.4 and PHP 8.x
- ⚠️ Make sure to clear any PHP opcache after deployment

## If Issues Persist

1. Check PHP error logs on server
2. Verify file permissions (644 for PHP files)
3. Clear browser cache
4. Check Apache/Nginx configuration
5. Verify .htaccess is present and correct

---

**Last Updated**: December 3, 2025
**Deployment Required**: YES - 6 files need to be uploaded
