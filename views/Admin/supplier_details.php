<!-- Admin Supplier Details Page -->
<link rel="stylesheet" href="<?= \App\View::relAsset('assets/css/admin-supplier-details.css') ?>">

<div class="admin-supplier-details-page">
    <div class="container">
        <div class="admin-details-header">
            <h2>Supplier Details: <?= \App\View::e($supplier['business_name'] ?? 'N/A') ?></h2>
            <a href="<?= \App\View::relUrl('/admin/suppliers') ?>" class="btn btn-outline-secondary">
                <i class="icon-arrow-left"></i> Back to Suppliers
            </a>
        </div>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle') ?>"></i>
                <?= \App\View::e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <!-- Business Information Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3><i class="bi bi-building"></i> Business Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="info-label">Business Name:</div>
                            <div class="info-value"><?= \App\View::e($supplier['business_name'] ?? 'Not provided') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Registration Number:</div>
                            <div class="info-value"><?= \App\View::e($supplier['business_registration'] ?? 'Not provided') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Business Address:</div>
                            <div class="info-value">
                                <?= nl2br(\App\View::e($supplier['address'] ?? 'Not provided')) ?>
                                <?php if (!empty($supplier['city']) || !empty($supplier['region'])): ?>
                                    <br>
                                    <small style="color: #666;">
                                        <?php if (!empty($supplier['city'])): ?>
                                            <?= \App\View::e($supplier['city']) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($supplier['city']) && !empty($supplier['region'])): ?>, <?php endif; ?>
                                        <?php if (!empty($supplier['region'])): ?>
                                            <?= \App\View::e($supplier['region']) ?>
                                        <?php endif; ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">KYC Status:</div>
                            <div class="info-value">
                                <span class="badge bg-<?= $supplier['kyc_status'] === 'approved' ? 'success' : ($supplier['kyc_status'] === 'rejected' ? 'danger' : ($supplier['kyc_status'] === 'pending' ? 'warning' : 'secondary')) ?>">
                                    <?= ucfirst($supplier['kyc_status'] ?? 'Not Submitted') ?>
                                </span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Verified Badge:</div>
                            <div class="info-value">
                                <?php if ($supplier['verified_badge'] ?? 0): ?>
                                    <span class="badge bg-success"><i class="bi bi-shield-check"></i> Verified</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="bi bi-shield-x"></i> Not Verified</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($supplier['created_at'])): ?>
                        <div class="info-row">
                            <div class="info-label">Registered:</div>
                            <div class="info-value"><?= date('F j, Y g:i A', strtotime($supplier['created_at'])) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Contact Person Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3><i class="bi bi-person"></i> Contact Person</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="info-label">Name:</div>
                            <div class="info-value"><?= \App\View::e($user['name'] ?? 'N/A') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value">
                                <a href="mailto:<?= \App\View::e($user['email'] ?? '') ?>"><?= \App\View::e($user['email'] ?? 'N/A') ?></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KYC Documents Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3><i class="bi bi-file-earmark-text"></i> KYC Documents</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($documents)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> No KYC documents uploaded yet.
                            </div>
                        <?php else: ?>
                            <div class="documents-list">
                                <?php foreach ($documents as $doc): ?>
                                    <div class="document-item">
                                        <div class="document-info">
                                            <i class="bi bi-file-earmark-pdf document-icon"></i>
                                            <div class="document-details">
                                                <div class="document-type"><?= \App\View::e(ucfirst(str_replace('_', ' ', $doc['document_type']))) ?></div>
                                                <div class="document-name"><?= \App\View::e(basename($doc['document_path'] ?? 'document')) ?></div>
                                            </div>
                                        </div>
                                        <a href="<?= \App\View::relUrl('/storage/uploads/' . ($doc['document_path'] ?? '')) ?>" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="icon-eye"></i> View Document
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="bi bi-gear"></i> Actions</h3>
                    </div>
                    <div class="card-body">
                        <?php if (($supplier['kyc_status'] ?? NULL) === 'pending'): ?>
                            <form method="POST" action="<?= \App\View::relUrl('/admin/suppliers/' . $supplier['id'] . '/approve') ?>" class="mb-2">
                                <?= \App\Csrf::field() ?>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-circle"></i> Approve Supplier
                                </button>
                            </form>
                            <form method="POST" action="<?= \App\View::relUrl('/admin/suppliers/' . $supplier['id'] . '/reject') ?>">
                                <?= \App\Csrf::field() ?>
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-x-circle"></i> Reject Supplier
                                </button>
                            </form>
                        <?php elseif (($supplier['kyc_status'] ?? NULL) === 'approved'): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> Supplier is Approved.
                            </div>
                            <form method="POST" action="<?= \App\View::relUrl('/admin/suppliers/' . $supplier['id'] . '/reject') ?>">
                                <?= \App\Csrf::field() ?>
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="bi bi-x-circle"></i> Mark as Rejected
                                </button>
                            </form>
                        <?php elseif (($supplier['kyc_status'] ?? NULL) === 'rejected'): ?>
                            <div class="alert alert-danger mb-3">
                                <i class="bi bi-x-circle"></i> <strong>Supplier is Rejected.</strong>
                                <p class="mb-0 mt-2" style="font-size: 0.9rem;">You can re-approve this supplier if they've addressed the issues.</p>
                            </div>
                            <form method="POST" action="<?= \App\View::relUrl('/admin/suppliers/' . $supplier['id'] . '/approve') ?>" class="mb-2">
                                <?= \App\Csrf::field() ?>
                                <button type="submit" class="btn btn-success w-100" style="padding: 12px; background-color: #28a745; border-color: #28a745; color: white; font-weight: 600;">
                                    <i class="bi bi-check-circle"></i> Re-Approve Supplier
                                </button>
                            </form>
                            <hr class="my-3">
                            <form method="POST" action="<?= \App\View::relUrl('/admin/suppliers/' . $supplier['id'] . '/delete') ?>">
                                <?= \App\Csrf::field() ?>
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to permanently delete this supplier?')">
                                    <i class="bi bi-trash"></i> Delete Supplier
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> KYC not yet submitted.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
