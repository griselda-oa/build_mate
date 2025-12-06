<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= \App\View::relAsset('assets/css/homepage-new.css') ?>">

<style>
/* Hide the main navbar on homepage only */
body > nav.navbar {
    display: none !important;
}

/* Remove top margin/padding from main content */
body > main {
    padding-top: 0 !important;
    margin-top: 0 !important;
}

/* Hide default footer on homepage */
body > footer {
    display: none !important;
}
</style>

<div class="homepage-container">
    <!-- Sidebar Navigation -->
    <aside class="sidebar-nav">
        <div class="sidebar-header">
            <h2 class="sidebar-brand">
                <i class="bi bi-hammer"></i>
                BuildMate
            </h2>
            <div class="stats-badge">
                <span class="stats-number"><?= count($featuredProducts ?? []) ?></span>
                <span class="stats-label">Products<br>Available</span>
            </div>
        </div>

        <nav class="sidebar-menu">
            <a href="<?= \App\View::relUrl('/catalog') ?>" class="menu-item active">
                <i class="bi bi-compass"></i>
                <span>Explore Now</span>
            </a>
            
            <?php if (\App\Auth::check()): ?>
                <?php $user = \App\Auth::user(); ?>
                
                <!-- Admin Dashboard Link -->
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="<?= \App\View::relUrl('/admin/dashboard') ?>" class="menu-item">
                        <i class="bi bi-speedometer2"></i>
                        <span>Admin Dashboard</span>
                    </a>
                <?php endif; ?>
                
                <!-- Customer Orders Link -->
                <?php if ($user['role'] === 'buyer' || $user['role'] === 'admin'): ?>
                    <a href="<?= \App\View::relUrl('/orders') ?>" class="menu-item">
                        <i class="bi bi-bag-check"></i>
                        <span>My Orders</span>
                    </a>
                <?php endif; ?>
                
                <!-- Supplier Dashboard Link -->
                <?php if ($user['role'] === 'supplier'): ?>
                    <a href="<?= \App\View::relUrl('/supplier/dashboard') ?>" class="menu-item">
                        <i class="bi bi-shop"></i>
                        <span>Supplier Dashboard</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if (!empty($categories)): ?>
                <?php foreach (array_slice($categories, 0, 4) as $category): ?>
                    <a href="<?= \App\View::relUrl('/catalog?category=' . $category['id']) ?>" class="menu-item">
                        <i class="bi bi-tag"></i>
                        <span><?= \App\View::e($category['name']) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <a href="<?= \App\View::relUrl('/catalog') ?>" class="menu-item">
                <i class="bi bi-lightbulb"></i>
                <span>Inspiration</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="quick-actions">
                <button class="quick-action-btn" onclick="window.location.href='<?= \App\View::relUrl('/catalog') ?>'">
                    <i class="bi bi-plus-circle"></i>
                    <span>Browse All</span>
                </button>
            </div>

            <?php if (\App\Auth::check()): ?>
                <div class="user-section">
                    <div class="user-avatar">
                        <?= strtoupper(substr(\App\Auth::user()['name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?= \App\View::e(\App\Auth::user()['name'] ?? 'User') ?></span>
                        <a href="<?= \App\View::relUrl('/logout') ?>" class="logout-link">
                            <i class="bi bi-box-arrow-right"></i> Log out
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="page-title">
                <h1>Discover Quality Materials</h1>
                <p class="subtitle">Trusted by 1000+ builders across Ghana</p>
            </div>
            
            <div class="top-bar-filters">
                <button class="filter-btn active" data-filter="all">
                    <i class="bi bi-grid-3x3-gap"></i> All Products
                </button>
                <button class="filter-btn" data-filter="featured">
                    <i class="bi bi-star-fill"></i> Best Sellers
                </button>
                <button class="filter-btn" data-filter="new">
                    <i class="bi bi-lightning-fill"></i> New Arrivals
                </button>
            </div>

            <div class="top-bar-actions">
                <button class="action-btn search-btn" onclick="toggleSearch()">
                    <i class="bi bi-search"></i>
                </button>
                <a href="<?= \App\View::relUrl('/cart') ?>" class="action-btn cart-btn">
                    <i class="bi bi-cart3"></i>
                    <?php if (!empty($_SESSION['cart'])): ?>
                        <span class="cart-badge"><?= count($_SESSION['cart']) ?></span>
                    <?php endif; ?>
                </a>
                <?php if (!\App\Auth::check()): ?>
                    <a href="<?= \App\View::relUrl('/login') ?>" class="btn-login">
                        <i class="bi bi-person"></i> Sign In
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Search Bar (Hidden by default) -->
        <div class="search-bar-container" id="searchBar" style="display: none;">
            <div class="search-bar">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Search for cement, blocks, roofing sheets..." id="searchInput">
                <button class="search-close" onclick="toggleSearch()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>

        <!-- Hero Banner -->
        <div class="hero-banner">
            <div class="banner-card promo-banner">
                <div class="banner-content">
                    <h2>GET UP TO 50% OFF</h2>
                    <p>On selected construction materials</p>
                    <a href="<?= \App\View::relUrl('/catalog') ?>" class="banner-btn">Get Discount</a>
                </div>
                <div class="banner-image">
                    <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600&q=80" alt="Discount">
                </div>
            </div>

            <div class="banner-card featured-banner">
                <div class="banner-content">
                    <span class="banner-tag">
                        <i class="bi bi-calendar-event"></i> This Weekend
                    </span>
                    <h3>Builder's Special</h3>
                    <p>Premium materials at everyday prices</p>
                    <a href="<?= \App\View::relUrl('/catalog') ?>" class="banner-link">
                        Shop Weekend Deals <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="banner-image">
                    <img src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=400&q=80" alt="Weekend Deals">
                </div>
            </div>
        </div>

        <!-- Featured Products Grid -->
        <div class="products-grid">
            <?php if (!empty($featuredProducts)): ?>
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="product-card" data-product-id="<?= $product['id'] ?>">
                        <div class="product-image">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?= \App\View::image($product['image_url']) ?>" 
                                     alt="<?= \App\View::e($product['name']) ?>"
                                     onerror="this.src='https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=400&q=80'">
                            <?php else: ?>
                                <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=400&q=80" 
                                     alt="<?= \App\View::e($product['name']) ?>">
                            <?php endif; ?>
                            <button class="wishlist-btn" onclick="toggleWishlist(<?= $product['id'] ?>)">
                                <i class="bi bi-heart"></i>
                            </button>
                            <?php if (!empty($product['verified'])): ?>
                                <span class="verified-badge">
                                    <i class="bi bi-patch-check-fill"></i> Verified
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($product['stock']) && $product['stock'] < 10): ?>
                                <span class="stock-badge low-stock">
                                    <i class="bi bi-exclamation-circle"></i> Only <?= $product['stock'] ?> left
                                </span>
                            <?php elseif (!empty($product['stock']) && $product['stock'] > 50): ?>
                                <span class="stock-badge in-stock">
                                    <i class="bi bi-check-circle-fill"></i> In Stock
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <div class="product-meta">
                                <span class="product-category">
                                    <i class="bi bi-tag"></i> <?= \App\View::e($product['category_name'] ?? 'Product') ?>
                                </span>
                                <?php if (!empty($product['rating'])): ?>
                                    <span class="product-rating">
                                        <i class="bi bi-star-fill"></i> <?= number_format($product['rating'], 1) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h3 class="product-name"><?= \App\View::e($product['name']) ?></h3>
                            <div class="product-footer">
                                <div class="price-section">
                                    <span class="product-price">
                                        <?= \App\Money::format($product['price_cents'], $product['currency'] ?? 'GHS') ?>
                                    </span>
                                    <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price_cents']): ?>
                                        <span class="original-price">
                                            <?= \App\Money::format($product['original_price'], $product['currency'] ?? 'GHS') ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= \App\View::relUrl('/product/' . $product['slug']) ?>" class="view-btn">
                                    <i class="bi bi-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-box-seam"></i>
                    <p>No products available at the moment</p>
                    <a href="<?= \App\View::relUrl('/catalog') ?>" class="btn-primary">Browse Catalog</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modern Footer for Homepage (Outside container) -->
<footer class="homepage-footer">
        <div class="footer-content">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3 class="footer-title">BuildMate Ghana</h3>
                    <p class="footer-description">Your trusted partner for quality construction materials across Ghana.</p>
                    <div class="footer-social">
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-heading">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?= \App\View::relUrl('/catalog') ?>">Browse Catalog</a></li>
                        <li><a href="<?= \App\View::relUrl('/') ?>">About Us</a></li>
                        <li><a href="<?= \App\View::relUrl('/contact') ?>">Contact</a></li>
                        <li><a href="<?= \App\View::relUrl('/') ?>">Become a Supplier</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-heading">Customer Service</h4>
                    <ul class="footer-links">
                        <li><a href="<?= \App\View::relUrl('/') ?>">Help Center</a></li>
                        <li><a href="<?= \App\View::relUrl('/orders') ?>">Track Order</a></li>
                        <li><a href="<?= \App\View::relUrl('/') ?>">Returns</a></li>
                        <li><a href="<?= \App\View::relUrl('/') ?>">Shipping Info</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-heading">Contact Us</h4>
                    <ul class="footer-contact">
                        <li><i class="bi bi-geo-alt"></i> Accra, Ghana</li>
                        <li><i class="bi bi-telephone"></i> +233 XX XXX XXXX</li>
                        <li><i class="bi bi-envelope"></i> info@buildmate.gh</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> BuildMate Ghana. All rights reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Cookie Policy</a>
                </div>
            </div>
        </div>
</footer>

<script>
// Search toggle
function toggleSearch() {
    const searchBar = document.getElementById('searchBar');
    const searchInput = document.getElementById('searchInput');
    
    if (searchBar.style.display === 'none') {
        searchBar.style.display = 'block';
        setTimeout(() => searchInput.focus(), 100);
    } else {
        searchBar.style.display = 'none';
        searchInput.value = '';
    }
}

// Search functionality
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    if (query.length > 2) {
        // Filter products in real-time
        document.querySelectorAll('.product-card').forEach(card => {
            const name = card.querySelector('.product-name').textContent.toLowerCase();
            const category = card.querySelector('.product-category').textContent.toLowerCase();
            if (name.includes(query) || category.includes(query)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    } else {
        document.querySelectorAll('.product-card').forEach(card => {
            card.style.display = 'flex';
        });
    }
});

// Filter functionality with animation
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Add loading animation
        const grid = document.querySelector('.products-grid');
        grid.style.opacity = '0.5';
        setTimeout(() => {
            grid.style.opacity = '1';
        }, 300);
    });
});

// Wishlist toggle with animation
function toggleWishlist(productId) {
    event.preventDefault();
    event.stopPropagation();
    
    const btn = event.currentTarget;
    btn.classList.toggle('active');
    const icon = btn.querySelector('i');
    icon.classList.toggle('bi-heart');
    icon.classList.toggle('bi-heart-fill');
    
    // Show notification
    showNotification(btn.classList.contains('active') ? 'Added to wishlist!' : 'Removed from wishlist');
}

// Notification system
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification-toast';
    notification.innerHTML = `
        <i class="bi bi-check-circle-fill"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 100);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Smooth scroll for product cards
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (!e.target.closest('.wishlist-btn') && !e.target.closest('.view-btn')) {
            window.location.href = this.querySelector('.view-btn').href;
        }
    });
});

// Add entrance animations
window.addEventListener('load', function() {
    const cards = document.querySelectorAll('.product-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, index * 50);
    });
});
</script>
