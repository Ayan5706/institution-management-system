<?php
/** @var array $pending_requests */
$activeNav = 'email-requests';
$pending_requests = $pending_requests ?? [];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Email Change Requests</h2>
            <div style="color:#64748b;">Review and approve student email change requests</div>
        </div>
        <div>
            <span class="widget-pill">
                <?php echo e(count($pending_requests)); ?> Pending
            </span>
        </div>
    </div>

    <style>
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 16px;
        }

        .empty-state-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #0f172a;
        }

        .empty-state-text {
            color: #64748b;
            margin-bottom: 20px;
        }

        .request-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .request-user-info {
            flex: 1;
        }

        .request-user-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .request-user-details {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 2px;
        }

        .request-timestamp {
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .request-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-action {
            padding: 10px 18px;
            border-radius: 8px;
            border: 0;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .btn-approve {
            background: #10b981;
            color: #fff;
        }

        .btn-approve:hover {
            background: #059669;
        }

        .btn-reject {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-reject:hover {
            background: #d1d5db;
        }

        .notice-banner {
            padding: 12px 16px;
            border-radius: 8px;
            margin: 16px 0;
            font-size: 0.9rem;
            display: none;
        }

        .notice-success {
            background: #dcfce7;
            color: #166534;
        }

        .notice-error {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>

    <div id="notice" class="notice-banner"></div>

    <?php if (empty($pending_requests)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">@</div>
            <div class="empty-state-title">All caught up</div>
            <div class="empty-state-text">All email change requests have been processed.</div>
        </div>
    <?php else: ?>
        <div id="requests-container">
            <?php foreach ($pending_requests as $request): ?>
                <div class="request-card" data-request-id="<?php echo e($request['id']); ?>">
                    <div class="request-header">
                        <div class="request-user-info">
                            <div class="request-user-name">Student</div>
                            <div class="request-user-details">Request ID: <?php echo e($request['id']); ?></div>
                            <div class="request-user-details">New Email: <?php echo e($request['new_email'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="request-timestamp">
                            <?php echo e(date('M d, Y', strtotime($request['created_at'] ?? 'now'))); ?>
                        </div>
                    </div>
                    <div class="request-actions">
                        <button class="btn-action btn-approve" data-action="approve" data-id="<?php echo e($request['id']); ?>">Approve</button>
                        <button class="btn-action btn-reject" data-action="reject" data-id="<?php echo e($request['id']); ?>">Reject</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    const noticeEl = document.getElementById('notice');
    const requestsContainer = document.getElementById('requests-container');

    function showNotice(message, type) {
        if (!noticeEl) return;
        noticeEl.textContent = message;
        noticeEl.classList.remove('notice-success', 'notice-error');
        noticeEl.classList.add(type === 'success' ? 'notice-success' : 'notice-error');
        noticeEl.style.display = 'block';
        setTimeout(() => {
            noticeEl.style.display = 'none';
        }, 3500);
    }

    async function handleAction(button) {
        const action = button.getAttribute('data-action');
        const id = button.getAttribute('data-id');
        if (!action || !id) return;

        try {
            const response = await fetch(`<?php echo e(url('manager/email-requests')); ?>/${id}/${action}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const result = await response.json();

            if (result.success) {
                showNotice(result.message || 'Request updated.', 'success');
                const card = document.querySelector(`.request-card[data-request-id="${id}"]`);
                if (card) {
                    card.remove();
                }
                if (requestsContainer && !requestsContainer.children.length) {
                    location.reload();
                }
            } else {
                showNotice(result.message || 'Unable to update request.', 'error');
            }
        } catch (error) {
            showNotice('An error occurred. Please try again.', 'error');
        }
    }

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }
        if (target.matches('[data-action][data-id]')) {
            event.preventDefault();
            handleAction(target);
        }
    });

    async function refreshRequests() {
        try {
            const response = await fetch('<?php echo e(url('api/manager/email-requests')); ?>', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store',
            });
            const result = await response.json();

            if (!result.success) {
                return;
            }

            if (Array.isArray(result.data)) {
                const pending = result.data;
                if (!requestsContainer) {
                    return;
                }

                requestsContainer.innerHTML = '';
                if (!pending.length) {
                    location.reload();
                    return;
                }

                pending.forEach((request) => {
                    const card = document.createElement('div');
                    card.className = 'request-card';
                    card.dataset.requestId = String(request.id || '');

                    card.innerHTML = `
                        <div class="request-header">
                            <div class="request-user-info">
                                <div class="request-user-name">${request.user_name || 'Student'}</div>
                                <div class="request-user-details">Role: ${request.user_role || 'STUDENT'}</div>
                                <div class="request-user-details">Current Email: ${request.current_email || 'N/A'}</div>
                                <div class="request-user-details">New Email: ${request.requested_email || 'N/A'}</div>
                            </div>
                            <div class="request-timestamp">${request.created_at ? new Date(request.created_at).toLocaleDateString() : ''}</div>
                        </div>
                        <div class="request-actions">
                            <button class="btn-action btn-approve" data-action="approve" data-id="${request.id}">Approve</button>
                            <button class="btn-action btn-reject" data-action="reject" data-id="${request.id}">Reject</button>
                        </div>
                    `;

                    requestsContainer.appendChild(card);
                });
            }
        } catch (error) {
            console.log('[Email Requests] Refresh error', error);
        }
    }

    setInterval(refreshRequests, 15000);
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
