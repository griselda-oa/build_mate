<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-heart-fill text-danger"></i> My Wishlist
        </h1>
        <a href="/build_mate/catalog" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Continue Shopping
        </a>
    </div>
    
    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
            <?php if ($flash['type'] === 'warning'): ?>
                <?= $flash['message'] ?>
            <?php else: ?>
                <?= \App\View::e($flash['message']) ?>
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (empty($wishlistItems)): ?>
        <div class="text-center py-5">
            <i class="bi bi-heart" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
            <h3 class="text-muted">Your wishlist is empty</h3>
            <p class="text-muted">Start adding products you love to your wishlist!</p>
            <a href="/build_mate/catalog" class="btn btn-primary mt-3">
                <i class="bi bi-search"></i> Browse Products
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($wishlistItems as $item): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="position-relative">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?= \App\View::e($item['image_url']) ?>" 
                                     class="card-img-top" 
                                     alt="<?= \App\View::e($item['name']) ?>"
                                     style="height: 200px; object-fit: cover;"
                                     onerror="this.onerror=null;this.src='/build_mate/assets/images/placeholder.png';">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="bi bi-image" style="font-size: 3rem; color: #ccc;"></i>
                                </div>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" 
                                    onclick="removeFromWishlist(<?= $item['product_id'] ?>, this)"
                                    style="border-radius: 50%; width: 35px; height: 35px; padding: 0;">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <a href="/build_mate/product/<?= \App\View::e($item['slug']) ?>" class="text-decoration-none">
                                    <?= \App\View::e($item['name']) ?>
                                </a>
                            </h5>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-shop"></i> <?= \App\View::e($item['supplier_name'] ?? 'Unknown Supplier') ?>
                            </p>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-tag"></i> <?= \App\View::e($item['category_name'] ?? 'Uncategorized') ?>
                            </p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="h5 mb-0 text-primary">
                                        <?= \App\Money::format($item['price_cents'], $item['currency']) ?>
                                    </span>
                                    <?php if ($item['stock'] > 0): ?>
                                        <span class="badge bg-success">In Stock</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Out of Stock</span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="/build_mate/product/<?= \App\View::e($item['slug']) ?>" class="btn btn-primary">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                    <?php if ($item['stock'] > 0): ?>
                                        <?php 
                                        $user = \App\Auth::check() ? \App\Auth::user() : null;
                                        $isSupplier = $user && $user['role'] === 'supplier';
                                        $isAdmin = $user && $user['role'] === 'admin';
                                        $cannotPurchase = $isSupplier || $isAdmin;
                                        ?>
                                        <?php if (!$cannotPurchase): ?>
                                            <button class="btn btn-outline-primary" onclick="addToCartFromWishlist(<?= $item['product_id'] ?>)">
                                                <i class="bi bi-cart-plus"></i> Add to Cart
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-outline-secondary" disabled title="<?= $isAdmin ? 'Admins cannot purchase products' : 'Suppliers cannot purchase products' ?>">
                                                <i class="bi bi-cart-x"></i> Not Available
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
async function removeFromWishlist(productId, btn) {
    const card = btn.closest('.col-md-6');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    
    try {
        const response = await fetch('/build_mate/product/wishlist/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ product_id: productId })
        });
        const data = await response.json();
        
        if (data.success) {
            card.style.transition = 'opacity 0.3s';
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
                updateWishlistCount(-1);
                // Check if wishlist is now empty
                if (document.querySelectorAll('.col-md-6').length === 0) {
                    location.reload();
                }
            }, 300);
            showNotification('Removed from wishlist', 'success');
        } else {
            showNotification(data.message || 'Failed to remove', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-heart-fill"></i>';
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Network error. Please try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-heart-fill"></i>';
    }
}

async function addToCartFromWishlist(productId) {
    try {
        const response = await fetch(`/build_mate/cart/add/${productId}/`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({})
        });
        
        if (response.ok) {
            showNotification('Added to cart!', 'success');
            // Update cart count
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                const current = parseInt(cartCount.textContent) || 0;
                cartCount.textContent = current + 1;
            }
        } else {
            showNotification('Failed to add to cart', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Network error. Please try again.', 'error');
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

<style>
.wishlist-btn-catalog {
    transition: all 0.3s ease;
}

.wishlist-btn-catalog.in-wishlist {
    color: hsl(var(--danger)) !important;
}

.wishlist-btn-catalog:hover {
    transform: scale(1.1);
}
</style>

