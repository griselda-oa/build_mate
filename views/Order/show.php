<!-- Modern Order Detail & Tracking Page -->
<div class="order-detail-page">
    <!-- Hero Header -->
    <div class="order-detail-hero">
        <div class="container">
            <a href="<?= \App\View::url('/orders') ?>" class="back-btn-hero">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Orders</span>
            </a>
            <div class="order-hero-content">
                <div class="order-hero-badge">
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>Order Details</span>
                </div>
                <h1 class="order-hero-title">Order Confirmed</h1>
                <p class="order-hero-subtitle">
                    Placed on <?= date('F d, Y', strtotime($order['created_at'])) ?> • 
                    <?= \App\Money::format($order['total_cents'], $order['currency'] ?? 'GHS') ?>
                </p>
            </div>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="container mt-4">
            <div class="alert-modern alert-<?= $flash['type'] ?>-modern">
                <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle') ?>"></i>
                <span><?= \App\View::e($flash['message']) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="container mt-5">
        <div class="order-detail-grid">
            <!-- Main Content -->
            <div class="order-main-content">
                <!-- Order Items Card -->
                <div class="order-card-modern">
                    <div class="order-card-header">
                        <h3><i class="bi bi-box-seam"></i> Order Items</h3>
                        <span class="item-count-badge"><?= count($order['items']) ?> <?= count($order['items']) === 1 ? 'item' : 'items' ?></span>
                    </div>
                    <div class="order-card-body">
                        <div class="order-items-list">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="order-item-modern">
                                    <div class="item-image-wrapper">
                                        <?php if (!empty($item['image_path'])): ?>
                                            <img src="<?= \App\View::image($item['image_path']) ?>" 
                                                 alt="<?= \App\View::e($item['product_name']) ?>"
                                                 onerror="this.src='<?= \App\View::asset('assets/images/placeholder.png') ?>'">
                                        <?php else: ?>
                                            <div class="item-placeholder">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-details">
                                        <h4 class="item-name">
                                            <a href="<?= \App\View::url('/product/' . \App\View::e($item['product_slug'] ?? '')) ?>">
                                                <?= \App\View::e($item['product_name']) ?>
                                            </a>
                                        </h4>
                                        <div class="item-meta">
                                            <span class="item-quantity">Quantity: <?= $item['qty'] ?? $item['quantity'] ?></span>
                                            <span class="item-price"><?= \App\Money::format($item['price_cents'], 'GHS') ?> each</span>
                                        </div>
                                    </div>
                                    <div class="item-total">
                                        <strong><?= \App\Money::format(($item['qty'] ?? $item['quantity']) * $item['price_cents'], 'GHS') ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
            </div>
        </div>
        
                <!-- Modern Horizontal Delivery Tracker -->
                <div class="delivery-tracker-modern">
                <div class="delivery-tracker">
                    <div class="delivery-tracker-header">
                        <h3><i class="bi bi-truck"></i> Delivery Tracking</h3>
                        <?php
                        $orderStatus = $order['status'] ?? 'placed';
                        
                        // Determine overall status from order.status only
                        $statusLabels = [
                            'placed' => 'Placed',
                            'paid' => 'Payment Confirmed',
                            'processing' => 'Processing',
                            'out_for_delivery' => 'Out for Delivery',
                            'delivered' => 'Delivered'
                        ];
                        $statusColors = [
                            'placed' => 'warning',
                            'paid' => 'info',
                            'processing' => 'primary',
                            'out_for_delivery' => 'warning',
                            'delivered' => 'success'
                        ];
                        $overallStatus = $statusLabels[$orderStatus] ?? 'Placed';
                        $statusColor = $statusColors[$orderStatus] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $statusColor ?>">
                            <?= $overallStatus ?>
                        </span>
                    </div>
                    
                    <div class="tracker-steps" 
                         id="deliveryTracker"
                         data-order-id="<?= $order['id'] ?>"
                         data-order-status="<?= htmlspecialchars($order['status'] ?? 'placed') ?>"
                         data-payment-reference="<?= htmlspecialchars($order['payment_reference'] ?? ($order['payment_method'] ?? '')) ?>"
                         data-payment-method="<?= htmlspecialchars($order['payment_method'] ?? '') ?>"
                         data-raw-status="<?= htmlspecialchars($order['status'] ?? 'placed') ?>">
                        <!-- Progress line -->
                        <div class="tracker-progress-line" id="trackerProgress"></div>
                        
                        <!-- Step 1: Order Placed (Always completed, locked) -->
                        <div class="tracker-step step completed locked" data-step="placed" title="Order Placed - Cannot be changed">
                            <div class="tracker-step-icon">
                                <i class="bi bi-cart-check-fill"></i>
                            </div>
                            <div class="tracker-step-label">Order Placed</div>
                            <div class="tracker-step-time">
                                <?= date('M d, Y', strtotime($order['created_at'])) ?>
                            </div>
                        </div>
                        
                        <!-- Step 2: Payment Successful (Always completed, locked) -->
                        <div class="tracker-step step completed locked" data-step="paid" title="Payment Successful - Cannot be changed">
                            <div class="tracker-step-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="tracker-step-label">Payment Successful</div>
                            <div class="tracker-step-time">
                                <?= date('M d, Y', strtotime($order['payment_confirmed_at'] ?? $order['created_at'])) ?>
                            </div>
                        </div>
                        
                        <!-- Step 3: Supplier Processing (Editable for suppliers) -->
                        <?php
                        // Check both status and current_status fields
                        $orderStatusForCheck = $order['current_status'] ?? $order['status'] ?? 'placed';
                        $isProcessing = in_array($orderStatusForCheck, ['processing', 'out_for_delivery', 'delivered']) ||
                                       in_array($order['status'] ?? '', ['processing', 'out_for_delivery', 'delivered', 'shipped']);
                        
                        // Check if user is supplier and owns products in this order
                        // Supplier can ALWAYS click if order exists (payment successful) and not yet processing
                        $canClickProcessing = false;
                        if (isset($user) && $user['role'] === 'supplier' && !$isProcessing) {
                            // If order exists, payment was successful - supplier can process
                            // Check if supplier owns products in this order
                            $supplierModel = new \App\Supplier();
                            $supplier = $supplierModel->findByUserId($user['id']);
                            if ($supplier) {
                                $db = \App\DB::getInstance();
                                $stmt = $db->prepare("
                                    SELECT COUNT(*) as count
                                    FROM order_items oi
                                    JOIN products p ON oi.product_id = p.id
                                    WHERE oi.order_id = ? AND p.supplier_id = ?
                                ");
                                $stmt->execute([$order['id'], $supplier['id']]);
                                $result = $stmt->fetch();
                                $canClickProcessing = (int)($result['count'] ?? 0) > 0;
                            }
                        }
                        $processingClass = $isProcessing ? 'completed locked' : ($canClickProcessing ? 'pending clickable' : 'pending locked');
                        ?>
                        <div class="tracker-step step <?= $processingClass ?>" 
                             data-step="processing"
                             data-order-id="<?= $order['id'] ?>"
                             data-new-status="processing"
                             <?= $canClickProcessing ? 'title="Click to mark as Processing"' : 'title="Supplier Processing"' ?>>
                            <div class="tracker-step-icon">
                                <i class="bi bi-gear-fill"></i>
                            </div>
                            <div class="tracker-step-label">Supplier Processing</div>
                            <div class="tracker-step-time">
                                <?php if ($isProcessing): ?>
                                    <?= date('M d, Y', strtotime($order['updated_at'] ?? $order['created_at'])) ?>
                                <?php else: ?>
                                    Click to start
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Step 4: Out for Delivery (Editable for admin/logistics only) -->
                        <?php
                        $isOutForDelivery = in_array($order['status'], ['out_for_delivery', 'delivered']);
                        // Only admin/logistics can mark as "Out for Delivery"
                        $canClickOutForDelivery = false;
                        if (isset($user) && in_array($user['role'], ['admin', 'logistics']) && $isProcessing && !$isOutForDelivery) {
                            $canClickOutForDelivery = true;
                        }
                        $outForDeliveryClass = $isOutForDelivery ? 'completed locked' : ($canClickOutForDelivery ? 'pending clickable' : 'pending locked');
                        ?>
                        <div class="tracker-step step <?= $outForDeliveryClass ?>" 
                             data-step="out_for_delivery"
                             data-order-id="<?= $order['id'] ?>"
                             data-new-status="out_for_delivery"
                             <?= $canClickOutForDelivery ? 'title="Click to mark as Out for Delivery"' : 'title="Out for Delivery - Only admin/logistics can update"' ?>>
                            <div class="tracker-step-icon">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div class="tracker-step-label">Out for Delivery</div>
                            <div class="tracker-step-time">
                                <?php if ($isOutForDelivery): ?>
                                    On the way
                                <?php else: ?>
                                    <?= $canClickOutForDelivery ? 'Click when ready' : 'Pending' ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Step 5: Delivered (Locked - only admin/logistics can mark) -->
                        <?php
                        $isDelivered = $order['status'] === 'delivered';
                        $deliveredClass = $isDelivered ? 'completed locked' : 'pending locked';
                        ?>
                        <div class="tracker-step step <?= $deliveredClass ?>" 
                             data-step="delivered"
                             title="Delivered - Only admin can mark">
                            <div class="tracker-step-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="tracker-step-label">Delivered</div>
                            <div class="tracker-step-time">
                                <?php if ($isDelivered): ?>
                                    <?= date('M d, Y', strtotime($order['updated_at'] ?? $order['created_at'])) ?>
                                <?php else: ?>
                                    Pending
                                <?php endif; ?>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Address Card -->
                <?php
                $addressJson = $order['address_json'] ?? null;
                $address = null;
                if (!empty($addressJson)) {
                    $address = json_decode($addressJson, true);
                }
                if (!$address && !empty($order['delivery_address'])) {
                    $parts = explode(', ', $order['delivery_address']);
                    $address = [
                        'street' => $parts[0] ?? '',
                        'city' => $parts[1] ?? '',
                        'region' => $parts[2] ?? '',
                        'country' => $parts[3] ?? 'Ghana',
                        'phone' => $order['delivery_phone'] ?? ''
                    ];
                }
                if ($address):
                ?>
                    <div class="order-card-modern">
                        <div class="order-card-header">
                            <h3><i class="bi bi-geo-alt-fill"></i> Delivery Address</h3>
                        </div>
                        <div class="order-card-body">
                            <div class="address-display">
                                <div class="address-line">
                                    <i class="bi bi-signpost"></i>
                                    <span><?= \App\View::e($address['street']) ?></span>
                                </div>
                                <div class="address-line">
                                    <i class="bi bi-building"></i>
                                    <span><?= \App\View::e($address['city']) ?></span>
                                </div>
                                <div class="address-line">
                                    <i class="bi bi-map"></i>
                                    <span><?= \App\View::e($address['region']) ?></span>
                                </div>
                                <?php if (!empty($address['phone'])): ?>
                                    <div class="address-line">
                                        <i class="bi bi-telephone"></i>
                                        <a href="tel:<?= \App\View::e($address['phone']) ?>"><?= \App\View::e($address['phone']) ?></a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Actions -->
                <?php if ($order['status'] === 'delivered'): ?>
                    <div class="order-card-modern">
                        <div class="order-card-header">
                            <h3><i class="bi bi-check-circle"></i> Confirm Delivery</h3>
                        </div>
                        <div class="order-card-body">
                            <p>Please confirm that you have received your order. This will release Paystack funds to the supplier.</p>
                            <form method="POST" action="<?= \App\View::url('/orders/' . $order['id'] . '/confirm-delivery/') ?>">
                                <?= \App\Csrf::field() ?>
                                <button type="submit" class="btn-action-primary">
                                    <i class="bi bi-check-circle"></i>
                                    Confirm Delivery
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="order-sidebar">
                <!-- Order Summary -->
                <div class="order-summary-card">
                    <div class="summary-header">
                        <h3>Order Summary</h3>
                    </div>
                    <div class="summary-body">
                        <div class="summary-row">
                            <span>Status</span>
                            <span class="summary-value">
                                <?php
                                $statusLabels = [
                                    'pending' => 'Pending',
                                    'paid_escrow' => 'Paid',
                                    'paid_paystack_secure' => 'Paid',
                                    'in_transit' => 'In Transit',
                                    'delivered' => 'Delivered',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled'
                                ];
                                $statusClass = [
                                    'pending' => 'warning',
                                    'paid_escrow' => 'info',
                                    'paid_paystack_secure' => 'success',
                                    'in_transit' => 'primary',
                                    'delivered' => 'success',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $label = $statusLabels[$order['status']] ?? ucwords(str_replace('_', ' ', $order['status']));
                                $class = $statusClass[$order['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $class ?>"><?= $label ?></span>
                            </span>
                        </div>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span class="summary-value"><?= \App\Money::format($order['total_cents'], $order['currency'] ?? 'GHS') ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery</span>
                            <span class="summary-value">Free</span>
                        </div>
                        <hr class="summary-divider">
                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <span class="summary-value"><?= \App\Money::format($order['total_cents'], $order['currency'] ?? 'GHS') ?></span>
                        </div>
                        <?php if (!empty($order['escrow_held']) || !empty($order['paystack_secure_held'])): ?>
                            <div class="payment-notice">
                                <i class="bi bi-shield-check"></i>
                                <span>Payment held securely by Paystack</span>
                            </div>
                        <?php endif; ?>
                        <div class="summary-actions">
                            <a href="<?= \App\View::url('/orders/' . $order['id'] . '/invoice.pdf') ?>" class="btn-action-outline">
                                <i class="bi bi-download"></i>
                                Download Invoice
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Support Card -->
                <div class="support-card">
                    <h4><i class="bi bi-headset"></i> Need Help?</h4>
                    <p>Have questions about your order?</p>
                    <a href="<?= \App\View::url('/contact') ?>" class="btn-action-outline">
                        <i class="bi bi-envelope"></i>
                        Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Detail Styles -->
<link rel="stylesheet" href="<?= \App\View::asset('assets/css/order-detail.css?v=' . time()) ?>">
<link rel="stylesheet" href="<?= \App\View::asset('assets/css/delivery-tracker.css?v=' . time()) ?>">
<link rel="stylesheet" href="<?= \App\View::asset('assets/css/order-tracking.css?v=' . time()) ?>">
<script src="<?= \App\View::asset('assets/js/delivery-tracker.js?v=' . time()) ?>"></script>
<script>
// Initialize tracker with current order status
document.addEventListener('DOMContentLoaded', function() {
    const tracker = document.getElementById('deliveryTracker');
    if (tracker) {
        const orderStatus = tracker.dataset.orderStatus || 'placed';
        updateTimeline(orderStatus);
    }
    
    // Use event delegation for clickable steps (works even when classes change)
    const trackerContainer = document.getElementById('deliveryTracker');
    if (trackerContainer) {
        trackerContainer.addEventListener('click', async function(e) {
            // Find the closest tracker-step element
            const step = e.target.closest('.tracker-step');
            if (!step) {
                return; // Not a step element
            }
            
            // Check if it's clickable - log for debugging
            const isClickable = step.classList.contains('clickable');
            const stepName = step.dataset.step;
            const orderId = step.dataset.orderId;
            const newStatus = step.dataset.newStatus;
            
            console.log('Step clicked:', {
                stepName: stepName,
                isClickable: isClickable,
                hasOrderId: !!orderId,
                hasNewStatus: !!newStatus,
                classes: step.className
            });
            
            if (!isClickable) {
                console.log('Step is not clickable, ignoring click');
                return; // Not a clickable step
            }
            
            if (!orderId || !newStatus) {
                console.error('Missing orderId or newStatus:', { orderId, newStatus });
                return;
            }
            
            // Use the step element for the rest of the logic
            const stepElement = step;
            
            // Confirm action
            const stepLabels = {
                'processing': 'Supplier Processing',
                'out_for_delivery': 'Out for Delivery'
            };
            const label = stepLabels[stepName] || stepName;
            
            if (!confirm(`Mark this order as "${label}"?\n\nThis action cannot be undone.`)) {
                return;
            }
            
            // Disable step during update
            stepElement.classList.remove('clickable');
            stepElement.classList.add('locked');
            const originalTime = stepElement.querySelector('.tracker-step-time').textContent;
            stepElement.querySelector('.tracker-step-time').textContent = 'Updating...';
            
            try {
                // Get CSRF token from meta tag or form field
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfInput = document.querySelector('input[name="csrf_token"]');
                const csrfToken = csrfMeta?.getAttribute('content') || csrfInput?.value || '';
                
                if (!csrfToken) {
                    alert('✗ Security token missing. Please refresh the page and try again.');
                    stepElement.classList.remove('locked');
                    stepElement.classList.add('clickable');
                    stepElement.querySelector('.tracker-step-time').textContent = originalTime;
                    return;
                }
                
                const formData = new FormData();
                formData.append('status', newStatus);
                formData.append('order_id', orderId);
                formData.append('csrf_token', csrfToken); // Also add to form data for compatibility
                
                // Determine endpoint based on step and user role
                // Suppliers can only update to 'processing'
                // Admins/logistics can update to 'out_for_delivery' and 'delivered'
                let endpoint = '';
                if (newStatus === 'processing') {
                    endpoint = `/build_mate/supplier/orders/${orderId}/update-status`;
                } else if (newStatus === 'out_for_delivery' || newStatus === 'delivered') {
                    // Admin endpoint expects order_id in POST body, not URL
                    endpoint = `/build_mate/admin/orders/${orderId}/update-status`;
                    formData.append('order_id', orderId);
                } else {
                    alert('✗ Invalid status update');
                    return;
                }
                
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                // Check if response is OK
                if (!response.ok) {
                    const errorText = await response.text();
                    let errorMessage = 'Failed to update status';
                    
                    // Try to parse as JSON first
                    try {
                        const errorData = JSON.parse(errorText);
                        errorMessage = errorData.message || errorMessage;
                    } catch (e) {
                        // If not JSON, check if it's HTML (error page)
                        if (errorText.includes('<!DOCTYPE') || errorText.includes('<html')) {
                            errorMessage = 'Server error occurred. Please check the console for details.';
                            console.error('Server returned HTML error page:', errorText.substring(0, 500));
                        } else {
                            errorMessage = errorText.substring(0, 200) || errorMessage;
                        }
                    }
                    
                    throw new Error(errorMessage);
                }
                
                // Check content type before parsing JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Expected JSON but got:', contentType, text.substring(0, 200));
                    throw new Error('Server returned invalid response format');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    // Mark step as completed
                    stepElement.classList.remove('pending');
                    stepElement.classList.add('completed', 'locked');
                    stepElement.querySelector('.tracker-step-time').textContent = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    
                    // Update progress line
                    const progressLine = document.getElementById('trackerProgress');
                    if (progressLine) {
                        const statusOrder = ['placed', 'paid', 'processing', 'out_for_delivery', 'delivered'];
                        const currentIndex = statusOrder.indexOf(newStatus);
                        if (currentIndex >= 0) {
                            progressLine.style.width = `${((currentIndex + 1) / 5) * 100}%`;
                        }
                    }
                    
                    // Reload page after short delay to show updated status
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('✗ ' + (data.message || 'Failed to update status'));
                    stepElement.classList.remove('locked');
                    stepElement.classList.add('clickable');
                    stepElement.querySelector('.tracker-step-time').textContent = originalTime;
                }
            } catch (error) {
                console.error('Error:', error);
                const errorMessage = error.message || 'Network error. Please try again.';
                alert('✗ ' + errorMessage);
                stepElement.classList.remove('locked');
                stepElement.classList.add('clickable');
                stepElement.querySelector('.tracker-step-time').textContent = originalTime;
            }
        });
    }
});
</script>
