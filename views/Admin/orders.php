<link rel="stylesheet" href="<?= \App\View::relAsset('assets/css/admin-orders.css') ?>">

<div class="admin-orders-page">
    <div class="container">
        <div class="admin-orders-header">
            <h2><i class="bi bi-list-ul"></i> All Orders</h2>
            <a href="<?= \App\View::relUrl('/admin/dashboard') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Status Filter Tabs -->
        <div class="status-filter-tabs">
            <a class="nav-link <?= !$statusFilter ? 'active' : '' ?>" href="<?= \App\View::relUrl('/admin/orders') ?>">
                All Orders
            </a>
            <a class="nav-link <?= $statusFilter === 'pending' ? 'active' : '' ?>" href="<?= \App\View::relUrl('/admin/orders?status=pending') ?>">
                Pending
            </a>
            <a class="nav-link <?= $statusFilter === 'processing' ? 'active' : '' ?>" href="<?= \App\View::relUrl('/admin/orders?status=processing') ?>">
                Processing
            </a>
            <a class="nav-link <?= $statusFilter === 'delivered' ? 'active' : '' ?>" href="<?= \App\View::relUrl('/admin/orders?status=delivered') ?>">
                Delivered
            </a>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-orders-state">
                <i class="bi bi-inbox"></i>
                <p>No orders found.</p>
            </div>
        <?php else: ?>
            <div class="orders-table-wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <strong>#<?= $order['id'] ?></strong>
                                    <?php if (!empty($order['order_number'])): ?>
                                        <br><small class="text-muted"><?= \App\View::e($order['order_number']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= \App\View::e($order['buyer_name'] ?? 'N/A') ?></td>
                                <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                <td><?= $order['item_count'] ?? 0 ?> items</td>
                                <td><?= \App\Money::format($order['total_cents'], $order['currency'] ?? 'GHS') ?></td>
                                <td>
                                    <?php
                                    require_once __DIR__ . '/../../includes/order_functions.php';
                                    echo getStatusBadgeHtml($order['current_status'] ?? $order['status'] ?? 'pending');
                                    ?>
                                </td>
                                <td>
                                    <a href="<?= \App\View::relUrl('/admin/orders/' . $order['id']) ?>" class="btn-view-update" onclick="event.stopPropagation(); return true;">
                                        <i class="bi bi-eye"></i> View & Update
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
