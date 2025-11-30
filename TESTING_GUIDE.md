# Build Mate Testing Guide

## Testing Order Flow & Review System

### Prerequisites
1. Have at least 2 user accounts:
   - **Buyer Account**: Regular user who can purchase products
   - **Supplier Account**: Supplier who sells products
   - **Admin Account**: (Optional) For managing orders

### Test Scenario 1: Complete Order Flow

#### Step 1: Create a Product (as Supplier)
1. Log in as a supplier
2. Go to "Manage Products" → "Add New Product"
3. Fill in product details and upload an image
4. Save the product

#### Step 2: Place an Order (as Buyer)
1. Log out and log in as a buyer
2. Browse catalog and find the product you just created
3. Click "Add to Cart"
4. Go to Cart and proceed to Checkout
5. Fill in delivery address and phone number
6. Complete payment via Paystack (use test mode)

**Expected Result:**
- Order status should be `'placed'` initially
- After Paystack payment, status should update to `'paid'`
- Payment Successful step should turn GREEN in the tracker
- Order should appear in "My Orders" page

#### Step 3: Supplier Processes Order
1. Log out and log in as the supplier
2. Go to "Supplier Orders"
3. Find the order (should show status "Paid")
4. Click "Mark as Processing"

**Expected Result:**
- Order status changes to `'processing'`
- "Supplier Processing" step should turn GREEN in tracker
- Order should still be visible to buyer

#### Step 4: Mark Out for Delivery
1. Still as supplier, click "Out for Delivery" button

**Expected Result:**
- Order status changes to `'out_for_delivery'`
- "Out for Delivery" step should turn GREEN
- Progress line should advance

#### Step 5: Mark as Delivered
1. Still as supplier, click "Mark as Delivered" button

**Expected Result:**
- Order status changes to `'delivered'`
- "Delivered" step should turn GREEN
- All steps should be completed
- Progress line should be 100%

### Test Scenario 2: Review System

#### Step 1: Try to Review Before Delivery
1. As buyer, go to the product page
2. Try to write a review

**Expected Result:**
- Review form should NOT appear
- Message should show: "You can only review products after delivery"

#### Step 2: Review After Delivery
1. After order is marked as "Delivered" (from Test Scenario 1)
2. As buyer, go to the product page
3. Click "Write a Review" button

**Expected Result:**
- Review form should appear
- You should be able to:
  - Select star rating (1-5)
  - Write review text
  - Submit the review

#### Step 3: Verify Review Appears
1. After submitting review, refresh the product page

**Expected Result:**
- Your review should appear in the reviews section
- Review should show:
  - Your name
  - Star rating
  - Review text
  - "Verified Purchase" badge
  - Date submitted

#### Step 4: Try to Review Again
1. Try to write another review for the same product

**Expected Result:**
- Review form should NOT appear
- Message should show: "You have already reviewed this product"

### Test Scenario 3: Payment Tracker Detection

#### Test Payment Reference Detection
1. Create an order and complete payment
2. Check browser console (F12 → Console tab)
3. Look for payment detection logs

**Expected Result:**
- Console should show: `Payment Detection: { orderStatus: 'paid', hasPaymentRef: true, ... }`
- Console should show: `✓ Payment Successful step marked as COMPLETED`
- Payment step should be GREEN in the UI

#### If Payment Step is NOT Green:
1. Check the order in database:
   ```sql
   SELECT id, status, payment_reference, payment_method 
   FROM orders 
   WHERE id = [ORDER_ID];
   ```

2. Verify:
   - `status` should be `'paid'`, `'processing'`, `'out_for_delivery'`, or `'delivered'`
   - `payment_reference` should NOT be NULL
   - `payment_method` should NOT be NULL

3. If missing, check `PaymentController::processPaymentSuccess()` to ensure it sets these fields

### Test Scenario 4: Multiple Orders & Reviews

#### Test Multiple Products from Same Supplier
1. As supplier, create 2-3 different products
2. As buyer, purchase all products
3. Mark all orders as delivered
4. Try to review each product

**Expected Result:**
- You should be able to review each product separately
- Each review should appear independently
- Reviews should be counted in product stats

### Database Queries for Testing

#### Check Order Status
```sql
SELECT o.id, o.status, o.payment_reference, o.payment_method, 
       o.created_at, u.name as buyer_name
FROM orders o
JOIN users u ON o.buyer_id = u.id
ORDER BY o.created_at DESC
LIMIT 10;
```

#### Check Reviews
```sql
SELECT r.*, u.name as reviewer_name, p.name as product_name
FROM reviews r
JOIN users u ON r.buyer_id = u.id
JOIN products p ON r.product_id = p.id
ORDER BY r.created_at DESC;
```

#### Check if User Can Review
```sql
-- Check if user has purchased and order is delivered
SELECT o.id, o.status, oi.product_id, p.name as product_name
FROM orders o
JOIN order_items oi ON o.id = oi.order_id
JOIN products p ON oi.product_id = p.id
WHERE o.buyer_id = [USER_ID]
AND o.status = 'delivered';
```

### Common Issues & Fixes

#### Issue: Payment Step Not Green
**Fix:**
1. Ensure `PaymentController::processPaymentSuccess()` sets:
   - `status = 'paid'`
   - `payment_reference` (from Paystack)
   - `payment_method = 'paystack'`

2. Check browser console for JavaScript errors

3. Verify `data-payment-reference` attribute is set in HTML

#### Issue: Can't Write Review
**Fix:**
1. Verify order status is `'delivered'`:
   ```sql
   SELECT status FROM orders WHERE id = [ORDER_ID];
   ```

2. Check if user already reviewed:
   ```sql
   SELECT * FROM reviews 
   WHERE buyer_id = [USER_ID] 
   AND product_id = [PRODUCT_ID];
   ```

3. Verify `Review::hasPurchasedProduct()` returns true for delivered orders

#### Issue: Review Not Appearing
**Fix:**
1. Check if review was saved:
   ```sql
   SELECT * FROM reviews WHERE product_id = [PRODUCT_ID];
   ```

2. Verify `Review::getByProduct()` is working correctly

3. Check for PHP errors in error logs

### Manual Status Updates (For Testing)

If you need to manually update order status for testing:

```sql
-- Update order to paid
UPDATE orders 
SET status = 'paid', 
    payment_reference = 'test_ref_123',
    payment_method = 'paystack'
WHERE id = [ORDER_ID];

-- Update order to processing
UPDATE orders SET status = 'processing' WHERE id = [ORDER_ID];

-- Update order to out_for_delivery
UPDATE orders SET status = 'out_for_delivery' WHERE id = [ORDER_ID];

-- Update order to delivered (allows reviews)
UPDATE orders SET status = 'delivered' WHERE id = [ORDER_ID];
```

### Testing Checklist

- [ ] Order placed → Status: `placed`
- [ ] Payment completed → Status: `paid`, Payment step GREEN
- [ ] Supplier marks processing → Status: `processing`, Processing step GREEN
- [ ] Supplier marks out for delivery → Status: `out_for_delivery`, Out for Delivery step GREEN
- [ ] Supplier marks delivered → Status: `delivered`, Delivered step GREEN
- [ ] Review form appears ONLY after delivery
- [ ] Review can be submitted after delivery
- [ ] Review appears on product page
- [ ] Cannot review same product twice
- [ ] Cannot review before delivery



