<?php
/**
 * Login Page
 */
?>
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="bi bi-box-arrow-in-right"></i> Welcome Back</h1>
                <p>Sign in to your Build Mate account</p>
            </div>

            <?php if ($flash && $flash['type'] === 'error'): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i> <?= \App\View::e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($flash && $flash['type'] === 'success'): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?= \App\View::e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= \App\View::url('/login') ?>" class="auth-form">
                <?= \App\Csrf::field() ?>
                
                <div class="form-group">
                    <label for="email">
                        <i class="bi bi-envelope"></i> Email Address
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           required 
                           autofocus
                           placeholder="your@email.com">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="bi bi-lock"></i> Password
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           required
                           placeholder="Enter your password">
                </div>

                <button type="submit" class="btn-auth-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="<?= \App\View::url('/register') ?>">Sign up here</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
    padding: 2rem;
}

.auth-container {
    width: 100%;
    max-width: 450px;
}

.auth-card {
    background: white;
    border-radius: 24px;
    padding: 3rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #8B4513;
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: #6B7280;
    font-size: 1rem;
}

.auth-form {
    margin-top: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9375rem;
}

.form-group label i {
    margin-right: 0.5rem;
    color: #8B4513;
}

.form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #E5E7EB;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #8B4513;
    box-shadow: 0 0 0 4px rgba(139, 69, 19, 0.1);
}

.btn-auth-primary {
    width: 100%;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.0625rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
}

.btn-auth-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(139, 69, 19, 0.3);
}

.auth-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #E5E7EB;
}

.auth-footer p {
    color: #6B7280;
    font-size: 0.9375rem;
}

.auth-footer a {
    color: #8B4513;
    font-weight: 600;
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.alert {
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-danger {
    background: #FEE2E2;
    color: #DC2626;
    border: 1px solid #FECACA;
}

.alert-success {
    background: #D1FAE5;
    color: #059669;
    border: 1px solid #A7F3D0;
}
</style>


 * Login Page
 */
?>
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="bi bi-box-arrow-in-right"></i> Welcome Back</h1>
                <p>Sign in to your Build Mate account</p>
            </div>

            <?php if ($flash && $flash['type'] === 'error'): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i> <?= \App\View::e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($flash && $flash['type'] === 'success'): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?= \App\View::e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= \App\View::url('/login') ?>" class="auth-form">
                <?= \App\Csrf::field() ?>
                
                <div class="form-group">
                    <label for="email">
                        <i class="bi bi-envelope"></i> Email Address
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           required 
                           autofocus
                           placeholder="your@email.com">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="bi bi-lock"></i> Password
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           required
                           placeholder="Enter your password">
                </div>

                <button type="submit" class="btn-auth-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="<?= \App\View::url('/register') ?>">Sign up here</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
    padding: 2rem;
}

.auth-container {
    width: 100%;
    max-width: 450px;
}

.auth-card {
    background: white;
    border-radius: 24px;
    padding: 3rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #8B4513;
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: #6B7280;
    font-size: 1rem;
}

.auth-form {
    margin-top: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9375rem;
}

.form-group label i {
    margin-right: 0.5rem;
    color: #8B4513;
}

.form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #E5E7EB;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #8B4513;
    box-shadow: 0 0 0 4px rgba(139, 69, 19, 0.1);
}

.btn-auth-primary {
    width: 100%;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.0625rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
}

.btn-auth-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(139, 69, 19, 0.3);
}

.auth-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #E5E7EB;
}

.auth-footer p {
    color: #6B7280;
    font-size: 0.9375rem;
}

.auth-footer a {
    color: #8B4513;
    font-weight: 600;
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.alert {
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-danger {
    background: #FEE2E2;
    color: #DC2626;
    border: 1px solid #FECACA;
}

.alert-success {
    background: #D1FAE5;
    color: #059669;
    border: 1px solid #A7F3D0;
}
</style>


 * Login Page
 */
?>
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="bi bi-box-arrow-in-right"></i> Welcome Back</h1>
                <p>Sign in to your Build Mate account</p>
            </div>

            <?php if ($flash && $flash['type'] === 'error'): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i> <?= \App\View::e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($flash && $flash['type'] === 'success'): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?= \App\View::e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= \App\View::url('/login') ?>" class="auth-form">
                <?= \App\Csrf::field() ?>
                
                <div class="form-group">
                    <label for="email">
                        <i class="bi bi-envelope"></i> Email Address
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           required 
                           autofocus
                           placeholder="your@email.com">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="bi bi-lock"></i> Password
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           required
                           placeholder="Enter your password">
                </div>

                <button type="submit" class="btn-auth-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="<?= \App\View::url('/register') ?>">Sign up here</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
    padding: 2rem;
}

.auth-container {
    width: 100%;
    max-width: 450px;
}

.auth-card {
    background: white;
    border-radius: 24px;
    padding: 3rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #8B4513;
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: #6B7280;
    font-size: 1rem;
}

.auth-form {
    margin-top: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9375rem;
}

.form-group label i {
    margin-right: 0.5rem;
    color: #8B4513;
}

.form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #E5E7EB;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #8B4513;
    box-shadow: 0 0 0 4px rgba(139, 69, 19, 0.1);
}

.btn-auth-primary {
    width: 100%;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.0625rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
}

.btn-auth-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(139, 69, 19, 0.3);
}

.auth-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #E5E7EB;
}

.auth-footer p {
    color: #6B7280;
    font-size: 0.9375rem;
}

.auth-footer a {
    color: #8B4513;
    font-weight: 600;
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.alert {
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-danger {
    background: #FEE2E2;
    color: #DC2626;
    border: 1px solid #FECACA;
}

.alert-success {
    background: #D1FAE5;
    color: #059669;
    border: 1px solid #A7F3D0;
}
</style>


 * Login Page
 */
?>
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="bi bi-box-arrow-in-right"></i> Welcome Back</h1>
                <p>Sign in to your Build Mate account</p>
            </div>

            <?php if ($flash && $flash['type'] === 'error'): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i> <?= \App\View::e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($flash && $flash['type'] === 'success'): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?= \App\View::e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= \App\View::url('/login') ?>" class="auth-form">
                <?= \App\Csrf::field() ?>
                
                <div class="form-group">
                    <label for="email">
                        <i class="bi bi-envelope"></i> Email Address
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           required 
                           autofocus
                           placeholder="your@email.com">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="bi bi-lock"></i> Password
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           required
                           placeholder="Enter your password">
                </div>

                <button type="submit" class="btn-auth-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="<?= \App\View::url('/register') ?>">Sign up here</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
    padding: 2rem;
}

.auth-container {
    width: 100%;
    max-width: 450px;
}

.auth-card {
    background: white;
    border-radius: 24px;
    padding: 3rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #8B4513;
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: #6B7280;
    font-size: 1rem;
}

.auth-form {
    margin-top: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9375rem;
}

.form-group label i {
    margin-right: 0.5rem;
    color: #8B4513;
}

.form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #E5E7EB;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #8B4513;
    box-shadow: 0 0 0 4px rgba(139, 69, 19, 0.1);
}

.btn-auth-primary {
    width: 100%;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.0625rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
}

.btn-auth-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(139, 69, 19, 0.3);
}

.auth-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #E5E7EB;
}

.auth-footer p {
    color: #6B7280;
    font-size: 0.9375rem;
}

.auth-footer a {
    color: #8B4513;
    font-weight: 600;
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.alert {
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-danger {
    background: #FEE2E2;
    color: #DC2626;
    border: 1px solid #FECACA;
}

.alert-success {
    background: #D1FAE5;
    color: #059669;
    border: 1px solid #A7F3D0;
}
</style>


 * Login Page
 */
?>
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="bi bi-box-arrow-in-right"></i> Welcome Back</h1>
                <p>Sign in to your Build Mate account</p>
            </div>

            <?php if ($flash && $flash['type'] === 'error'): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i> <?= \App\View::e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($flash && $flash['type'] === 'success'): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?= \App\View::e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= \App\View::url('/login') ?>" class="auth-form">
                <?= \App\Csrf::field() ?>
                
                <div class="form-group">
                    <label for="email">
                        <i class="bi bi-envelope"></i> Email Address
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           required 
                           autofocus
                           placeholder="your@email.com">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="bi bi-lock"></i> Password
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           required
                           placeholder="Enter your password">
                </div>

                <button type="submit" class="btn-auth-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="<?= \App\View::url('/register') ?>">Sign up here</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
    padding: 2rem;
}

.auth-container {
    width: 100%;
    max-width: 450px;
}

.auth-card {
    background: white;
    border-radius: 24px;
    padding: 3rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #8B4513;
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: #6B7280;
    font-size: 1rem;
}

.auth-form {
    margin-top: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9375rem;
}

.form-group label i {
    margin-right: 0.5rem;
    color: #8B4513;
}

.form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #E5E7EB;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #8B4513;
    box-shadow: 0 0 0 4px rgba(139, 69, 19, 0.1);
}

.btn-auth-primary {
    width: 100%;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.0625rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
}

.btn-auth-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(139, 69, 19, 0.3);
}

.auth-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #E5E7EB;
}

.auth-footer p {
    color: #6B7280;
    font-size: 0.9375rem;
}

.auth-footer a {
    color: #8B4513;
    font-weight: 600;
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.alert {
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-danger {
    background: #FEE2E2;
    color: #DC2626;
    border: 1px solid #FECACA;
}

.alert-success {
    background: #D1FAE5;
    color: #059669;
    border: 1px solid #A7F3D0;
}
</style>


 * Login Page
 */
?>
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="bi bi-box-arrow-in-right"></i> Welcome Back</h1>
                <p>Sign in to your Build Mate account</p>
            </div>

            <?php if ($flash && $flash['type'] === 'error'): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i> <?= \App\View::e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($flash && $flash['type'] === 'success'): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?= \App\View::e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= \App\View::url('/login') ?>" class="auth-form">
                <?= \App\Csrf::field() ?>
                
                <div class="form-group">
                    <label for="email">
                        <i class="bi bi-envelope"></i> Email Address
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           required 
                           autofocus
                           placeholder="your@email.com">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="bi bi-lock"></i> Password
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           required
                           placeholder="Enter your password">
                </div>

                <button type="submit" class="btn-auth-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="<?= \App\View::url('/register') ?>">Sign up here</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
    padding: 2rem;
}

.auth-container {
    width: 100%;
    max-width: 450px;
}

.auth-card {
    background: white;
    border-radius: 24px;
    padding: 3rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #8B4513;
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: #6B7280;
    font-size: 1rem;
}

.auth-form {
    margin-top: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9375rem;
}

.form-group label i {
    margin-right: 0.5rem;
    color: #8B4513;
}

.form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #E5E7EB;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #8B4513;
    box-shadow: 0 0 0 4px rgba(139, 69, 19, 0.1);
}

.btn-auth-primary {
    width: 100%;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.0625rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
}

.btn-auth-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(139, 69, 19, 0.3);
}

.auth-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #E5E7EB;
}

.auth-footer p {
    color: #6B7280;
    font-size: 0.9375rem;
}

.auth-footer a {
    color: #8B4513;
    font-weight: 600;
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.alert {
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-danger {
    background: #FEE2E2;
    color: #DC2626;
    border: 1px solid #FECACA;
}

.alert-success {
    background: #D1FAE5;
    color: #059669;
    border: 1px solid #A7F3D0;
}
</style>

