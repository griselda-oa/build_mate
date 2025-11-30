<!-- Modern Supplier Dashboard -->
<link rel="stylesheet" href="/build_mate/assets/css/supplier-dashboard.css">
<link rel="stylesheet" href="/build_mate/assets/css/ad-banner.css">

<div class="supplier-dashboard-page">
    <div class="container">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="/build_mate/" class="back-button">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Home</span>
            </a>
        </div>

        <!-- Dashboard Header -->
        <div class="dashboard-header-modern">
            <h1 class="dashboard-title-modern">Supplier Dashboard</h1>
            <p class="dashboard-subtitle-modern">Manage your products, orders, and business</p>
        </div>
        
        <!-- Advertisement Banner Section -->
        <?php include __DIR__ . '/../Shared/ad-banner.php'; ?>

        <!-- Premium Status Banner -->
        <?php if (isset($isPremium) && $isPremium): ?>
            <div class="premium-banner-modern" style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="font-size: 2rem;">
                            <i class="bi bi-star-fill" style="color: white;"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; color: white; font-size: 1.25rem;">Premium Supplier</div>
                            <div style="color: rgba(255, 255, 255, 0.9); font-size: 0.875rem;">
                                <?php if ($expiresAt): ?>
                                    Expires: <?= date('M d, Y', strtotime($expiresAt)) ?>
                                <?php else: ?>
                                    Active Subscription
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <a href="/build_mate/supplier/advertisements/create" class="btn" style="background: white; color: #FFA500; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; text-decoration: none;">
                            <i class="bi bi-megaphone"></i> Create Ad
                        </a>
                        <a href="/build_mate/supplier/advertisements" class="btn" style="background: rgba(255, 255, 255, 0.2); color: white; border: 2px solid white; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; text-decoration: none;">
                            <i class="bi bi-list-ul"></i> My Ads
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="premium-banner-modern" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="font-size: 2rem;">
                            <i class="bi bi-star" style="color: white;"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; color: white; font-size: 1.25rem;">Upgrade to Premium</div>
                            <div style="color: rgba(255, 255, 255, 0.9); font-size: 0.875rem;">
                                Get priority placement, create ads, and boost your visibility
                            </div>
                        </div>
                    </div>
                    <a href="/build_mate/supplier/premium/upgrade" class="btn" style="background: white; color: #667eea; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 1rem;">
                        <i class="bi bi-arrow-up-circle"></i> Upgrade Now
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="stats-grid-modern">
            <div class="stat-card-modern">
                <div class="stat-icon-modern products">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-value-modern"><?= $stats['total_products'] ?? 0 ?></div>
                <div class="stat-label-modern">Total Products</div>
            </div>

            <div class="stat-card-modern">
                <div class="stat-icon-modern orders">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div class="stat-value-modern"><?= $stats['total_orders'] ?? 0 ?></div>
                <div class="stat-label-modern">Total Orders</div>
            </div>

            <div class="stat-card-modern">
                <div class="stat-icon-modern pending">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-value-modern"><?= $stats['pending_orders'] ?? 0 ?></div>
                <div class="stat-label-modern">Pending Orders</div>
            </div>

            <div class="stat-card-modern">
                <div class="stat-icon-modern kyc">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="stat-badge-modern <?= ($supplier && $supplier['verified_badge']) ? 'verified' : 'pending' ?>">
                    <i class="bi bi-<?= ($supplier && $supplier['verified_badge']) ? 'check-circle-fill' : 'clock' ?>"></i>
                    <?= ($supplier && $supplier['verified_badge']) ? 'Verified' : 'Pending' ?>
                </div>
                <div class="stat-label-modern">KYC Status</div>
            </div>
        </div>
        
        <!-- Performance Metrics (if premium or has warnings) -->
        <?php if ((isset($isPremium) && $isPremium) || (isset($performanceWarnings) && $performanceWarnings > 0)): ?>
            <div class="section-card-modern" style="margin-bottom: 2rem;">
                <div class="section-header-modern">
                    <h3 class="section-title-modern">
                        <i class="bi bi-graph-up"></i>
                        Performance Metrics
                    </h3>
                </div>
                <div class="section-body-modern">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                        <div>
                            <div style="font-size: 0.875rem; color: #6B7280; margin-bottom: 0.5rem;">Sentiment Score</div>
                            <div style="font-size: 2rem; font-weight: 700; color: <?= (isset($sentimentScore) && $sentimentScore >= 0.7) ? '#10B981' : (($sentimentScore ?? 0) >= 0.4 ? '#F59E0B' : '#EF4444') ?>;">
                                <?= number_format(($sentimentScore ?? 1.0) * 100, 0) ?>%
                            </div>
                            <div style="font-size: 0.75rem; color: #6B7280; margin-top: 0.25rem;">
                                <?php if (($sentimentScore ?? 1.0) >= 0.7): ?>
                                    Excellent
                                <?php elseif (($sentimentScore ?? 1.0) >= 0.4): ?>
                                    Good
                                <?php else: ?>
                                    Needs Improvement
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (isset($performanceWarnings) && $performanceWarnings > 0): ?>
                            <div>
                                <div style="font-size: 0.875rem; color: #6B7280; margin-bottom: 0.5rem;">Performance Warnings</div>
                                <div style="font-size: 2rem; font-weight: 700; color: #EF4444;">
                                    <?= $performanceWarnings ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #EF4444; margin-top: 0.25rem;">
                                    <?php if ($performanceWarnings >= 3): ?>
                                        ⚠️ Risk of Downgrade
                                    <?php else: ?>
                                        Warning Level
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Content Grid -->
        <div class="dashboard-content-grid">
            <!-- Recent Products -->
            <div class="section-card-modern">
                <div class="section-header-modern">
                    <h3 class="section-title-modern">
                        <i class="bi bi-box-seam"></i>
                        Recent Products
                    </h3>
                    <a href="/build_mate/supplier/products" class="section-action-btn">
                        <i class="bi bi-gear"></i>
                        Manage
                    </a>
                </div>
                <div class="section-body-modern">
                    <?php if (empty($products)): ?>
                        <div class="empty-state-modern">
                            <i class="bi bi-inbox"></i>
                            <p>No products yet. Add your first product to get started!</p>
                        </div>
                    <?php else: ?>
                        <div class="items-list-modern">
                            <?php foreach (array_slice($products, 0, 5) as $product): ?>
                                <a href="/build_mate/product/<?= \App\View::e($product['slug'] ?? '') ?>" class="item-modern" style="text-decoration: none; color: inherit;">
                                    <span class="item-name-modern"><?= \App\View::e($product['name']) ?></span>
                                    <span class="item-badge-modern <?= $product['verified'] ? 'verified' : 'pending' ?>">
                                        <i class="bi bi-<?= $product['verified'] ? 'check-circle-fill' : 'clock' ?>"></i>
                                        <?= $product['verified'] ? 'Verified' : 'Pending' ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="section-card-modern">
                <div class="section-header-modern">
                    <h3 class="section-title-modern">
                        <i class="bi bi-cart-check"></i>
                        Recent Orders
                    </h3>
                    <a href="/build_mate/supplier/orders" class="section-action-btn">
                        <i class="bi bi-arrow-right"></i>
                        View All
                    </a>
                </div>
                <div class="section-body-modern">
                    <?php if (empty($orders)): ?>
                        <div class="empty-state-modern">
                            <i class="bi bi-inbox"></i>
                            <p>No orders yet. Your orders will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="items-list-modern">
                            <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                                <a href="/build_mate/orders/<?= $order['id'] ?>" class="item-modern" style="text-decoration: none; color: inherit;">
                                    <div style="flex: 1;">
                                        <div class="item-name-modern">
                                            <?= \App\Money::format($order['total_cents'] ?? 0, $order['currency'] ?? 'GHS') ?>
                                        </div>
                                        <div class="item-date-modern">
                                            <i class="bi bi-calendar"></i>
                                            <?= date('M d, Y', strtotime($order['created_at'])) ?>
                                        </div>
                                    </div>
                                    <div class="item-meta-modern">
                                        <span class="item-badge-modern <?= strtolower(str_replace(' ', '_', $order['status'] ?? 'placed')) ?>">
                                            <?= ucwords(str_replace('_', ' ', $order['status'] ?? 'placed')) ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="dashboard-actions-modern">
            <a href="/build_mate/supplier/products" class="action-btn-modern">
                <i class="bi bi-plus-circle"></i>
                <span>Add New Product</span>
            </a>
            <a href="/build_mate/supplier/orders" class="action-btn-modern secondary">
                <i class="bi bi-list-ul"></i>
                <span>View All Orders</span>
            </a>
            <?php if (isset($isPremium) && $isPremium): ?>
                <a href="/build_mate/supplier/advertisements/create" class="action-btn-modern" style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); border: none;">
                    <i class="bi bi-megaphone"></i>
                    <span>Create Advertisement</span>
                </a>
            <?php else: ?>
                <a href="/build_mate/supplier/premium/upgrade" class="action-btn-modern" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                    <i class="bi bi-star"></i>
                    <span>Upgrade to Premium</span>
                </a>
            <?php endif; ?>
            <a href="/build_mate/supplier/kyc" class="action-btn-modern secondary">
                <i class="bi bi-shield-check"></i>
                <span>Manage KYC</span>
            </a>
        </div>
    </div>
</div>
