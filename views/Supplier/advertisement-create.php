<!-- Create Advertisement Page -->
<link rel="stylesheet" href="<?= \App\View::asset('assets/css/supplier-dashboard.css') ?>">
<link rel="stylesheet" href="<?= \App\View::asset('assets/css/advertisement-form.css') ?>">

<div class="supplier-dashboard-page">
    <div class="container">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="<?= \App\View::url('/supplier/dashboard') ?>" class="back-button">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>

        <!-- Page Header -->
        <div class="dashboard-header-modern">
            <h1 class="dashboard-title-modern">Create Advertisement</h1>
            <p class="dashboard-subtitle-modern">Promote your products with sponsored ads - GHS 250 per advertisement</p>
        </div>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle"></i> <?= \App\View::e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Payment Section -->
        <div class="ad-payment-section" id="paymentSection">
            <div class="ad-payment-card">
                <div class="ad-payment-header">
                    <i class="bi bi-star-fill"></i>
                    <h3>Create Sponsored Advertisement</h3>
                </div>
                <div class="ad-payment-body">
                    <div class="ad-payment-amount">250.00</div>
                    <p class="ad-payment-description">
                        <i class="bi bi-check-circle-fill"></i> 30 days of premium placement<br>
                        <i class="bi bi-check-circle-fill"></i> Featured in catalog & homepage<br>
                        <i class="bi bi-check-circle-fill"></i> Increased visibility & clicks
                    </p>
                    <button type="button" class="ad-btn ad-btn-primary" id="payNowBtn">
                        <i class="bi bi-credit-card-2-front"></i>
                        <span>Pay GHS 250 with Paystack</span>
                    </button>
                    <p style="margin-top: 1.5rem; font-size: 0.875rem; opacity: 0.8;">
                        <i class="bi bi-shield-check"></i> Secure payment powered by Paystack
                    </p>
                </div>
            </div>
        </div>

        <!-- Advertisement Form (Hidden until payment is complete) -->
        <div class="ad-form-container" id="adFormContainer" style="display: none;">
            <form method="POST" action="<?= \App\View::url('/supplier/advertisements/create') ?>" id="adForm" enctype="multipart/form-data">
                <?= \App\Csrf::field() ?>
                <input type="hidden" name="payment_reference" id="paymentReference" value="">
                
                <div class="ad-form-card">
                    <!-- Product Selection -->
                    <div class="ad-form-section">
                        <label class="ad-form-label">
                            Select Product <span class="required">*</span>
                        </label>
                        <?php if (empty($products)): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>No products found.</strong> You need to create at least one product before you can create an advertisement.
                                <br><br>
                                <a href="<?= \App\View::url('/supplier/products') ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Create Your First Product
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="ad-product-select-wrapper">
                                <select name="product_id" class="ad-form-select" id="productSelect" required>
                                    <option value="">-- Choose a product --</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= $product['id'] ?>" data-image="<?= \App\View::e($product['image_url'] ?? '') ?>">
                                            <?= \App\View::e($product['name']) ?> - <?= \App\Money::format($product['price_cents'] ?? 0, $product['currency'] ?? 'GHS') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Title -->
                    <div class="ad-form-section">
                        <label class="ad-form-label">
                            Advertisement Title <span style="color: #9CA3AF; font-weight: normal;">(Optional)</span>
                        </label>
                        <input type="text" name="title" id="adTitle" class="ad-form-input" placeholder="Leave empty to use product name" maxlength="255">
                        <div class="ad-char-counter"><span id="titleCounter">0</span>/255</div>
                    </div>

                    <!-- Description -->
                    <div class="ad-form-section">
                        <label class="ad-form-label">
                            Description <span style="color: #9CA3AF; font-weight: normal;">(Optional)</span>
                        </label>
                        <textarea name="description" id="adDescription" class="ad-form-textarea" placeholder="Leave empty to use product description" maxlength="1000"></textarea>
                        <div class="ad-char-counter"><span id="descCounter">0</span>/1000</div>
                    </div>

                    <!-- Media Upload -->
                    <div class="ad-form-section">
                        <label class="ad-form-label">
                            Advertisement Media <span style="color: #9CA3AF; font-weight: normal;">(Optional)</span>
                        </label>
                        <div class="ad-upload-area" id="uploadArea">
                            <input type="file" name="media_files[]" id="mediaFiles" class="ad-file-input" accept="image/*,video/*" multiple>
                            <div class="ad-upload-icon">
                                <i class="bi bi-cloud-upload"></i>
                            </div>
                            <div class="ad-upload-text">Drag & drop images or videos here</div>
                            <div class="ad-upload-hint">or click to browse</div>
                            <div class="ad-upload-hint" style="margin-top: 0.5rem; font-size: 0.75rem;">
                                Supports: JPG, PNG, GIF, MP4, MOV (Max 10MB per file)
                            </div>
                        </div>
                        <div class="ad-preview-container" id="previewContainer"></div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="ad-form-actions">
                    <button type="submit" class="ad-btn ad-btn-primary" id="submitBtn">
                        <i class="bi bi-lightning-fill"></i>
                        <span>Create Advertisement</span>
                    </button>
                    <a href="<?= \App\View::url('/supplier/dashboard') ?>" class="ad-btn ad-btn-secondary">
                        <i class="bi bi-x-circle"></i>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= \App\View::asset('assets/js/advertisement-form.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if payment was successful (from callback)
    const urlParams = new URLSearchParams(window.location.search);
    const paymentSuccess = urlParams.get('payment') === 'success';
    const paymentRef = urlParams.get('ref') || '';
    
    const paymentSection = document.getElementById('paymentSection');
    const adFormContainer = document.getElementById('adFormContainer');
    const paymentReferenceInput = document.getElementById('paymentReference');
    const payNowBtn = document.getElementById('payNowBtn');
    
    if (paymentSuccess && paymentRef) {
        // Payment was successful, show form
        paymentSection.style.display = 'none';
        adFormContainer.style.display = 'block';
        paymentReferenceInput.value = paymentRef;
    } else {
        // Payment not completed, show payment section
        paymentSection.style.display = 'block';
        adFormContainer.style.display = 'none';
    }
    
    // Handle payment button click
    if (payNowBtn) {
        payNowBtn.addEventListener('click', function() {
            payNowBtn.disabled = true;
            payNowBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
            
            // Get CSRF token
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfInput = document.querySelector('input[name="csrf_token"]');
            const csrfToken = csrfMeta?.getAttribute('content') || csrfInput?.value || '';
            
            fetch('/build_mate/supplier/advertisements/payment/initialize', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(text); });
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.authorization_url) {
                    window.location.href = data.authorization_url;
                } else {
                    alert(data.message || 'Failed to initialize payment');
                    payNowBtn.disabled = false;
                    payNowBtn.innerHTML = '<i class="bi bi-wallet2"></i> <span>Pay with Paystack</span>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again. Details: ' + error.message);
                payNowBtn.disabled = false;
                payNowBtn.innerHTML = '<i class="bi bi-wallet2"></i> <span>Pay with Paystack</span>';
            });
        });
    }
});
</script>
