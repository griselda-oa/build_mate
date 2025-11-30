<!-- Modern Checkout Page -->
<div class="checkout-page-modern">
    <!-- Header Section -->
    <div class="checkout-header-modern mb-5">
        <div class="container">
            <a href="/build_mate/cart" class="back-button-modern">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Cart</span>
            </a>
            <h1 class="checkout-title-modern">
                <i class="bi bi-credit-card-2-front"></i>
                Secure Checkout
            </h1>
            <p class="checkout-subtitle-modern">Complete your order with confidence</p>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="container mt-4 mb-4">
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
                <strong><?= $flash['type'] === 'error' ? '⚠️ Error:' : 'ℹ️ Info:' ?></strong> <?= \App\View::e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Delivery Notice Banner -->
    <div class="container mb-4">
        <div class="delivery-notice-modern">
            <i class="bi bi-truck-flatbed"></i>
            <div>
                <strong>Delivery Notice:</strong> We currently deliver to <strong>Greater Accra</strong> and <strong>Ashanti Region</strong> only. 
                <span class="text-muted">Stay tuned for expansion!</span>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row g-4">
            <!-- Left Column: Delivery Address Form -->
            <div class="col-lg-7 col-md-12">
                <div class="checkout-card-modern">
                    <div class="checkout-card-header-modern">
                        <div class="d-flex align-items-center">
                            <div class="checkout-icon-wrapper-modern">
                                <i class="bi bi-geo-alt-fill"></i>
                    </div>
                            <div>
                                <h3 class="checkout-card-title-modern mb-0">Delivery Address</h3>
                                <p class="checkout-card-subtitle-modern mb-0">Where should we deliver your order?</p>
                        </div>
                        </div>
                    </div>
                    <div class="checkout-card-body-modern">
                        <form method="POST" action="/build_mate/checkout" id="checkoutForm" novalidate>
                            <?= \App\Csrf::field() ?>
                            
                            <!-- Hidden fields for coordinates -->
                            <input type="hidden" id="delivery_lat" name="delivery_lat" value="">
                            <input type="hidden" id="delivery_lng" name="delivery_lng" value="">
                            
                            <!-- Street Address -->
                            <div class="form-group-modern">
                                <label for="street" class="form-label-modern">
                                    <i class="bi bi-house-door"></i>
                                    Street Address / Landmark
                                    <span class="required-asterisk">*</span>
                                </label>
                                <input type="text" 
                                       class="form-input-modern" 
                                       id="street" 
                                       name="street" 
                                       required 
                                       placeholder="e.g., 123 Main Street, Near Accra Mall">
                                <div class="form-hint-modern">Enter your complete street address or a nearby landmark</div>
                            </div>
                            
                            <!-- City and Region Row -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label for="city" class="form-label-modern">
                                            <i class="bi bi-building"></i>
                                            City
                                            <span class="required-asterisk">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-input-modern" 
                                               id="city" 
                                               name="city" 
                                               required 
                                               placeholder="e.g., Accra">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label for="region" class="form-label-modern">
                                            <i class="bi bi-map"></i>
                                            Region
                                            <span class="required-asterisk">*</span>
                                        </label>
                                        <select class="form-select-modern" id="region" name="region" required>
                                            <option value="">Select Region</option>
                                            <option value="Greater Accra">Greater Accra</option>
                                            <option value="Ashanti Region">Ashanti Region</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Country -->
                            <div class="form-group-modern">
                                <label for="country" class="form-label-modern">
                                    <i class="bi bi-globe"></i>
                                    Country
                                </label>
                                <input type="text" 
                                       class="form-input-modern" 
                                       id="country" 
                                       name="country" 
                                       value="Ghana" 
                                       readonly 
                                       style="background-color: #f8f9fa; cursor: not-allowed;">
                            </div>
                            
                            <!-- Phone Number -->
                            <div class="form-group-modern">
                                <label for="phone" class="form-label-modern">
                                    <i class="bi bi-telephone"></i>
                                    Phone Number
                                    <span class="required-asterisk">*</span>
                                </label>
                                <input type="tel" 
                                       class="form-input-modern" 
                                       id="phone" 
                                       name="phone" 
                                       required 
                                       placeholder="e.g., 0244123456">
                                <div class="form-hint-modern">We'll use this to contact you about your delivery</div>
                    </div>
                            
                            <!-- Error Messages -->
                            <div id="formErrors" class="alert-modern alert-danger-modern" style="display: none;"></div>
                            
                            <!-- Submit Button -->
                            <button type="submit" class="checkout-submit-btn-modern" id="submitBtn">
                                <span class="btn-content">
                                    <i class="bi bi-lock-fill"></i>
                                    <span>Continue to Payment</span>
                                </span>
                                <span class="btn-loader" style="display: none;">
                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                    Processing...
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Order Summary -->
            <div class="col-lg-5 col-md-12">
                <div class="order-summary-modern sticky-top-modern">
                    <div class="order-summary-header-modern">
                        <div class="d-flex align-items-center">
                            <div class="summary-icon-wrapper-modern">
                                <i class="bi bi-receipt-cutoff"></i>
                            </div>
                            <div>
                                <h3 class="order-summary-title-modern mb-0">Order Summary</h3>
                                <p class="order-summary-subtitle-modern mb-0">Review your items</p>
            </div>
        </div>
    </div>
                    
                    <div class="order-summary-body-modern">
                        <!-- Order Items -->
                        <div class="order-items-modern">
                <?php foreach ($products as $product): ?>
                                <div class="order-item-modern">
                                    <div class="order-item-content-modern">
                                        <div class="order-item-name-modern">
                                            <?= \App\View::e($product['name']) ?>
                                        </div>
                                        <div class="order-item-meta-modern">
                                            <span class="order-item-qty-modern">
                                                <i class="bi bi-x"></i>
                                                <?= $product['qty'] ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="order-item-price-modern">
                                        <?= \App\Money::format($product['price_cents'] * $product['qty'], $product['currency']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Divider -->
                        <div class="order-divider-modern"></div>
                        
                        <!-- Total -->
                        <div class="order-total-modern">
                            <div class="order-total-label-modern">
                                <span>Total Amount</span>
                            </div>
                            <div class="order-total-value-modern" data-price-cents="<?= $total ?>" data-currency="GHS">
                                <?= \App\Money::format($total, 'GHS') ?>
                            </div>
                        </div>
                        
                        <!-- Security Badge -->
                        <div class="security-badge-modern">
                            <div class="security-badge-icon-modern">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="security-badge-content-modern">
                                <strong>Paystack Secure Payment</strong>
                                <p>Your payment is held securely by Paystack until delivery is confirmed. Your funds are protected.</p>
                            </div>
                        </div>
                        
                        <!-- Trust Indicators -->
                        <div class="trust-indicators-modern">
                            <div class="trust-item-modern">
                                <i class="bi bi-lock-fill"></i>
                                <span>SSL Encrypted</span>
                            </div>
                            <div class="trust-item-modern">
                                <i class="bi bi-shield-check"></i>
                                <span>Secure Payment</span>
                            </div>
                            <div class="trust-item-modern">
                                <i class="bi bi-truck"></i>
                                <span>Tracked Delivery</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<link rel="stylesheet" href="/build_mate/assets/css/checkout-modern.css?v=<?= filemtime(__DIR__ . '/../../assets/css/checkout-modern.css') ?>">

<!-- Form validation -->
<script>
const allowedRegions = ['Greater Accra', 'Ashanti Region'];

document.addEventListener('DOMContentLoaded', function() {
    // Set default coordinates based on selected region
    const regionSelect = document.getElementById('region');
    if (regionSelect) {
        regionSelect.addEventListener('change', function() {
            const region = this.value;
            if (region === 'Greater Accra') {
                document.getElementById('delivery_lat').value = '5.6037';
                document.getElementById('delivery_lng').value = '-0.1870';
            } else if (region === 'Ashanti Region') {
                document.getElementById('delivery_lat').value = '6.6885';
                document.getElementById('delivery_lng').value = '-1.6244';
            }
        });
    }
    
    // Form validation
    const checkoutForm = document.getElementById('checkoutForm');
    const submitBtn = document.getElementById('submitBtn');
    
    if (checkoutForm && submitBtn) {
        checkoutForm.addEventListener('submit', function(e) {
            console.log('Form submit event triggered');
            
            // Set default coordinates if not set (do this first)
            const region = document.getElementById('region').value;
            const latInput = document.getElementById('delivery_lat');
            const lngInput = document.getElementById('delivery_lng');
            const lat = latInput ? latInput.value : '';
            const lng = lngInput ? lngInput.value : '';
            
            if (!lat || !lng) {
                if (region === 'Greater Accra') {
                    if (latInput) latInput.value = '5.6037';
                    if (lngInput) lngInput.value = '-0.1870';
                } else if (region === 'Ashanti Region') {
                    if (latInput) latInput.value = '6.6885';
                    if (lngInput) lngInput.value = '-1.6244';
                } else {
                    if (latInput) latInput.value = '5.6037';
                    if (lngInput) lngInput.value = '-0.1870';
                }
            }
            
            // Basic validation - let HTML5 validation handle required fields
            // Only add custom validation for region
            const regionValue = document.getElementById('region').value;
            if (!regionValue || !allowedRegions.includes(regionValue)) {
                console.log('Validation failed: Invalid region');
                e.preventDefault();
                e.stopPropagation();
                const errorDiv = document.getElementById('formErrors');
                if (errorDiv) {
                    errorDiv.innerHTML = '<strong><i class="bi bi-exclamation-triangle"></i> Please select a valid region (Greater Accra or Ashanti Region)</strong>';
                    errorDiv.style.display = 'block';
                    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                return false;
            }
            
            console.log('Validation passed, allowing form submission');
            
            // If validation passes, show loading state and allow form to submit
            submitBtn.disabled = true;
            const btnContent = submitBtn.querySelector('.btn-content');
            const btnLoader = submitBtn.querySelector('.btn-loader');
            if (btnContent) btnContent.style.display = 'none';
            if (btnLoader) btnLoader.style.display = 'flex';
            
            // Don't prevent default - let form submit normally
            // Form will submit via POST to /build_mate/checkout
            return true;
        });
    } else {
        console.error('Checkout form or submit button not found!');
    }
    
    // Add focus effects to inputs
    const inputs = document.querySelectorAll('.form-input-modern, .form-select-modern');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
});
</script>
