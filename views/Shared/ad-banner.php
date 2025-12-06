<?php
// Shared Advertisement Banner Component
// Used across all dashboards (Buyer, Supplier, Admin, Logistics)

$advertisements = $advertisements ?? [];
?>

<?php if (!empty($advertisements)): ?>
    <div class="ad-banner-section-modern mb-4">
        <div class="ad-banner-carousel" id="adBannerCarousel">
            <?php foreach ($advertisements as $index => $ad): ?>
                <?php 
                $adImage = $ad['image_url'] ?? $ad['product_image'] ?? '';
                // Make path absolute if relative
                if (!empty($adImage) && !preg_match('/^https?:\/\//', $adImage)) {
                    if (strpos($adImage, \App\View::basePath() . '/') !== 0) {
                        $adImage = '/build_mate' . (strpos($adImage, '/') === 0 ? '' : '/') . $adImage;
                    }
                }
                $isVideo = !empty($adImage) && preg_match('/\.(mp4|mov|webm)$/i', $adImage);
                $productSlug = $ad['product_slug'] ?? '';
                ?>
                <div class="ad-banner-slide <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>">
                    <a href="<?= \App\View::relUrl('/product/<?= \App\View::e($productSlug) ?>') ?>" class="ad-banner-link-modern">
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
                                        <source src="<?= \App\View::relImage($adImage) ?>" type="video/<?= pathinfo($adImage, PATHINFO_EXTENSION) === 'mov' ? 'quicktime' : pathinfo($adImage, PATHINFO_EXTENSION) ?>">
                                    </video>
                                <?php else: ?>
                                    <img src="<?= \App\View::relImage($adImage) ?>" 
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
    
    <script>
    // Advertisement Banner Carousel
    document.addEventListener('DOMContentLoaded', function() {
        const carousel = document.getElementById('adBannerCarousel');
        if (!carousel) return;
        
        const slides = carousel.querySelectorAll('.ad-banner-slide');
        const dots = document.querySelectorAll('.ad-banner-dot-modern');
        let currentSlide = 0;
        let autoSlideInterval;
        
        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
            currentSlide = index;
        }
        
        function nextSlide() {
            const next = (currentSlide + 1) % slides.length;
            showSlide(next);
        }
        
        // Auto-advance slides every 5 seconds
        function startAutoSlide() {
            autoSlideInterval = setInterval(nextSlide, 5000);
        }
        
        function stopAutoSlide() {
            if (autoSlideInterval) {
                clearInterval(autoSlideInterval);
            }
        }
        
        // Dot navigation
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showSlide(index);
                stopAutoSlide();
                startAutoSlide(); // Restart auto-slide
            });
        });
        
        // Pause on hover
        carousel.addEventListener('mouseenter', stopAutoSlide);
        carousel.addEventListener('mouseleave', startAutoSlide);
        
        // Start auto-slide
        if (slides.length > 1) {
            startAutoSlide();
        }
    });
    </script>
<?php endif; ?>

