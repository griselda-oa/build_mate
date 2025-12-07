<!-- Modern Payment Page -->
<div class="payment-page-modern">
    <!-- Header -->
    <div class="payment-header-modern mb-5">
        <div class="container">
            <a href="<?= \App\View::relUrl('/checkout') ?>" class="back-button-modern">
                <i class="icon-arrow-left"></i>
                <span>Back to Checkout</span>
            </a>
            <h1 class="payment-title-modern">
                <i class="bi bi-credit-card-2-front"></i>
                Complete Payment
            </h1>
            <p class="payment-subtitle-modern">Secure payment powered by Paystack</p>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="container mb-4">
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                <?= \App\View::e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="row g-4">
            <!-- Left: Payment Form -->
            <div class="col-lg-7">
                <div class="payment-card-modern">
                    <div class="payment-card-header-modern">
                        <div class="d-flex align-items-center">
                            <div class="payment-icon-wrapper-modern">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <div>
                                <h3 class="payment-card-title-modern mb-0">Payment Details</h3>
                                <p class="payment-card-subtitle-modern mb-0">Your payment is secure and encrypted</p>
                            </div>
                        </div>
                    </div>
                    <div class="payment-card-body-modern">
                        <!-- Order Summary -->
                        <div class="payment-order-summary-modern mb-4">
                            <h5 class="mb-3">
                                <i class="bi bi-receipt"></i>
                                Order Summary
                            </h5>
                            <div class="payment-items-modern">
                                <?php 
                                $orderCurrency = $order['currency'] ?? 'GHS';
                                foreach ($order['items'] ?? [] as $item): 
                                ?>
                                    <div class="payment-item-modern">
                                        <span class="payment-item-name-modern"><?= \App\View::e($item['product_name'] ?? $item['name'] ?? 'Product') ?></span>
                                        <span class="payment-item-qty-modern">x <?= $item['qty'] ?? $item['quantity'] ?? 1 ?></span>
                                        <span class="payment-item-price-modern">
                                            <?= \App\Money::format(($item['price_cents'] ?? 0) * ($item['qty'] ?? $item['quantity'] ?? 1), $orderCurrency) ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="payment-total-modern">
                                <span>Total Amount:</span>
                                <strong><?= \App\Money::format($order['total_cents'] ?? 0, $orderCurrency) ?></strong>
                            </div>
                        </div>

                        <!-- Paystack Payment Button -->
                        <div class="paystack-payment-section-modern">
                            <?php if (!empty($paystack_public_key)): ?>
                                <button type="button" id="paystackBtn" class="paystack-button-modern">
                                    <span class="btn-content">
                                        <i class="bi bi-credit-card"></i>
                                        <span>Pay with Paystack</span>
                                    </span>
                                    <span class="btn-loader" style="display: none;">
                                        <span class="spinner-border spinner-border-sm" role="status"></span>
                                        Processing...
                                    </span>
                                </button>
                                
                                <div class="payment-security-modern">
                                    <div class="security-badges-modern">
                                        <span class="security-badge-modern">
                                            <i class="bi bi-lock-fill"></i>
                                            SSL Encrypted
                                        </span>
                                        <span class="security-badge-modern">
                                            <i class="bi bi-shield-check"></i>
                                            Secure Payment
                                        </span>
                                        <span class="security-badge-modern">
                                            <i class="bi bi-bank"></i>
                                            Powered by Paystack
                                        </span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    Paystack is not configured. Please add your API keys to continue.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Order Details -->
            <div class="col-lg-5">
                <div class="order-details-card-modern sticky-top-modern">
                    <div class="order-details-header-modern">
                        <h3 class="mb-0">
                            <i class="bi bi-info-circle"></i>
                            Order Information
                        </h3>
                    </div>
                    <div class="order-details-body-modern">
                        <div class="order-info-item-modern">
                            <span class="order-info-label-modern">
                                <i class="bi bi-hash"></i>
                                Order ID
                            </span>
                            <span class="order-info-value-modern">#<?= $order['id'] ?></span>
                        </div>
                        <div class="order-info-item-modern">
                            <span class="order-info-label-modern">
                                <i class="bi bi-calendar"></i>
                                Order Date
                            </span>
                            <span class="order-info-value-modern">
                                <?= date('M d, Y H:i', strtotime($order['created_at'])) ?>
                            </span>
                        </div>
                        <div class="order-info-item-modern">
                            <span class="order-info-label-modern">
                                <i class="bi bi-cash-coin"></i>
                                Total Amount
                            </span>
                            <span class="order-info-value-modern order-total-highlight-modern">
                                <?= \App\Money::format($order['total_cents'] ?? 0, $order['currency'] ?? 'GHS') ?>
                            </span>
                        </div>
                        
                        <div class="order-divider-modern"></div>
                        
                        <div class="payment-notice-modern">
                            <div class="payment-notice-icon-modern">
                                <i class="bi bi-info-circle-fill"></i>
                            </div>
                            <div class="payment-notice-content-modern">
                                <strong>Paystack Secure Payment</strong>
                                <p>Your payment is held securely by Paystack until delivery is confirmed. This protects both you and the supplier.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Paystack Inline JS -->
<?php if (!empty($paystack_public_key)): ?>
<script src="https://js.paystack.co/v1/inline.js"></script>
<?php endif; ?>

<!-- Payment Styles -->
<link rel="stylesheet" href="<?= \App\View::relAsset('assets/css/payment-modern.css?v=' . time()) ?>">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Helper function to build URLs with base path
    const buildUrl = (path) => {
        const basePath = '<?= rtrim(\App\View::relUrl('/'), '/') ?>';
        return basePath + '/' + path.replace(/^\/+/, '');
    };
    
    const paystackBtn = document.getElementById('paystackBtn');
    const orderId = <?= $order['id'] ?>;
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = metaTag ? metaTag.getAttribute('content') : '';
    
    if (paystackBtn) {
        paystackBtn.addEventListener('click', async function(e) {
            e.preventDefault(); // Prevent any default behavior
            e.stopPropagation(); // Stop event bubbling
            
            // Disable button
            paystackBtn.disabled = true;
            const btnContent = paystackBtn.querySelector('.btn-content');
            const btnLoader = paystackBtn.querySelector('.btn-loader');
            
            if (btnContent) btnContent.style.display = 'none';
            if (btnLoader) btnLoader.style.display = 'flex';
            
            try {
                // Initialize payment
                const response = await fetch(buildUrl('payment/initialize'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        order_id: orderId
                    })
                });
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text);
                    throw new Error('Invalid response from server. Please try again.');
                }
                
                const data = await response.json();
                
                if (data.success && data.authorization_url) {
                    // Redirect to Paystack payment page
                    window.location.href = data.authorization_url;
                } else {
                    throw new Error(data.message || 'Payment initialization failed');
                }
            } catch (error) {
                console.error('Payment error:', error);
                alert('Payment initialization failed: ' + (error.message || 'Unknown error. Please try again.'));
                
                // Re-enable button
                paystackBtn.disabled = false;
                if (btnContent) btnContent.style.display = 'flex';
                if (btnLoader) btnLoader.style.display = 'none';
            }
        });
    }
});
</script>