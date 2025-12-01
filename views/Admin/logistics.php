<!-- Admin Logistics Dashboard -->
<link rel="stylesheet" href="<?= \App\View::asset('assets/css/supplier-dashboard.css">

<div class="supplier-dashboard-page">
    <div class="container">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="<?= \App\View::url('/admin/dashboard') ?>" class="back-button">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>

        <!-- Page Header -->
        <div class="dashboard-header-modern">
            <h1 class="dashboard-title-modern">Build Mate Logistics</h1>
            <p class="dashboard-subtitle-modern">Manage all deliveries and track status</p>
        </div>

        <!-- Filters -->
        <div class="section-card-modern" style="margin-bottom: 2rem;">
            <div class="section-body-modern">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select id="statusFilter" class="form-select" onchange="filterDeliveries()">
                            <option value="">All Statuses</option>
                            <option value="ready_for_pickup">Ready for Pickup</option>
                            <option value="picked_up">Picked Up</option>
                            <option value="in_transit">In Transit</option>
                            <option value="delivered">Delivered</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Region</label>
                        <select id="regionFilter" class="form-select" onchange="filterDeliveries()">
                            <option value="">All Regions</option>
                            <option value="Greater Accra">Greater Accra</option>
                            <option value="Ashanti Region">Ashanti Region</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vehicle Type</label>
                        <select id="vehicleFilter" class="form-select" onchange="filterDeliveries()">
                            <option value="">All Vehicles</option>
                            <option value="motorbike">Motorbike</option>
                            <option value="truck">Truck</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deliveries List -->
        <?php if (empty($deliveries)): ?>
            <div class="section-card-modern">
                <div class="section-body-modern">
                    <div class="empty-state-modern">
                        <i class="bi bi-inbox"></i>
                        <p>No deliveries found.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="deliveries-list">
                <?php foreach ($deliveries as $delivery): ?>
                    <div class="section-card-modern delivery-card" 
                         data-status="<?= $delivery['status'] ?>" 
                         data-region="<?= $delivery['region'] ?>"
                         data-vehicle="<?= $delivery['vehicle_type'] ?>"
                         style="margin-bottom: 1.5rem;">
                        <div class="section-header-modern">
                            <h3 class="section-title-modern">
                                <i class="bi bi-truck"></i>
                                Delivery #<?= $delivery['id'] ?> - Order #<?= $delivery['order_id'] ?>
                            </h3>
                            <span class="badge" style="background: <?= $delivery['vehicle_type'] === 'truck' ? '#F59E0B' : '#3B82F6' ?>; color: white;">
                                <?= $delivery['vehicle_type'] === 'truck' ? '🚚 Truck' : '🏍️ Motorbike' ?>
                            </span>
                        </div>
                        <div class="section-body-modern">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <strong>Customer:</strong> <?= \App\View::e($delivery['buyer_name'] ?? 'N/A') ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Phone:</strong> <?= \App\View::e($delivery['phone'] ?? 'N/A') ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Address:</strong><br>
                                        <?= \App\View::e($delivery['street']) ?>,<br>
                                        <?= \App\View::e($delivery['city']) ?>, <?= \App\View::e($delivery['region']) ?>
                                    </div>
                                    <?php if ($delivery['delivery_lat'] && $delivery['delivery_lng']): ?>
                                        <div class="mb-3">
                                            <a href="https://www.google.com/maps?q=<?= $delivery['delivery_lat'] ?>,<?= $delivery['delivery_lng'] ?>" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-geo-alt"></i> View on Map
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <strong>Supplier:</strong> <?= \App\View::e($delivery['supplier_name'] ?? 'N/A') ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Current Status:</strong><br>
                                        <span class="item-badge-modern <?= str_replace('_', '-', $delivery['status']) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $delivery['status'])) ?>
                                        </span>
                                    </div>
                                    <?php if ($delivery['delivery_code']): ?>
                                        <div class="mb-3">
                                            <strong>Delivery Code:</strong><br>
                                            <code style="font-size: 1.5rem; font-weight: bold; color: #8B4513; letter-spacing: 4px;">
                                                <?= $delivery['delivery_code'] ?>
                                            </code>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="mt-4 pt-4 border-top">
                                <?php if ($delivery['status'] === 'ready_for_pickup'): ?>
                                    <button class="btn btn-primary" onclick="updateDeliveryStatus(<?= $delivery['id'] ?>, 'picked_up')">
                                        <i class="bi bi-box-seam"></i> Mark as Picked Up
                                    </button>
                                    <textarea id="notes-<?= $delivery['id'] ?>" 
                                              class="form-control mt-2" 
                                              rows="2" 
                                              placeholder="Optional notes..."></textarea>
                                
                                <?php elseif ($delivery['status'] === 'picked_up'): ?>
                                    <button class="btn btn-primary" onclick="updateDeliveryStatus(<?= $delivery['id'] ?>, 'in_transit')">
                                        <i class="bi bi-truck"></i> Mark as In Transit
                                    </button>
                                    <p class="text-muted mt-2 small">This will generate and send a delivery code to the buyer</p>
                                    <textarea id="notes-<?= $delivery['id'] ?>" 
                                              class="form-control mt-2" 
                                              rows="2" 
                                              placeholder="Optional notes..."></textarea>
                                
                                <?php elseif ($delivery['status'] === 'in_transit'): ?>
                                    <div class="alert alert-info">
                                        <strong>⏳ Awaiting buyer confirmation</strong><br>
                                        Delivery Code: <code style="font-size: 1.25rem; font-weight: bold;"><?= $delivery['delivery_code'] ?></code>
                                    </div>
                                    <p class="text-muted small mb-3">Or mark as delivered with photo proof:</p>
                                    <form onsubmit="markDeliveredWithPhoto(event, <?= $delivery['id'] ?>)" class="d-inline">
                                        <input type="file" 
                                               name="delivery_photo" 
                                               accept="image/*" 
                                               required
                                               class="form-control d-inline-block" 
                                               style="width: auto; max-width: 300px;">
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-camera"></i> Mark Delivered (with photo)
                                        </button>
                                    </form>
                                    <button class="btn btn-danger ms-2" onclick="updateDeliveryStatus(<?= $delivery['id'] ?>, 'failed')">
                                        <i class="bi bi-x-circle"></i> Mark as Failed
                                    </button>
                                
                                <?php elseif ($delivery['status'] === 'delivered'): ?>
                                    <div class="alert alert-success">
                                        <strong>✓ Delivered</strong> on <?= date('M d, Y H:i', strtotime($delivery['delivered_at'] ?? $delivery['updated_at'])) ?>
                                    </div>
                                    <?php if ($delivery['confirmed_by_buyer'] ?? 0): ?>
                                        <span class="badge bg-success">✓ Confirmed by buyer</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">⏳ Awaiting buyer confirmation</span>
                                    <?php endif; ?>
                                    <?php if ($delivery['delivery_photo']): ?>
                                        <div class="mt-2">
                                            <a href="<?= \App\View::url('/storage/uploads/deliveries/<?= $delivery['delivery_photo'] ?>') ?>" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-image"></i> View Delivery Photo
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                
                                <?php elseif ($delivery['status'] === 'failed'): ?>
                                    <div class="alert alert-danger">
                                        <strong>❌ Delivery Failed</strong>
                                        <?php if ($delivery['admin_notes']): ?>
                                            <br>Reason: <?= \App\View::e($delivery['admin_notes']) ?>
                                        <?php endif; ?>
                                    </div>
                                    <button class="btn btn-primary" onclick="updateDeliveryStatus(<?= $delivery['id'] ?>, 'picked_up')">
                                        <i class="bi bi-arrow-clockwise"></i> Retry Delivery
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateDeliveryStatus(deliveryId, newStatus) {
    const notes = document.getElementById('notes-' + deliveryId)?.value || '';
    
    let confirmMsg = '';
    if (newStatus === 'picked_up') {
        confirmMsg = 'Confirm that you have picked up this order from the supplier?';
    } else if (newStatus === 'in_transit') {
        confirmMsg = 'Mark as in transit? A delivery code will be sent to the buyer.';
    } else if (newStatus === 'failed') {
        const reason = prompt('Please enter the reason for failure:');
        if (!reason) return;
        updateDeliveryStatusAjax(deliveryId, newStatus, reason);
        return;
    }
    
    if (!confirm(confirmMsg)) return;
    
    updateDeliveryStatusAjax(deliveryId, newStatus, notes);
}

function updateDeliveryStatusAjax(deliveryId, newStatus, notes) {
    const btn = event.target;
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    fetch('/build_mate/admin/update-delivery-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            delivery_id: deliveryId,
            status: newStatus,
            notes: notes
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✓ Status updated successfully' + (data.delivery_code ? '\nDelivery Code: ' + data.delivery_code : ''));
            location.reload();
        } else {
            alert('✗ ' + data.message);
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('✗ Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function markDeliveredWithPhoto(event, deliveryId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    formData.append('delivery_id', deliveryId);
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    formData.append('csrf_token', csrfToken);
    
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Uploading...';
    
    fetch('/build_mate/admin/mark-delivered-with-photo', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✓ Marked as delivered with photo proof');
            location.reload();
        } else {
            alert('✗ ' + data.message);
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('✗ Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function filterDeliveries() {
    const statusFilter = document.getElementById('statusFilter').value;
    const regionFilter = document.getElementById('regionFilter').value;
    const vehicleFilter = document.getElementById('vehicleFilter').value;
    
    document.querySelectorAll('.delivery-card').forEach(card => {
        const status = card.dataset.status;
        const region = card.dataset.region;
        const vehicle = card.dataset.vehicle;
        
        const statusMatch = !statusFilter || status === statusFilter;
        const regionMatch = !regionFilter || region === regionFilter;
        const vehicleMatch = !vehicleFilter || vehicle === vehicleFilter;
        
        card.style.display = (statusMatch && regionMatch && vehicleMatch) ? 'block' : 'none';
    });
}
</script>





