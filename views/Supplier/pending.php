<link rel="stylesheet" href="<?= \App\View::asset('assets/css/pending-dashboard.css">

<div class="pending-dashboard">
    <!-- Hero Section -->
    <section class="pending-hero" id="pendingHero">
        <div class="container">
            <div class="hero-content">
                <div class="success-icon" id="successIcon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h1 class="hero-title" id="heroTitle">Application Successfully Submitted!</h1>
                <p class="hero-subtitle" id="heroSubtitle">
                    Your Build Mate supplier account is being reviewed.<br>
                    We'll notify you within 24-48 hours.
                </p>
                <div class="hero-actions" id="heroActions">
                    <a href="<?= \App\View::url('/') ?>" class="btn-glass btn-glass-light">
                        <i class="bi bi-house"></i> Back to Home
                    </a>
                    <a href="<?= \App\View::url('/contact') ?>" class="btn-glass btn-glass-dark">
                        <i class="bi bi-headset"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Status Card -->
    <section class="status-section">
        <div class="container">
            <div class="status-card" id="statusCard">
                <div class="status-header">
                    <h2>📋 We're Reviewing Your Documents</h2>
                    <span class="status-badge-pending">🟡 Pending Review</span>
                </div>
                <p class="status-description">
                    Thank you for applying! While we verify your business information, some dashboard features will remain locked.
                </p>
                
                <!-- Progress Bar -->
                <div class="progress-container">
                    <div class="progress-steps">
                        <div class="progress-line" id="progressLine"></div>
                        <div class="progress-step completed">
                            <div class="step-dot completed">
                                <i class="bi bi-check"></i>
                            </div>
                            <span class="step-label">Submitted</span>
                        </div>
                        <div class="progress-step active" id="activeStep">
                            <div class="step-dot active">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <span class="step-label">Under Review</span>
                        </div>
                        <div class="progress-step">
                            <div class="step-dot">
                                <i class="bi bi-lock-fill"></i>
                            </div>
                            <span class="step-label">Approved</span>
                        </div>
                    </div>
                </div>
                
                <div class="estimated-time">
                    <i class="bi bi-clock"></i> Estimated: 24-48 hours
                </div>
            </div>
        </div>
    </section>

    <!-- Feature Preview Grid -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Dashboard Preview</h2>
            <div class="features-grid">
                <!-- Card 1: Profile Review (Active) -->
                <div class="feature-card active" id="card1">
                    <div class="card-icon active-icon">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <h3 class="card-title">Profile Review</h3>
                    <p class="card-description">
                        We're validating your company info and documents.
                    </p>
                    <span class="card-badge active-badge">In Progress</span>
                    <a href="<?= \App\View::url('/supplier/kyc') ?>" class="card-link">View Status →</a>
                </div>

                <!-- Card 2: Product Upload (Locked) -->
                <div class="feature-card locked" id="card2">
                    <div class="lock-overlay">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                    <div class="card-icon locked-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h3 class="card-title">Products Management</h3>
                    <p class="card-description">
                        You'll be able to add and manage products once your account is approved.
                    </p>
                    <span class="card-badge locked-badge">Locked</span>
                </div>

                <!-- Card 3: Payments Setup (Locked) -->
                <div class="feature-card locked" id="card3">
                    <div class="lock-overlay">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                    <div class="card-icon locked-icon">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <h3 class="card-title">Payments & Payouts</h3>
                    <p class="card-description">
                        Payment processing activates after verification.
                    </p>
                    <span class="card-badge locked-badge">Locked</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline Section -->
    <section class="timeline-section" id="timelineSection">
        <div class="container">
            <h2 class="section-title">What Happens Next?</h2>
            <div class="timeline-container">
                <div class="timeline-line" id="timelineLine"></div>
                <div class="timeline-steps">
                    <div class="timeline-step" id="timelineStep1">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="step-title">Compliance Check</h4>
                        <p class="step-description">
                            We verify your business registration, ID, and contact details.
                        </p>
                    </div>
                    <div class="timeline-step" id="timelineStep2">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <i class="bi bi-envelope-open"></i>
                        </div>
                        <h4 class="step-title">Email Notification</h4>
                        <p class="step-description">
                            You'll get notified once your account is approved.
                        </p>
                    </div>
                    <div class="timeline-step" id="timelineStep3">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <h4 class="step-title">Start Selling</h4>
                        <p class="step-description">
                            Full dashboard unlocked immediately.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Help & Support Section -->
    <section class="support-section">
        <div class="container">
            <div class="support-card">
                <h3>Need help with your application?</h3>
                <p>Our support team is here to assist you</p>
                <div class="support-actions">
                    <a href="mailto:support@buildmate.com" class="btn-support">
                        <i class="bi bi-envelope"></i> Email Support
                    </a>
                    <a href="<?= \App\View::url('/contact') ?>" class="btn-support btn-support-primary">
                        <i class="bi bi-chat-dots"></i> Live Chat
                    </a>
                </div>
                <p class="support-phone">
                    Or call us: <a href="tel:+233596211352">+233 596 211 352</a>
                </p>
            </div>
        </div>
    </section>
</div>

<script src="<?= \App\View::asset('assets/js/pending-animations.js') ?>"></script>
