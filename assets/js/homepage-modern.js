/**
 * Modern Homepage JavaScript
 * Build Mate Ghana - Interactive Features
 */

// Hero Slideshow
let currentSlide = 0;
const slides = document.querySelectorAll('.hero-slide-modern');
const dots = document.querySelectorAll('.slide-dot-modern');
const totalSlides = slides.length;
let slideInterval;

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
    resetSlideInterval();
}

function goToSlide(index) {
    currentSlide = index;
    showSlide(currentSlide);
    resetSlideInterval();
}

function resetSlideInterval() {
    clearInterval(slideInterval);
    slideInterval = setInterval(() => {
        changeSlide(1);
    }, 5000);
}

// Pause slideshow on hover
const heroSlideshow = document.querySelector('.hero-slideshow-modern');
if (heroSlideshow) {
    heroSlideshow.addEventListener('mouseenter', () => {
        clearInterval(slideInterval);
    });
    
    heroSlideshow.addEventListener('mouseleave', () => {
        resetSlideInterval();
    });
}

// Start auto-advance
if (totalSlides > 0) {
    resetSlideInterval();
}

// Products Slider
function scrollProducts(direction) {
    const slider = document.getElementById('productsSlider');
    if (!slider) return;
    
    const scrollAmount = 300;
    slider.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}

// Touch/swipe support for mobile
let touchStartX = 0;
let touchEndX = 0;

const productsSlider = document.getElementById('productsSlider');
if (productsSlider) {
    productsSlider.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    productsSlider.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
}

function handleSwipe() {
    if (touchEndX < touchStartX - 50) {
        scrollProducts(1); // Swipe left - scroll right
    }
    if (touchEndX > touchStartX + 50) {
        scrollProducts(-1); // Swipe right - scroll left
    }
}

// Testimonials Auto-Play
let currentTestimonial = 0;
const testimonialsTrack = document.getElementById('testimonialsTrack');
const testimonials = document.querySelectorAll('.testimonial-card-modern');
const totalTestimonials = testimonials.length;

function showTestimonial(index) {
    if (!testimonialsTrack || totalTestimonials === 0) return;
    
    const offset = -index * 100;
    testimonialsTrack.style.transform = `translateX(${offset}%)`;
}

function nextTestimonial() {
    currentTestimonial = (currentTestimonial + 1) % totalTestimonials;
    showTestimonial(currentTestimonial);
}

if (totalTestimonials > 0) {
    setInterval(nextTestimonial, 6000); // Change every 6 seconds
}

// Statistics Counter Animation
function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-target'));
    const duration = 2000; // 2 seconds
    const increment = target / (duration / 16); // 60fps
    let current = 0;
    
    const updateCounter = () => {
        current += increment;
        if (current < target) {
            element.textContent = Math.floor(current) + '+';
            requestAnimationFrame(updateCounter);
        } else {
            element.textContent = target + '+';
        }
    };
    
    updateCounter();
}

// Scroll Animations using Intersection Observer
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            
            // Animate counters when stats section is visible
            if (entry.target.classList.contains('stats-section')) {
                const statNumbers = entry.target.querySelectorAll('.stat-number');
                statNumbers.forEach(stat => {
                    if (!stat.classList.contains('animated')) {
                        stat.classList.add('animated');
                        animateCounter(stat);
                    }
                });
            }
            
            // Animate step cards
            if (entry.target.classList.contains('how-it-works-section')) {
                const stepCards = entry.target.querySelectorAll('.step-card');
                stepCards.forEach((card, index) => {
                    setTimeout(() => {
                        card.classList.add('visible');
                    }, index * 200);
                });
            }
        }
    });
}, observerOptions);

// Observe all fade-in elements
document.querySelectorAll('.fade-in-on-scroll').forEach(el => {
    observer.observe(el);
});

// Back to Top Button
const backToTopBtn = document.getElementById('backToTop');

window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
        backToTopBtn.classList.add('visible');
    } else {
        backToTopBtn.classList.remove('visible');
    }
});

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Add to Cart Function
function addToCart(productId) {
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    // Make AJAX request to add to cart
    fetch(`/build_mate/cart/add/${productId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            // Show success message
            const btn = event.target.closest('.add-to-cart-btn-modern');
            if (btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check"></i> Added!';
                btn.style.background = '#10B981';
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                }, 2000);
            }
            
            // Update cart count if element exists
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                const currentCount = parseInt(cartCount.textContent) || 0;
                cartCount.textContent = currentCount + 1;
            }
        } else if (data && data.message) {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Fallback to page navigation
        window.location.href = `/build_mate/cart/add/${productId}`;
    });
}

// Quick View Function
function quickView(productId) {
    window.location.href = `/build_mate/product/${productId}`;
}

// Smooth Scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href.length > 1) {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Lazy load images
    const images = document.querySelectorAll('img[loading="lazy"]');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    }
    
    console.log('Build Mate Ghana - Modern Homepage Loaded');
});

