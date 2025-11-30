<!-- Ultra Modern Delivery Tracking Page -->
<div class="tracking-page-ultra">
    <!-- Hero Header with Gradient -->
    <div class="tracking-hero-ultra">
        <div class="container">
            <div class="tracking-hero-content-ultra">
                <a href="/build_mate/orders/<?= $order['id'] ?>" class="back-btn-ultra">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back</span>
                </a>
                <div class="tracking-hero-text-ultra">
                    <div class="tracking-badge-ultra">
                        <i class="bi bi-truck"></i>
                        <span>Live Tracking</span>
                    </div>
                    <h1 class="tracking-title-ultra">Track Your Delivery</h1>
                    <p class="tracking-subtitle-ultra"><?= date('F d, Y', strtotime($order['created_at'])) ?> • <?= \App\Money::format($order['total_cents'], $order['currency'] ?? 'GHS') ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="container mt-4">
            <div class="alert-ultra alert-<?= $flash['type'] ?>-ultra">
                <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle') ?>"></i>
                <span><?= \App\View::e($flash['message']) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="container mt-5">
        <!-- Modern Horizontal Delivery Tracker -->
        <div class="delivery-tracker">
            <div class="delivery-tracker-header">
                <h3><i class="bi bi-truck"></i> Delivery Tracking</h3>
                <?php
                // Get order status - normalize old statuses to new ones
                $rawStatus = $order['status'] ?? 'placed';
                $orderStatus = $rawStatus;
                
                // Map old status values to new standardized ones
                $statusMap = [
                    'pending' => 'placed',
                    'paid_escrow' => 'paid',
                    'paid_paystack_secure' => 'paid',
                    'payment_confirmed' => 'paid',
                    'in_transit' => 'out_for_delivery'
                ];
                if (isset($statusMap[$rawStatus])) {
                    $orderStatus = $statusMap[$rawStatus];
                }
                
                // Check if payment was made - multiple ways to detect
                $hasPaymentRef = !empty($order['payment_reference']);
                $hasPaymentMethod = !empty($order['payment_method']);
                $isPaidStatus = ($orderStatus === 'paid' || in_array($orderStatus, ['processing', 'out_for_delivery', 'delivered']));
                $hasPayment = $isPaidStatus || $hasPaymentRef || $hasPaymentMethod;
                $paymentStepClass = $hasPayment ? 'completed' : 'pending';
                
                // Debug: Log the status for troubleshooting
                error_log("Track Delivery - Order #{$order['id']}: raw_status='{$rawStatus}', normalized='{$orderStatus}', payment_ref='" . ($order['payment_reference'] ?? 'NULL') . "', payment_method='" . ($order['payment_method'] ?? 'NULL') . "', has_payment=" . ($hasPayment ? 'YES' : 'NO') . ", payment_step_class='{$paymentStepClass}'");
                
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
                 data-order-status="<?= htmlspecialchars($orderStatus) ?>"
                 data-payment-reference="<?= htmlspecialchars($order['payment_reference'] ?? '') ?>"
                 data-raw-status="<?= htmlspecialchars($order['status'] ?? 'placed') ?>">
                <!-- Progress line -->
                <div class="tracker-progress-line" id="trackerProgress"></div>
                
                <!-- Step 1: Order Placed -->
                <div class="tracker-step step" data-step="placed">
                    <div class="tracker-step-icon">
                        <i class="bi bi-cart-check-fill"></i>
                    </div>
                    <div class="tracker-step-label">Order Placed</div>
                    <div class="tracker-step-time">
                        <?= date('M d, Y', strtotime($order['created_at'])) ?>
                    </div>
                </div>
                
                <!-- Step 2: Payment Successful -->
                <div class="tracker-step step <?= $paymentStepClass ?>" data-step="paid">
                    <div class="tracker-step-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="tracker-step-label">Payment Successful</div>
                    <div class="tracker-step-time" id="payment-time">
                        <?php 
                        // Check if payment was made - look for payment_reference or status
                        $hasPayment = ($orderStatus === 'paid' || in_array($orderStatus, ['processing', 'out_for_delivery', 'delivered'])) 
                                      || !empty($order['payment_reference']);
                        if ($hasPayment): 
                        ?>
                            <?= date('M d, Y', strtotime($order['payment_confirmed_at'] ?? $order['created_at'])) ?>
                        <?php else: ?>
                            Pending
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Step 3: Supplier Processing -->
                <div class="tracker-step step" data-step="processing">
                    <div class="tracker-step-icon">
                        <i class="bi bi-gear-fill"></i>
                    </div>
                    <div class="tracker-step-label">Supplier Processing</div>
                    <div class="tracker-step-time">
                        <?php if (in_array($orderStatus, ['processing', 'out_for_delivery', 'delivered'])): ?>
                            Processing
                        <?php else: ?>
                            Pending
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Step 4: Out for Delivery -->
                <div class="tracker-step step" data-step="out_for_delivery">
                    <div class="tracker-step-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div class="tracker-step-label">Out for Delivery</div>
                    <div class="tracker-step-time">
                        <?php if (in_array($order['status'], ['out_for_delivery', 'delivered'])): ?>
                            On the way
                        <?php else: ?>
                            Pending
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Step 5: Delivered -->
                <div class="tracker-step step" data-step="delivered">
                    <div class="tracker-step-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="tracker-step-label">Delivered</div>
                    <div class="tracker-step-time">
                        <?php if ($orderStatus === 'delivered'): ?>
                            <?= date('M d, Y', strtotime($order['updated_at'] ?? $order['created_at'])) ?>
                        <?php else: ?>
                            Pending
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delivery Confirmation Section -->
        <?php if ($delivery && $delivery['status'] === 'in_transit' && !($delivery['confirmed_by_buyer'] ?? 0)): ?>
        <div class="confirm-delivery-card" style="background: white; border-radius: 20px; padding: 2rem; margin: 2rem 0; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <h3 style="color: #8B4513; margin-bottom: 1rem;">
                <i class="bi bi-box-seam"></i> Received Your Order?
            </h3>
            <p style="color: #6B7280; margin-bottom: 1.5rem;">
                When the delivery arrives, you'll receive a 6-digit delivery code. Enter it here to confirm delivery:
            </p>
            
            <form id="confirmDeliveryForm" style="max-width: 400px;">
                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <input type="text" 
                           id="delivery_code" 
                           name="delivery_code"
                           maxlength="6" 
                           placeholder="000000" 
                           pattern="[0-9]{6}"
                           required
                           style="flex: 1; padding: 0.75rem; border: 2px solid #8B4513; border-radius: 8px; font-size: 1.25rem; text-align: center; letter-spacing: 4px; font-weight: bold;">
                    <button type="submit" 
                            class="btn btn-primary"
                            style="background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%); border: none; padding: 0.75rem 2rem; border-radius: 8px; color: white; font-weight: 700;">
                        <i class="bi bi-check-circle"></i> Confirm
                    </button>
                </div>
            </form>
            
            <div style="background: #FFF7ED; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                <p style="margin: 0; font-size: 0.875rem; color: #92400E;">
                    <i class="bi bi-info-circle"></i> 
                    The delivery code has been sent to your email. Only confirm delivery after inspecting your order and ensuring everything is correct.
                </p>
            </div>
        </div>
        <?php elseif ($delivery && $delivery['status'] === 'delivered' && ($delivery['confirmed_by_buyer'] ?? 0)): ?>
        <div class="delivery-confirmed-card" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; border-radius: 20px; padding: 2rem; margin: 2rem 0; text-align: center;">
            <h3 style="margin-bottom: 1rem;">
                <i class="bi bi-check-circle-fill"></i> Delivery Confirmed
            </h3>
            <p>Thank you for confirming your delivery! Payment has been released to the supplier.</p>
        </div>
        <?php elseif ($delivery && $delivery['status'] === 'delivered' && !($delivery['confirmed_by_buyer'] ?? 0)): ?>
        <div class="pending-confirmation-card" style="background: #FEF3C7; border: 2px solid #F59E0B; border-radius: 20px; padding: 2rem; margin: 2rem 0;">
            <h3 style="color: #92400E; margin-bottom: 1rem;">
                <i class="bi bi-clock"></i> Delivery Pending Confirmation
            </h3>
            <p style="color: #92400E; margin-bottom: 1.5rem;">
                Build Mate Logistics has marked this order as delivered. If you have received your order, please enter your delivery code to confirm:
            </p>
            
            <form id="confirmDeliveryForm" style="max-width: 400px;">
                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <input type="text" 
                           id="delivery_code" 
                           name="delivery_code"
                           maxlength="6" 
                           placeholder="000000" 
                           pattern="[0-9]{6}"
                           required
                           style="flex: 1; padding: 0.75rem; border: 2px solid #F59E0B; border-radius: 8px; font-size: 1.25rem; text-align: center; letter-spacing: 4px; font-weight: bold;">
                    <button type="submit" 
                            class="btn btn-warning"
                            style="background: #F59E0B; border: none; padding: 0.75rem 2rem; border-radius: 8px; color: white; font-weight: 700;">
                        <i class="bi bi-check-circle"></i> Confirm
                    </button>
                </div>
            </form>
            
            <p style="color: #92400E; font-size: 0.875rem; margin-top: 1rem;">
                If you have NOT received your order, please <a href="/build_mate/contact" style="color: #92400E; font-weight: 700;">contact support</a>.
            </p>
        </div>
        <?php endif; ?>
        
        <div class="order-actions-card">
            <a href="/build_mate/orders/<?= $order['id'] ?>" class="btn-modern btn-primary-modern">
                <i class="bi bi-receipt"></i>
                View Full Order Details
            </a>
            <a href="/build_mate/orders" class="btn-modern btn-secondary-modern">
                <i class="bi bi-arrow-left"></i>
                Back to Orders
            </a>
        </div>
        
        <?php if (!$delivery): ?>
            <!-- Empty State -->
            <div class="empty-state-ultra">
                <div class="empty-icon-wrapper-ultra">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <h2>Preparing Your Delivery</h2>
                <p>Your order has been confirmed and payment received. Delivery information will be available shortly.</p>
                <div class="empty-actions-ultra">
                    <a href="/build_mate/orders/<?= $order['id'] ?>" class="btn-ultra btn-primary-ultra">
                        <i class="bi bi-receipt"></i>
                        View Order Details
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="tracking-grid-ultra">
                <!-- Main Tracking Card -->
                <div class="tracking-main-ultra">
                    <!-- Status Progress Bar -->
                    <div class="progress-card-ultra">
                        <div class="progress-header-ultra">
                            <h3><i class="bi bi-activity"></i> Delivery Status</h3>
                            <span class="status-badge-ultra status-<?= $delivery['status'] ?>-ultra">
                                <?= ucfirst(str_replace('_', ' ', $delivery['status'])) ?>
                            </span>
                        </div>
                        
                        <!-- Progress Steps -->
                        <div class="progress-steps-ultra">
                            <?php
                            $statuses = [
                                'pending_pickup' => ['icon' => 'bi-cart-check', 'label' => 'Order Placed', 'color' => '#6c757d'],
                                'ready_for_pickup' => ['icon' => 'bi-box-seam', 'label' => 'Ready', 'color' => '#ffc107'],
                                'picked_up' => ['icon' => 'bi-check-circle', 'label' => 'Picked Up', 'color' => '#17a2b8'],
                                'in_transit' => ['icon' => 'bi-truck', 'label' => 'In Transit', 'color' => '#007bff'],
                                'delivered' => ['icon' => 'bi-check-circle-fill', 'label' => 'Delivered', 'color' => '#28a745']
                            ];
                            
                            $currentStatus = $delivery['status'];
                            $statusOrder = array_keys($statuses);
                            $currentIndex = array_search($currentStatus, $statusOrder);
                            if ($currentIndex === false) $currentIndex = -1;
                            
                            foreach ($statuses as $status => $info):
                                $index = array_search($status, $statusOrder);
                                $isActive = $index <= $currentIndex;
                                $isCurrent = $status === $currentStatus;
                            ?>
                                <div class="progress-step-ultra <?= $isActive ? 'active' : '' ?> <?= $isCurrent ? 'current' : '' ?>">
                                    <div class="step-icon-ultra" style="--step-color: <?= $info['color'] ?>">
                                        <i class="bi <?= $info['icon'] ?>"></i>
                                    </div>
                                    <div class="step-content-ultra">
                                        <h4><?= $info['label'] ?></h4>
                                        <?php if ($isCurrent): ?>
                                            <span class="step-badge-ultra current">Current</span>
                                        <?php elseif ($isActive): ?>
                                            <span class="step-badge-ultra completed">Done</span>
                                        <?php else: ?>
                                            <span class="step-badge-ultra pending">Pending</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Vehicle Type Card -->
                    <div class="vehicle-card-ultra">
                        <div class="vehicle-icon-ultra <?= $delivery['vehicle_type'] === 'truck' ? 'truck' : 'bike' ?>">
                            <i class="bi bi-<?= $delivery['vehicle_type'] === 'truck' ? 'truck' : 'motorcycle' ?>"></i>
                        </div>
                        <div class="vehicle-info-ultra">
                            <h4><?= $delivery['vehicle_type'] === 'truck' ? 'Truck Delivery' : 'Motorbike Delivery' ?></h4>
                            <p><?= $delivery['vehicle_type'] === 'truck' ? 'Large items • Standard delivery' : 'Fast delivery • Quick service' ?></p>
                        </div>
                    </div>

                    <!-- Driver Information -->
                    <?php if (!empty($delivery['driver_name']) || !empty($delivery['driver_phone'])): ?>
                        <div class="driver-card-ultra">
                            <div class="driver-header-ultra">
                                <h3><i class="bi bi-person-badge"></i> Delivery Driver</h3>
                            </div>
                            <div class="driver-details-ultra">
                                <?php if (!empty($delivery['driver_name'])): ?>
                                    <div class="driver-item-ultra">
                                        <i class="bi bi-person"></i>
                                        <div>
                                            <span class="label-ultra">Name</span>
                                            <span class="value-ultra"><?= \App\View::e($delivery['driver_name']) ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($delivery['driver_phone'])): ?>
                                    <div class="driver-item-ultra">
                                        <i class="bi bi-telephone"></i>
                                        <div>
                                            <span class="label-ultra">Phone</span>
                                            <a href="tel:<?= \App\View::e($delivery['driver_phone']) ?>" class="value-ultra">
                                                <?= \App\View::e($delivery['driver_phone']) ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($delivery['driver_vehicle_number'])): ?>
                                    <div class="driver-item-ultra">
                                        <i class="bi bi-upc-scan"></i>
                                        <div>
                                            <span class="label-ultra">Vehicle Number</span>
                                            <span class="value-ultra"><?= \App\View::e($delivery['driver_vehicle_number']) ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="driver-placeholder-ultra">
                            <i class="bi bi-info-circle"></i>
                            <p>Driver information will be updated once assigned</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="tracking-sidebar-ultra">
                    <!-- Address Card -->
                    <div class="address-card-ultra">
                        <div class="card-header-ultra">
                            <h3><i class="bi bi-geo-alt-fill"></i> Delivery Address</h3>
                        </div>
                        <div class="card-body-ultra">
                            <div class="address-line-ultra">
                                <i class="bi bi-signpost"></i>
                                <span><?= \App\View::e($delivery['street']) ?></span>
                            </div>
                            <div class="address-line-ultra">
                                <i class="bi bi-building"></i>
                                <span><?= \App\View::e($delivery['city']) ?></span>
                            </div>
                            <div class="address-line-ultra">
                                <i class="bi bi-map"></i>
                                <span><?= \App\View::e($delivery['region']) ?></span>
                            </div>
                            <div class="address-line-ultra">
                                <i class="bi bi-telephone"></i>
                                <a href="tel:<?= \App\View::e($delivery['phone']) ?>"><?= \App\View::e($delivery['phone']) ?></a>
                            </div>
                        </div>
                    </div>

                    <!-- Map Card -->
                    <?php if ($delivery['delivery_lat'] && $delivery['delivery_lng']): ?>
                        <div class="map-card-ultra">
                            <div class="card-header-ultra">
                                <h3><i class="bi bi-map-fill"></i> Location</h3>
                            </div>
                            <div class="card-body-ultra">
                                <div id="deliveryMapUltra" class="map-container-ultra"></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Estimated Delivery -->
                    <div class="estimate-card-ultra">
                        <i class="bi bi-clock-history"></i>
                        <div>
                            <h4>Estimated Delivery</h4>
                            <p>1-3 business days</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Google Maps -->
<?php
$config = require __DIR__ . '/../../settings/config.php';
$googleMapsKey = $config['google_maps']['api_key'] ?? '';
if ($delivery && $delivery['delivery_lat'] && $delivery['delivery_lng'] && !empty($googleMapsKey)):
?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars($googleMapsKey) ?>&callback=initDeliveryMapUltra" async defer></script>
<script>
function initDeliveryMapUltra() {
    const deliveryLocation = {
        lat: <?= $delivery['delivery_lat'] ?>,
        lng: <?= $delivery['delivery_lng'] ?>
    };
    
    const map = new google.maps.Map(document.getElementById('deliveryMapUltra'), {
        center: deliveryLocation,
        zoom: 15,
        mapTypeControl: true,
        streetViewControl: true,
        fullscreenControl: true,
        styles: [
            {
                featureType: "all",
                elementType: "geometry",
                stylers: [{ color: "#f5f5f5" }]
            }
        ]
    });
    
    new google.maps.Marker({
        position: deliveryLocation,
        map: map,
        title: 'Delivery Location',
        icon: {
            url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
        }
    });
}
</script>
<?php endif; ?>

<!-- Modern Delivery Tracker Styles -->
<link rel="stylesheet" href="<?= \App\View::asset('assets/css/delivery-tracker.css?v=<?= time() ?>">
<!-- Order Tracking Timeline Styles -->
<link rel="stylesheet" href="<?= \App\View::asset('assets/css/order-tracking.css?v=<?= time() ?>">
<!-- Ultra Modern Tracking Styles -->
<link rel="stylesheet" href="<?= \App\View::asset('assets/css/tracking-ultra.css?v=<?= time() ?>">

<!-- Delivery Tracker JavaScript -->
<script src="<?= \App\View::asset('assets/js/delivery-tracker.js') ?>?v=<?= time() ?> ?>"></script>
<script>
// Initialize tracker with current order status
document.addEventListener('DOMContentLoaded', function() {
    const tracker = document.getElementById('deliveryTracker');
    if (!tracker) {
        console.error('Delivery tracker element not found!');
        return;
    }
    
    const orderStatus = tracker.dataset.orderStatus || '<?= htmlspecialchars($orderStatus) ?>';
    const rawStatus = tracker.dataset.rawStatus || '<?= htmlspecialchars($order['status'] ?? 'placed') ?>';
    const paymentRef = tracker.dataset.paymentReference || '';
    
    console.log('=== Delivery Tracker Debug ===');
    console.log('Order status (normalized):', orderStatus);
    console.log('Order status (raw from DB):', rawStatus);
    console.log('Payment reference:', paymentRef || '(none)');
    console.log('Tracker element found:', !!tracker);
    
    updateTimeline(orderStatus);
    
    // Delivery confirmation form handler
    const confirmForm = document.getElementById('confirmDeliveryForm');
    if (confirmForm) {
        confirmForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const code = document.getElementById('delivery_code').value;
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Verifying...';
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                const response = await fetch(`/build_mate/orders/<?= $order['id'] ?>/confirm-delivery`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        order_id: <?= $order['id'] ?>,
                        delivery_code: code
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✓ Delivery confirmed! Thank you for your order.');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert('✗ ' + (data.message || 'Invalid delivery code. Please check and try again.'));
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    document.getElementById('delivery_code').value = '';
                    document.getElementById('delivery_code').focus();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('✗ Network error. Please try again.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    }
});
</script>
