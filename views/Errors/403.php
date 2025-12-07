<div class="text-center py-5">
    <h1 class="display-1">403</h1>
    <p class="lead">Access Forbidden</p>
    <?php if (isset($message)): ?>
        <p class="text-danger"><?= \App\View::e($message) ?></p>
    <?php endif; ?>
    <a href="<?= \App\View::relUrl('/') ?>" class="btn btn-primary">Go Home</a>
</div>

