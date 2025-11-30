<div class="mb-3">
    <a href="/build_mate/supplier/dashboard" class="back-button">
        <i class="bi bi-arrow-left"></i>
        <span>Back to Dashboard</span>
    </a>
</div>

<h2 class="mb-4">Manage Products</h2>

<div class="mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">Add New Product</button>
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Delivery Size</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= \App\View::e($product['name']) ?></td>
                    <td><?= \App\View::e($product['category_name'] ?? '') ?></td>
                    <td><?= \App\Money::format($product['price_cents'], $product['currency']) ?></td>
                    <td><?= $product['stock'] ?></td>
                    <td>
                        <?php 
                        $deliverySize = $product['delivery_size'] ?? 'small';
                        $sizeLabel = $deliverySize === 'large' ? 'Large (Truck)' : 'Small (Motorbike)';
                        $sizeIcon = $deliverySize === 'large' ? '🚚' : '🏍️';
                        ?>
                        <span class="badge bg-<?= $deliverySize === 'large' ? 'warning' : 'info' ?>">
                            <?= $sizeIcon ?> <?= $sizeLabel ?>
                        </span>
                    </td>
                    <td>
                        <?php 
                        $isSupplierApproved = ($supplier['kyc_status'] ?? 'pending') === 'approved';
                        if ($product['verified']): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Verified
                            </span>
                        <?php elseif ($isSupplierApproved): ?>
                            <span class="badge bg-info">
                                <i class="bi bi-sync"></i> Auto-verifying...
                            </span>
                            <br><small class="text-muted">Refresh page to see updated status</small>
                        <?php else: ?>
                            <span class="badge bg-warning">
                                <i class="bi bi-clock"></i> Pending
                            </span>
                            <br><small class="text-muted">Will auto-verify when supplier is approved</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editProduct(<?= htmlspecialchars(json_encode($product)) ?>)">Edit</button>
                        <form method="POST" action="/build_mate/supplier/products/<?= $product['id'] ?>/delete/" class="d-inline">
                            <?= \App\Csrf::field() ?>
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/build_mate/supplier/products/" id="productForm" enctype="multipart/form-data">
                <?= \App\Csrf::field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= \App\View::e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="delivery_size" class="form-label">Delivery Size <span class="text-muted">(Required)</span></label>
                        <select class="form-select" id="delivery_size" name="delivery_size" required>
                            <option value="small">Small (Motorbike Delivery)</option>
                            <option value="large">Large (Truck Delivery)</option>
                        </select>
                        <small class="form-text text-muted">
                            <strong>Small:</strong> Items that can be delivered by motorbike (e.g., cement bags, paint, small tools)<br>
                            <strong>Large:</strong> Items requiring truck delivery (e.g., blocks, iron rods, roofing sheets, bulk materials)
                        </small>
                    </div>
                    <div class="mb-3">
                        <label for="currency" class="form-label">Currency</label>
                        <select class="form-select" id="currency" name="currency">
                            <option value="GHS">GHS</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <div class="mb-2">
                            <label for="product_image_file" class="form-label small text-muted">Upload Image File</label>
                            <input type="file" class="form-control" id="product_image_file" name="product_image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="form-text text-muted">Max size: 5MB. Accepted formats: JPG, PNG, GIF, WebP</small>
                        </div>
                        <div class="text-center my-2">
                            <strong class="text-muted">OR</strong>
                        </div>
                        <div>
                            <label for="image_url" class="form-label small text-muted">Enter Image URL</label>
                            <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
                            <small class="form-text text-muted">Alternative: Enter a URL to an image (e.g., from Imgur, Unsplash, or your server)</small>
                        </div>
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <img id="previewImg" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Image preview functionality
document.getElementById('product_image_file')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const urlInput = document.getElementById('image_url');
    
    if (file) {
        // Clear URL input when file is selected
        if (urlInput) urlInput.value = '';
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});

document.getElementById('image_url')?.addEventListener('input', function(e) {
    const url = e.target.value;
    const fileInput = document.getElementById('product_image_file');
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (url && url.startsWith('http')) {
        // Clear file input when URL is entered
        if (fileInput) fileInput.value = '';
        
        // Show preview from URL
        previewImg.src = url;
        previewImg.onload = function() {
            preview.style.display = 'block';
        };
        previewImg.onerror = function() {
            preview.style.display = 'none';
        };
    } else {
        preview.style.display = 'none';
    }
});
</script>

<script>
function editProduct(product) {
    document.getElementById('productForm').action = '/build_mate/supplier/products/' + product.id + '/update';
    document.getElementById('name').value = product.name;
    document.getElementById('category_id').value = product.category_id;
    document.getElementById('description').value = product.description || '';
    document.getElementById('price').value = product.price_cents / 100;
    document.getElementById('stock').value = product.stock;
    document.getElementById('currency').value = product.currency;
    document.getElementById('image_url').value = product.image_url || '';
    document.getElementById('delivery_size').value = product.delivery_size || 'small';
    
    // Clear file input and show current image if URL exists
    const fileInput = document.getElementById('product_image_file');
    if (fileInput) fileInput.value = '';
    
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    if (product.image_url) {
        previewImg.src = product.image_url;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('productModal')).show();
}
</script>

