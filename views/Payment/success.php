<!-- Payment Success Page -->
<div class="payment-success-page-modern">
    <div class="container">
        <div class="payment-success-card-modern">
            <div class="success-icon-wrapper-modern">
                <div class="success-checkmark-modern">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
            
            <h1 class="success-title-modern">Payment Successful!</h1>
            <p class="success-subtitle-modern">Your order has been confirmed</p>
            
            <div class="success-details-modern">
                <div class="success-detail-item-modern">
                    <i class="bi bi-hash"></i>
                    <div>
                        <span class="detail-label-modern">Order ID</span>
                        <span class="detail-value-modern">#<?= $order['id'] ?></span>
                    </div>
                </div>
                <div class="success-detail-item-modern">
                    <i class="bi bi-cash-coin"></i>
                    <div>
                        <span class="detail-label-modern">Amount Paid</span>
                        <span class="detail-value-modern">
                            <?= \App\Money::format($order['total_cents'], $order['currency']) ?>
                        </span>
                    </div>
                </div>
                <div class="success-detail-item-modern">
                    <i class="bi bi-shield-check"></i>
                    <div>
                        <span class="detail-label-modern">Payment Status</span>
                        <span class="detail-value-modern success-badge-modern">Confirmed</span>
                    </div>
                </div>
            </div>
            
            <div class="success-message-modern">
                <i class="bi bi-envelope-check"></i>
                <p>We've sent a confirmation email to your registered email address with order details and tracking information.</p>
            </div>
            
            <!-- Delivery Tracking Card -->
            <div class="delivery-tracking-card-modern">
                <div class="delivery-tracking-header-modern">
                    <i class="bi bi-truck-flatbed"></i>
                    <div>
                        <h4 class="mb-0">Track Your Delivery</h4>
                        <p class="mb-0">Monitor your order in real-time</p>
                    </div>
                </div>
                <div class="delivery-tracking-body-modern">
                    <p>Your order is being prepared and will be delivered to your address. You can track the delivery status and see driver information once your order is dispatched.</p>
                    <a href="/build_mate/orders/<?= $order['id'] ?>/track-delivery" class="btn-track-delivery-modern">
                        <i class="bi bi-truck"></i>
                        <span>Track Delivery Now</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="success-actions-modern">
                <a href="/build_mate/orders/<?= $order['id'] ?>" class="btn-primary-modern">
                    <i class="bi bi-eye"></i>
                    View Order Details
                </a>
                <a href="/build_mate/orders/<?= $order['id'] ?>/track-delivery" class="btn-secondary-modern">
                    <i class="bi bi-truck"></i>
                    Track Delivery
                </a>
                <a href="/build_mate/catalog" class="btn-outline-modern">
                    <i class="bi bi-arrow-left"></i>
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?= \App\View::asset('assets/css/payment-modern.css?v=<?= time() ?>">

<script>
// Auto-redirect to tracking page after 5 seconds (give user time to see success message)
let countdown = 5;
const countdownElement = document.createElement('div');
countdownElement.className = 'auto-redirect-notice-modern';
countdownElement.innerHTML = '<i class="bi bi-clock"></i> Redirecting to delivery tracking in <span id="countdown">' + countdown + '</span> seconds...';
document.querySelector('.payment-success-card-modern').appendChild(countdownElement);

const countdownSpan = document.getElementById('countdown');
const interval = setInterval(function() {
    countdown--;
    if (countdownSpan) {
        countdownSpan.textContent = countdown;
    }
    if (countdown <= 0) {
        clearInterval(interval);
        window.location.href = '/build_mate/orders/<?= $order['id'] ?>/track-delivery';
    }
}, 1000);

// Also allow manual redirect
document.querySelector('.btn-track-delivery-modern')?.addEventListener('click', function(e) {
    clearInterval(interval);
});
</script>
