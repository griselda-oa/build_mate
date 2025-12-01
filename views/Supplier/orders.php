<!-- Modern Supplier Orders Page -->
<link rel="stylesheet" href="<?= \App\View::asset('assets/css/supplier-dashboard.css') ?>">

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
            <h1 class="dashboard-title-modern">Supplier Orders</h1>
            <p class="dashboard-subtitle-modern">Manage and track all your orders</p>
        </div>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle') ?>"></i>
                <?= \App\View::e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="section-card-modern">
                <div class="section-body-modern">
                    <div class="empty-state-modern">
                        <i class="bi bi-inbox"></i>
                        <p>No orders found. Your orders will appear here once customers make purchases.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Orders Table Card -->
            <div class="section-card-modern">
                <div class="section-header-modern">
                    <h3 class="section-title-modern">
                        <i class="bi bi-list-ul"></i>
                        All Orders
                    </h3>
                </div>
                <div class="section-body-modern" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%); color: white;">
                                <tr>
                                    <th style="border: none; padding: 1rem;">Date</th>
                                    <th style="border: none; padding: 1rem;">Customer</th>
                                    <th style="border: none; padding: 1rem;">Total</th>
                                    <th style="border: none; padding: 1rem;">Status</th>
                                    <th style="border: none; padding: 1rem;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr style="border-bottom: 1px solid rgba(139, 69, 19, 0.1);">
                                        <td style="padding: 1.25rem;">
                                            <div style="font-weight: 600; color: #1F2937;">
                                                <?= date('M d, Y', strtotime($order['created_at'])) ?>
                                            </div>
                                            <div style="font-size: 0.875rem; color: #9CA3AF;">
                                                <?= date('h:i A', strtotime($order['created_at'])) ?>
                                            </div>
                                        </td>
                                        <td style="padding: 1.25rem;">
                                            <div style="font-weight: 600; color: #1F2937;">
                                                <?= \App\View::e($order['buyer_name'] ?? 'N/A') ?>
                                            </div>
                                        </td>
                                        <td style="padding: 1.25rem;">
                                            <div style="font-weight: 700; color: #8B4513; font-size: 1.125rem;" 
                                                 data-price-cents="<?= $order['total_cents'] ?>" 
                                                 data-currency="<?= $order['currency'] ?>">
                                                <?= \App\Money::format($order['total_cents'], $order['currency']) ?>
                                            </div>
                                        </td>
                                        <td style="padding: 1.25rem;">
                                            <?php
                                            $statusLabels = [
                                                'placed' => 'Placed',
                                                'paid' => 'Paid',
                                                'processing' => 'Processing',
                                                'out_for_delivery' => 'Out for Delivery',
                                                'delivered' => 'Delivered'
                                            ];
                                            $status = $order['status'] ?? 'placed';
                                            $label = $statusLabels[$status] ?? ucwords(str_replace('_', ' ', $status));
                                            ?>
                                            <span class="item-badge-modern <?= strtolower(str_replace(' ', '_', $status)) ?>">
                                                <?= $label ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1.25rem;">
                                            <div class="btn-group" role="group">
                                                <a href="<?= \App\View::url('/orders/' . $order['id']) ?>" 
                                                   class="btn btn-sm" 
                                                   style="background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%); color: white; border: none; border-radius: 8px; padding: 0.5rem 1rem;">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <?php
                                                $currentStatus = $order['status'] ?? 'placed';
                                                $deliveryStatus = $order['delivery_status'] ?? 'pending_pickup';
                                                
                                                // Show action buttons based on order status
                                                if ($currentStatus === 'paid'): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-primary update-status-btn" 
                                                            data-order-id="<?= $order['id'] ?>"
                                                            data-new-status="processing"
                                                            style="border-radius: 8px; padding: 0.5rem 1rem; margin-right: 0.5rem; cursor: pointer; z-index: 10; position: relative;">
                                                        <i class="bi bi-gear"></i> Start Processing
                                                    </button>
                                                
                                                <?php elseif ($currentStatus === 'processing' && $deliveryStatus === 'pending_pickup'): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-success mark-ready-btn" 
                                                            data-order-id="<?= $order['id'] ?>"
                                                            style="border-radius: 8px; padding: 0.5rem 1rem; cursor: pointer; z-index: 10; position: relative;">
                                                        <i class="bi bi-check-circle"></i> Mark Ready for Pickup
                                                    </button>
                                                
                                                <?php elseif ($deliveryStatus === 'ready_for_pickup'): ?>
                                                    <span class="item-badge-modern processing">
                                                        <i class="bi bi-clock"></i> Waiting for Pickup
                                                    </span>
                                                
                                                <?php elseif ($deliveryStatus === 'picked_up'): ?>
                                                    <span class="item-badge-modern processing">
                                                        <i class="bi bi-box-seam"></i> Picked Up by Logistics
                                                    </span>
                                                
                                                <?php elseif ($deliveryStatus === 'in_transit'): ?>
                                                    <span class="item-badge-modern out_for_delivery">
                                                        <i class="bi bi-truck"></i> In Transit
                                                    </span>
                                                
                                                <?php elseif ($deliveryStatus === 'delivered'): ?>
                                                    <?php if ($order['payment_released'] ?? 0): ?>
                                                        <span class="item-badge-modern delivered">
                                                            <i class="bi bi-check-circle-fill"></i> Delivered & Paid
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="item-badge-modern delivered">
                                                            <i class="bi bi-clock"></i> Awaiting Buyer Confirmation
                                                        </span>
                                                    <?php endif; ?>
                                                
                                                <?php elseif ($deliveryStatus === 'failed'): ?>
                                                    <span class="item-badge-modern" style="background: #ef4444; color: white;">
                                                        <i class="bi bi-x-circle"></i> Delivery Failed
                                                    </span>
                                                
                                                <?php else: ?>
                                                    <span class="item-badge-modern <?= strtolower(str_replace(' ', '_', $currentStatus)) ?>">
                                                        <?= ucwords(str_replace('_', ' ', $currentStatus)) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update Order Status button handler (Processing, Out for Delivery, etc.)
    document.querySelectorAll('.update-status-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const orderId = this.dataset.orderId;
            const newStatus = this.dataset.newStatus;
            
            const statusLabels = {
                'processing': 'Processing',
                'out_for_delivery': 'Out for Delivery',
                'delivered': 'Delivered'
            };
            
            if (!confirm(`Update this order status to "${statusLabels[newStatus] || newStatus}"?`)) {
                return;
            }
            
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                const formData = new FormData();
                formData.append('status', newStatus);
                
                const response = await fetch(`/build_mate/supplier/orders/${orderId}/update-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message || 'Order status updated successfully', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(data.message || 'Failed to update status', 'error');
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Network error. Please try again.', 'error');
                this.disabled = false;
                this.innerHTML = originalText;
            }
        });
    });
    
    // Mark Ready for Pickup button handler
    document.querySelectorAll('.mark-ready-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const orderId = this.dataset.orderId;
            
            if (!confirm('Confirm that this order is packed and ready for Build Mate Logistics to pick up?')) {
                return;
            }
            
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                const response = await fetch(`/build_mate/supplier/orders/${orderId}/mark-ready`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message || 'Order marked as ready for pickup', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(data.message || 'Failed to update status', 'error');
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Network error. Please try again.', 'error');
                this.disabled = false;
                this.innerHTML = originalText;
            }
        });
    });
});

function showNotification(message, type) {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    alertContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);';
    alertContainer.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle')}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alertContainer);
    setTimeout(() => {
        const alert = bootstrap.Alert.getInstance(alertContainer);
        if (alert) alert.close();
        else alertContainer.remove();
    }, 3000);
}
</script>
