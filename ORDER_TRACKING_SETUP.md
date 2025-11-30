# Order Tracking System - Setup Guide

## ✅ What's Been Implemented

### 1. Database Migration
- **File**: `db/order_tracking_migration.sql`
- **Runner**: `run_order_tracking_migration_web.php`
- **What it does**:
  - Adds tracking fields to `orders` table (order_number, delivery_fee, customer_phone, special_instructions, current_status, timestamps)
  - Creates `order_status_history` table for tracking status changes
  - Generates order numbers for existing orders

### 2. Helper Functions
- **File**: `includes/order_functions.php`
- **Functions**:
  - `getOrderDetails()` - Get complete order info
  - `getOrderTimeline()` - Generate timeline stages
  - `updateOrderStatus()` - Update status with history logging
  - `getNextStatuses()` - Get valid next statuses
  - `formatCurrency()` - Format GHS currency
  - `getStatusBadge()` - Generate status badge HTML
  - `getUserOrders()` - Get filtered user orders

### 3. Order Detail Page with Timeline
- **File**: `views/Order/show.php` (updated)
- **Features**:
  - Beautiful vertical timeline showing all stages
  - Visual indicators (completed = green, current = brown with pulse, pending = gray)
  - Timestamps for completed/current stages
  - Estimated times for pending stages
  - Responsive design

### 4. Orders List Page
- **File**: `views/Order/index.php` (updated)
- **Features**:
  - Status filter tabs (All, Pending, Processing, Delivered)
  - Track Delivery button for paid orders
  - Enhanced status badges with colors
  - Responsive table

### 5. Admin Order Management
- **Controller**: `controllers/AdminOrderController.php`
- **Views**: 
  - `views/Admin/orders.php` - List all orders with filters
  - `views/Admin/order-details.php` - Order details with status update form
- **Features**:
  - View all orders
  - Filter by status
  - Update order status with notes
  - View order timeline
  - AJAX status updates

### 6. CSS Styling
- **File**: `assets/css/order-tracking.css`
- **Features**:
  - Timeline vertical line
  - Stage icons with animations
  - Pulsing animation for current stage
  - Responsive mobile design
  - Brand colors (brown theme)

### 7. Post-Payment Integration
- **File**: `controllers/PaymentController.php` (updated)
- **What it does**:
  - After payment, automatically sets status to `payment_confirmed`
  - Logs status change in history
  - Redirects to order detail page

## 🚀 Setup Steps

### Step 1: Run Database Migration
Visit: `http://localhost/build_mate/run_order_tracking_migration_web.php`

This will:
- Add tracking fields to orders table
- Create order_status_history table
- Generate order numbers for existing orders

### Step 2: Run Status Column Fix (if needed)
Visit: `http://localhost/build_mate/run_fix_status_column_web.php`

This ensures the status column can handle longer status values.

### Step 3: Run Payment Columns Migration (if needed)
Visit: `http://localhost/build_mate/run_payment_columns_migration_web.php`

This adds payment_reference and payment_method columns.

### Step 4: Test the Flow
1. Create an order
2. Complete payment
3. Check order detail page - should show timeline
4. As admin, update order status
5. Check timeline updates

## 📋 Order Status Flow

```
pending → payment_confirmed → processing → shipped → out_for_delivery → delivered
   ↓              ↓                ↓           ↓              ↓
cancelled    cancelled       cancelled    cancelled      cancelled
```

## 🎨 Timeline Stages

1. **Order Placed** (pending)
   - Icon: Cart check
   - Shows: Order placed timestamp

2. **Payment Confirmed** (payment_confirmed)
   - Icon: Check circle
   - Shows: Payment confirmed timestamp

3. **Processing Order** (processing)
   - Icon: Gear
   - Shows: Processing started timestamp

4. **Shipped** (shipped)
   - Icon: Box
   - Shows: Shipped timestamp

5. **Out for Delivery** (out_for_delivery)
   - Icon: Truck
   - Shows: Out for delivery timestamp or estimated time

6. **Delivered** (delivered)
   - Icon: Check circle fill
   - Shows: Delivered timestamp

## 🔧 Admin Usage

### View All Orders
- URL: `/build_mate/admin/orders`
- Filter by status using tabs

### Update Order Status
- URL: `/build_mate/admin/orders/{id}`
- Select new status from dropdown
- Add optional notes
- Click "Update Status"

### Status Update Rules
- Only shows valid next statuses
- Cannot skip stages
- Can cancel at any stage
- Automatically sets timestamps

## 🎯 Key Features

✅ **No External APIs** - Everything is database-driven
✅ **HTTP Compatible** - Works on school servers
✅ **Self-Contained** - No API keys needed
✅ **Mobile Responsive** - Works on all devices
✅ **Beautiful UI** - Modern, professional design
✅ **Status History** - Complete audit trail
✅ **Timeline Visualization** - Clear progress tracking

## 📱 Mobile Responsive

- Timeline stacks nicely on mobile
- Buttons full-width on small screens
- Tables scroll horizontally
- Touch-friendly interface

## 🔒 Security

- CSRF protection on status updates
- User authentication required
- Admin role check for admin pages
- Prepared statements for all queries
- Input sanitization

## 🐛 Troubleshooting

### Timeline not showing?
- Check if migration ran successfully
- Verify `order_status_history` table exists
- Check browser console for errors

### Status not updating?
- Check admin has proper role
- Verify CSRF token is included
- Check database permissions

### Button not appearing?
- Verify order has payment_reference or payment_method
- Check order status is in paid statuses
- Refresh page after payment

## 📝 Notes

- Timeline automatically creates delivery record if missing
- Status updates log to history table
- Timestamps are set automatically
- Order numbers format: BM-000001, BM-000002, etc.



