<!-- Premium Upgrade Page -->
<link rel="stylesheet" href="<?= \App\View::asset('assets/css/supplier-dashboard.css">

<div class="supplier-dashboard-page">
    <div class="container">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="<?= \App\View::url('/supplier/dashboard') ?>" class="back-button">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>

        <!-- Page Header -->
        <div class="dashboard-header-modern">
            <h1 class="dashboard-title-modern">Upgrade to Premium</h1>
            <p class="dashboard-subtitle-modern">Boost your visibility and grow your business</p>
        </div>

        <?php if ($isPremium): ?>
            <!-- Already Premium -->
            <div class="section-card-modern" style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); border: none;">
                <div style="text-align: center; padding: 2rem;">
                    <div style="font-size: 4rem; color: white; margin-bottom: 1rem;">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <h2 style="color: white; margin-bottom: 1rem;">You're Already Premium!</h2>
                    <p style="color: rgba(255, 255, 255, 0.9); margin-bottom: 1.5rem;">
                        Your premium subscription is active.
                        <?php if ($expiresAt): ?>
                            <br>Expires on: <strong><?= date('F d, Y', strtotime($expiresAt)) ?></strong>
                        <?php endif; ?>
                    </p>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="<?= \App\View::url('/supplier/advertisements/create') ?>" class="btn" style="background: white; color: #FFA500; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; text-decoration: none;">
                            <i class="bi bi-megaphone"></i> Create Advertisement
                        </a>
                        <a href="<?= \App\View::url('/supplier/dashboard') ?>" class="btn" style="background: rgba(255, 255, 255, 0.2); color: white; border: 2px solid white; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; text-decoration: none;">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Premium Benefits -->
            <div class="stats-grid-modern" style="margin-bottom: 2rem;">
                <div class="stat-card-modern" style="text-align: center; padding: 2rem;">
                    <div style="font-size: 3rem; color: #667eea; margin-bottom: 1rem;">
                        <i class="bi bi-arrow-up-circle"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem;">Priority Placement</h3>
                    <p style="color: #6B7280; font-size: 0.875rem;">Your products appear at the top of search results and catalog listings</p>
                </div>
                
                <div class="stat-card-modern" style="text-align: center; padding: 2rem;">
                    <div style="font-size: 3rem; color: #10B981; margin-bottom: 1rem;">
                        <i class="bi bi-megaphone"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem;">Create Advertisements</h3>
                    <p style="color: #6B7280; font-size: 0.875rem;">Promote your products with sponsored ads that appear prominently</p>
                </div>
                
                <div class="stat-card-modern" style="text-align: center; padding: 2rem;">
                    <div style="font-size: 3rem; color: #F59E0B; margin-bottom: 1rem;">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem;">Premium Badge</h3>
                    <p style="color: #6B7280; font-size: 0.875rem;">Display a premium badge on all your products to build trust</p>
                </div>
                
                <div class="stat-card-modern" style="text-align: center; padding: 2rem;">
                    <div style="font-size: 3rem; color: #8B5CF6; margin-bottom: 1rem;">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem;">Better Analytics</h3>
                    <p style="color: #6B7280; font-size: 0.875rem;">Access detailed performance metrics and sentiment tracking</p>
                </div>
            </div>

            <!-- Pricing Card -->
            <div class="section-card-modern" style="max-width: 600px; margin: 0 auto 2rem;">
                <div style="text-align: center; padding: 2rem;">
                    <div style="font-size: 4rem; color: #667eea; margin-bottom: 1rem;">
                        <i class="bi bi-star"></i>
                    </div>
                    <h2 style="margin-bottom: 0.5rem;">Premium Plan</h2>
                    <div style="font-size: 3rem; font-weight: 700; color: #667eea; margin-bottom: 0.5rem;">
                        GHS 250
                    </div>
                    <p style="color: #6B7280; margin-bottom: 2rem;">per 30 days</p>
                    
                    <div style="text-align: left; margin-bottom: 2rem; background: #F9FAFB; padding: 1.5rem; border-radius: 12px;">
                        <h4 style="margin-bottom: 1rem; color: #1F2937;">What's Included:</h4>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="padding: 0.5rem 0; display: flex; align-items: center; gap: 0.75rem;">
                                <i class="bi bi-check-circle-fill" style="color: #10B981;"></i>
                                <span>Priority product placement in search results</span>
                            </li>
                            <li style="padding: 0.5rem 0; display: flex; align-items: center; gap: 0.75rem;">
                                <i class="bi bi-check-circle-fill" style="color: #10B981;"></i>
                                <span>Create unlimited product advertisements</span>
                            </li>
                            <li style="padding: 0.5rem 0; display: flex; align-items: center; gap: 0.75rem;">
                                <i class="bi bi-check-circle-fill" style="color: #10B981;"></i>
                                <span>Premium badge on all your products</span>
                            </li>
                            <li style="padding: 0.5rem 0; display: flex; align-items: center; gap: 0.75rem;">
                                <i class="bi bi-check-circle-fill" style="color: #10B981;"></i>
                                <span>Enhanced analytics and performance tracking</span>
                            </li>
                            <li style="padding: 0.5rem 0; display: flex; align-items: center; gap: 0.75rem;">
                                <i class="bi bi-check-circle-fill" style="color: #10B981;"></i>
                                <span>Priority customer support</span>
                            </li>
                        </ul>
                    </div>
                    
                    <form id="upgradeForm" style="display: none;">
                        <?= \App\Csrf::field() ?>
                    </form>
                    
                    <button id="upgradeBtn" class="btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 1rem 3rem; border-radius: 12px; font-weight: 700; font-size: 1.125rem; cursor: pointer; width: 100%; max-width: 400px;">
                        <i class="bi bi-arrow-up-circle"></i> Upgrade to Premium
                    </button>
                    
                    <p style="color: #6B7280; font-size: 0.875rem; margin-top: 1rem;">
                        <i class="bi bi-shield-check"></i> Secure payment via Paystack
                    </p>
                </div>
            </div>

            <!-- Performance Metrics (if available) -->
            <?php if (isset($sentimentScore) || isset($performanceWarnings)): ?>
                <div class="section-card-modern">
                    <div class="section-header-modern">
                        <h3 class="section-title-modern">
                            <i class="bi bi-graph-up"></i>
                            Your Current Performance
                        </h3>
                    </div>
                    <div class="section-body-modern">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                            <?php if (isset($sentimentScore)): ?>
                                <div>
                                    <div style="font-size: 0.875rem; color: #6B7280; margin-bottom: 0.5rem;">Sentiment Score</div>
                                    <div style="font-size: 2rem; font-weight: 700; color: <?= $sentimentScore >= 0.7 ? '#10B981' : ($sentimentScore >= 0.4 ? '#F59E0B' : '#EF4444') ?>;">
                                        <?= number_format($sentimentScore * 100, 0) ?>%
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($performanceWarnings) && $performanceWarnings > 0): ?>
                                <div>
                                    <div style="font-size: 0.875rem; color: #6B7280; margin-bottom: 0.5rem;">Performance Warnings</div>
                                    <div style="font-size: 2rem; font-weight: 700; color: #EF4444;">
                                        <?= $performanceWarnings ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const upgradeBtn = document.getElementById('upgradeBtn');
    if (upgradeBtn) {
        upgradeBtn.addEventListener('click', function() {
            upgradeBtn.disabled = true;
            upgradeBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
            
            // Get CSRF token from form or meta tag
            let csrfToken = '';
            const csrfInput = document.querySelector('input[name="csrf_token"]');
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfInput) {
                csrfToken = csrfInput.value;
            } else if (csrfMeta) {
                csrfToken = csrfMeta.getAttribute('content');
            }
            
            // Create form data
            const formData = new FormData();
            if (csrfToken) {
                formData.append('csrf_token', csrfToken);
            }
            
            fetch('/build_mate/supplier/premium/initialize', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                // Check if response is OK
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Non-JSON response:', text);
                        throw new Error('Invalid response from server');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.authorization_url) {
                    window.location.href = data.authorization_url;
                } else {
                    alert(data.message || 'Failed to initialize payment');
                    upgradeBtn.disabled = false;
                    upgradeBtn.innerHTML = '<i class="bi bi-arrow-up-circle"></i> Upgrade to Premium';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + (error.message || 'Please try again.'));
                upgradeBtn.disabled = false;
                upgradeBtn.innerHTML = '<i class="bi bi-arrow-up-circle"></i> Upgrade to Premium';
            });
        });
    }
});
</script>

