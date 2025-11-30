<!-- Admin Order Details with Editable Tracker -->
<?php
// Calculate order status at the top - used throughout the page
// Prioritize current_status, but also check status field
$rawStatus = $order['current_status'] ?? $order['status'] ?? 'placed';
$orderStatus = $rawStatus;

// Map old status values to new standardized ones
$statusMap = [
    'pending' => 'placed',
    'paid_escrow' => 'paid',
    'paid_paystack_secure' => 'paid',
    'payment_confirmed' => 'paid',
    'in_transit' => 'out_for_delivery',
    'shipped' => 'out_for_delivery'  // Map 'shipped' (ENUM value) to 'out_for_delivery' for display
];
if (isset($statusMap[$rawStatus])) {
    $orderStatus = $statusMap[$rawStatus];
}

// Also check if status field has 'shipped' but current_status might be different
if ($orderStatus === 'placed' && ($order['status'] ?? '') === 'shipped') {
    $orderStatus = 'out_for_delivery';
}
?>
<div class="admin-order-details-page">
<div class="container">
    <div class="mb-4">
        <a href="/build_mate/admin/orders" class="admin-back-btn">
            <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-modern alert-dismissible fade show" role="alert">
            <?= \App\View::e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

            <!-- Order Info Card -->
    <div class="admin-order-card">
        <div class="admin-order-header">
            <h4><i class="bi bi-receipt"></i> Order #<?= $order['id'] ?></h4>
        </div>
        <div class="admin-order-body">
            <div class="admin-order-info-grid">
                <div class="admin-info-group">
                    <div class="admin-info-label">Customer</div>
                    <div class="admin-info-value"><?= \App\View::e($order['buyer_name'] ?? 'N/A') ?></div>
                    
                    <div class="admin-info-label">Email</div>
                    <div class="admin-info-value">
                        <?php if (!empty($order['buyer_email']) && $order['buyer_email'] !== 'N/A'): ?>
                            <a href="mailto:<?= \App\View::e($order['buyer_email']) ?>"><?= \App\View::e($order['buyer_email']) ?></a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </div>
                    
                    <div class="admin-info-label">Phone</div>
                    <div class="admin-info-value">
                        <?php
                        // Extract phone number from address_json or delivery_phone
                        $phone = 'N/A';
                        if (!empty($order['address_json'])) {
                            $address = json_decode($order['address_json'], true);
                            if (!empty($address['phone'])) {
                                $phone = $address['phone'];
                            }
                        }
                        if ($phone === 'N/A' && !empty($order['delivery_phone'])) {
                            $phone = $order['delivery_phone'];
                        }
                        if ($phone !== 'N/A'): ?>
                            <a href="tel:<?= \App\View::e($phone) ?>"><?= \App\View::e($phone) ?></a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="admin-info-group">
                    <div class="admin-info-label">Order Date</div>
                    <div class="admin-info-value"><?= date('M d, Y g:i A', strtotime($order['created_at'])) ?></div>
                    
                    <div class="admin-info-label">Total</div>
                    <div class="admin-info-value" style="font-size: 1.5rem; color: var(--admin-primary);"><?= \App\Money::format($order['total_cents'], $order['currency'] ?? 'GHS') ?></div>
                    
                    <div class="admin-info-label">Status</div>
                    <div class="admin-info-value">
                        <span class="admin-status-badge <?= $orderStatus === 'delivered' ? 'success' : ($orderStatus === 'processing' ? 'primary' : 'warning') ?>">
                            <?= ucwords(str_replace('_', ' ', $orderStatus ?? 'placed')) ?>
                        </span>
                    </div>
                        </div>
                    </div>

                    <!-- Order Items -->
            <h5 style="margin-bottom: 1.5rem; font-weight: 700; color: var(--admin-text);">Order Items</h5>
            <table class="admin-items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?= \App\View::e($item['product_name']) ?></td>
                                    <td><?= $item['qty'] ?? $item['quantity'] ?></td>
                                    <td><?= \App\Money::format($item['price_cents'], 'GHS') ?></td>
                                    <td><?= \App\Money::format(($item['qty'] ?? $item['quantity']) * $item['price_cents'], 'GHS') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    <!-- Editable Delivery Tracker -->
    <div class="admin-tracker-card">
        <div class="admin-tracker-header">
            <i class="bi bi-truck"></i>
            <h5>Order Tracking (Admin - Click to Update)</h5>
                </div>
        <div class="admin-tracker-body">
            <?php
            // If an order exists, payment was successful (orders are only created after payment)
            // This applies to ALL orders, including those with "pending" status
            // The order wouldn't exist in the system if payment hadn't been successful
            $hasPayment = true; // Always true for existing orders
            $paymentStepClass = 'completed'; // Always completed for existing orders
            
            // Get payment timestamp - prefer payment_confirmed_at, fallback to created_at
            $paymentTimestamp = $order['payment_confirmed_at'] ?? $order['created_at'] ?? date('Y-m-d H:i:s');
            ?>
            
            <div class="delivery-tracker">
                <div class="tracker-steps" 
                     id="adminDeliveryTracker"
                     data-order-id="<?= $order['id'] ?>"
                     data-order-status="<?= htmlspecialchars($orderStatus) ?>">
                    <!-- Progress line -->
                    <div class="tracker-progress-line" id="trackerProgress"></div>
                    
                    <!-- Step 1: Order Placed (Always completed) -->
                    <div class="tracker-step step completed locked">
                        <div class="tracker-step-icon">
                            <i class="bi bi-cart-check-fill"></i>
                                </div>
                        <div class="tracker-step-label">Order Placed</div>
                        <div class="tracker-step-time">
                            <?= date('M d, Y', strtotime($order['created_at'])) ?>
                                </div>
                            </div>
                    
                    <!-- Step 2: Payment Successful (Always completed - order exists = payment successful) -->
                    <div class="tracker-step step completed locked">
                        <div class="tracker-step-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="tracker-step-label">Payment Successful</div>
                        <div class="tracker-step-time">
                            <?= date('M d, Y', strtotime($paymentTimestamp)) ?>
                        </div>
                    </div>
                    
                    <!-- Step 3: Supplier Processing (NOT editable by admin - only suppliers can do this) -->
                    <?php
                    // Check both status and current_status - also map 'shipped' to 'out_for_delivery' for display
                    $orderStatusValue = $order['status'] ?? 'placed';
                    $currentStatusValue = $order['current_status'] ?? null;
                    
                    // Map 'shipped' (ENUM value) to 'out_for_delivery' for logic checks
                    $statusForCheck = $currentStatusValue ?? $orderStatusValue;
                    if ($statusForCheck === 'shipped') {
                        $statusForCheck = 'out_for_delivery';
                    }
                    
                    // Processing is completed if status is processing, out_for_delivery, or delivered
                    // Also check if status is 'shipped' (which means out_for_delivery)
                    $isProcessing = in_array($orderStatusValue, ['processing', 'shipped', 'delivered']) ||
                                    in_array($currentStatusValue, ['processing', 'out_for_delivery', 'delivered']) ||
                                    in_array($statusForCheck, ['processing', 'out_for_delivery', 'delivered']);
                    
                    // Admin CANNOT mark orders as processing - only suppliers can do that
                    // This step is always locked for admin, only shows status
                    $processingClass = $isProcessing ? 'completed locked' : 'pending locked';
                    ?>
                    <div class="tracker-step step <?= $processingClass ?>"
                         data-step="processing"
                         title="Supplier Processing - Only suppliers can update this step">
                        <div class="tracker-step-icon">
                            <i class="bi bi-gear-fill"></i>
                </div>
                        <div class="tracker-step-label">Supplier Processing</div>
                        <div class="tracker-step-time">
                            <?php if ($isProcessing): ?>
                                <?php 
                                $processingTime = $order['processing_started_at'] ?? $order['updated_at'] ?? $order['created_at'];
                                echo date('M d, Y', strtotime($processingTime));
                                ?>
                            <?php else: ?>
                                Waiting for supplier
                            <?php endif; ?>
            </div>
        </div>

                    <!-- Step 4: Out for Delivery (Editable for admin) -->
                    <?php
                    // Check both status and current_status - 'shipped' in status means 'out_for_delivery'
                    $statusForDeliveryCheck = $order['current_status'] ?? $order['status'] ?? 'placed';
                    // Map 'shipped' to 'out_for_delivery' for logic
                    if ($statusForDeliveryCheck === 'shipped') {
                        $statusForDeliveryCheck = 'out_for_delivery';
                    }
                    // Also check the raw status field
                    $rawStatusForCheck = $order['status'] ?? 'placed';
                    
                    $isOutForDelivery = in_array($statusForDeliveryCheck, ['out_for_delivery', 'delivered']) ||
                                       in_array($rawStatusForCheck, ['shipped', 'delivered']) ||
                                       in_array($orderStatus, ['out_for_delivery', 'delivered']);
                    
                    // Admin can click "Out for Delivery" if:
                    // 1. Order is paid (payment successful) AND not already out for delivery/delivered
                    // 2. OR order is in processing or beyond AND not already out for delivery/delivered
                    $paidStatuses = ['paid', 'paid_escrow', 'paid_paystack_secure', 'payment_confirmed'];
                    $isPaid = in_array($orderStatusValue, $paidStatuses) || 
                             in_array($currentStatusValue, $paidStatuses) ||
                             in_array($rawStatusForCheck, $paidStatuses) ||
                             !empty($order['payment_reference']);
                    
                    $canClickOutForDelivery = ($isPaid || $isProcessing) && !$isOutForDelivery;
                    $outForDeliveryClass = $isOutForDelivery ? 'completed' : ($canClickOutForDelivery ? 'pending clickable' : 'pending locked');
                    ?>
                    <div class="tracker-step step <?= $outForDeliveryClass ?>"
                         data-step="out_for_delivery"
                         data-order-id="<?= $order['id'] ?>"
                         data-new-status="out_for_delivery"
                         <?= $canClickOutForDelivery ? 'title="Click to mark as Out for Delivery"' : 'title="Out for Delivery"' ?>>
                        <div class="tracker-step-icon">
                            <i class="bi bi-truck"></i>
                </div>
                        <div class="tracker-step-label">Out for Delivery</div>
                        <div class="tracker-step-time">
                            <?php if ($isOutForDelivery): ?>
                                <?php
                                $deliveryTime = $order['out_for_delivery_at'] ?? $order['updated_at'] ?? $order['created_at'];
                                echo date('M d, Y', strtotime($deliveryTime));
                                ?>
                            <?php else: ?>
                                <?= $canClickOutForDelivery ? 'Click when ready' : 'Pending' ?>
                            <?php endif; ?>
                            </div>
                        </div>

                    <!-- Step 5: Delivered (Editable for admin) -->
                    <?php
                    // Check both status and current_status for delivered
                    $statusForDeliveredCheck = $order['current_status'] ?? $order['status'] ?? 'placed';
                    $isDelivered = ($statusForDeliveredCheck === 'delivered') || 
                                  ($order['status'] ?? '') === 'delivered' ||
                                  ($orderStatus === 'delivered');
                    $canClickDelivered = $isOutForDelivery && !$isDelivered;
                    $deliveredClass = $isDelivered ? 'completed' : ($canClickDelivered ? 'pending clickable' : 'pending locked');
                    ?>
                    <div class="tracker-step step <?= $deliveredClass ?>"
                         data-step="delivered"
                         data-order-id="<?= $order['id'] ?>"
                         data-new-status="delivered"
                         <?= $canClickDelivered ? 'title="Click to mark as Delivered"' : 'title="Delivered"' ?>>
                        <div class="tracker-step-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="tracker-step-label">Delivered</div>
                        <div class="tracker-step-time">
                            <?php if ($isDelivered): ?>
                                <?php 
                                $deliveredTime = $order['delivered_at'] ?? $order['updated_at'] ?? $order['created_at'];
                                echo date('M d, Y', strtotime($deliveredTime));
                                ?>
                            <?php else: ?>
                                <?= $canClickDelivered ? 'Click when delivered' : 'Pending' ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>

            <!-- Delivery Address -->
            <?php
            $addressJson = $order['address_json'] ?? null;
            $address = null;
            if (!empty($addressJson)) {
                $address = json_decode($addressJson, true);
            }
            if ($address):
            ?>
        <div class="admin-address-card">
            <div class="admin-address-header">
                <h6><i class="bi bi-geo-alt"></i> Delivery Address</h6>
                    </div>
            <div class="admin-address-body">
                        <p class="mb-1"><?= \App\View::e($address['street']) ?></p>
                        <p class="mb-1"><?= \App\View::e($address['city']) ?>, <?= \App\View::e($address['region']) ?></p>
                        <?php if (!empty($address['phone'])): ?>
                            <p class="mb-0"><strong>Phone:</strong> <?= \App\View::e($address['phone']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

<link rel="stylesheet" href="/build_mate/assets/css/admin-order-details.css?v=<?= time() ?>">
<link rel="stylesheet" href="/build_mate/assets/css/delivery-tracker.css?v=<?= time() ?>">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prevent infinite reload loops - check if we just reloaded
    if (sessionStorage.getItem('orderUpdateReloading')) {
        sessionStorage.removeItem('orderUpdateReloading');
        // Don't do anything else if we're in a reload loop
        return;
    }
    
    // Make tracker steps clickable for admin (but NOT processing - only suppliers can do that)
    document.querySelectorAll('.tracker-step.clickable').forEach(step => {
        step.addEventListener('click', async function() {
            const stepName = this.dataset.step;
            const orderId = this.dataset.orderId;
            const newStatus = this.dataset.newStatus;

            if (!orderId || !newStatus) return;
            
            // Admin cannot update processing step - only suppliers can
            if (stepName === 'processing' || newStatus === 'processing') {
                alert('Only suppliers can mark orders as "Processing". Please wait for the supplier to process this order.');
                return;
            }
            
            // Confirm action
            const stepLabels = {
                'out_for_delivery': 'Out for Delivery',
                'delivered': 'Delivered'
            };
            const label = stepLabels[stepName] || stepName;

            if (!confirm(`Mark this order as "${label}"?\n\nThis action cannot be undone.`)) {
                return;
            }

            // Disable step during update
            this.classList.remove('clickable');
            this.classList.add('locked');
            const originalTime = this.querySelector('.tracker-step-time').textContent;
            this.querySelector('.tracker-step-time').textContent = 'Updating...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                 document.querySelector('input[name="csrf_token"]')?.value || '';
    
                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }
                
                const formData = new FormData();
                formData.append('order_id', orderId);
                formData.append('status', newStatus);

                const response = await fetch(`/build_mate/admin/orders/${orderId}/update-status`, {
        method: 'POST',
        headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
                });

                // Check if response is ok
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server error:', response.status, errorText);
                    throw new Error(`Server error: ${response.status}`);
                }

                // Try to parse JSON
                let data;
                try {
                    const text = await response.text();
                    data = JSON.parse(text);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    throw new Error('Invalid response from server');
                }

        if (data.success) {
                    // Mark step as completed
                    this.classList.remove('pending');
                    this.classList.add('completed', 'locked');
                    this.querySelector('.tracker-step-time').textContent = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

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
                    // Use a flag to prevent infinite loops
                    if (!sessionStorage.getItem('orderUpdateReloading')) {
                        sessionStorage.setItem('orderUpdateReloading', 'true');
                        setTimeout(() => {
                            sessionStorage.removeItem('orderUpdateReloading');
            location.reload();
                        }, 1000);
                    } else {
                        // If we're already reloading, just remove the flag and don't reload again
                        sessionStorage.removeItem('orderUpdateReloading');
                    }
                } else {
                    alert('✗ ' + (data.message || 'Failed to update status'));
                    this.classList.remove('locked');
                    this.classList.add('clickable');
                    this.querySelector('.tracker-step-time').textContent = originalTime;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('✗ ' + (error.message || 'Network error. Please try again.'));
                this.classList.remove('locked');
                this.classList.add('clickable');
                this.querySelector('.tracker-step-time').textContent = originalTime;
            }
        });
    });

    // Update progress line on page load
    const progressLine = document.getElementById('trackerProgress');
    if (progressLine) {
        const orderStatus = '<?= $orderStatus ?>';
        const statusOrder = ['placed', 'paid', 'processing', 'out_for_delivery', 'delivered'];
        const currentIndex = statusOrder.indexOf(orderStatus);
        if (currentIndex >= 0) {
            progressLine.style.width = `${((currentIndex + 1) / 5) * 100}%`;
        } else {
            // Payment is always completed for existing orders
            // Calculate progress based on current status
            const statusOrder = ['placed', 'paid', 'processing', 'out_for_delivery', 'delivered'];
            const currentIndex = statusOrder.indexOf(orderStatus);
            if (currentIndex >= 0) {
                // At minimum, payment is completed (index 1), so show at least 40%
                const minProgress = 40; // Payment step (2 out of 5)
                const calculatedProgress = ((currentIndex + 1) / 5) * 100;
                progressLine.style.width = `${Math.max(minProgress, calculatedProgress)}%`;
            } else {
                // Default to payment completed (40%)
                progressLine.style.width = '40%';
            }
        }
    }
});
</script>
