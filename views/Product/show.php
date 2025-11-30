<div class="product-detail-page">
    <div class="container">
        <!-- Back Button and Breadcrumb Container -->
        <div class="product-navigation-header">
            <a href="/build_mate/catalog" class="back-button">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Catalog</span>
            </a>
            <nav aria-label="breadcrumb" class="product-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/build_mate/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/build_mate/catalog">Catalog</a></li>
                    <li class="breadcrumb-item"><a href="/build_mate/catalog?cat=<?= $product['category_id'] ?>"><?= \App\View::e($product['category_name'] ?? '') ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= \App\View::e($product['name']) ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row g-4">
        <!-- Product Images -->
        <div class="col-lg-6">
            <div class="product-image-gallery">
                <div class="main-image-wrapper">
        <?php if (!empty($product['image_url'])): ?>
                        <img src="<?= \App\View::e($product['image_url']) ?>" 
                             class="main-product-image" 
                             alt="<?= \App\View::e($product['name']) ?>"
                             id="mainProductImage"
                             onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="product-image-placeholder-large" style="display: none;">
                            <i class="bi bi-image"></i>
                            <p>Image not available</p>
                        </div>
                        <button class="image-zoom-btn" onclick="toggleImageZoom()" id="zoomBtn" style="display: none;">
                            <i class="bi bi-zoom-in"></i>
                        </button>
        <?php else: ?>
                        <div class="product-image-placeholder-large">
                            <i class="bi bi-image"></i>
                            <p>No Image Available</p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Badges -->
                    <div class="product-badges-overlay">
                        <?php if ($product['verified'] ?? false): ?>
                            <span class="product-badge-modern verified-modern">
                                <i class="bi bi-check-circle-fill"></i> Verified
                            </span>
                        <?php endif; ?>
                        <?php if ($product['verified_badge'] ?? false): ?>
                            <span class="product-badge-modern supplier-modern">
                                <i class="bi bi-shield-check"></i> Verified Supplier
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="product-info-modern">
                <!-- Category & Supplier -->
                <div class="product-meta-modern">
                    <span class="product-category-modern">
                        <i class="bi bi-tag"></i> <?= \App\View::e($product['category_name'] ?? '') ?>
                    </span>
                    <span class="product-supplier-modern">
                        <i class="bi bi-shop"></i> <?= \App\View::e($product['supplier_name'] ?? 'Unknown Supplier') ?>
                    </span>
                </div>

                <!-- Product Title -->
                <h1 class="product-title-modern"><?= \App\View::e($product['name']) ?></h1>

                <!-- Price -->
                <div class="product-price-modern">
                    <span class="price-amount-modern" data-price-cents="<?= $product['price_cents'] ?>" data-currency="<?= $product['currency'] ?>">
            <?= \App\Money::format($product['price_cents'], $product['currency']) ?>
                    </span>
                    <span class="price-label">per unit</span>
                </div>

                <!-- Stock Status -->
                <div class="product-stock-modern">
                    <?php if ($product['stock'] > 0): ?>
                        <div class="stock-badge in-stock">
                            <i class="bi bi-check-circle"></i>
                            <span>In Stock (<?= $product['stock'] ?> units available)</span>
                        </div>
                    <?php else: ?>
                        <div class="stock-badge out-of-stock">
                            <i class="bi bi-x-circle"></i>
                            <span>Out of Stock</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Purchase Card -->
                <div class="purchase-card-modern">
                    <form method="POST" action="/build_mate/cart/add/<?= $product['id'] ?>/" id="addToCartForm">
                        <?= \App\Csrf::field() ?>
                        <div class="quantity-selector-modern">
                            <label class="quantity-label">
                                <i class="bi bi-cart-plus"></i> Quantity
                            </label>
                            <div class="quantity-controls">
                                <button type="button" class="qty-btn qty-decrease" onclick="decreaseQty()">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" 
                                       class="qty-input" 
                                       id="qty" 
                                       name="qty" 
                                       value="1" 
                                       min="1" 
                                       max="<?= $product['stock'] ?>"
                                       readonly>
                                <button type="button" class="qty-btn qty-increase" onclick="increaseQty()">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
        </div>
        
                        <?php 
                        $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
                        $isSupplier = $user && $user['role'] === 'supplier';
                        $isAdmin = $user && $user['role'] === 'admin';
                        $cannotPurchase = $isSupplier || $isAdmin;
                        ?>
                        <?php if ($isSupplier): ?>
                            <div class="alert alert-warning" style="background: linear-gradient(135deg, #FFF7ED 0%, #FEF3E2 100%); border: 2px solid rgba(139, 69, 19, 0.2); border-radius: 16px; padding: 1.5rem; margin-bottom: 1rem;">
                                <i class="bi bi-info-circle"></i>
                                <strong>Supplier Account</strong>
                                <p class="mb-0" style="margin-top: 0.5rem; color: #6B7280;">Suppliers cannot purchase products. To make purchases, please create a separate buyer account.</p>
                            </div>
                        <?php elseif ($isAdmin): ?>
                            <div class="alert alert-warning" style="background: linear-gradient(135deg, #FFF7ED 0%, #FEF3E2 100%); border: 2px solid rgba(139, 69, 19, 0.2); border-radius: 16px; padding: 1.5rem; margin-bottom: 1rem;">
                                <i class="bi bi-info-circle"></i>
                                <strong>Admin Account</strong>
                                <p class="mb-0" style="margin-top: 0.5rem; color: #6B7280;">Admins cannot purchase products. To make purchases, please create a separate buyer account.</p>
                            </div>
                        <?php elseif ($product['stock'] > 0): ?>
                            <button type="submit" 
                                    class="add-to-cart-btn-modern" 
                                    id="addToCartBtn">
                                <i class="bi bi-cart-plus-fill"></i>
                                <span>Add to Cart</span>
                            </button>
                        <?php else: ?>
                            <button type="button" 
                                    class="add-to-cart-btn-modern waitlist-btn" 
                                    id="waitlistBtn"
                                    onclick="toggleWaitlist(<?= $product['id'] ?>)">
                                <i class="bi bi-bell"></i>
                                <span id="waitlistBtnText"><?= ($isInWaitlist ?? false) ? 'Remove from Waitlist' : 'Join Waitlist' ?></span>
                            </button>
                        <?php endif; ?>

                        <?php if (!$cannotPurchase): ?>
                            <div class="purchase-actions">
                                <button type="button" class="action-btn-secondary" onclick="addToWishlist()" id="wishlistBtn">
                                    <i class="bi bi-heart<?= ($isInWishlist ?? false) ? '-fill' : '' ?>"></i> 
                                    <span><?= ($isInWishlist ?? false) ? 'Saved' : 'Save for Later' ?></span>
                                </button>
                                <button type="button" class="action-btn-secondary" onclick="shareProduct()">
                                    <i class="bi bi-share"></i> Share
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Quick Info -->
                <div class="quick-info-modern">
                    <div class="info-item">
                        <i class="bi bi-truck"></i>
                        <div>
                            <strong>Fast Delivery</strong>
                            <small>Available in your area</small>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="bi bi-shield-check"></i>
                        <div>
                            <strong>Secure Payment</strong>
                            <small>Paystack secure payment</small>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <div>
                            <strong>Easy Returns</strong>
                            <small>7-day return policy</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Details Tabs -->
    <div class="product-tabs-modern mt-5">
        <ul class="nav nav-tabs-modern" role="tablist">
            <li class="nav-item">
                <button class="nav-link-modern active" data-bs-toggle="tab" data-bs-target="#description" type="button">
                    <i class="bi bi-file-text"></i> Description
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link-modern" data-bs-toggle="tab" data-bs-target="#specifications" type="button">
                    <i class="bi bi-list-check"></i> Specifications
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link-modern" data-bs-toggle="tab" data-bs-target="#reviews" type="button">
                    <i class="bi bi-star"></i> Reviews
                </button>
            </li>
        </ul>
        <div class="tab-content-modern">
            <div class="tab-pane fade show active" id="description">
                <div class="tab-content-inner">
                    <h3>Product Description</h3>
                    <div class="description-content">
                        <?= nl2br(\App\View::e($product['description'] ?? 'No description available for this product.')) ?>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="specifications">
                <div class="tab-content-inner">
                    <h3>Product Specifications</h3>
                    <div class="specs-grid">
                        <div class="spec-item">
                            <span class="spec-label">Category</span>
                            <span class="spec-value"><?= \App\View::e($product['category_name'] ?? 'N/A') ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Supplier</span>
                            <span class="spec-value"><?= \App\View::e($product['supplier_name'] ?? 'N/A') ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Stock Available</span>
                            <span class="spec-value"><?= $product['stock'] ?> units</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="reviews">
                <div class="tab-content-inner">
                    <!-- Review Stats -->
                    <?php if (!empty($reviewStats['total_reviews'])): ?>
                        <div class="review-stats-modern mb-4">
                            <div class="review-overview">
                                <div class="review-rating-large">
                                    <span class="rating-number"><?= number_format($reviewStats['average_rating'], 1) ?></span>
                                    <div class="rating-stars-large">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?= $i <= round($reviewStats['average_rating']) ? '-fill' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="review-count-text">Based on <?= $reviewStats['total_reviews'] ?> review<?= $reviewStats['total_reviews'] !== 1 ? 's' : '' ?></span>
                                </div>
                                <div class="review-breakdown">
                                    <?php 
                                    // Map numeric star values to word format keys
                                    $starMap = [
                                        5 => 'five_star',
                                        4 => 'four_star',
                                        3 => 'three_star',
                                        2 => 'two_star',
                                        1 => 'one_star'
                                    ];
                                    for ($star = 5; $star >= 1; $star--): 
                                        $starKey = $starMap[$star];
                                        $starCount = $reviewStats[$starKey] ?? 0;
                                        $percentage = $reviewStats['total_reviews'] > 0 ? ($starCount / $reviewStats['total_reviews'] * 100) : 0;
                                    ?>
                                        <div class="star-row">
                                            <span class="star-label"><?= $star ?> star</span>
                                            <div class="star-bar">
                                                <div class="star-bar-fill" style="width: <?= $percentage ?>%"></div>
                                            </div>
                                            <span class="star-count"><?= $starCount ?></span>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Write Review Button -->
                    <?php if (isset($canReview) && $canReview): ?>
                        <div class="write-review-section mb-4">
                            <button class="btn-write-review" onclick="showReviewForm()">
                                <i class="bi bi-pencil-square"></i> Write a Review
                            </button>
                        </div>
                        
                        <!-- Review Form (Hidden by default) -->
                        <div class="review-form-modern" id="reviewForm" style="display: none;">
                            <form method="POST" action="/build_mate/product/review" id="submitReviewForm">
                                <?= \App\Csrf::field() ?>
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                
                                <div class="form-group-review">
                                    <label class="review-label">Your Rating *</label>
                                    <div class="rating-input">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" name="rating" value="<?= $i ?>" id="rating<?= $i ?>" required>
                                            <label for="rating<?= $i ?>" class="star-label-input">
                                                <i class="bi bi-star"></i>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <div class="form-group-review">
                                    <label class="review-label" for="review_text">Your Review</label>
                                    <textarea class="review-textarea" 
                                              id="review_text" 
                                              name="review_text" 
                                              rows="5" 
                                              placeholder="Share your experience with this product..."></textarea>
                                </div>
                                
                                <div class="review-form-actions">
                                    <button type="submit" class="btn-submit-review">
                                        <i class="bi bi-check-circle"></i> Submit Review
                                    </button>
                                    <button type="button" class="btn-cancel-review" onclick="hideReviewForm()">
                                        Cancel
                    </button>
                                </div>
                </form>
                        </div>
                    <?php elseif (isset($hasPurchased) && $hasPurchased): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> You have already reviewed this product.
                        </div>
                    <?php elseif (!isset($hasPurchased) || !$hasPurchased): ?>
                        <div class="review-restriction-notice">
                            <div class="restriction-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="restriction-content">
                                <h4>Verified Reviews Only</h4>
                                <p>Only customers who have purchased from this supplier can leave a review. This ensures authentic and trustworthy feedback.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Reviews List -->
                    <div class="reviews-list-modern">
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item-modern">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <div class="reviewer-avatar">
                                                <?= strtoupper(substr($review['buyer_name'] ?? 'U', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="reviewer-name"><?= \App\View::e($review['buyer_name'] ?? 'Anonymous') ?></div>
                                                <?php if ($review['is_verified_purchase']): ?>
                                                    <span class="verified-badge-review">
                                                        <i class="bi bi-check-circle-fill"></i> Verified Purchase
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="review-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?= $i <= $review['rating'] ? '-fill' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="review-date">
                                        <?= date('M d, Y', strtotime($review['created_at'])) ?>
                                        <?php if (!empty($review['sentiment_label'])): ?>
                                            <?php
                                            $sentimentColors = [
                                                'positive' => '#10B981',
                                                'neutral' => '#6B7280',
                                                'negative' => '#EF4444'
                                            ];
                                            $sentimentIcons = [
                                                'positive' => 'bi-emoji-smile',
                                                'neutral' => 'bi-emoji-neutral',
                                                'negative' => 'bi-emoji-frown'
                                            ];
                                            $color = $sentimentColors[$review['sentiment_label']] ?? '#6B7280';
                                            $icon = $sentimentIcons[$review['sentiment_label']] ?? 'bi-emoji-neutral';
                                            ?>
                                            <span class="sentiment-badge" style="background: <?= $color ?>20; color: <?= $color ?>; border: 1px solid <?= $color ?>40; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; margin-left: 8px;">
                                                <i class="bi <?= $icon ?>"></i> <?= ucfirst($review['sentiment_label']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($review['review_text'])): ?>
                                        <div class="review-text">
                                            <?= nl2br(\App\View::e($review['review_text'])) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="review-helpful">
                                        <button class="helpful-btn" onclick="markHelpful(<?= $review['id'] ?>)">
                                            <i class="bi bi-hand-thumbs-up"></i> Helpful (<?= $review['helpful_count'] ?? 0 ?>)
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="reviews-placeholder">
                                <i class="bi bi-chat-quote"></i>
                                <p>No reviews yet. Be the first to review this product!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Zoom Modal -->
<div class="image-zoom-modal" id="imageZoomModal" onclick="closeImageZoom()">
    <div class="zoom-modal-content">
        <button class="zoom-close-btn" onclick="closeImageZoom()">
            <i class="bi bi-x-lg"></i>
        </button>
        <img src="<?= !empty($product['image_url']) ? \App\View::e($product['image_url']) : '' ?>" 
             class="zoomed-image" 
             alt="<?= \App\View::e($product['name']) ?>">
    </div>
</div>

<!-- Include Product Detail Styles -->
<link rel="stylesheet" href="/build_mate/assets/css/product-detail.css">

<script>
// Quantity Controls
function increaseQty() {
    const input = document.getElementById('qty');
    const max = parseInt(input.getAttribute('max'));
    const current = parseInt(input.value);
    if (current < max) {
        input.value = current + 1;
    }
}

function decreaseQty() {
    const input = document.getElementById('qty');
    const current = parseInt(input.value);
    if (current > 1) {
        input.value = current - 1;
    }
}

// Image Zoom
function toggleImageZoom() {
    const img = document.getElementById('mainProductImage');
    if (!img || img.style.display === 'none') {
        return; // Don't zoom if image failed to load
    }
    const modal = document.getElementById('imageZoomModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

// Handle image load errors
document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.getElementById('mainProductImage');
    if (mainImage) {
        mainImage.addEventListener('error', function() {
            this.style.display = 'none';
            const placeholder = this.nextElementSibling;
            if (placeholder && placeholder.classList.contains('product-image-placeholder-large')) {
                placeholder.style.display = 'flex';
            }
            const zoomBtn = document.getElementById('zoomBtn');
            if (zoomBtn) {
                zoomBtn.style.display = 'none';
            }
        });
        
        mainImage.addEventListener('load', function() {
            const zoomBtn = document.getElementById('zoomBtn');
            if (zoomBtn) {
                zoomBtn.style.display = 'flex';
            }
        });
    }
});

function closeImageZoom() {
    const modal = document.getElementById('imageZoomModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// Add to Cart with Loading State
document.getElementById('addToCartForm')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('addToCartBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> <span>Adding...</span>';
    }
});

// Wishlist Functions
async function addToWishlist() {
    const productId = <?= $product['id'] ?>;
    const isInWishlist = <?= ($isInWishlist ?? false) ? 'true' : 'false' ?>;
    
    if (isInWishlist) {
        // Remove from wishlist
        await toggleWishlist(productId, 'remove');
    } else {
        // Add to wishlist
        await toggleWishlist(productId, 'add');
    }
}

async function toggleWishlist(productId, action) {
    const url = `/build_mate/product/wishlist/${action}`;
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update button state
            const wishlistBtn = document.querySelector('.action-btn-secondary[onclick="addToWishlist()"]');
            if (wishlistBtn) {
                const icon = wishlistBtn.querySelector('i');
                const span = wishlistBtn.querySelector('span');
                if (action === 'add') {
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill');
                    wishlistBtn.style.color = 'hsl(var(--danger))';
                    if (span) span.textContent = 'Saved';
                    updateWishlistCount(1);
                } else {
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.add('bi-heart');
                    wishlistBtn.style.color = '';
                    if (span) span.textContent = 'Save for Later';
                    updateWishlistCount(-1);
                }
            }
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Operation failed', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

function shareProduct() {
    if (navigator.share) {
        navigator.share({
            title: '<?= \App\View::e($product['name']) ?>',
            text: 'Check out this product on Build Mate',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Product link copied to clipboard!');
    }
}

// Waitlist Functions
async function toggleWaitlist(productId) {
    const isInWaitlist = <?= ($isInWaitlist ?? false) ? 'true' : 'false' ?>;
    const action = isInWaitlist ? 'remove' : 'add';
    const url = `/build_mate/product/waitlist/${action}`;
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update button text
            const btn = document.getElementById('waitlistBtn');
            const btnText = document.getElementById('waitlistBtnText');
            if (btn && btnText) {
                if (action === 'add') {
                    btnText.textContent = 'Remove from Waitlist';
                    btn.classList.add('in-waitlist');
                } else {
                    btnText.textContent = 'Join Waitlist';
                    btn.classList.remove('in-waitlist');
                }
            }
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Operation failed', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

// Review Form Functions
function showReviewForm() {
    const form = document.getElementById('reviewForm');
    if (form) {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function hideReviewForm() {
    const form = document.getElementById('reviewForm');
    if (form) {
        form.style.display = 'none';
        document.getElementById('submitReviewForm').reset();
        // Reset star display
        document.querySelectorAll('.star-label-input i').forEach(icon => {
            icon.classList.remove('bi-star-fill');
            icon.classList.add('bi-star');
        });
    }
}

// Rating Input Interaction
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.rating-input input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const rating = parseInt(this.value);
            const labels = document.querySelectorAll('.star-label-input');
            labels.forEach((label, index) => {
                const starIndex = 5 - index;
                const icon = label.querySelector('i');
                if (starIndex <= rating) {
                    icon.classList.remove('bi-star');
                    icon.classList.add('bi-star-fill');
                } else {
                    icon.classList.remove('bi-star-fill');
                    icon.classList.add('bi-star');
                }
            });
        });
    });
});

// Notification System
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `product-notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 10);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Mark Review as Helpful
function markHelpful(reviewId) {
    // Placeholder - implement if needed
    console.log('Mark helpful:', reviewId);
}
</script>
