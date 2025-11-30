<link rel="stylesheet" href="/build_mate/assets/css/admin-suppliers.css">

<div class="admin-suppliers-page">
    <div class="admin-suppliers-container">
        <!-- Page Header -->
        <div class="admin-suppliers-header">
            <div>
                <h1 class="admin-suppliers-title">Supplier Management</h1>
                <p class="admin-suppliers-subtitle">Manage and monitor all suppliers on the platform</p>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if ($flash && $flash['type'] === 'error'): ?>
            <div class="admin-suppliers-flash error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span><?= \App\View::e($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($flash && $flash['type'] === 'success'): ?>
            <div class="admin-suppliers-flash success">
                <i class="bi bi-check-circle-fill"></i>
                <span><?= \App\View::e($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <!-- Stats Bar -->
        <?php
        $totalSuppliers = count($suppliers);
        $approvedSuppliers = count(array_filter($suppliers, fn($s) => $s['kyc_status'] === 'approved'));
        $pendingSuppliers = count(array_filter($suppliers, fn($s) => $s['kyc_status'] === 'pending'));
        ?>
        <div class="admin-suppliers-stats">
            <div class="admin-supplier-stat">
                <div class="admin-supplier-stat-icon total">
                    <i class="bi bi-people"></i>
                </div>
                <div class="admin-supplier-stat-content">
                    <div class="admin-supplier-stat-value"><?= $totalSuppliers ?></div>
                    <div class="admin-supplier-stat-label">Total Suppliers</div>
                </div>
            </div>
            <div class="admin-supplier-stat">
                <div class="admin-supplier-stat-icon approved">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="admin-supplier-stat-content">
                    <div class="admin-supplier-stat-value"><?= $approvedSuppliers ?></div>
                    <div class="admin-supplier-stat-label">Approved</div>
                </div>
            </div>
            <div class="admin-supplier-stat">
                <div class="admin-supplier-stat-icon pending">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="admin-supplier-stat-content">
                    <div class="admin-supplier-stat-value"><?= $pendingSuppliers ?></div>
                    <div class="admin-supplier-stat-label">Pending Review</div>
                </div>
            </div>
        </div>

        <!-- Suppliers Table -->
        <div class="admin-suppliers-table-container">
            <div class="admin-suppliers-table-wrapper">
                <?php if (empty($suppliers)): ?>
                    <div class="admin-suppliers-empty">
                        <div class="admin-suppliers-empty-icon">
                            <i class="bi bi-inbox"></i>
                        </div>
                        <div class="admin-suppliers-empty-title">No Suppliers Yet</div>
                        <div class="admin-suppliers-empty-text">Suppliers will appear here once they register and apply.</div>
                    </div>
                <?php else: ?>
                    <table class="admin-suppliers-table">
                        <thead>
                            <tr>
                                <th>Business Name</th>
                                <th>Email</th>
                                <th>KYC Status</th>
                                <th>Verified</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suppliers as $supplier): ?>
                                <tr>
                                    <td>
                                        <div class="supplier-business-name">
                                            <?= \App\View::e($supplier['business_name'] ?? 'N/A') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="supplier-email">
                                            <?= \App\View::e($supplier['email'] ?? 'N/A') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="supplier-status-badge <?= $supplier['kyc_status'] ?>">
                                            <i class="bi bi-<?= $supplier['kyc_status'] === 'approved' ? 'check-circle' : ($supplier['kyc_status'] === 'rejected' ? 'x-circle' : 'clock') ?>"></i>
                                            <?= ucfirst($supplier['kyc_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="supplier-verified-badge <?= $supplier['verified_badge'] ? 'yes' : 'no' ?>">
                                            <?php if ($supplier['verified_badge']): ?>
                                                <i class="bi bi-shield-check"></i> Verified
                                            <?php else: ?>
                                                <i class="bi bi-shield-x"></i> Not Verified
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="supplier-actions">
                                            <?php if ($supplier['kyc_status'] === 'pending'): ?>
                                                <form method="POST" action="/build_mate/admin/suppliers/<?= $supplier['id'] ?>/approve" class="d-inline">
                                                    <?= \App\Csrf::field() ?>
                                                    <button type="submit" class="supplier-action-btn approve">
                                                        <i class="bi bi-check-circle"></i> Approve
                                                    </button>
                                                </form>
                                                <form method="POST" action="/build_mate/admin/suppliers/<?= $supplier['id'] ?>/reject" class="d-inline">
                                                    <?= \App\Csrf::field() ?>
                                                    <button type="submit" class="supplier-action-btn reject">
                                                        <i class="bi bi-x-circle"></i> Reject
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <a href="/build_mate/admin/suppliers/<?= $supplier['id'] ?>" class="supplier-action-btn view">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <form method="POST" 
                                                  action="/build_mate/admin/suppliers/<?= $supplier['id'] ?>/delete" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this supplier? This will permanently delete the supplier and ALL their products. This action cannot be undone.');">
                                                <?= \App\Csrf::field() ?>
                                                <button type="submit" class="supplier-action-btn delete">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

