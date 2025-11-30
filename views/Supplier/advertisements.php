<!-- My Advertisements Page -->
<link rel="stylesheet" href="/build_mate/assets/css/supplier-dashboard.css">

<div class="supplier-dashboard-page">
    <div class="container">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="/build_mate/supplier/dashboard" class="back-button">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>

        <!-- Page Header -->
        <div class="dashboard-header-modern">
            <h1 class="dashboard-title-modern">My Advertisements</h1>
            <p class="dashboard-subtitle-modern">Manage your product advertisements</p>
        </div>

        <!-- Create New Button -->
        <div style="margin-bottom: 2rem;">
            <a href="/build_mate/supplier/advertisements/create" class="btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-plus-circle"></i> Create New Advertisement
            </a>
        </div>

        <!-- Advertisements List -->
        <?php if (empty($advertisements)): ?>
            <div class="section-card-modern">
                <div class="empty-state-modern" style="padding: 3rem 2rem;">
                    <i class="bi bi-megaphone" style="font-size: 4rem; color: #D1D5DB; margin-bottom: 1rem;"></i>
                    <h3 style="color: #1F2937; margin-bottom: 0.5rem;">No Advertisements Yet</h3>
                    <p style="color: #6B7280; margin-bottom: 1.5rem;">Create your first advertisement to promote your products</p>
                    <a href="/build_mate/supplier/advertisements/create" class="btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; text-decoration: none;">
                        <i class="bi bi-plus-circle"></i> Create Advertisement
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="section-card-modern">
                <div class="section-header-modern">
                    <h3 class="section-title-modern">
                        <i class="bi bi-list-ul"></i>
                        All Advertisements
                    </h3>
                </div>
                <div class="section-body-modern">
                    <div class="items-list-modern">
                        <?php foreach ($advertisements as $ad): ?>
                            <div class="item-modern" style="padding: 1.5rem; border-bottom: 1px solid #F3F4F6;">
                                <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem; flex-wrap: wrap;">
                                    <div style="flex: 1; min-width: 200px;">
                                        <div class="item-name-modern" style="font-size: 1.125rem; margin-bottom: 0.5rem;">
                                            <?= \App\View::e($ad['title'] ?? $ad['product_name'] ?? 'Untitled Ad') ?>
                                        </div>
                                        <div style="color: #6B7280; font-size: 0.875rem; margin-bottom: 0.5rem;">
                                            Product: <a href="/build_mate/product/<?= \App\View::e($ad['product_slug'] ?? '') ?>" style="color: #667eea; text-decoration: none;"><?= \App\View::e($ad['product_name'] ?? 'N/A') ?></a>
                                        </div>
                                        <div class="item-date-modern">
                                            <i class="bi bi-calendar"></i>
                                            <?= date('M d, Y H:i', strtotime($ad['start_date'])) ?> - 
                                            <?= date('M d, Y H:i', strtotime($ad['end_date'])) ?>
                                        </div>
                                        <?php if ($ad['clicks'] > 0 || $ad['impressions'] > 0): ?>
                                            <div style="color: #6B7280; font-size: 0.875rem; margin-top: 0.5rem;">
                                                <i class="bi bi-eye"></i> <?= $ad['impressions'] ?? 0 ?> impressions | 
                                                <i class="bi bi-cursor"></i> <?= $ad['clicks'] ?? 0 ?> clicks
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;">
                                        <span class="item-badge-modern <?= $ad['status'] === 'active' ? 'verified' : ($ad['status'] === 'pending' ? 'pending' : ($ad['status'] === 'rejected' ? '' : '')) ?>" style="white-space: nowrap;">
                                            <?php
                                            $statusColors = [
                                                'active' => ['color' => '#065F46', 'bg' => '#D1FAE5', 'icon' => 'check-circle'],
                                                'pending' => ['color' => '#92400E', 'bg' => '#FEF3C7', 'icon' => 'clock'],
                                                'rejected' => ['color' => '#991B1B', 'bg' => '#FEE2E2', 'icon' => 'x-circle'],
                                                'expired' => ['color' => '#6B7280', 'bg' => '#F3F4F6', 'icon' => 'calendar-x']
                                            ];
                                            $statusInfo = $statusColors[$ad['status']] ?? $statusColors['pending'];
                                            ?>
                                            <i class="bi bi-<?= $statusInfo['icon'] ?>"></i> <?= ucfirst($ad['status']) ?>
                                        </span>
                                        <?php if ($ad['status'] === 'active'): ?>
                                            <span style="color: #10B981; font-size: 0.875rem; font-weight: 600;">
                                                <i class="bi bi-check-circle"></i> Live
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

