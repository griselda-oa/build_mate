<?php
/**
 * Admin Chat Session Detail View
 */
$title = $title ?? 'Chat Session Details';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-chat-dots"></i> Chat Session Details
            </h1>
            <p class="text-muted mb-0">
                Session: <code><?= \App\View::e($session['session_id'] ?? 'N/A') ?></code>
            </p>
        </div>
        <a href="<?= \App\View::url('/admin/chat') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Chat Management
        </a>
    </div>

    <?php if (isset($flash)): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
            <?= \App\View::e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Session Info -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Session Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Session ID:</dt>
                        <dd class="col-sm-8"><code><?= \App\View::e($session['session_id'] ?? 'N/A') ?></code></dd>
                        
                        <dt class="col-sm-4">User ID:</dt>
                        <dd class="col-sm-8">
                            <?php if ($session['user_id'] ?? null): ?>
                                User #<?= $session['user_id'] ?>
                            <?php else: ?>
                                <span class="text-muted">Guest</span>
                            <?php endif; ?>
                        </dd>
                        
                        <dt class="col-sm-4">User Role:</dt>
                        <dd class="col-sm-8">
                            <?php if ($session['user_role'] ?? null): ?>
                                <span class="badge bg-secondary"><?= \App\View::e($session['user_role']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </dd>
                        
                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-<?= ($session['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($session['status'] ?? 'active') ?>
                            </span>
                        </dd>
                        
                        <dt class="col-sm-4">Message Count:</dt>
                        <dd class="col-sm-8"><?= number_format($session['message_count'] ?? 0) ?></dd>
                        
                        <dt class="col-sm-4">Started:</dt>
                        <dd class="col-sm-8">
                            <?php if ($session['started_at'] ?? null): ?>
                                <?= date('M d, Y H:i:s', strtotime($session['started_at'])) ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </dd>
                        
                        <dt class="col-sm-4">Last Message:</dt>
                        <dd class="col-sm-8">
                            <?php if ($session['last_message_at'] ?? null): ?>
                                <?= date('M d, Y H:i:s', strtotime($session['last_message_at'])) ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Page Context</h5>
                </div>
                <div class="card-body">
                    <?php 
                    $context = json_decode($session['page_context'] ?? '{}', true);
                    if (!empty($context)): 
                    ?>
                        <dl class="row mb-0">
                            <?php foreach ($context as $key => $value): ?>
                                <dt class="col-sm-4"><?= ucfirst(str_replace('_', ' ', $key)) ?>:</dt>
                                <dd class="col-sm-8"><?= \App\View::e(is_array($value) ? json_encode($value) : $value) ?></dd>
                            <?php endforeach; ?>
                        </dl>
                    <?php else: ?>
                        <p class="text-muted mb-0">No context information available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Messages -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-chat-text"></i> Conversation History
            </h5>
        </div>
        <div class="card-body">
            <div class="chat-history-admin" style="max-height: 600px; overflow-y: auto; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <?php if (empty($messages)): ?>
                    <p class="text-center text-muted py-4">No messages in this session</p>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="mb-3 d-flex <?= $msg['role'] === 'user' ? 'justify-content-end' : 'justify-content-start' ?>">
                            <div class="message-admin <?= $msg['role'] === 'user' ? 'user-message' : 'bot-message' ?>" style="max-width: 70%;">
                                <div class="d-flex align-items-start gap-2">
                                    <?php if ($msg['role'] !== 'user'): ?>
                                        <div class="avatar-small bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">
                                            <i class="bi bi-robot"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <div class="message-bubble-admin <?= $msg['role'] === 'user' ? 'bg-primary text-white' : 'bg-white' ?> p-3 rounded shadow-sm">
                                            <p class="mb-1"><?= nl2br(\App\View::e($msg['message'])) ?></p>
                                            <small class="<?= $msg['role'] === 'user' ? 'text-white-50' : 'text-muted' ?>">
                                                <?= date('M d, Y H:i:s', strtotime($msg['created_at'])) ?>
                                            </small>
                                        </div>
                                        <?php if ($msg['role'] === 'assistant' && !empty($msg['ai_model'])): ?>
                                            <small class="text-muted">
                                                Model: <?= \App\View::e($msg['ai_model']) ?>
                                                <?php if ($msg['tokens_used'] ?? 0): ?>
                                                    | Tokens: <?= number_format($msg['tokens_used']) ?>
                                                <?php endif; ?>
                                                <?php if ($msg['response_time_ms'] ?? 0): ?>
                                                    | Time: <?= number_format($msg['response_time_ms']) ?>ms
                                                <?php endif; ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($msg['role'] === 'user'): ?>
                                        <div class="avatar-small bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.chat-history-admin::-webkit-scrollbar {
    width: 6px;
}

.chat-history-admin::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.chat-history-admin::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}
</style>





