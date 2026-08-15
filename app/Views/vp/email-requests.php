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
            <div style="color:#64748b;">Review and approve teacher email change requests</div>
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

        .notice-banner.success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .notice-banner.error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            align-items: center;
            justify-content: center;
            z-index: 1200;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .modal-card {
            width: min(420px, 92vw);
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2);
        }

        .modal-title {
            margin: 0 0 8px;
            font-size: 1.1rem;
            color: #0f172a;
        }

        .modal-text {
            margin: 0 0 16px;
            color: #475569;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
    </style>

    <div id="emailChangeMessage" class="notice-banner"></div>

    <div id="requestsContainer">
        <div class="empty-state">
            <div class="empty-state-icon">✓</div>
            <div class="empty-state-title">Loading requests...</div>
        </div>
    </div>
</div>

<div id="confirmModal" class="modal-backdrop" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
        <h3 class="modal-title" id="confirmTitle">Confirm Action</h3>
        <p class="modal-text" id="confirmText">Are you sure you want to continue?</p>
        <div class="modal-actions">
            <button type="button" class="btn-action btn-reject" onclick="closeConfirmModal()">Cancel</button>
            <button type="button" class="btn-action btn-approve" id="confirmButton">Confirm</button>
        </div>
    </div>
</div>

<script>
    let allRequests = [];

    document.addEventListener('DOMContentLoaded', function() {
        loadEmailRequests();
    });

    function loadEmailRequests() {
        fetch('<?php echo e(url('api/vp/email-requests')); ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                allRequests = data.data;
                displayRequests(allRequests);
                updateBadge(allRequests.length);
            } else {
                showEmptyState();
            }
        })
        .catch(() => showEmptyState());
    }

    function displayRequests(requests) {
        const container = document.getElementById('requestsContainer');

        if (!requests || requests.length === 0) {
            showEmptyState();
            return;
        }

        container.innerHTML = `<div>${requests.map(request => `
            <div class="request-card">
                <div class="request-header">
                    <div class="request-user-info">
                        <div class="request-user-name">${escapeHtml(request.user_name || 'N/A')}</div>
                        <div class="request-user-details"><strong>Role:</strong> ${escapeHtml(request.user_role || 'N/A')}</div>
                        <div class="request-user-details"><strong>Current Email:</strong> ${escapeHtml(request.current_email || 'N/A')}</div>
                        <div class="request-user-details"><strong>Requested Email:</strong> ${escapeHtml(request.requested_email || 'N/A')}</div>
                        <div class="request-timestamp">Requested: ${escapeHtml(request.created_at || 'N/A')}</div>
                    </div>
                </div>
                <div class="request-actions">
                    <button class="btn-action btn-reject" onclick="confirmReject(${request.id})">Reject</button>
                    <button class="btn-action btn-approve" onclick="confirmApprove(${request.id})">Approve</button>
                </div>
            </div>
        `).join('')}</div>`;
    }

    function approveRequest(requestId) {
        fetch(`<?php echo e(url('vp/email-requests')); ?>/${requestId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.ok ? response.json() : Promise.reject(new Error('Failed to approve request')))
        .then(data => {
            if (data.success) {
                showMessage('Email change approved.', 'success');
                loadEmailRequests();
            } else {
                showMessage(data.message || 'Failed to approve request', 'error');
            }
        })
        .catch(error => showMessage(error.message, 'error'));
    }

    function rejectRequest(requestId) {
        fetch(`<?php echo e(url('vp/email-requests')); ?>/${requestId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.ok ? response.json() : Promise.reject(new Error('Failed to reject request')))
        .then(data => {
            if (data.success) {
                showMessage('Email change request rejected.', 'success');
                loadEmailRequests();
            } else {
                showMessage(data.message || 'Failed to reject request', 'error');
            }
        })
        .catch(error => showMessage(error.message, 'error'));
    }

    function updateBadge(count) {
        const badge = document.querySelector('.toolbar span');
        if (badge) {
            badge.textContent = `${count} Pending`;
        }
    }

    function showEmptyState() {
        const container = document.getElementById('requestsContainer');
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">✓</div>
                <div class="empty-state-title">No Pending Requests</div>
                <div class="empty-state-text">All email change requests have been processed.</div>
            </div>
        `;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showMessage(message, type) {
        const banner = document.getElementById('emailChangeMessage');
        if (!banner) return;

        banner.textContent = message;
        banner.classList.remove('success', 'error');
        banner.classList.add(type === 'error' ? 'error' : 'success');
        banner.style.display = 'block';

        if (type !== 'error') {
            setTimeout(() => {
                banner.style.display = 'none';
            }, 3000);
        }
    }

    function confirmApprove(requestId) {
        openConfirmModal(
            'Approve Email Change',
            'Approve this request? The user email will be updated immediately.',
            () => approveRequest(requestId)
        );
    }

    function confirmReject(requestId) {
        openConfirmModal(
            'Reject Email Change',
            'Reject this request?',
            () => rejectRequest(requestId)
        );
    }

    let confirmAction = null;

    function openConfirmModal(title, text, action) {
        const modal = document.getElementById('confirmModal');
        const titleEl = document.getElementById('confirmTitle');
        const textEl = document.getElementById('confirmText');
        const confirmBtn = document.getElementById('confirmButton');

        confirmAction = action;
        titleEl.textContent = title;
        textEl.textContent = text;
        confirmBtn.onclick = () => {
            if (confirmAction) {
                confirmAction();
            }
            closeConfirmModal();
        };
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeConfirmModal() {
        const modal = document.getElementById('confirmModal');
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        confirmAction = null;
    }
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
