<div class="mb-3">
    <a href="<?= \App\View::relUrl('/catalog') ?>" class="back-button">
        <i class="icon-arrow-left"></i>
        <span>Continue Shopping</span>
    </a>
</div>

<h2 class="mb-4">Shopping Cart</h2>

<?php if (empty($products)): ?>
    <div class="alert alert-info">
        <p>Your cart is empty. <a href="<?= \App\View::relUrl('/catalog') ?>">Browse products</a></p>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-md-8">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <a href="<?= \App\View::relUrl('/product/' . \App\View::e($product['slug'])) ?>">
                                    <?= \App\View::e($product['name']) ?>
                                </a>
                            </td>
                            <td data-price-cents="<?= $product['price_cents'] ?>" data-currency="<?= $product['currency'] ?>">
                                <?= \App\Money::format($product['price_cents'], $product['currency']) ?>
                            </td>
                            <td>
                                <form method="POST" action="<?= \App\View::relUrl('/cart/update/') ?>" class="d-inline">
                                    <?= \App\Csrf::field() ?>
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="number" name="qty" value="<?= $product['qty'] ?>" min="1" max="<?= $product['stock'] ?>" class="form-control form-control-sm" style="width: 80px; display: inline-block;" onchange="this.form.submit()">
                                </form>
                            </td>
                            <td data-price-cents="<?= $product['price_cents'] * $product['qty'] ?>" data-currency="<?= $product['currency'] ?>">
                                <?= \App\Money::format($product['price_cents'] * $product['qty'], $product['currency']) ?>
                            </td>
                            <td>
                                <form method="POST" action="<?= \App\View::relUrl('/cart/remove/' . $product['id'] . '/') ?>" class="d-inline">
                                    <?= \App\Csrf::field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>Order Summary</h5>
                    <hr>
                    <p><strong>Total:</strong> <span data-price-cents="<?= $total ?>" data-currency="GHS"><?= \App\Money::format($total, 'GHS') ?></span></p>
                    <a href="<?= \App\View::relUrl('/checkout') ?>" class="btn btn-primary w-100">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
