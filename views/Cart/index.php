<link rel="stylesheet" href="<?= \App\View::relAsset('assets/css/cart-modern.css') ?>">

<div class="cart-page-modern">
    <div class="container">
        <a href="<?= \App\View::relUrl('/catalog') ?>" class="back-button-modern">
            <i class="bi bi-arrow-left"></i>
            <span>Continue Shopping</span>
        </a>

        <div class="cart-header-modern">
            <h1 class="cart-title-modern">
                <i class="bi bi-cart3"></i> Shopping Cart
            </h1>
            <p class="cart-subtitle-modern">
                <?= count($products ?? []) ?> item(s) in your cart
            </p>
        </div>

        <?php if (empty($products)): ?>
            <div class="cart-empty-modern">
                <div class="cart-empty-icon">
                    <i class="bi bi-cart-x"></i>
                </div>
                <h3>Your cart is empty</h3>
                <p class="text-muted mb-4">Looks like you haven't added anything to your cart yet.</p>
                <a href="<?= \App\View::relUrl('/catalog') ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-shop"></i> Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="cart-items-card">
                        <?php foreach ($products as $product): ?>
                            <div class="cart-item-modern">
                                <?php if (!empty($product['image_url'])): ?>
                                    <img src="<?= \App\View::image($product['image_url']) ?>" 
                                         alt="<?= \App\View::e($product['name']) ?>" 
                                         class="cart-item-image"
                                         onerror="this.src='<?= \App\View::asset('assets/images/placeholder.png') ?>'">
                                <?php else: ?>
                                    <div class="cart-item-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-image" style="font-size: 2rem; color: #ccc;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="cart-item-details">
                                    <a href="<?= \App\View::relUrl('/product/' . \App\View::e($product['slug'])) ?>" class="cart-item-name">
                                        <?= \App\View::e($product['name']) ?>
                                    </a>
                                    <div class="cart-item-price" data-price-cents="<?= $product['price_cents'] ?>" data-currency="<?= $product['currency'] ?>">
                                        <?= \App\Money::format($product['price_cents'], $product['currency']) ?>
                                    </div>
                                    <div class="cart-item-stock">
                                        <i class="bi bi-box-seam"></i> <?= $product['stock'] ?> in stock
                                    </div>
                                </div>
                                
                                <div class="cart-item-actions">
                                    <form method="POST" action="<?= \App\View::relUrl('/cart/update/') ?>" class="quantity-control-modern">
                                        <?= \App\Csrf::field() ?>
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <label style="margin: 0; font-weight: 600; color: #6c757d;">Qty:</label>
                                        <input type="number" name="qty" value="<?= $product['qty'] ?>" min="1" max="<?= $product['stock'] ?>" onchange="this.form.submit()">
                                    </form>
                                    
                                    <form method="POST" action="<?= \App\View::relUrl('/cart/remove/' . $product['id'] . '/') ?>">
                                        <?= \App\Csrf::field() ?>
                                        <button type="submit" class="btn-remove-modern">
                                            <i class="bi bi-trash"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="cart-summary-card">
                        <h3 class="summary-title">Order Summary</h3>
                        
                        <div class="summary-row">
                            <span class="summary-label">Subtotal (<?= count($products) ?> items)</span>
                            <span class="summary-value" data-price-cents="<?= $total ?>" data-currency="GHS">
                                <?= \App\Money::format($total, 'GHS') ?>
                            </span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">Shipping</span>
                            <span class="summary-value">Calculated at checkout</span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">Total</span>
                            <span class="summary-value" data-price-cents="<?= $total ?>" data-currency="GHS">
                                <?= \App\Money::format($total, 'GHS') ?>
                            </span>
                        </div>
                        
                        <a href="<?= \App\View::relUrl('/checkout') ?>" class="btn-checkout-modern">
                            <i class="bi bi-lock-fill"></i> Proceed to Checkout
                        </a>
                        
                        <a href="<?= \App\View::relUrl('/catalog') ?>" class="continue-shopping-link">
                            <i class="bi bi-arrow-left"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
