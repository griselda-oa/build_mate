<link rel="stylesheet" href="/build_mate/assets/css/homepage-modern.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

<!-- Hero Slideshow -->
<section class="hero-slideshow-modern">
    <div class="hero-slide-modern active" style="background-image: url('https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1920&q=80');">
        <div class="hero-slide-overlay"></div>
        <div class="hero-slide-content">
            <h1>Build Your Dreams with Quality Materials</h1>
            <p>Connect with Ghana's verified construction suppliers</p>
            <a href="/build_mate/catalog" class="hero-cta-button pulse">Shop Now</a>
        </div>
    </div>
    
    <div class="hero-slide-modern" style="background-image: url('https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=1920&q=80');">
        <div class="hero-slide-overlay"></div>
        <div class="hero-slide-content">
            <h1>Fast Delivery Across Ghana</h1>
            <p>From Accra to Tamale - We deliver nationwide</p>
            <a href="/build_mate/catalog" class="hero-cta-button">Explore Products</a>
        </div>
    </div>
    
    <div class="hero-slide-modern" style="background-image: url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1920&q=80');">
        <div class="hero-slide-overlay"></div>
        <div class="hero-slide-content">
            <h1>Secure Paystack Payments</h1>
            <p>Pay safely with mobile money, cards, and bank transfers</p>
            <a href="/build_mate/catalog" class="hero-cta-button">Start Shopping</a>
        </div>
    </div>
    
    <button class="slide-nav-modern prev" onclick="changeSlide(-1)">
        <i class="bi bi-chevron-left"></i>
    </button>
    <button class="slide-nav-modern next" onclick="changeSlide(1)">
        <i class="bi bi-chevron-right"></i>
    </button>
    
    <div class="hero-slideshow-controls">
        <span class="slide-dot-modern active" onclick="goToSlide(0)"></span>
        <span class="slide-dot-modern" onclick="goToSlide(1)"></span>
        <span class="slide-dot-modern" onclick="goToSlide(2)"></span>
    </div>
</section>

<!-- Features Bar -->
<section class="features-bar">
    <div class="features-container">
        <div class="feature-card-modern">
            <div class="feature-icon-modern">🚚</div>
            <h3>Fast Delivery</h3>
            <p>Nationwide shipping</p>
        </div>
        <div class="feature-card-modern">
            <div class="feature-icon-modern">✅</div>
            <h3>Verified Suppliers</h3>
            <p>Trusted partners</p>
        </div>
        <div class="feature-card-modern">
            <div class="feature-icon-modern">💳</div>
            <h3>Secure Payment</h3>
            <p>Paystack protected</p>
        </div>
        <div class="feature-card-modern">
            <div class="feature-icon-modern">📞</div>
            <h3>24/7 Support</h3>
            <p>Always here to help</p>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section-modern fade-in-on-scroll">
    <div class="section-container">
        <h2 class="section-title-modern">Shop by Category</h2>
        <div class="categories-grid">
            <?php
            $categoryData = [
                ['name' => 'Cement & Concrete', 'icon' => 'bi-box', 'count' => '150+', 'id' => 1],
                ['name' => 'Steel & Iron Rods', 'icon' => 'bi-layers', 'count' => '120+', 'id' => 2],
                ['name' => 'Roofing Materials', 'icon' => 'bi-house', 'count' => '90+', 'id' => 3],
                ['name' => 'Tiles & Flooring', 'icon' => 'bi-grid', 'count' => '200+', 'id' => 4],
                ['name' => 'Paints & Coatings', 'icon' => 'bi-palette', 'count' => '80+', 'id' => 5],
                ['name' => 'Plumbing Supplies', 'icon' => 'bi-droplet', 'count' => '110+', 'id' => 6],
                ['name' => 'Electrical Materials', 'icon' => 'bi-lightning', 'count' => '95+', 'id' => 7],
                ['name' => 'Tools & Equipment', 'icon' => 'bi-tools', 'count' => '140+', 'id' => 8]
            ];
            
            $categoryImages = [
                1 => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=400&q=80',
                2 => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=400&q=80',
                3 => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=400&q=80',
                4 => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=400&q=80',
                5 => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=400&q=80',
                6 => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=400&q=80',
                7 => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=400&q=80',
                8 => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=400&q=80'
            ];
            
            foreach ($categoryData as $cat):
                $catId = $cat['id'] ?? null;
                $catSlug = strtolower(str_replace([' ', '&'], ['-', ''], $cat['name']));
            ?>
            <div class="category-card-modern">
                <img src="<?= $categoryImages[$cat['id']] ?? 'https://via.placeholder.com/400x300' ?>" 
                     alt="<?= \App\View::e($cat['name']) ?>" 
                     class="category-card-image"
                     loading="lazy">
                <div class="category-card-content">
                    <h3><?= \App\View::e($cat['name']) ?></h3>
                    <span class="product-count"><?= $cat['count'] ?> products</span>
                    <a href="/build_mate/catalog?cat=<?= $catId ?>" class="category-browse-btn">Browse →</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Slider -->
<?php if (!empty($featured)): ?>
<section class="products-section-modern fade-in-on-scroll">
    <div class="section-container">
        <h2 class="section-title-modern">Featured Products</h2>
        <div class="products-slider-container">
            <button class="slider-nav-btn prev" onclick="scrollProducts(-1)">
                <i class="bi bi-chevron-left"></i>
            </button>
            <div class="products-slider" id="productsSlider">
                <?php foreach (array_slice($featured, 0, 8) as $product): ?>
                <div class="product-card-modern">
                    <div class="product-image-wrapper">
                        <?php if (!empty($product['image_url'])): ?>
                            <img src="<?= \App\View::e($product['image_url']) ?>" 
                                 alt="<?= \App\View::e($product['name']) ?>" 
                                 class="product-image-modern"
                                 loading="lazy">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-image" style="font-size: 3rem; color: #999;"></i>
                            </div>
                        <?php endif; ?>
                        <button class="quick-view-btn" onclick="quickView(<?= $product['id'] ?>)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="product-card-body">
                        <h4 class="product-name-modern"><?= \App\View::e($product['name']) ?></h4>
                        <div class="product-price-modern" 
                             data-price-cents="<?= $product['price_cents'] ?>" 
                             data-currency="<?= $product['currency'] ?? 'GHS' ?>">
                            <?= \App\Money::format($product['price_cents'] ?? 0, $product['currency'] ?? 'GHS') ?>
                        </div>
                        <?php if ($product['verified'] ?? false): ?>
                            <span class="badge bg-success mb-2">Verified</span>
                        <?php endif; ?>
                        <?php 
                        $user = \App\Auth::check() ? \App\Auth::user() : null;
                        $isSupplier = $user && $user['role'] === 'supplier';
                        $isAdmin = $user && $user['role'] === 'admin';
                        $cannotPurchase = $isSupplier || $isAdmin;
                        ?>
                        <?php if (!$cannotPurchase): ?>
                            <button class="add-to-cart-btn-modern" onclick="addToCart(<?= $product['id'] ?>)">
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                        <?php else: ?>
                            <button class="add-to-cart-btn-modern" disabled style="opacity: 0.5; cursor: not-allowed;" title="<?= $isAdmin ? 'Admins cannot purchase products' : 'Suppliers cannot purchase products' ?>">
                                <i class="bi bi-cart-x"></i> Not Available
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="slider-nav-btn next" onclick="scrollProducts(1)">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- How It Works -->
<section class="how-it-works-section fade-in-on-scroll">
    <div class="section-container">
        <h2 class="section-title-modern">How Build Mate Works</h2>
        <div class="steps-timeline">
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-icon">🛒</div>
                <h3>Browse & Select</h3>
                <p>Search through thousands of quality construction materials from verified suppliers across Ghana.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-icon">💳</div>
                <h3>Secure Checkout</h3>
                <p>Pay safely with Paystack - Mobile Money, Cards, Bank Transfer. Your payment is protected until delivery.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-icon">🚚</div>
                <h3>Fast Delivery</h3>
                <p>Receive your materials at your construction site. Track your order in real-time from warehouse to doorstep.</p>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Counter -->
<section class="stats-section fade-in-on-scroll">
    <div class="section-container">
        <div class="stats-container">
            <div class="stat-item">
                <div class="stat-number" data-target="500">0</div>
                <div class="stat-label">Products</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-target="1000">0</div>
                <div class="stat-label">Happy Customers</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-target="50">0</div>
                <div class="stat-label">Suppliers</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-target="10000">0</div>
                <div class="stat-label">Deliveries Made</div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials-section-modern fade-in-on-scroll">
    <div class="section-container">
        <h2 class="section-title-modern">What Our Customers Say</h2>
        <div class="testimonials-slider">
            <div class="testimonials-track" id="testimonialsTrack">
                <div class="testimonial-card-modern">
                    <div class="testimonial-stars">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">"Build Mate transformed how we source materials. The Paystack secure payment system gives us peace of mind, and delivery tracking is excellent."</p>
                    <div class="testimonial-author">
                        <strong>Kwame Mensah</strong>
                        <small>Contractor, Accra</small>
                    </div>
                </div>
                <div class="testimonial-card-modern">
                    <div class="testimonial-stars">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">"As a supplier, Build Mate helped us reach more customers while maintaining our reputation. The verification badge increased our sales significantly."</p>
                    <div class="testimonial-author">
                        <strong>Abena Osei</strong>
                        <small>Owner, Elite Tiles Ghana</small>
                    </div>
                </div>
                <div class="testimonial-card-modern">
                    <div class="testimonial-stars">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">"Transparent pricing and reliable delivery. We saved 20% on our last project thanks to Build Mate's competitive marketplace."</p>
                    <div class="testimonial-author">
                        <strong>Kofi Asante</strong>
                        <small>Developer, Kumasi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Suppliers Section -->
<section class="suppliers-section fade-in-on-scroll">
    <div class="section-container">
        <h2 class="section-title-modern">Our Trusted Suppliers</h2>
        <div class="suppliers-carousel">
            <div class="suppliers-track">
                <!-- Duplicate logos for seamless loop -->
                <img src="https://via.placeholder.com/150x100/8B4513/FFFFFF?text=Supplier+1" alt="Supplier" class="supplier-logo" loading="lazy">
                <img src="https://via.placeholder.com/150x100/D2691E/FFFFFF?text=Supplier+2" alt="Supplier" class="supplier-logo" loading="lazy">
                <img src="https://via.placeholder.com/150x100/F59E0B/FFFFFF?text=Supplier+3" alt="Supplier" class="supplier-logo" loading="lazy">
                <img src="https://via.placeholder.com/150x100/8B4513/FFFFFF?text=Supplier+4" alt="Supplier" class="supplier-logo" loading="lazy">
                <img src="https://via.placeholder.com/150x100/D2691E/FFFFFF?text=Supplier+5" alt="Supplier" class="supplier-logo" loading="lazy">
                <img src="https://via.placeholder.com/150x100/F59E0B/FFFFFF?text=Supplier+6" alt="Supplier" class="supplier-logo" loading="lazy">
                <!-- Duplicate for seamless loop -->
                <img src="https://via.placeholder.com/150x100/8B4513/FFFFFF?text=Supplier+1" alt="Supplier" class="supplier-logo" loading="lazy">
                <img src="https://via.placeholder.com/150x100/D2691E/FFFFFF?text=Supplier+2" alt="Supplier" class="supplier-logo" loading="lazy">
                <img src="https://via.placeholder.com/150x100/F59E0B/FFFFFF?text=Supplier+3" alt="Supplier" class="supplier-logo" loading="lazy">
                <img src="https://via.placeholder.com/150x100/8B4513/FFFFFF?text=Supplier+4" alt="Supplier" class="supplier-logo" loading="lazy">
                <img src="https://via.placeholder.com/150x100/D2691E/FFFFFF?text=Supplier+5" alt="Supplier" class="supplier-logo" loading="lazy">
                <img src="https://via.placeholder.com/150x100/F59E0B/FFFFFF?text=Supplier+6" alt="Supplier" class="supplier-logo" loading="lazy">
            </div>
        </div>
    </div>
</section>

<!-- CTA Banner -->
<section class="cta-banner fade-in-on-scroll">
    <div class="cta-content">
        <h2 class="cta-title-modern">Ready to Start Your Next Project?</h2>
        <p class="cta-subtitle-modern">Browse thousands of quality construction materials from verified suppliers</p>
        <div class="cta-buttons">
            <a href="/build_mate/catalog" class="cta-btn-primary">Shop Now</a>
            <a href="/build_mate/register?role=supplier" class="cta-btn-secondary">Become a Supplier</a>
        </div>
    </div>
</section>

<!-- Back to Top Button -->
<button class="back-to-top" onclick="scrollToTop()" id="backToTop">
    <i class="bi bi-arrow-up"></i>
</button>

<script src="/build_mate/assets/js/homepage-modern.js"></script>

