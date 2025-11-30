<?php
/**
 * Admin Chat Management Dashboard
 */
$title = $title ?? 'Chat Management';
?>

<div class="container-fluid py-4">
    <div class="mb-3">
        <a href="/build_mate/admin/dashboard" class="back-button">
            <i class="bi bi-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-chat-dots"></i> Chat Management
        </h1>
        <button class="btn btn-primary" onclick="refreshSessions()">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>

    <?php if (isset($flash)): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
            <?= \App\View::e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-chat-dots fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Messages</h6>
                            <h3 class="mb-0"><?= number_format($stats['total_messages'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-people fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Sessions</h6>
                            <h3 class="mb-0"><?= number_format($stats['total_sessions'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-person fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Users</h6>
                            <h3 class="mb-0"><?= number_format($stats['total_users'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-speedometer2 fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Avg Response Time</h6>
                            <h3 class="mb-0"><?= number_format($stats['avg_response_time'] ?? 0) ?>ms</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Sessions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i> Active Chat Sessions
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Session ID</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Messages</th>
                            <th>Last Message</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sessions-table">
                        <?php if (empty($activeSessions)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No active sessions
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($activeSessions as $session): ?>
                                <tr>
                                    <td>
                                        <code class="small"><?= \App\View::e(substr($session['session_id'], 0, 20)) ?>...</code>
                                    </td>
                                    <td>
                                        <?php if ($session['user_id']): ?>
                                            User #<?= $session['user_id'] ?>
                                        <?php else: ?>
                                            <span class="text-muted">Guest</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($session['user_role']): ?>
                                            <span class="badge bg-secondary"><?= \App\View::e($session['user_role']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($session['message_count'] ?? 0) ?></td>
                                    <td>
                                        <?php if ($session['last_message_at']): ?>
                                            <?= date('M d, Y H:i', strtotime($session['last_message_at'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $session['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($session['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/build_mate/admin/chat/session/<?= urlencode($session['session_id']) ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function refreshSessions() {
    fetch('/build_mate/admin/chat/sessions')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error refreshing sessions:', error);
        });
}

// Auto-refresh every 30 seconds
setInterval(refreshSessions, 30000);
</script>

