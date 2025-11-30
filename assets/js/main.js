/**
 * Build Mate - Main JavaScript
 */

// Cart functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add to cart buttons
    const addToCartForms = document.querySelectorAll('form[data-action="add-to-cart"]');
    addToCartForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const productId = formData.get('product_id');
            const qty = formData.get('qty') || 1;
            
            try {
                // Get CSRF token from meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                const response = await fetch(`/build_mate/cart/add/${productId}/`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        qty: qty,
                        csrf_token: csrfToken
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message || 'Product added to cart!');
                    // Update cart count if element exists
                    const cartCount = document.getElementById('cartCount');
                    if (cartCount) {
                        const current = parseInt(cartCount.textContent) || 0;
                        cartCount.textContent = current + parseInt(qty);
                    }
                } else {
                    alert(data.message || data.error || 'Failed to add to cart');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // Currency toggle
    const currencyToggle = document.querySelector('[data-currency-toggle]');
    if (currencyToggle) {
        currencyToggle.addEventListener('change', function() {
            const currency = this.value;
            const priceElements = document.querySelectorAll('[data-price-cents]');
            const rate = parseFloat(this.dataset.rate || '12.5');
            
            priceElements.forEach(el => {
                const priceCents = parseInt(el.dataset.priceCents);
                const currentCurrency = el.dataset.currency || 'GHS';
                
                if (currency === 'USD' && currentCurrency === 'GHS') {
                    const usdPrice = (priceCents / 100) / rate;
                    el.textContent = '$' + usdPrice.toFixed(2);
                } else if (currency === 'GHS' && currentCurrency === 'GHS') {
                    const ghsPrice = priceCents / 100;
                    el.textContent = '₵' + ghsPrice.toFixed(2);
                }
            });
        });
    }
});

