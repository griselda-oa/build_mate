<div class="catalog-page">
    <!-- Modern Header with Search Bar -->
    <div class="catalog-hero mb-5">
        <div class="container">
            <!-- Back Button - Positioned in hero section -->
            <div class="catalog-back-button">
                <a href="<?= \App\View::relUrl('/') ?>" class="back-button back-button-hero">
                    <i class="icon-arrow-left"></i>
                    <span>Back to Home</span>
                </a>
            </div>
            <h1 class="catalog-title">
                <?php if (isset($is_supplier_view) && $is_supplier_view): ?>
                    My Products
                <?php else: ?>
                    Discover Building Materials
                <?php endif; ?>
            </h1>
            <p class="catalog-subtitle">
                <?php if (isset($is_supplier_view) && $is_supplier_view): ?>
                    Manage your product inventory
                <?php else: ?>
                    Find everything you need for your construction projects
                <?php endif; ?>
            </p>
            
            <!-- Quick Search Bar -->
            <div class="quick-search-wrapper">
                <div class="quick-search">
                    <i class="icon-search"></i>
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

    <div class="container">
        <div class="row">
            <!-- Modern Filter Sidebar -->
            <div class="col-lg-3 col-md-4">
                <button class="filter-toggle-btn d-md-none" data-filter-toggle>
                    <i class="bi bi-funnel"></i> Filters
                </button>
                
                <div class="filter-sidebar-modern">
                    <div class="filter-header-modern">
                        <h5 class="mb-0">
                            <i class="bi bi-sliders"></i> Filters
                        </h5>
                        <button class="filter-clear-btn" onclick="document.getElementById('filterForm').reset(); document.getElementById('filterForm').submit();">
                            Clear All
                        </button>
                    </div>
                    
                    <form method="GET" action="<?= \App\View::relUrl('/catalog') ?>" id="filterForm">
                        <!-- Search -->
                        <div class="filter-section-modern">
                            <label class="filter-label-modern">
                                <i class="icon-search"></i> Search
                            </label>
                            <input type="text" 
                                   class="filter-input-modern" 
                                   id="q" 
                                   name="q" 
                                   value="<?= \App\View::e($filters['query'] ?? '') ?>" 
                                   placeholder="Search products...">
                        </div>
                        
                        <!-- Category -->
                        <div class="filter-section-modern">
                            <label class="filter-label-modern">
                                <i class="bi bi-grid"></i> Category
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
                                <i class="bi bi-currency-exchange"></i> Price Range
                            </label>
                            <div class="price-slider-modern">
                                <div class="price-display-modern">
                                    <div class="price-value-modern">
                                        <span class="price-label">Min</span>
                                        <span class="price-amount" id="minPriceDisplay"><?= number_format($filters['min_price'] ?? ($price_range['min'] / 100), 2) ?></span>
                                        <span class="price-currency">GHS</span>
                                    </div>
                                    <div class="price-separator-modern">—</div>
                                    <div class="price-value-modern">
                                        <span class="price-label">Max</span>
                                        <span class="price-amount" id="maxPriceDisplay"><?= number_format($filters['max_price'] ?? ($price_range['max'] / 100), 2) ?></span>
                            <span class="price-currency">GHS</span>
                        </div>
                                </div>
                                <div class="range-slider-wrapper-modern">
                            <input type="range" 
                                           class="range-slider-modern range-slider-min" 
                                   id="minPrice" 
                                   name="min" 
                                   min="<?= $price_range['min'] / 100 ?>" 
                                   max="<?= $price_range['max'] / 100 ?>" 
                                   value="<?= $filters['min_price'] ?? ($price_range['min'] / 100) ?>" 
                                   step="0.01">
                            <input type="range" 
                                           class="range-slider-modern range-slider-max" 
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
                
                        <!-- Verified Filter -->
                        <div class="filter-section-modern">
                            <label class="filter-checkbox-modern">
                        <input type="checkbox" id="verified" name="verified" <?= ($filters['verified_only'] ?? false) ? 'checked' : '' ?>>
                                <span class="checkmark-modern"></span>
                                <span class="checkbox-label-modern">
                                    <i class="bi bi-check-circle-fill"></i> Verified Products Only
                                </span>
                    </label>
                </div>
                
                        <button type="submit" class="filter-submit-btn-modern">
                            <i class="bi bi-funnel-fill"></i> Apply Filters
                        </button>
            </form>
        </div>
    </div>
    
            <!-- Products Grid -->
            <div class="col-lg-9 col-md-8">
                <!-- Sponsored Products Section (Top of Catalog) -->
                <?php 
                $sponsoredProducts = $sponsored_products ?? $sponsoredProducts ?? [];
                // Debug: Log if sponsored products are empty
                if (empty($sponsoredProducts) && !$is_supplier_view) {
                    error_log("Catalog: No sponsored products found. sponsored_products var: " . (isset($sponsored_products) ? 'set' : 'not set'));
                }
                if (!empty($sponsoredProducts) && !$is_supplier_view): 
                ?>
                    <div class="sponsored-section-modern">
                        <div class="sponsored-header-modern">
                            <div class="sponsored-title-wrapper">
                                <i class="icon-star-fill"></i>
                                <h2 class="sponsored-title-modern">Sponsored Products</h2>
                                <span class="sponsored-badge-modern">Premium Ads</span>
                            </div>
                            <p class="sponsored-subtitle-modern">Featured products from premium suppliers</p>
                        </div>
                        <div class="sponsored-grid-modern">
                            <?php foreach ($sponsoredProducts as $product): ?>
                                <?php 
                                // Get advertisement image/video - check both ad_image and image_url fields
                                $adImage = $product['ad_image'] ?? $product['image_url'] ?? $product['product_image'] ?? '';
                                
                                // If it's a relative path, make it absolute
                                if (!empty($adImage) && !preg_match('/^https?:\/\//', $adImage)) {
                                    if (strpos($adImage, \App\View::basePath() . '/') !== 0) {
                                        $adImage = '/build_mate' . (strpos($adImage, '/') === 0 ? '' : '/') . $adImage;
                                    }
                                }
                                
                                $isVideo = !empty($adImage) && preg_match('/\.(mp4|mov|webm)$/i', $adImage);
                                ?>
                                <div class="sponsored-card-modern">
                                    <a href="<?= \App\View::relUrl('/product/' . $product['slug']) ?>" class="sponsored-link-modern">
                                        <div class="sponsored-image-modern">
                                            <?php if (!empty($adImage)): ?>
                                                <?php if ($isVideo): ?>
                                                    <video src="<?= \App\View::relImage($adImage) ?>" 
                                                           class="sponsored-media-modern" 
                                                           muted
                                                           loop
                                                           playsinline
                                                           autoplay
                                                           onmouseover="this.play()"
                                                           onmouseout="this.pause()"
                                                           onerror="console.error('Video load error:', this.src)">
                                                    </video>
                                                <?php else: ?>
                                                    <img src="<?= \App\View::relImage($adImage) ?>" 
                                                         class="sponsored-media-modern" 
                                                         alt="<?= \App\View::e($product['name']) ?>"
                                                         loading="lazy">
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div class="sponsored-placeholder-modern">
                                                    <i class="bi bi-image"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="sponsored-badge-overlay-modern">
                                                <span class="sponsored-label-modern">
                                                    <i class="icon-star-fill"></i> Sponsored
                                                </span>
                                            </div>
                                        </div>
                                        <div class="sponsored-content-modern">
                                            <div class="sponsored-category-modern"><?= \App\View::e($product['category_name'] ?? '') ?></div>
                                            <h3 class="sponsored-name-modern"><?= \App\View::e($product['name']) ?></h3>
                                            <p class="sponsored-supplier-modern">
                                                <i class="bi bi-shop"></i> <?= \App\View::e($product['supplier_name'] ?? 'Unknown') ?>
                                            </p>
                                            <div class="sponsored-price-modern">
                                                <?= \App\Money::format($product['price_cents'], $product['currency'] ?? 'GHS') ?>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Catalog Toolbar -->
                <div class="catalog-toolbar">
                    <div class="results-info">
                        <strong class="results-count-modern"><?= count($products) ?></strong>
                        <span class="results-text">product<?= count($products) !== 1 ? 's' : '' ?> found</span>
            </div>
                    
                    <div class="toolbar-actions">
                        <div class="sort-wrapper">
                            <label class="sort-label">
                                <i class="bi bi-sort-down"></i> Sort:
                            </label>
                            <select class="sort-select" data-sort>
                                <option value="default">Default</option>
                                <option value="price-low">Price: Low to High</option>
                                <option value="price-high">Price: High to Low</option>
                                <option value="name">Name: A to Z</option>
                            </select>
        </div>
        
                        <div class="view-toggle">
                            <button class="view-btn active" data-view="grid" title="Grid View">
                                <i class="bi bi-grid-3x3-gap"></i>
                            </button>
                            <button class="view-btn" data-view="list" title="List View">
                                <i class="bi bi-list-ul"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <div class="product-grid grid-view" id="productGrid">
                    <?php if (empty($products)): ?>
                        <div class="empty-state-modern">
                            <div class="empty-icon">
                                <i class="bi bi-inbox"></i>
                            </div>
                            <h3>No products found</h3>
                            <p>Try adjusting your filters or search terms to find what you're looking for.</p>
                            <button class="btn btn-primary" onclick="document.getElementById('filterForm').reset(); document.getElementById('filterForm').submit();">
                                Clear All Filters
                            </button>
                </div>
            <?php else: ?>
                <?php 
                // Get active advertisements for products
                $adModel = new \App\Advertisement();
                $activeAds = [];
                try {
                    $allAds = $adModel->getActive();
                    foreach ($allAds as $ad) {
                        $activeAds[$ad['product_id']] = $ad;
                    }
                } catch (\Exception $e) {
                    error_log("Error fetching advertisements: " . $e->getMessage());
                }
                ?>
                <?php foreach ($products as $product): ?>
                            <?php 
                            $isAdvertised = isset($activeAds[$product['id']]);
                            $adData = $isAdvertised ? $activeAds[$product['id']] : null;
                            $displayImage = $adData && !empty($adData['image_url']) ? $adData['image_url'] : ($product['image_url'] ?? '');
                            $isVideo = $adData && !empty($adData['image_url']) && preg_match('/\.(mp4|mov|webm)$/i', $adData['image_url']);
                            ?>
                            <div class="product-card-sleek <?= $isAdvertised ? 'advertised-product' : '' ?>">
                                <a href="<?= \App\View::relUrl('/product/' . $product['slug']) ?>" class="product-link-sleek">
                                    <div class="product-image-sleek">
                                    <?php if (!empty($displayImage)): ?>
                                        <?php if ($isVideo): ?>
                                            <video src="<?= \App\View::relImage($displayImage) ?>" 
                                                   class="product-img" 
                                                   muted
                                                   loop
                                                   playsinline
                                                   onmouseover="this.play()"
                                                   onmouseout="this.pause()"
                                                   onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            </video>
                                        <?php else: ?>
                                            <img src="<?= \App\View::relImage($displayImage) ?>" 
                                                 class="product-img" 
                                                 alt="<?= \App\View::e($product['name']) ?>"
                                                 loading="lazy"
                                                 onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <?php endif; ?>
                                            <div class="product-placeholder-sleek" style="display: none;">
                                                <i class="bi bi-image"></i>
                                                <span>Image not available</span>
                                            </div>
                                    <?php else: ?>
                                            <div class="product-placeholder-sleek">
                                            <i class="bi bi-image"></i>
                                            <span>No image</span>
                                        </div>
                                    <?php endif; ?>
                                        
                                        <!-- Badges -->
                                        <div class="product-badges-sleek">
                                    <?php if ($isAdvertised): ?>
                                                <span class="badge-sleek sponsored-sleek">
                                                    <i class="icon-star-fill"></i> Sponsored
                                                </span>
                                    <?php endif; ?>
                                    <?php if ($product['verified'] ?? false): ?>
                                                <span class="badge-sleek verified-sleek">
                                                    <i class="bi bi-check-circle-fill"></i> Verified
                                                </span>
                                    <?php endif; ?>
                                    <?php if ($product['verified_badge'] ?? false): ?>
                                                <span class="badge-sleek supplier-sleek">
                                                    <i class="bi bi-shield-check"></i> Verified Supplier
                                                </span>
                                    <?php endif; ?>
                                    <?php if (($product['plan_type'] ?? '') === 'premium'): ?>
                                                <span class="badge-sleek premium-sleek">
                                                    <i class="bi bi-gem"></i> Premium
                                                </span>
                                    <?php endif; ?>
                                </div>
                                        
                                        <!-- Quick Actions -->
                                        <div class="product-actions-sleek">
                                            <button class="action-btn-sleek" onclick="event.preventDefault(); window.location.href=window.buildUrl('/product/<?= \App\View::e($product['slug']) ?>')">
                                                <i class="icon-eye"></i>
                                            </button>
                                            <?php 
                                            $user = \App\Auth::check() ? \App\Auth::user() : null;
                                            $isSupplier = $user && $user['role'] === 'supplier';
                                            $isAdmin = $user && $user['role'] === 'admin';
                                            $cannotPurchase = $isSupplier || $isAdmin;
                                            // Suppliers and admins see products for management, not for purchase
                                            if (!$cannotPurchase): 
                                            ?>
                                                <button class="action-btn-sleek" onclick="event.preventDefault(); addToCart(<?= $product['id'] ?>)">
                                                    <i class="icon-cart-plus"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (\App\Auth::check() && !$cannotPurchase): ?>
                                                <?php
                                                $wishlistModel = new \App\Wishlist();
                                                $isInWishlist = $wishlistModel->isInWishlist(\App\Auth::user()['id'], $product['id']);
                                                ?>
                                                <button class="action-btn-sleek wishlist-btn-catalog <?= $isInWishlist ? 'in-wishlist' : '' ?>" 
                                                        onclick="event.preventDefault(); toggleWishlistCatalog(<?= $product['id'] ?>, this)">
                                                    <i class="icon-heart<?= $isInWishlist ? '-fill' : '' ?>"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="product-content-sleek">
                                        <div class="product-category-sleek">
                                            <?= \App\View::e($product['category_name'] ?? '') ?>
                                        </div>
                                        <h3 class="product-name-sleek"><?= \App\View::e($product['name']) ?></h3>
                                        <p class="product-supplier-sleek">
                                            <i class="bi bi-shop"></i> <?= \App\View::e($product['supplier_name'] ?? 'Unknown') ?>
                                        </p>
                                        <div class="product-price-sleek">
                                            <span class="price-amount-sleek" data-price-cents="<?= $product['price_cents'] ?>" data-currency="<?= $product['currency'] ?>">
                                            <?= \App\Money::format($product['price_cents'], $product['currency']) ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Catalog JavaScript -->
<script src="<?= \App\View::relAsset('assets/js/catalog.js') ?>?v=<?= time() ?>"></script>

<script>
// Wishlist toggle for catalog
async function toggleWishlistCatalog(productId, btn) {
    <?php if (!\App\Auth::check()): ?>
        window.location.href = window.buildUrl('/login');
        return;
    <?php endif; ?>
    
    const icon = btn.querySelector('i');
    const isInWishlist = btn.classList.contains('in-wishlist');
    const action = isInWishlist ? 'remove' : 'add';
    const url = buildUrl(`product/wishlist/${action}`);
    
    btn.disabled = true;
    const originalClass = icon.className;
    icon.className = 'bi bi-hourglass-split';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ product_id: productId })
        });
        const data = await response.json();
        
        if (data.success) {
            if (action === 'add') {
                btn.classList.add('in-wishlist');
                icon.className = 'icon-heart-fill';
                showNotification('Added to wishlist!', 'success');
                updateWishlistCount(1);
            } else {
                btn.classList.remove('in-wishlist');
                icon.className = 'icon-heart';
                showNotification('Removed from wishlist', 'info');
                updateWishlistCount(-1);
            }
        } else {
            showNotification(data.message || 'Operation failed', 'error');
            icon.className = originalClass;
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Network error. Please try again.', 'error');
        icon.className = originalClass;
    } finally {
        btn.disabled = false;
    }
}

function updateWishlistCount(change) {
    const wishlistCountEl = document.getElementById('wishlistCount');
    if (wishlistCountEl) {
        const current = parseInt(wishlistCountEl.textContent) || 0;
        const newCount = Math.max(0, current + change);
        wishlistCountEl.textContent = newCount;
        if (newCount === 0) {
            wishlistCountEl.style.display = 'none';
        } else {
            wishlistCountEl.style.display = 'flex';
        }
    }
}

function showNotification(message, type) {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show fixed-top-alert`;
    alertContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertContainer.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alertContainer);
    setTimeout(() => {
        const alert = bootstrap.Alert.getInstance(alertContainer);
        if (alert) alert.close();
        else alertContainer.remove();
    }, 3000);
}
</script>

<!-- Catalog Styles -->
<link rel="stylesheet" href="<?= \App\View::relAsset('assets/css/catalog.css') ?>">
