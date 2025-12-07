<h2 class="mb-4">Product Management</h2>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Supplier</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Verified</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= \App\View::e($product['name']) ?></td>
                    <td><?= \App\View::e($product['supplier_name'] ?? '') ?></td>
                    <td><?= \App\Money::format($product['price_cents'], $product['currency']) ?></td>
                    <td><?= $product['stock'] ?></td>
                    <td>
                        <span class="badge bg-<?= $product['verified'] ? 'success' : 'warning' ?>">
                            <?= $product['verified'] ? 'Yes' : 'No' ?>
                        </span>
                    </td>
                    <td><?= date('M d, Y', strtotime($product['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

