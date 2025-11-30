<link rel="stylesheet" href="<?= \App\View::asset('assets/css/ad-banner.css">

<div class="mb-3">
    <a href="<?= \App\View::url('/') ?>" class="back-button">
        <i class="bi bi-arrow-left"></i>
        <span>Back to Home</span>
    </a>
</div>

<h2 class="mb-4">Logistics Dashboard</h2>

<!-- Advertisement Banner Section -->
<?php include __DIR__ . '/../Shared/ad-banner.php'; ?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5><?= $stats['total'] ?? 0 ?></h5>
                <p class="text-muted mb-0">Total Assignments</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5><?= $stats['in_transit'] ?? 0 ?></h5>
                <p class="text-muted mb-0">In Transit</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5><?= $stats['delivered'] ?? 0 ?></h5>
                <p class="text-muted mb-0">Delivered</p>
            </div>
        </div>
    </div>
</div>

<h3 class="mb-3">Recent Deliveries</h3>
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Status</th>
                <th>Started</th>
                <th>Delivered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($deliveries as $delivery): ?>
                <tr>
                    <td>#<?= $delivery['order_id'] ?></td>
                    <td>
                        <span class="badge bg-<?= $delivery['status'] === 'delivered' ? 'success' : 'primary' ?>">
                            <?= ucwords(str_replace('_', ' ', $delivery['status'])) ?>
                        </span>
                    </td>
                    <td><?= $delivery['started_at'] ? date('M d, Y', strtotime($delivery['started_at'])) : '-' ?></td>
                    <td><?= $delivery['delivered_at'] ? date('M d, Y', strtotime($delivery['delivered_at'])) : '-' ?></td>
                    <td>
                        <a href="<?= \App\View::url('/logistics/assignments') ?>" class="btn btn-sm btn-primary">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

