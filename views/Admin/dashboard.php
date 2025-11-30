<link rel="stylesheet" href="/build_mate/assets/css/admin-dashboard.css">
<link rel="stylesheet" href="/build_mate/assets/css/ad-banner.css">

<div class="admin-dashboard-page">
    <div class="admin-dashboard-container">
        <!-- Dashboard Header -->
        <div class="admin-dashboard-header">
            <h1 class="admin-dashboard-title">Admin Dashboard</h1>
            <p class="admin-dashboard-subtitle">Welcome back! Here's what's happening with your platform today.</p>
        </div>
        
        <!-- Advertisement Banner Section -->
        <?php include __DIR__ . '/../Shared/ad-banner.php'; ?>

        <!-- Stats Cards -->
        <div class="admin-stats-grid">
            <div class="admin-stat-card orders">
                <div class="admin-stat-value"><?= number_format($stats['total_orders'] ?? 0) ?></div>
                <p class="admin-stat-label">Total Orders</p>
            </div>
            
            <div class="admin-stat-card gmv">
                <div class="admin-stat-value"><?= \App\Money::format($stats['total_gmv'] ?? 0, 'GHS') ?></div>
                <p class="admin-stat-label">Total GMV</p>
            </div>
            
            <div class="admin-stat-card suppliers">
                <div class="admin-stat-value"><?= number_format($stats['verified_suppliers'] ?? 0) ?></div>
                <p class="admin-stat-label">Verified Suppliers</p>
            </div>
            
            <div class="admin-stat-card kyc">
                <div class="admin-stat-value"><?= number_format($stats['pending_kyc'] ?? 0) ?></div>
                <p class="admin-stat-label">Pending KYC</p>
            </div>
        </div>

        <!-- Recent Orders Section -->
        <div class="admin-orders-section">
            <div class="admin-section-header">
                <h2 class="admin-section-title">
                    <i class="bi bi-clock-history"></i>
                    Recent Orders
                </h2>
            </div>
            
            <?php if (empty($recentOrders)): ?>
                <div class="admin-empty-state">
                    <div class="admin-empty-state-icon">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <p>No orders yet. Orders will appear here as they come in.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Buyer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            require_once __DIR__ . '/../../includes/order_functions.php';
                            foreach ($recentOrders as $order): 
                                // Get status for badge
                                $orderStatus = $order['current_status'] ?? $order['status'] ?? 'pending';
                                // Map 'shipped' to 'out_for_delivery' for display
                                if ($orderStatus === 'shipped') {
                                    $orderStatus = 'out_for_delivery';
                                }
                                // Map old statuses
                                $statusMap = [
                                    'pending' => 'pending',
                                    'placed' => 'pending',
                                    'paid' => 'paid',
                                    'paid_escrow' => 'paid',
                                    'payment_confirmed' => 'paid',
                                    'processing' => 'processing',
                                    'out_for_delivery' => 'out_for_delivery',
                                    'in_transit' => 'out_for_delivery',
                                    'delivered' => 'delivered',
                                    'completed' => 'delivered'
                                ];
                                $displayStatus = $statusMap[$orderStatus] ?? 'pending';
                            ?>
                                <tr>
                                    <td data-label="Order ID">
                                        <span class="admin-order-id">#<?= $order['id'] ?></span>
                                    </td>
                                    <td data-label="Buyer">
                                        <span class="admin-order-buyer"><?= \App\View::e($order['buyer_name'] ?? 'N/A') ?></span>
                                    </td>
                                    <td data-label="Total">
                                        <span class="admin-order-total" 
                                              data-price-cents="<?= $order['total_cents'] ?>" 
                                              data-currency="<?= $order['currency'] ?? 'GHS' ?>">
                                            <?= \App\Money::format($order['total_cents'] ?? 0, $order['currency'] ?? 'GHS') ?>
                                        </span>
                                    </td>
                                    <td data-label="Status">
                                        <span class="admin-status-badge <?= $displayStatus ?>">
                                            <?= ucwords(str_replace('_', ' ', $displayStatus)) ?>
                                        </span>
                                    </td>
                                    <td data-label="Date">
                                        <span class="admin-order-date"><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                                    </td>
                                    <td data-label="Action">
                                        <a href="/build_mate/admin/orders/<?= $order['id'] ?>" 
                                           class="admin-action-btn primary" 
                                           style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="admin-action-buttons">
            <a href="/build_mate/admin/suppliers" class="admin-action-btn primary">
                <i class="bi bi-people"></i> Manage Suppliers
            </a>
            <a href="/build_mate/admin/orders" class="admin-action-btn primary">
                <i class="bi bi-list-ul"></i> View All Orders
            </a>
            <a href="/build_mate/admin/audit-logs" class="admin-action-btn secondary">
                <i class="bi bi-journal-text"></i> Audit Logs
            </a>
        </div>
    </div>
</div>
