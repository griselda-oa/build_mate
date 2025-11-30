# Paystack Integration Setup Guide

## Required Credentials

To integrate Paystack payment, you need the following credentials from your Paystack account:

### 1. **Public Key** (Test/Live)
   - Used for frontend payment initialization
   - Format: `pk_test_...` (test) or `pk_live_...` (live)
   - Get it from: Paystack Dashboard → Settings → API Keys & Webhooks

### 2. **Secret Key** (Test/Live)
   - Used for backend payment verification
   - Format: `sk_test_...` (test) or `sk_live_...` (live)
   - Get it from: Paystack Dashboard → Settings → API Keys & Webhooks
   - ⚠️ **Keep this secret! Never expose it in frontend code.**

### 3. **Payment Mode**
   - `mock` - For testing without Paystack (default)
   - `test` - For Paystack test mode
   - `live` - For production payments

## Setup Instructions

### Step 1: Get Your Paystack API Keys

1. Sign up at [https://paystack.com](https://paystack.com)
2. Log in to your Paystack Dashboard
3. Go to **Settings** → **API Keys & Webhooks**
4. Copy your **Test Public Key** and **Test Secret Key** (for development)
5. For production, use **Live Public Key** and **Live Secret Key**

### Step 2: Add Keys to Environment File

Add the following to your `.env` file in the project root:

```env
# Paystack Configuration
PAYMENT_MODE=test
PAYSTACK_PUBLIC_KEY=pk_test_your_test_public_key_here
PAYSTACK_SECRET_KEY=sk_test_your_test_secret_key_here
```

For production:
```env
PAYMENT_MODE=live
PAYSTACK_PUBLIC_KEY=pk_live_your_live_public_key_here
PAYSTACK_SECRET_KEY=sk_live_your_live_secret_key_here
```

### Step 3: Configure Email Settings (Optional)

For email notifications, add to `.env`:

```env
# Email Configuration
EMAIL_FROM=noreply@buildmate.com
EMAIL_FROM_NAME=Build Mate Ghana
APP_URL=http://localhost/build_mate
```

For SMTP (recommended for production):
```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
SMTP_ENCRYPTION=tls
```

### Step 4: Run Database Migration

Visit: `http://localhost/build_mate/run_driver_fields_migration_web.php`

This adds driver/rider fields to the deliveries table.

### Step 5: Test Payment Flow

1. Add items to cart
2. Go to checkout
3. Fill delivery address
4. Click "Continue to Payment"
5. Click "Pay with Paystack"
6. Use Paystack test cards:
   - **Success**: `4084084084084081`
   - **Decline**: `5060666666666666666`
   - **3D Secure**: `5060666666666666669`
   - Use any future expiry date and any CVV

## Payment Flow

1. **Checkout** → User fills delivery address
2. **Payment Page** → User clicks "Pay with Paystack"
3. **Paystack Gateway** → User completes payment
4. **Callback** → Paystack redirects back with reference
5. **Verification** → Backend verifies payment
6. **Order Confirmation** → Order status updated, emails sent
7. **Delivery Tracking** → User can track delivery with driver details

## Email Notifications

The system automatically sends:
- **Order Confirmation** - When order is created
- **Payment Confirmation** - When payment is verified
- **Delivery Updates** - When delivery status changes (with driver info)

## Testing Without Paystack

If you don't have Paystack keys yet, the system works in `mock` mode:
- Set `PAYMENT_MODE=mock` in `.env`
- Payments will be simulated
- No actual money is processed

## Security Notes

- ✅ Never commit `.env` file to version control
- ✅ Use test keys for development
- ✅ Switch to live keys only in production
- ✅ Keep secret keys secure
- ✅ Use HTTPS in production
- ✅ Verify webhook signatures (future enhancement)

## Support

For Paystack API documentation:
- [Paystack API Docs](https://paystack.com/docs/api)
- [Paystack Test Cards](https://paystack.com/docs/payments/test-payments)



