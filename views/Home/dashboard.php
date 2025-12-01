<!-- Buyer Dashboard -->
<div class="catalog-page">
    <!-- Modern Header with Search Bar -->
    <div class="catalog-hero mb-5">
        <div class="container">
            <!-- Back Button - Positioned in hero section -->
            <div class="catalog-back-button">
                <a href="<?= \App\View::url('/') ?>" class="back-button back-button-hero">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Home</span>
                </a>
            </div>
            <h1 class="catalog-title">Browse Building Materials</h1>
            <p class="catalog-subtitle">Find the best materials from verified suppliers</p>
            
            <!-- Quick Search Bar -->
            <div class="quick-search-wrapper">
                <div class="quick-search">
                    <i class="bi bi-search"></i>
                    <input type="text" 
                           id="quickSearch" 
                           class="quick-search-input" 
                           placeholder="Search products, suppliers, categories..." 
                           value="<?= \App\View::e($filters['query'] ?? '') ?>">
                    <button class="quick-search-btn" onclick="document.getElementById('filterForm').submit()">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Advertisement Banner Section -->
        <?php include __DIR__ . '/../Shared/ad-banner.php'; ?>
        
        <?php if (!empty($recentOrders) && isset($recentOrders[0])): ?>
            <div class="ad-banner-section-modern mb-4">
                <div class="ad-banner-carousel" id="adBannerCarousel">
                    <?php foreach ($advertisements as $index => $ad): ?>
                        <?php 
                        $adImage = $ad['image_url'] ?? $ad['product_image'] ?? '';
                        // Make path absolute if relative
                        if (!empty($adImage) && !preg_match('/^https?:\/\//', $adImage)) {
                            $basePath = \App\View::basePath();
                            if (strpos($adImage, $basePath) !== 0) {
                                $adImage = $basePath . ltrim($adImage, '/');
                            }
                        }
                        $isVideo = !empty($adImage) && preg_match('/\.(mp4|mov|webm)$/i', $adImage);
                        $productSlug = $ad['product_slug'] ?? '';
                        ?>
                        <div class="ad-banner-slide <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>">
                            <a href="<?= \App\View::url('/') ?>product/<?= \App\View::e($productSlug) ?>" class="ad-banner-link-modern">
                                <div class="ad-banner-content-modern">
                                    <?php if (!empty($adImage)): ?>
                                        <?php if ($isVideo): ?>
                                            <video class="ad-banner-media-modern" 
                                                   muted 
                                                   loop 
                                                   playsinline
                                                   autoplay
                                                   onmouseover="this.play()"
                                                   onmouseout="this.pause()">
                                                <source src="<?= \App\View::image($adImage) ?>" type="video/<?= pathinfo($adImage, PATHINFO_EXTENSION) === 'mov' ? 'quicktime' : pathinfo($adImage, PATHINFO_EXTENSION) ?>">
                                            </video>
                                        <?php else: ?>
                                            <img src="<?= \App\View::image($adImage) ?>" 
                                                 class="ad-banner-media-modern" 
                                                 alt="<?= \App\View::e($ad['title'] ?? $ad['product_name'] ?? 'Advertisement') ?>"
                                                 loading="lazy">
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="ad-banner-placeholder-modern">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="ad-banner-overlay-modern">
                                        <div class="ad-banner-text-modern">
                                            <span class="ad-banner-badge-modern">
                                                <i class="bi bi-star-fill"></i> Sponsored
                                            </span>
                                            <?php if (!empty($ad['title'])): ?>
                                                <h3 class="ad-banner-title-modern"><?= \App\View::e($ad['title']) ?></h3>
                                            <?php endif; ?>
                                            <?php if (!empty($ad['product_name'])): ?>
                                                <p class="ad-banner-product-modern"><?= \App\View::e($ad['product_name']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Banner Navigation Dots -->
                <?php if (count($advertisements) > 1): ?>
                    <div class="ad-banner-dots-modern">
                        <?php foreach ($advertisements as $index => $ad): ?>
                            <button class="ad-banner-dot-modern <?= $index === 0 ? 'active' : '' ?>" 
                                    data-slide="<?= $index ?>"
                                    aria-label="Go to slide <?= $index + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($recentOrders) && isset($recentOrders[0])): ?>
            <!-- Recent Orders & Tracking Section -->
            <div class="recent-orders-section mb-5">
                <div class="orders-header-modern">
                    <h3><i class="bi bi-truck"></i> My Recent Orders & Tracking</h3>
                    <a href="<?= \App\View::url('/') ?>orders" class="view-all-orders-btn">
                        View All Orders
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="orders-grid-modern">
                    <?php foreach (array_slice($recentOrders, 0, 3) as $order): ?>
                        <?php
                        $currentStatus = $order['current_status'] ?? $order['status'] ?? 'pending';
                        $paidStatuses = ['paid', 'paid_escrow', 'paid_paystack_secure', 'payment_confirmed', 'processing', 'out_for_delivery', 'delivered'];
                        $isPaid = in_array($currentStatus, $paidStatuses) || !empty($order['payment_reference']);
                        $statusLabels = [
                            'pending' => 'Pending',
                            'payment_confirmed' => 'Payment Confirmed',
                            'processing' => 'Processing',
                            'out_for_delivery' => 'Out for Delivery',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled'
                        ];
                        $statusClass = [
                            'pending' => 'warning',
                            'payment_confirmed' => 'info',
                            'processing' => 'primary',
                            'out_for_delivery' => 'primary',
                            'delivered' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $label = $statusLabels[$currentStatus] ?? ucfirst($currentStatus);
                        $class = $statusClass[$currentStatus] ?? 'secondary';
                        ?>
                        <div class="order-card-dashboard">
                            <div class="order-card-header-dashboard">
                                <div class="order-date-dashboard">
                                    <i class="bi bi-calendar"></i>
                                    <?= date('M d, Y', strtotime($order['created_at'])) ?>
                                </div>
                                <span class="badge bg-<?= $class ?>"><?= $label ?></span>
                            </div>
                            <div class="order-card-body-dashboard">
                                <div class="order-total-dashboard">
                                    <strong><?= \App\Money::format($order['total_cents'], $order['currency'] ?? 'GHS') ?></strong>
                                </div>
                                <div class="order-items-count-dashboard">
                                    <i class="bi bi-box"></i>
                                    <?= $order['item_count'] ?? 0 ?> item<?= ($order['item_count'] ?? 0) !== 1 ? 's' : '' ?>
                                </div>
                            </div>
                            <div class="order-card-actions-dashboard">
                                <a href="<?= \App\View::url('/') ?>orders/<?= $order['id'] ?>" class="btn-view-order">
                                    <i class="bi bi-eye"></i>
                                    View Details
                                </a>
                                <?php if ($isPaid): ?>
                                    <a href="<?= \App\View::url('/') ?>orders/<?= $order['id'] ?>/track-delivery" class="btn-track-order">
                                        <i class="bi bi-truck"></i>
                                        Track Delivery
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3 col-md-4">
                <button class="filter-toggle-btn d-md-none" data-filter-toggle>
                    <i class="bi bi-funnel"></i> Filters
                </button>
                
                <div class="filter-sidebar-modern">
                    <div class="filter-header-modern">
                        <h5 class="mb-0">
                            <i class="bi bi-sliders"></i> Filters
                        </h5>
                        <button type="button" class="filter-clear-btn" onclick="document.getElementById('filterForm').reset(); document.getElementById('filterForm').submit();">
                            Clear All
                        </button>
                    </div>
                    <form method="GET" action="<?= \App\View::url('/') ?>dashboard" id="filterForm">
                        <!-- Search (hidden since we have horizontal search) -->
                        <input type="hidden" 
                               id="q" 
                               name="q" 
                               value="<?= \App\View::e($filters['query'] ?? '') ?>">
                        
                        <!-- Category -->
                        <div class="filter-section-modern">
                            <label class="filter-label-modern">
                                <i class="bi bi-tags"></i>
                                Category
                            </label>
                            <select class="filter-select-modern" id="cat" name="cat">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? null) == $cat['id'] ? 'selected' : '' ?>>
                                        <?= \App\View::e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="filter-section-modern">
                            <label class="filter-label-modern">
                                <i class="bi bi-currency-exchange"></i>
                                Price Range (GHS)
                            </label>
                            <div class="price-slider-container-modern">
                                <div class="price-display-modern">
                                    <span class="price-value-modern" id="minPriceDisplay"><?= number_format($filters['min_price'] ?? ($price_range['min'] / 100), 2) ?></span>
                                    <span class="price-separator-modern">-</span>
                                    <span class="price-value-modern" id="maxPriceDisplay"><?= number_format($filters['max_price'] ?? ($price_range['max'] / 100), 2) ?></span>
                                </div>
                                <div class="range-slider-wrapper-modern">
                                    <input type="range" 
                                           class="range-slider-modern range-slider-min-modern" 
                                           id="minPrice" 
                                           name="min" 
                                           min="<?= $price_range['min'] / 100 ?>" 
                                           max="<?= $price_range['max'] / 100 ?>" 
                                           value="<?= $filters['min_price'] ?? ($price_range['min'] / 100) ?>" 
                                           step="0.01">
                                    <input type="range" 
                                           class="range-slider-modern range-slider-max-modern" 
                                           id="maxPrice" 
                                           name="max" 
                                           min="<?= $price_range['min'] / 100 ?>" 
                                           max="<?= $price_range['max'] / 100 ?>" 
                                           value="<?= $filters['max_price'] ?? ($price_range['max'] / 100) ?>" 
                                           step="0.01">
                                    <div class="range-track-modern"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Verified Only -->
                        <div class="filter-section-modern">
                            <label class="filter-checkbox-modern">
                                <input type="checkbox" id="verified" name="verified" <?= ($filters['verified_only'] ?? false) ? 'checked' : '' ?>>
                                <span class="checkmark-modern"></span>
                                <span class="checkbox-label-modern">
                                    <i class="bi bi-shield-check"></i>
                                    Verified Products Only
                                </span>
                            </label>
                        </div>
                        
                        <button type="submit" class="filter-submit-btn-modern">
                            <i class="bi bi-check-circle"></i>
                            Apply Filters
                        </button>
                        <a href="<?= \App\View::url('/') ?>dashboard" class="filter-reset-btn-modern">
                            <i class="bi bi-x-circle"></i>
                            Reset
                        </a>
                    </form>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-lg-9 col-md-8">
                <div class="products-header-modern mb-4">
                    <div class="results-count-modern">
                        <strong><?= count($products) ?></strong> product<?= count($products) !== 1 ? 's' : '' ?> found
                    </div>
                    <div class="view-options">
                        <a href="<?= \App\View::url('/') ?>catalog" class="view-all-link">
                            <i class="bi bi-grid"></i>
                            View Full Catalog
                        </a>
                    </div>
                </div>
                
                <!-- Sync quick search with hidden input -->
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const quickSearch = document.getElementById('quickSearch');
                    const hiddenInput = document.getElementById('q');
                    const filterForm = document.getElementById('filterForm');
                    
                    if (quickSearch && hiddenInput) {
                        // Update hidden input when quick search changes
                        quickSearch.addEventListener('input', function() {
                            hiddenInput.value = this.value;
                        });
                        
                        // Submit form on Enter key
                        quickSearch.addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                filterForm.submit();
                            }
                        });
                    }
                });
                </script>
                
                <?php if (empty($products)): ?>
                    <div class="empty-state-modern">
                        <div class="empty-icon">
                            <i class="bi bi-inbox"></i>
                        </div>
                        <h4>No products found</h4>
                        <p>Try adjusting your filters or search terms</p>
                        <a href="<?= \App\View::url('/') ?>dashboard" class="btn-modern-primary">Clear Filters</a>
                    </div>
                <?php else: ?>
                    <div class="products-grid-modern">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card-ultra-modern">
                                <a href="<?= \App\View::url('/') ?>product/<?= \App\View::e($product['slug']) ?>" class="product-link-modern">
                                    <div class="product-image-wrapper-modern">
                                        <?php if (!empty($product['image_url'])): ?>
                                            <img src="<?= \App\View::image($product['image_url']) ?>" 
                                                 class="product-image-ultra-modern" 
                                                 alt="<?= \App\View::e($product['name']) ?>"
                                                 loading="lazy">
                                        <?php else: ?>
                                            <div class="product-image-placeholder-ultra-modern">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($product['verified'] ?? false): ?>
                                            <span class="product-badge-modern verified-modern">
                                                <i class="bi bi-shield-check"></i>
                                                Verified
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($product['verified_badge'] ?? false): ?>
                                            <span class="product-badge-modern supplier-modern">
                                                <i class="bi bi-patch-check"></i>
                                                Verified Supplier
                                            </span>
                                        <?php endif; ?>
                                        <div class="product-overlay-modern">
                                            <span class="view-details-modern">
                                                <i class="bi bi-eye"></i>
                                                View Details
                                            </span>
                                        </div>
                                    </div>
                                    <div class="product-info-modern">
                                        <h6 class="product-name-modern"><?= \App\View::e($product['name']) ?></h6>
                                        <p class="product-supplier-modern">
                                            <i class="bi bi-shop"></i>
                                            <?= \App\View::e($product['supplier_name'] ?? '') ?>
                                        </p>
                                        <p class="product-category-modern">
                                            <i class="bi bi-tag"></i>
                                            <?= \App\View::e($product['category_name'] ?? '') ?>
                                        </p>
                                        <div class="product-price-wrapper-modern">
                                            <span class="product-price-modern" data-price-cents="<?= $product['price_cents'] ?>" data-currency="<?= $product['currency'] ?>">
                                                <?= \App\Money::format($product['price_cents'], $product['currency']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Container */
.dashboard-container {
    min-height: calc(100vh - 200px);
    background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
    padding-bottom: 3rem;
}

/* Dashboard Header */
.dashboard-header {
    background: linear-gradient(135deg, hsl(var(--primary)) 0%, hsl(var(--primary-dark)) 100%);
    color: white;
    padding: 3rem 0 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.dashboard-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.dashboard-title i {
    font-size: 2rem;
}

.dashboard-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.dashboard-stats {
    display: flex;
    gap: 2rem;
    justify-content: flex-end;
}

.stat-item {
    text-align: center;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    padding: 1rem 1.5rem;
    border-radius: 12px;
}

.stat-value {
    display: block;
    font-size: 2rem;
    font-weight: 700;
}

.stat-label {
    display: block;
    font-size: 0.875rem;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Modern Filter Sidebar */
.filter-sidebar-modern {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    position: sticky;
    top: 20px;
    margin-bottom: 2rem;
}

.filter-header-modern {
    background: linear-gradient(135deg, hsl(var(--primary)) 0%, hsl(var(--primary-dark)) 100%);
    color: white;
    padding: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.filter-header-modern i {
    font-size: 1.25rem;
}

.filter-section-modern {
    padding: 1.5rem;
    border-bottom: 1px solid hsl(var(--border));
}

.filter-section-modern:last-of-type {
    border-bottom: none;
}

.filter-label-modern {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    color: hsl(var(--foreground));
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-input-modern,
.filter-select-modern {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid hsl(var(--border));
    border-radius: 12px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: white;
}

.filter-input-modern:focus,
.filter-select-modern:focus {
    outline: none;
    border-color: hsl(var(--primary));
    box-shadow: 0 0 0 4px hsl(var(--primary) / 0.1);
}

/* Price Slider */
.price-slider-container-modern {
    margin-top: 0.5rem;
}

.price-display-modern {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding: 0.875rem;
    background: linear-gradient(135deg, hsl(var(--primary) / 0.05) 0%, hsl(var(--primary-dark) / 0.05) 100%);
    border-radius: 12px;
    font-weight: 600;
}

.price-value-modern {
    color: hsl(var(--primary));
    font-size: 1.1rem;
}

.price-separator-modern {
    color: hsl(var(--muted-foreground));
}

.range-slider-wrapper-modern {
    position: relative;
    height: 50px;
    margin: 1rem 0;
}

.range-slider-modern {
    position: absolute;
    width: 100%;
    height: 6px;
    background: none;
    pointer-events: none;
    -webkit-appearance: none;
    appearance: none;
    z-index: 2;
}

.range-slider-modern::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: hsl(var(--primary));
    cursor: pointer;
    pointer-events: all;
    border: 3px solid white;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    transition: all 0.2s ease;
}

.range-slider-modern::-webkit-slider-thumb:hover {
    transform: scale(1.15);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.range-slider-modern::-moz-range-thumb {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: hsl(var(--primary));
    cursor: pointer;
    border: 3px solid white;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
}

.range-slider-modern::-webkit-slider-runnable-track {
    height: 6px;
    background: transparent;
}

.range-slider-modern::-moz-range-track {
    height: 6px;
    background: transparent;
}

.range-track-modern {
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 6px;
    background: hsl(var(--border));
    border-radius: 3px;
    transform: translateY(-50%);
    z-index: 1;
}

.range-track-modern::before {
    content: '';
    position: absolute;
    left: var(--range-left, 0%);
    right: calc(100% - var(--range-right, 100%));
    height: 100%;
    background: linear-gradient(90deg, hsl(var(--primary)) 0%, hsl(var(--primary-light)) 100%);
    border-radius: 3px;
    transition: all 0.1s ease;
}

.range-slider-min-modern {
    z-index: 3;
}

.range-slider-max-modern {
    z-index: 4;
}

/* Checkbox */
.filter-checkbox-modern {
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
}

.filter-checkbox-modern input {
    display: none;
}

.checkmark-modern {
    width: 22px;
    height: 22px;
    border: 2px solid hsl(var(--border));
    border-radius: 6px;
    margin-right: 0.75rem;
    position: relative;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.filter-checkbox-modern input:checked + .checkmark-modern {
    background: hsl(var(--primary));
    border-color: hsl(var(--primary));
}

.filter-checkbox-modern input:checked + .checkmark-modern::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 14px;
    font-weight: bold;
}

.checkbox-label-modern {
    font-size: 0.95rem;
    color: hsl(var(--foreground));
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Buttons */
.filter-submit-btn-modern {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, hsl(var(--primary)) 0%, hsl(var(--primary-dark)) 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.filter-submit-btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px hsl(var(--primary) / 0.4);
}

.filter-reset-btn-modern {
    width: 100%;
    padding: 0.875rem;
    background: white;
    color: hsl(var(--primary));
    border: 2px solid hsl(var(--primary));
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
}

.filter-reset-btn-modern:hover {
    background: hsl(var(--primary));
    color: white;
    transform: translateY(-2px);
}

/* Products Header */
.products-header-modern {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.results-count-modern {
    color: hsl(var(--muted-foreground));
    font-size: 1rem;
}

.results-count-modern strong {
    color: hsl(var(--foreground));
    font-weight: 700;
    font-size: 1.2rem;
}

.view-all-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: white;
    color: hsl(var(--primary));
    border: 2px solid hsl(var(--primary));
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.view-all-link:hover {
    background: hsl(var(--primary));
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px hsl(var(--primary) / 0.3);
}

/* Products Grid */
.products-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

/* Ultra Modern Product Cards */
.product-card-ultra-modern {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card-ultra-modern:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.product-link-modern {
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.product-image-wrapper-modern {
    position: relative;
    width: 100%;
    height: 250px;
    overflow: hidden;
    background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
}

.product-image-ultra-modern {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card-ultra-modern:hover .product-image-ultra-modern {
    transform: scale(1.1);
}

.product-image-placeholder-ultra-modern {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: hsl(var(--muted-foreground));
}

.product-image-placeholder-ultra-modern i {
    font-size: 4rem;
}

.product-badge-modern {
    position: absolute;
    top: 1rem;
    right: 1rem;
    padding: 0.5rem 0.875rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 0.375rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.product-badge-modern.verified-modern {
    background: hsl(142, 76%, 36% / 0.95);
    color: white;
}

.product-badge-modern.supplier-modern {
    background: hsl(var(--primary) / 0.95);
    color: white;
    top: 4rem;
}

.product-overlay-modern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 100%);
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding: 1.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card-ultra-modern:hover .product-overlay-modern {
    opacity: 1;
}

.view-details-modern {
    color: white;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
}

.product-info-modern {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-name-modern {
    font-size: 1.1rem;
    font-weight: 700;
    color: hsl(var(--foreground));
    margin-bottom: 0.75rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-supplier-modern {
    font-size: 0.875rem;
    color: hsl(var(--muted-foreground));
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.product-category-modern {
    font-size: 0.8rem;
    color: hsl(var(--muted-foreground));
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.product-price-wrapper-modern {
    margin-top: auto;
    padding-top: 1rem;
    border-top: 2px solid hsl(var(--border));
}

.product-price-modern {
    font-size: 1.5rem;
    font-weight: 700;
    color: hsl(var(--primary));
    display: block;
}

/* Empty State */
.empty-state-modern {
    text-align: center;
    padding: 5rem 2rem;
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.empty-icon {
    font-size: 5rem;
    color: hsl(var(--muted-foreground));
    margin-bottom: 1.5rem;
    opacity: 0.5;
}

.empty-state-modern h4 {
    color: hsl(var(--foreground));
    margin-bottom: 0.75rem;
    font-weight: 700;
}

.empty-state-modern p {
    color: hsl(var(--muted-foreground));
    margin-bottom: 2rem;
}

.btn-modern-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, hsl(var(--primary)) 0%, hsl(var(--primary-dark)) 100%);
    color: white;
    border: none;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-modern-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px hsl(var(--primary) / 0.4);
    color: white;
}

/* Responsive */
@media (max-width: 992px) {
    .filter-sidebar-modern {
        position: static;
        margin-bottom: 2rem;
    }
    
    .dashboard-title {
        font-size: 2rem;
    }
    
    .products-grid-modern {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1rem;
    }
}

@media (max-width: 768px) {
    .dashboard-header {
        padding: 2rem 0 1.5rem;
    }
    
    .dashboard-title {
        font-size: 1.75rem;
    }
    
    .dashboard-stats {
        margin-top: 1rem;
        justify-content: flex-start;
    }
    
    .products-grid-modern {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
    
    .product-image-wrapper-modern {
        height: 200px;
    }
}

/* Recent Orders Dashboard Styles */
.recent-orders-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 2rem;
}

.orders-header-modern {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f0f0f0;
}

.orders-header-modern h3 {
    margin: 0;
    color: #8B4513;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.view-all-orders-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #8B4513;
    color: white;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.view-all-orders-btn:hover {
    background: #6B3410;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 69, 19, 0.3);
    color: white;
}

.orders-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.order-card-dashboard {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.order-card-dashboard:hover {
    border-color: #8B4513;
    box-shadow: 0 4px 12px rgba(139, 69, 19, 0.1);
    transform: translateY(-2px);
}

.order-card-header-dashboard {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.order-date-dashboard {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    font-size: 0.9rem;
}

.order-card-body-dashboard {
    margin-bottom: 1rem;
}

.order-total-dashboard {
    font-size: 1.5rem;
    font-weight: 700;
    color: #8B4513;
    margin-bottom: 0.5rem;
}

.order-items-count-dashboard {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    font-size: 0.9rem;
}

.order-card-actions-dashboard {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn-view-order,
.btn-track-order {
    flex: 1;
    min-width: 120px;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-view-order {
    background: white;
    color: #8B4513;
    border: 2px solid #8B4513;
}

.btn-view-order:hover {
    background: #8B4513;
    color: white;
}

.btn-track-order {
    background: #8B4513;
    color: white;
    border: 2px solid #8B4513;
}

.btn-track-order:hover {
    background: #6B3410;
    border-color: #6B3410;
    color: white;
}

@media (max-width: 768px) {
    .orders-grid-modern {
        grid-template-columns: 1fr;
    }
    
    .orders-header-modern {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}
</style>

<script>
// Price Range Slider Logic
(function() {
    const minSlider = document.getElementById('minPrice');
    const maxSlider = document.getElementById('maxPrice');
    const minDisplay = document.getElementById('minPriceDisplay');
    const maxDisplay = document.getElementById('maxPriceDisplay');
    const track = document.querySelector('.range-track-modern');
    
    if (!minSlider || !maxSlider) return;
    
    const min = parseFloat(minSlider.min);
    const max = parseFloat(maxSlider.max);
    
    function updateRange() {
        const minVal = parseFloat(minSlider.value);
        const maxVal = parseFloat(maxSlider.value);
        
        if (minVal > maxVal) {
            minSlider.value = maxVal;
        }
        
        if (maxVal < minVal) {
            maxSlider.value = minVal;
        }
        
        const finalMin = parseFloat(minSlider.value);
        const finalMax = parseFloat(maxSlider.value);
        
        minDisplay.textContent = finalMin.toFixed(2);
        maxDisplay.textContent = finalMax.toFixed(2);
        
        const leftPercent = ((finalMin - min) / (max - min)) * 100;
        const rightPercent = 100 - ((finalMax - min) / (max - min)) * 100;
        
        track.style.setProperty('--range-left', leftPercent + '%');
        track.style.setProperty('--range-right', (100 - rightPercent) + '%');
    }
    
    updateRange();
    minSlider.addEventListener('input', updateRange);
    maxSlider.addEventListener('input', updateRange);
})();

// Mobile filter toggle
document.addEventListener('DOMContentLoaded', function() {
    const filterToggle = document.querySelector('[data-filter-toggle]');
    const filterSidebar = document.querySelector('.filter-sidebar-modern');
    
    if (filterToggle && filterSidebar) {
        filterToggle.addEventListener('click', function() {
            filterSidebar.classList.toggle('mobile-open');
        });
    }
    
    // Close filter on outside click (mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 768) {
            if (filterSidebar && filterToggle && 
                !filterSidebar.contains(e.target) && 
                !filterToggle.contains(e.target) &&
                filterSidebar.classList.contains('mobile-open')) {
                filterSidebar.classList.remove('mobile-open');
            }
        }
    });
});
</script>

<!-- Include Catalog CSS for consistent styling -->
<link rel="stylesheet" href="<?= \App\View::asset('assets/css/catalog.css?v=' . filemtime(__DIR__ . '/../../assets/css/catalog.css')) ?>">
<link rel="stylesheet" href="<?= \App\View::asset('assets/css/ad-banner.css') ?>">

