<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? \App\View::e($title) . ' - ' : '' ?>Build Mate Ghana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" onerror="this.onerror=null; this.href='<?= \App\View::asset('assets/css/bootstrap-icons-fallback.css') ?>';">
    <noscript>
        <link rel="stylesheet" href="<?= \App\View::asset('assets/css/bootstrap-icons-fallback.css') ?>">
    </noscript>
    <link rel="stylesheet" href="<?= \App\View::asset('assets/css/main.css') ?>">
    <link rel="stylesheet" href="<?= \App\View::asset('assets/css/chat-widget.css') ?>">
    <meta name="csrf-token" content="<?= \App\Csrf::token() ?>">
    <!-- Fallback: Check if CSS loads -->
    <script>
        window.addEventListener('load', function() {
            var link = document.querySelector('link[href*="assets/css/main.css"]');
            if (link) {
                link.onerror = function() {
                    console.error('CSS failed to load');
                };
            }
        });
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= \App\View::url('/') ?>">
                <i class="bi bi-hammer"></i> Build Mate Ghana
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= \App\View::url('/') ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= \App\View::url('/catalog') ?>">Catalog</a>
                    </li>
                    <?php if (isset($_SESSION['user'])): ?>
                        <?php if ($_SESSION['user']['role'] === 'supplier'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= \App\View::url('/supplier/dashboard') ?>">Supplier Portal</a>
                            </li>
                        <?php elseif ($_SESSION['user']['role'] === 'logistics'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= \App\View::url('/logistics/dashboard') ?>">Logistics</a>
                            </li>
                        <?php elseif ($_SESSION['user']['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= \App\View::url('/admin/dashboard') ?>">Admin</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <select id="currencyToggle" class="form-select form-select-sm me-2" style="width: auto; display: inline-block;">
                            <option value="GHS">GHS</option>
                            <option value="USD">USD</option>
                        </select>
                    </li>
                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] !== 'supplier'): ?>
                        <?php
                        $wishlistCount = 0;
                        try {
                            $wishlistModel = new \App\Wishlist();
                            $wishlistCount = $wishlistModel->getCount($_SESSION['user']['id']);
                        } catch (\Exception $e) {
                            // Silently fail if wishlist table doesn't exist
                        }
                        ?>
                        <li class="nav-item">
                            <a class="nav-link wishlist-badge" href="<?= \App\View::url('/wishlist') ?>" title="My Wishlist">
                                <i class="bi bi-heart"></i>
                                <span class="wishlist-count" id="wishlistCount"><?= $wishlistCount ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'supplier'): ?>
                        <li class="nav-item">
                            <a class="nav-link cart-badge" href="<?= \App\View::url('/cart') ?>">
                                <i class="bi bi-cart"></i>
                                <span class="cart-count" id="cartCount"><?= count($_SESSION['cart'] ?? []) ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <?= \App\View::e($_SESSION['user']['name']) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= \App\View::url('/orders') ?>">My Orders</a></li>
                                <li><a class="dropdown-item" href="<?= \App\View::url('/wishlist') ?>">
                                    <i class="bi bi-heart"></i> My Wishlist
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="<?= \App\View::url('/logout/') ?>" style="display: inline;">
                                        <?= \App\Csrf::field() ?>
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= \App\View::url('/login') ?>">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= \App\View::url('/register') ?>">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <?php if (isset($flash)): ?>
            <div class="container mt-3">
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
                    <?= \App\View::e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>
        
        <?= $content ?>
    </main>

    <footer class="py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-3">
                        <i class="bi bi-hammer"></i> Build Mate Ghana
                    </h5>
                    <p>Ghana's trusted construction materials marketplace. Connect with verified suppliers, secure Paystack payments, and tracked delivery.</p>
                </div>
                <div class="col-md-2 mb-4 mb-md-0">
                    <h6 class="mb-3">Company</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= \App\View::url('/') ?>" class="text-decoration-none">About</a></li>
                        <li><a href="<?= \App\View::url('/') ?>" class="text-decoration-none">Contact</a></li>
                        <li><a href="<?= \App\View::url('/') ?>" class="text-decoration-none">Careers</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <h6 class="mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= \App\View::url('/') ?>" class="text-decoration-none">Help Center</a></li>
                        <li><a href="<?= \App\View::url('/') ?>" class="text-decoration-none">Privacy Policy</a></li>
                        <li><a href="<?= \App\View::url('/') ?>" class="text-decoration-none">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="mb-3">Resources</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= \App\View::url('/') ?>" class="text-decoration-none">Buyer Guide</a></li>
                        <li><a href="<?= \App\View::url('/') ?>" class="text-decoration-none">Supplier Guide</a></li>
                        <li><a href="<?= \App\View::url('/security.txt') ?>" class="text-decoration-none">Data Protection</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            <p class="text-center mb-0">&copy; <?= date('Y') ?> Build Mate Ghana Ltd. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= \App\View::asset('assets/js/main.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= \App\View::asset('assets/js/chat-widget.js') ?>?v=<?= time() ?>"></script>
    <script>
        // Currency conversion (client-side for demo)
        const usdToGhsRate = <?php 
            $configPath = __DIR__ . '/../../settings/config.php';
            if (file_exists($configPath)) {
                $config = require $configPath;
                echo $config['currency']['usd_to_ghs_rate'] ?? 12.5;
            } else {
                echo 12.5; // Default fallback
            }
        ?>;
        let currentCurrency = 'GHS';
        
        document.getElementById('currencyToggle')?.addEventListener('change', function(e) {
            currentCurrency = e.target.value;
            updatePrices();
        });
        
        function updatePrices() {
            document.querySelectorAll('[data-price-cents]').forEach(el => {
                let cents = parseInt(el.dataset.priceCents);
                let currency = el.dataset.currency || 'GHS';
                let amount = cents / 100;
                
                if (currentCurrency === 'USD' && currency === 'GHS') {
                    amount = amount / usdToGhsRate;
                    currency = 'USD';
                } else if (currentCurrency === 'GHS' && currency === 'USD') {
                    amount = amount * usdToGhsRate;
                    currency = 'GHS';
                }
                
                el.textContent = currency + ' ' + amount.toFixed(2);
            });
        }
        
        // Update cart count
        function updateCartCount() {
            fetch('<?= \App\View::url('/api/search?query=') ?>')
                .then(() => {
                    // Cart count would be updated via session
                });
        }
    </script>
    <!-- Auto-refresh removed - was causing issues with form inputs -->
    </body>
</html>

