/**
 * Build Mate - Authentication JavaScript
 * Handles login and register form submissions and redirect detection
 */

(function() {
    'use strict';
    
    // Track if form is being submitted to prevent double submission
    let isSubmitting = false;
    
    // Check for redirect loops
    function detectRedirectLoop() {
        const currentPath = window.location.pathname;
        const referrer = document.referrer;
        
        // If we're on login page and came from login page, it's a loop
        if (currentPath.includes('/login') && referrer.includes('/login')) {
            console.warn('⚠️ Redirect loop detected!');
            
            // Show error message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.innerHTML = '<strong>Redirect Loop Detected:</strong> Your session may not be persisting. Please clear your browser cookies and try again.';
            
            const loginBody = document.querySelector('.login-body, .register-body');
            if (loginBody) {
                loginBody.insertBefore(alertDiv, loginBody.firstChild);
            }
            
            // Try to clear any stuck session
            if (typeof sessionStorage !== 'undefined') {
                sessionStorage.clear();
            }
            
            return true;
        }
        
        return false;
    }
    
    // Check if already logged in (shouldn't be on login page)
    function checkIfLoggedIn() {
        // This would require an API endpoint to check session
        // For now, we'll just detect loops
        return false;
    }
    
    // Initialize login form
    function initLoginForm() {
        const loginForm = document.getElementById('loginForm');
        if (!loginForm) return;
        
        // Detect redirect loop on page load
        if (detectRedirectLoop()) {
            console.error('Login page is in a redirect loop');
        }
        
        // Handle form submission
        loginForm.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                console.warn('Form is already being submitted');
                return false;
            }
            
            isSubmitting = true;
            const btn = this.querySelector('button[type="submit"]');
            const originalHtml = btn.innerHTML;
            
            // Disable button and show loading state
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Signing in...';
            
            // Set a timeout to re-enable if redirect takes too long
            setTimeout(function() {
                if (isSubmitting) {
                    console.warn('Login taking longer than expected...');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    isSubmitting = false;
                }
            }, 10000); // 10 second timeout
            
            // Allow form to submit normally
            // Server will handle redirect
        });
    }
    
    // Initialize register form
    function initRegisterForm() {
        const registerForm = document.getElementById('registerForm');
        if (!registerForm) return;
        
        // Detect redirect loop on page load
        if (detectRedirectLoop()) {
            console.error('Register page is in a redirect loop');
        }
        
        // Handle form submission
        registerForm.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                console.warn('Form is already being submitted');
                return false;
            }
            
            isSubmitting = true;
            const btn = this.querySelector('button[type="submit"]');
            const originalHtml = btn.innerHTML;
            
            // Disable button and show loading state
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating account...';
            
            // Set a timeout to re-enable if redirect takes too long
            setTimeout(function() {
                if (isSubmitting) {
                    console.warn('Registration taking longer than expected...');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    isSubmitting = false;
                }
            }, 10000); // 10 second timeout
            
            // Allow form to submit normally
        });
    }
    
    // Password toggle function
    function initPasswordToggle() {
        window.togglePassword = function() {
            const password = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            
            if (!password || !icon) return;
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        };
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initLoginForm();
            initRegisterForm();
            initPasswordToggle();
        });
    } else {
        // DOM already loaded
        initLoginForm();
        initRegisterForm();
        initPasswordToggle();
    }
    
    // Reset submitting flag if page is unloaded (user navigates away)
    window.addEventListener('beforeunload', function() {
        isSubmitting = false;
    });
    
})();

