<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
.contact-hero {
    background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
    color: white;
    padding: 5rem 0;
    margin-bottom: 4rem;
}
.contact-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s;
    height: 100%;
    text-align: center;
}
.contact-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}
.contact-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #3B82F6, #2563EB);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2.5rem;
    color: white;
}
.form-modern {
    background: white;
    border-radius: 24px;
    padding: 3rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}
.form-control-modern {
    border: 2px solid #E5E7EB;
    border-radius: 12px;
    padding: 0.875rem 1.25rem;
}
.btn-modern {
    background: linear-gradient(135deg, #3B82F6, #2563EB);
    border: none;
    border-radius: 12px;
    padding: 1rem 2.5rem;
    font-weight: 600;
    color: white;
}
</style>

<div class="contact-hero">
    <div class="container">
        <div class="text-center">
            <h1 class="display-3 fw-bold mb-3">Let's Connect</h1>
            <p class="lead mb-0">Have questions? We're here to help you build your dreams</p>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="bi bi-telephone-fill"></i>
                </div>
                <h4 class="mb-3">Call Us</h4>
                <p class="text-muted mb-2">Mon - Sat, 8AM - 6PM</p>
                <a href="tel:+233XXXXXXXXX" class="text-primary fw-bold text-decoration-none">+233 XX XXX XXXX</a>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="bi bi-envelope-fill"></i>
                </div>
                <h4 class="mb-3">Email Us</h4>
                <p class="text-muted mb-2">We respond within 24 hours</p>
                <a href="mailto:info@buildmate.gh" class="text-primary fw-bold text-decoration-none">info@buildmate.gh</a>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <h4 class="mb-3">Visit Us</h4>
                <p class="text-muted mb-2">Accra, Ghana</p>
                <span class="text-primary fw-bold">Get Directions</span>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-lg-6">
            <div class="form-modern">
                <h2 class="mb-2">Send us a Message</h2>
                <p class="text-muted mb-4">Fill out the form and we'll get back to you shortly</p>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control form-control-modern" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" class="form-control form-control-modern" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject</label>
                        <input type="text" class="form-control form-control-modern" name="subject" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Message</label>
                        <textarea class="form-control form-control-modern" name="message" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-modern">
                        <i class="bi bi-send-fill me-2"></i>Send Message
                    </button>
                </form>
            </div>
        </div>
        
        <div class="col-lg-6">
            <h2 class="mb-4">Frequently Asked Questions</h2>
            
            <div class="mb-3 p-3 bg-white rounded shadow-sm">
                <h5 class="mb-2"><i class="bi bi-cart-check text-primary me-2"></i>How do I place an order?</h5>
                <p class="text-muted mb-0">Browse our catalog, add items to cart, and checkout securely.</p>
            </div>
            
            <div class="mb-3 p-3 bg-white rounded shadow-sm">
                <h5 class="mb-2"><i class="bi bi-truck text-primary me-2"></i>Do you offer delivery?</h5>
                <p class="text-muted mb-0">Yes! We deliver across Ghana.</p>
            </div>
            
            <div class="p-3 bg-white rounded shadow-sm">
                <h5 class="mb-2"><i class="bi bi-shop text-primary me-2"></i>Can I become a supplier?</h5>
                <p class="text-muted mb-0">Yes! Register and submit your documents.</p>
            </div>
        </div>
    </div>
</div>
