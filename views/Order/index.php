<link rel="stylesheet" href="<?= \App\View::asset('assets/css/buyer-orders.css') ?>">

<div class="buyer-orders-page">
    <div class="buyer-orders-container">
        <!-- Back Button -->
        <a href="<?= \App\View::url('/') ?>" class="buyer-back-btn">
            <i class="icon-arrow-left"></i>
            <span>Back to Home</span>
        </a>

        <!-- Header Section -->
        <div class="buyer-orders-header">
            <h1 class="buyer-orders-title">My Orders</h1>
            <div class="buyer-filter-tabs">
                <a href="<?= \App\View::url('/orders') ?>" 
                   class="buyer-filter-tab <?= !isset($_GET['status']) ? 'active' : '' ?>">
                    All
                </a>
                <a href="<?= \App\View::url('/orders?status=pending') ?>" 
                   class="buyer-filter-tab <?= ($_GET['status'] ?? '') === 'pending' ? 'active' : '' ?>">
                    Pending
                </a>
                <a href="<?= \App\View::url('/orders?status=processing') ?>" 
                   class="buyer-filter-tab <?= ($_GET['status'] ?? '') === 'processing' ? 'active' : '' ?>">
                    Processing
                </a>
                <a href="<?= \App\View::url('/orders?status=delivered') ?>" 
                   class="buyer-filter-tab <?= ($_GET['status'] ?? '') === 'delivered' ? 'active' : '' ?>">
                    Delivered
                </a>
            </div>
        </div>

        <?php
        // Filter orders by status if requested
        $statusFilter = $_GET['status'] ?? null;
        if ($statusFilter && !empty($orders)) {
            $orders = array_filter($orders, function($order) use ($statusFilter) {
                $status = $order['current_status'] ?? $order['status'] ?? 'pending';
                // Map 'shipped' to 'out_for_delivery' for filtering
                if ($status === 'shipped') {
                    $status = 'out_for_delivery';
                }
                // Map 'out_for_delivery' to 'processing' for the processing filter
                if ($statusFilter === 'processing' && $status === 'out_for_delivery') {
                    return true;
                }
                return $status === $statusFilter;
            });
        }
        ?>

        <?php if (empty($orders)): ?>
            <!-- Empty State -->
            <div class="buyer-empty-state">
                <div class="buyer-empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <h3 class="buyer-empty-state-title">No Orders Yet</h3>
                <p class="buyer-empty-state-text">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                <a href="<?= \App\View::url('/catalog') ?>" class="buyer-empty-state-link">
                    <i class="icon-cart"></i> Start Shopping
                </a>
            </div>
        <?php else: ?>
            <!-- Orders Card -->
            <div class="buyer-orders-card">
                <table class="buyer-orders-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php
                            // Get order status - prioritize current_status, fallback to status
                            $orderStatus = $order['current_status'] ?? $order['status'] ?? 'pending';
                            
                            // Map old status values and ENUM values to display values
                            $statusMap = [
                                'pending' => 'pending',
                                'paid' => 'paid',
                                'paid_escrow' => 'paid',
                                'paid_paystack_secure' => 'paid',
                                'payment_confirmed' => 'paid',
                                'processing' => 'processing',
                                'shipped' => 'out_for_delivery',
                                'out_for_delivery' => 'out_for_delivery',
                                'in_transit' => 'out_for_delivery',
                                'delivered' => 'delivered',
                                'completed' => 'delivered',
                                'cancelled' => 'cancelled'
                            ];
                            
                            $displayStatus = $statusMap[$orderStatus] ?? 'pending';
                            
                            // Status labels
                            $statusLabels = [
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'payment_confirmed' => 'Payment Confirmed',
                                'processing' => 'Processing',
                                'out_for_delivery' => 'Out for Delivery',
                                'shipped' => 'Shipped',
                                'in_transit' => 'In Transit',
                                'delivered' => 'Delivered',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled'
                            ];
                            
                            $statusLabel = $statusLabels[$displayStatus] ?? ucfirst(str_replace('_', ' ', $displayStatus));
                            
                            // Check if order has payment (for track delivery button)
                            $paidStatuses = ['paid', 'paid_escrow', 'paid_paystack_secure', 'payment_confirmed', 'processing', 'out_for_delivery', 'shipped', 'in_transit', 'delivered'];
                            $hasPayment = !empty($order['payment_reference']) || !empty($order['payment_method']) || !empty($order['has_payment']);
                            $isPaidStatus = in_array($orderStatus, $paidStatuses) || in_array($displayStatus, $paidStatuses);
                            $isPaid = $isPaidStatus || $hasPayment || $order['id'] > 0;
                            ?>
                            <tr>
                                <td data-label="Date">
                                    <div class="buyer-order-date">
                                        <?= date('M d, Y', strtotime($order['created_at'])) ?>
                                    </div>
                                </td>
                                <td data-label="Items">
                                    <div class="buyer-order-items">
                                        <?= $order['item_count'] ?? 0 ?> <?= ($order['item_count'] ?? 0) == 1 ? 'item' : 'items' ?>
                                    </div>
                                </td>
                                <td data-label="Total">
                                    <div class="buyer-order-total" 
                                         data-price-cents="<?= $order['total_cents'] ?>" 
                                         data-currency="<?= $order['currency'] ?>">
                                        <?= \App\Money::format($order['total_cents'], $order['currency']) ?>
                                    </div>
                                </td>
                                <td data-label="Status">
                                    <span class="buyer-status-badge <?= $displayStatus ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <td data-label="Actions">
                                    <div class="buyer-order-actions">
                                        <a href="<?= \App\View::url('/orders/' . $order['id']) ?>" 
                                           class="buyer-action-btn view">
                                            <i class="icon-eye"></i> View
                                        </a>
                                        <?php if ($isPaid): ?>
                                            <a href="<?= \App\View::url('/orders/' . $order['id'] . '/track-delivery') ?>" 
                                               class="buyer-action-btn track">
                                                <i class="icon-truck"></i> Track Delivery
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
