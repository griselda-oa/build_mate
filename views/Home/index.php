<?php
// Use the modern homepage if it exists, otherwise fall back to basic
$modernHomepage = __DIR__ . '/index-modern.php';
if (file_exists($modernHomepage)) {
    include $modernHomepage;
    return;
}
?>

<link rel="stylesheet" href="<?= \App\View::asset('assets/css/home-modern.css') ?>">

<!-- Hero Slideshow -->
<section class="hero-slideshow">
    <div class="hero-slide active" style="background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);">
        <div class="hero-slide-bg"></div>
        <div class="hero-slide-content">
            <h1>Faster, Fairer, and More Affordable Building</h1>
            <p>Ghana's trusted marketplace connecting buyers and verified suppliers in one transparent ecosystem.</p>
            <div class="hero-actions">
                <a href="<?= \App\View::url('/') ?>catalog" class="btn btn-light btn-lg">
                    <i class="bi bi-cart"></i> Shop Now
                </a>
                <a href="<?= \App\View::url('/') ?>register" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-person-plus"></i> Sign Up
                </a>
            </div>
        </div>
    </div>
    
    <div class="hero-slide" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
        <div class="hero-slide-bg"></div>
        <div class="hero-slide-content">
            <h1>Verified Suppliers, Trusted Quality</h1>
            <p>All suppliers are thoroughly vetted. Shop with confidence knowing you're dealing with trusted partners.</p>
            <div class="hero-actions">
                <a href="<?= \App\View::url('/') ?>catalog" class="btn btn-light btn-lg">
                    <i class="bi bi-shield-check"></i> Browse Verified Products
                </a>
            </div>
        </div>
    </div>
    
    <div class="hero-slide" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
        <div class="hero-slide-bg"></div>
        <div class="hero-slide-content">
            <h1>Secure Payments, Tracked Delivery</h1>
            <p>Your payment is held securely by Paystack until delivery. Real-time tracking from purchase to your door.</p>
            <div class="hero-actions">
                <a href="<?= \App\View::url('/') ?>catalog" class="btn btn-light btn-lg">
                    <i class="bi bi-truck"></i> Start Shopping
                </a>
            </div>
        </div>
    </div>
    
    <button class="slide-nav prev" onclick="changeSlide(-1)">
        <i class="bi bi-chevron-left"></i>
    </button>
    <button class="slide-nav next" onclick="changeSlide(1)">
        <i class="bi bi-chevron-right"></i>
    </button>
    
    <div class="hero-slideshow-controls">
        <span class="slide-dot active" onclick="goToSlide(0)"></span>
        <span class="slide-dot" onclick="goToSlide(1)"></span>
        <span class="slide-dot" onclick="goToSlide(2)"></span>
    </div>
</section>

<!-- Shop by Category -->
<section class="categories-section fade-in-on-scroll">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="row g-4">
            <?php
            $categoryIcons = [
                'Cement' => 'bi-box',
                'Roofing' => 'bi-house',
                'Tiles' => 'bi-grid',
                'Paint' => 'bi-palette',
                'Plumbing' => 'bi-droplet',
                'Electrical' => 'bi-lightning',
                'Steel' => 'bi-layers',
                'Blocks' => 'bi-grid-3x3'
            ];
            
            if (!empty($categories)):
                foreach (array_slice($categories, 0, 6) as $category):
                    $icon = $categoryIcons[$category['name']] ?? 'bi-box';
            ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= \App\View::url('/') ?>catalog?cat=<?= $category['id'] ?>" class="category-card-link">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="bi <?= $icon ?>"></i>
                        </div>
                        <h5 class="category-name"><?= \App\View::e($category['name']) ?></h5>
                    </div>
                </a>
            </div>
            <?php 
                endforeach;
            else:
                $fallbackCategories = [
                    ['name' => 'Cement', 'icon' => 'bi-box'],
                    ['name' => 'Roofing', 'icon' => 'bi-house'],
                    ['name' => 'Tiles', 'icon' => 'bi-grid'],
                    ['name' => 'Paint', 'icon' => 'bi-palette'],
                    ['name' => 'Plumbing', 'icon' => 'bi-droplet'],
                    ['name' => 'Electrical', 'icon' => 'bi-lightning']
                ];
                foreach ($fallbackCategories as $category):
            ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= \App\View::url('/') ?>catalog" class="category-card-link">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="bi <?= $category['icon'] ?>"></i>
                        </div>
                        <h5 class="category-name"><?= \App\View::e($category['name']) ?></h5>
                    </div>
                </a>
            </div>
            <?php 
                endforeach;
            endif;
            ?>
        </div>
    </div>
</section>

<!-- Why Choose Build Mate -->
<section class="features-section fade-in-on-scroll">
    <div class="container">
        <h2 class="section-title">Why Choose Build Mate?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon verified" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4 class="feature-title">Verified Suppliers</h4>
                    <p class="feature-description">All suppliers are thoroughly vetted and verified by our team. Shop with confidence knowing you're dealing with trusted partners.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon escrow" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                        <i class="bi bi-lock"></i>
                    </div>
                    <h4 class="feature-title">Paystack Secure Payment</h4>
                    <p class="feature-description">Your payment is held securely by Paystack until delivery is confirmed. Build Mate ensures safe and fair transactions for everyone.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon delivery" style="background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h4 class="feature-title">Tracked Delivery</h4>
                    <p class="feature-description">Real-time tracking from purchase to delivery. Know exactly where your materials are at every step of the journey.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<?php if (!empty($featured)): ?>
<section class="products-section fade-in-on-scroll">
    <div class="container">
        <h2 class="section-title">Featured Products</h2>
        <div class="row g-4">
            <?php foreach (array_slice($featured, 0, 6) as $product): ?>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="product-card">
                    <?php if (!empty($product['image_url'])): ?>
                        <img src="<?= \App\View::e($product['image_url']) ?>" class="product-image" alt="<?= \App\View::e($product['name']) ?>">
                    <?php else: ?>
                        <div class="product-image-placeholder">
                            <i class="bi bi-image"></i>
                        </div>
                    <?php endif; ?>
                    <div class="product-body">
                        <h6 class="product-title">
                            <a href="<?= \App\View::url('/') ?>product/<?= \App\View::e($product['slug']) ?>">
                                <?= \App\View::e($product['name']) ?>
                            </a>
                        </h6>
                        <p class="product-supplier"><?= \App\View::e($product['supplier_name'] ?? '') ?></p>
                        <p class="product-price" data-price-cents="<?= $product['price_cents'] ?>" data-currency="<?= $product['currency'] ?>">
                            <?= \App\Money::format($product['price_cents'], $product['currency']) ?>
                        </p>
                        <?php if ($product['verified'] ?? false): ?>
                            <span class="badge verified-badge">Verified</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-footer">
                        <a href="<?= \App\View::url('/') ?>product/<?= \App\View::e($product['slug']) ?>" class="btn btn-sm btn-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?= \App\View::url('/') ?>catalog" class="btn btn-outline-primary btn-lg">View All Products</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Testimonials -->
<section class="testimonials-section fade-in-on-scroll">
    <div class="container">
        <h2 class="section-title">What Our Customers Say</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial-card">
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
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
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
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
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

<!-- Call to Action -->
<section class="cta-section fade-in-on-scroll">
    <div class="container text-center">
        <h2 class="cta-title">Ready to Build Better?</h2>
        <p class="cta-subtitle">Join thousands of buyers and suppliers who trust Build Mate</p>
        <a href="<?= \App\View::url('/') ?>register" class="btn btn-light btn-lg px-5 py-3" style="position: relative; z-index: 1;">Get Started Today</a>
    </div>
</section>

<script>
// Hero Slideshow
let currentSlide = 0;
const slides = document.querySelectorAll('.hero-slide');
const dots = document.querySelectorAll('.slide-dot');
const totalSlides = slides.length;

function showSlide(index) {
    // Remove active class from all slides and dots
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    // Add active class to current slide and dot
    if (slides[index]) {
        slides[index].classList.add('active');
    }
    if (dots[index]) {
        dots[index].classList.add('active');
    }
}

function changeSlide(direction) {
    currentSlide += direction;
    if (currentSlide >= totalSlides) {
        currentSlide = 0;
    } else if (currentSlide < 0) {
        currentSlide = totalSlides - 1;
    }
    showSlide(currentSlide);
}

function goToSlide(index) {
    currentSlide = index;
    showSlide(currentSlide);
}

// Auto-advance slideshow
setInterval(() => {
    changeSlide(1);
}, 5000); // Change slide every 5 seconds

// Scroll animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, observerOptions);

// Observe all fade-in elements
document.querySelectorAll('.fade-in-on-scroll').forEach(el => {
    observer.observe(el);
});
</script>
