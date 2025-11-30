<div class="mb-3">
    <a href="/build_mate/logistics/dashboard" class="back-button">
        <i class="bi bi-arrow-left"></i>
        <span>Back to Dashboard</span>
    </a>
</div>

<h2 class="mb-4">Delivery Assignments</h2>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Status</th>
                <th>Started</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($deliveries as $delivery): ?>
                <tr>
                    <td>#<?= $delivery['order_id'] ?></td>
                    <td>
                        <span class="badge bg-<?= $delivery['status'] === 'delivered' ? 'success' : ($delivery['status'] === 'in_transit' ? 'primary' : 'secondary') ?>">
                            <?= ucwords(str_replace('_', ' ', $delivery['status'])) ?>
                        </span>
                    </td>
                    <td><?= $delivery['started_at'] ? date('M d, Y H:i', strtotime($delivery['started_at'])) : '-' ?></td>
                    <td>
                        <?php if ($delivery['status'] === 'queued'): ?>
                            <form method="POST" action="/build_mate/logistics/orders/<?= $delivery['id'] ?>/start/" class="d-inline">
                                <?= \App\Csrf::field() ?>
                                <button type="submit" class="btn btn-sm btn-primary">Start Delivery</button>
                            </form>
                        <?php elseif ($delivery['status'] === 'in_transit'): ?>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#deliverModal<?= $delivery['id'] ?>">Mark Delivered</button>
                            
                            <!-- Deliver Modal -->
                            <div class="modal fade" id="deliverModal<?= $delivery['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Mark as Delivered</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="/build_mate/logistics/orders/<?= $delivery['id'] ?>/delivered/" enctype="multipart/form-data">
                                            <?= \App\Csrf::field() ?>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="proof" class="form-label">Delivery Proof (Photo)</label>
                                                    <input type="file" class="form-control" id="proof" name="proof" accept=".jpg,.jpeg,.png" required>
                                                    <small class="form-text text-muted">Upload proof of delivery</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">Confirm Delivery</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

