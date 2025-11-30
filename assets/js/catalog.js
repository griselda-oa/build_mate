/**
 * Build Mate - Modern Catalog JavaScript
 * Enhanced interactivity for product catalog
 */

class ModernCatalog {
    constructor() {
        this.products = [];
        this.filters = {
            query: '',
            category: '',
            minPrice: 0,
            maxPrice: 100000,
            verified: false
        };
        this.sortBy = 'default';
        this.viewMode = 'grid'; // 'grid' or 'list'
        this.debounceTimer = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupPriceSlider();
        this.setupViewToggle();
        this.setupInfiniteScroll();
        this.setupProductAnimations();
        this.setupQuickView();
        this.setupFilterSidebar();
    }

    setupEventListeners() {
        // Search with debounce
        const searchInput = document.getElementById('q');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.handleSearch(e.target.value);
                }, 300);
            });
        }

        // Category filter - don't auto-submit
        // const categorySelect = document.getElementById('cat');
        // if (categorySelect) {
        //     categorySelect.addEventListener('change', () => {
        //         this.handleFilterChange();
        //     });
        // }

        // Verified checkbox - don't auto-submit
        // const verifiedCheckbox = document.getElementById('verified');
        // if (verifiedCheckbox) {
        //     verifiedCheckbox.addEventListener('change', () => {
        //         this.handleFilterChange();
        //     });
        // }

        // Sort dropdown
        const sortSelect = document.querySelector('[data-sort], .sort-select');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.sortBy = e.target.value;
                console.log('Sort changed to:', this.sortBy);
                if (this.sortBy === 'default') {
                    // Reload page for default order
                    window.location.href = window.location.pathname;
                } else {
                    this.handleSort();
                }
            });
        }

        // Filter form submission - allow normal form submission
        // const filterForm = document.getElementById('filterForm');
        // if (filterForm) {
        //     filterForm.addEventListener('submit', (e) => {
        //         e.preventDefault();
        //         this.handleFilterChange();
        //     });
        // }
    }

    setupPriceSlider() {
        const minSlider = document.getElementById('minPrice');
        const maxSlider = document.getElementById('maxPrice');
        const minDisplay = document.getElementById('minPriceDisplay');
        const maxDisplay = document.getElementById('maxPriceDisplay');
        const track = document.querySelector('.range-track');

        if (!minSlider || !maxSlider) return;

        const min = parseFloat(minSlider.min);
        const max = parseFloat(maxSlider.max);

        const updateRange = () => {
            let minVal = parseFloat(minSlider.value);
            let maxVal = parseFloat(maxSlider.value);

            // Ensure min doesn't exceed max
            if (minVal > maxVal) {
                minSlider.value = maxVal;
                minVal = maxVal;
            }

            // Ensure max doesn't go below min
            if (maxVal < minVal) {
                maxSlider.value = minVal;
                maxVal = minVal;
            }

            // Update displays with animation
            if (minDisplay) {
                minDisplay.textContent = minVal.toFixed(2);
                minDisplay.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    minDisplay.style.transform = 'scale(1)';
                }, 200);
            }

            if (maxDisplay) {
                maxDisplay.textContent = maxVal.toFixed(2);
                maxDisplay.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    maxDisplay.style.transform = 'scale(1)';
                }, 200);
            }

            // Update track fill with smooth animation
            if (track) {
                const leftPercent = ((minVal - min) / (max - min)) * 100;
                const rightPercent = 100 - ((maxVal - min) / (max - min)) * 100;

                track.style.setProperty('--range-left', leftPercent + '%');
                track.style.setProperty('--range-right', (100 - rightPercent) + '%');
            }

            // Don't auto-submit - let user click "Apply Filters" button
            // clearTimeout(this.priceTimer);
            // this.priceTimer = setTimeout(() => {
            //     this.handleFilterChange();
            // }, 500);
        };

        minSlider.addEventListener('input', updateRange);
        maxSlider.addEventListener('input', updateRange);
        updateRange();
    }

    setupViewToggle() {
        const gridBtn = document.querySelector('[data-view="grid"]');
        const listBtn = document.querySelector('[data-view="list"]');
        const productGrid = document.querySelector('.product-grid');

        if (gridBtn && listBtn && productGrid) {
            gridBtn.addEventListener('click', () => {
                this.viewMode = 'grid';
                productGrid.classList.remove('list-view');
                productGrid.classList.add('grid-view');
                gridBtn.classList.add('active');
                listBtn.classList.remove('active');
                this.animateProducts();
            });

            listBtn.addEventListener('click', () => {
                this.viewMode = 'list';
                productGrid.classList.remove('grid-view');
                productGrid.classList.add('list-view');
                listBtn.classList.add('active');
                gridBtn.classList.remove('active');
                this.animateProducts();
            });
        }
    }

    setupInfiniteScroll() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.product-card-modern').forEach(card => {
            observer.observe(card);
        });
    }

    setupProductAnimations() {
        // Stagger animation for products - support both old and new class names
        const products = document.querySelectorAll('.product-card-sleek, .product-card-modern');
        products.forEach((product, index) => {
            product.style.animationDelay = `${index * 0.05}s`;
            product.classList.add('fade-in-up');
        });

        // Hover effects
        products.forEach(product => {
            product.addEventListener('mouseenter', () => {
                product.style.transform = 'translateY(-12px) scale(1.02)';
            });

            product.addEventListener('mouseleave', () => {
                product.style.transform = 'translateY(0) scale(1)';
            });
        });
    }

    setupQuickView() {
        // Add quick view buttons - support both old and new class names
        const products = document.querySelectorAll('.product-card-sleek, .product-card-modern');
        products.forEach(product => {
            const quickViewBtn = document.createElement('button');
            quickViewBtn.className = 'quick-view-btn';
            quickViewBtn.innerHTML = '<i class="bi bi-eye"></i> Quick View';
            quickViewBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const productLink = product.querySelector('.product-link');
                if (productLink) {
                    const url = productLink.getAttribute('href');
                    this.showQuickView(url);
                }
            });

            const imageWrapper = product.querySelector('.product-image-wrapper');
            if (imageWrapper) {
                imageWrapper.appendChild(quickViewBtn);
            }
        });
    }

    setupFilterSidebar() {
        const filterToggle = document.querySelector('[data-filter-toggle]');
        const filterSidebar = document.querySelector('.filter-sidebar');

        if (filterToggle && filterSidebar) {
            filterToggle.addEventListener('click', () => {
                filterSidebar.classList.toggle('mobile-open');
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                if (!filterSidebar.contains(e.target) && !filterToggle.contains(e.target)) {
                    filterSidebar.classList.remove('mobile-open');
                }
            });
        }
    }

    handleSearch(query) {
        this.filters.query = query;
        this.highlightSearchTerms(query);
        // Don't auto-submit - let user press Enter or click search button
        // this.handleFilterChange();
    }

    highlightSearchTerms(query) {
        if (!query) {
            document.querySelectorAll('.search-highlight').forEach(el => {
                el.classList.remove('search-highlight');
            });
            return;
        }

        const productNames = document.querySelectorAll('.product-name');
        productNames.forEach(nameEl => {
            const text = nameEl.textContent;
            const regex = new RegExp(`(${query})`, 'gi');
            const highlighted = text.replace(regex, '<mark class="search-highlight">$1</mark>');
            nameEl.innerHTML = highlighted;
        });
    }

    handleFilterChange() {
        // Don't auto-submit - let user click "Apply Filters" button manually
        // const form = document.getElementById('filterForm');
        // if (form) {
        //     // Show loading state
        //     this.showLoading();
        //     // Submit form
        //     setTimeout(() => {
        //         form.submit();
        //     }, 300);
        // }
    }

    handleSort() {
        // Try both old and new class names
        const products = Array.from(document.querySelectorAll('.product-card-sleek, .product-card-modern'));
        const container = document.querySelector('.product-grid') || document.querySelector('.row.g-4');

        if (!container || products.length === 0) {
            console.warn('No products or container found for sorting');
            return;
        }

        products.forEach(p => {
            p.style.opacity = '0.5';
            p.style.transition = 'opacity 0.3s ease';
        });

        setTimeout(() => {
            switch (this.sortBy) {
                case 'price-low':
                    products.sort((a, b) => {
                        // Find price element - try multiple selectors
                        let priceElA = a.querySelector('[data-price-cents]');
                        if (!priceElA) priceElA = a.querySelector('.price-amount-sleek');
                        if (!priceElA) priceElA = a.querySelector('.product-price');
                        
                        let priceElB = b.querySelector('[data-price-cents]');
                        if (!priceElB) priceElB = b.querySelector('.price-amount-sleek');
                        if (!priceElB) priceElB = b.querySelector('.product-price');
                        
                        // Extract price value
                        let priceA = 0;
                        let priceB = 0;
                        
                        if (priceElA) {
                            priceA = parseFloat(priceElA.getAttribute('data-price-cents') || priceElA.dataset.priceCents || 0);
                        }
                        
                        if (priceElB) {
                            priceB = parseFloat(priceElB.getAttribute('data-price-cents') || priceElB.dataset.priceCents || 0);
                        }
                        
                        if (isNaN(priceA) || isNaN(priceB)) {
                            console.warn('Price sorting issue:', {
                                priceA, priceB,
                                elA: priceElA?.outerHTML?.substring(0, 100),
                                elB: priceElB?.outerHTML?.substring(0, 100)
                            });
                        }
                        
                        return priceA - priceB;
                    });
                    break;
                case 'price-high':
                    products.sort((a, b) => {
                        // Find price element - try multiple selectors
                        let priceElA = a.querySelector('[data-price-cents]');
                        if (!priceElA) priceElA = a.querySelector('.price-amount-sleek');
                        if (!priceElA) priceElA = a.querySelector('.product-price');
                        
                        let priceElB = b.querySelector('[data-price-cents]');
                        if (!priceElB) priceElB = b.querySelector('.price-amount-sleek');
                        if (!priceElB) priceElB = b.querySelector('.product-price');
                        
                        // Extract price value
                        let priceA = 0;
                        let priceB = 0;
                        
                        if (priceElA) {
                            priceA = parseFloat(priceElA.getAttribute('data-price-cents') || priceElA.dataset.priceCents || 0);
                        }
                        
                        if (priceElB) {
                            priceB = parseFloat(priceElB.getAttribute('data-price-cents') || priceElB.dataset.priceCents || 0);
                        }
                        
                        if (isNaN(priceA) || isNaN(priceB)) {
                            console.warn('Price sorting issue:', {
                                priceA, priceB,
                                elA: priceElA?.outerHTML?.substring(0, 100),
                                elB: priceElB?.outerHTML?.substring(0, 100)
                            });
                        }
                        
                        return priceB - priceA;
                    });
                    break;
                case 'name':
                    products.sort((a, b) => {
                        const nameElA = a.querySelector('.product-name-sleek, .product-name');
                        const nameElB = b.querySelector('.product-name-sleek, .product-name');
                        const nameA = nameElA ? (nameElA.textContent || '').trim() : '';
                        const nameB = nameElB ? (nameElB.textContent || '').trim() : '';
                        return nameA.localeCompare(nameB);
                    });
                    break;
                case 'default':
                    // Reset to original order - reload page or restore original order
                    window.location.reload();
                    return;
            }

            // Re-append products in sorted order
            products.forEach((p, i) => {
                container.appendChild(p);
                p.style.opacity = '1';
                p.style.animationDelay = `${i * 0.05}s`;
            });
        }, 200);
    }

    showLoading() {
        const grid = document.querySelector('.row.g-4');
        if (grid) {
            grid.style.opacity = '0.5';
            grid.style.pointerEvents = 'none';
        }
    }

    animateProducts() {
        const products = document.querySelectorAll('.product-card-modern');
        products.forEach((product, index) => {
            product.style.animation = 'none';
            setTimeout(() => {
                product.style.animation = `fadeInUp 0.5s ease forwards`;
                product.style.animationDelay = `${index * 0.05}s`;
            }, 10);
        });
    }

    showQuickView(url) {
        // Create modal overlay
        const modal = document.createElement('div');
        modal.className = 'quick-view-modal';
        modal.innerHTML = `
            <div class="quick-view-content">
                <button class="quick-view-close">&times;</button>
                <iframe src="${url}" frameborder="0"></iframe>
            </div>
        `;

        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';

        // Close handlers
        const closeBtn = modal.querySelector('.quick-view-close');
        closeBtn.addEventListener('click', () => this.closeQuickView(modal));

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.closeQuickView(modal);
            }
        });

        // Animate in
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
    }

    closeQuickView(modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            document.body.removeChild(modal);
            document.body.style.overflow = '';
        }, 300);
    }
}

// Global addToCart function for quick actions
async function addToCart(productId) {
    try {
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const response = await fetch(window.buildUrl(`/cart/add/${productId}/`), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                qty: 1,
                csrf_token: csrfToken
            })
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Show success notification
            showNotification('Product added to cart!', 'success');
            
            // Update cart count in navbar
            const cartCount = document.getElementById('cartCount');
            if (cartCount) {
                const current = parseInt(cartCount.textContent) || 0;
                cartCount.textContent = current + 1;
                cartCount.style.animation = 'pulse 0.5s ease';
                setTimeout(() => {
                    cartCount.style.animation = '';
                }, 500);
            }
        } else {
            showNotification(data.message || data.error || 'Failed to add to cart', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

// Notification system
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `catalog-notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.catalog = new ModernCatalog();
});

