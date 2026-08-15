<?php
/** @var array $pending_resets */
$activeNav = 'password-resets';
$pending_resets = $pending_resets ?? [];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Password Reset Approvals</h2>
            <div style="color:#64748b;">Review and approve password reset requests</div>
        </div>
        <div>
            <span class="widget-pill">
                <?php echo e(count($pending_resets)); ?> Pending
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

        .reset-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .reset-card.urgent {
            border-left: 4px solid #dc2626;
        }

        .reset-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .reset-user-info {
            flex: 1;
        }

        .reset-user-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .reset-user-details {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 2px;
        }

        .reset-timestamp {
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .reset-reason {
            background: #f8fafc;
            border-left: 3px solid #64748b;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            color: #475569;
            font-size: 0.95rem;
        }

        .reason-label {
            font-weight: 600;
            display: block;
            margin-bottom: 4px;
            color: #0f172a;
        }

        .reset-actions {
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

        .priority-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .priority-high {
            background: #fecaca;
            color: #7f1d1d;
        }

        .priority-medium {
            background: #fed7aa;
            color: #7c2d12;
        }

        .priority-low {
            background: #d1fae5;
            color: #064e3b;
        }

        .reset-status {
            font-size: 0.85rem;
            color: #64748b;
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

    <div id="resetMessage" class="notice-banner"></div>

    <div id="resetsContainer">
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
    let allResets = [];
    const serverResets = <?php echo json_encode($pending_resets); ?>;

    // Load resets on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (Array.isArray(serverResets) && serverResets.length > 0) {
            allResets = serverResets;
            displayResets(allResets);
            updateBadge(allResets.length);
        }

        loadPasswordResets();
    });

    function loadPasswordResets() {
        fetch('<?php echo e(url('api/principal/password-resets')); ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                allResets = data.data;
                displayResets(allResets);
                updateBadge(allResets.length);
            } else if (!allResets.length) {
                showEmptyState();
            }
        })
        .catch(error => {
            console.error('Error loading password resets:', error);
            if (!allResets.length) {
                showEmptyState();
            }
        });
    }

    function displayResets(resets) {
        const container = document.getElementById('resetsContainer');
        
        if (!resets || resets.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">✓</div>
                    <div class="empty-state-title">No Pending Requests</div>
                    <div class="empty-state-text">All password reset requests have been processed.</div>
                </div>
            `;
            return;
        }

        container.innerHTML = `<div>${resets.map(reset => `
            <div class="reset-card ${reset.is_admin_user ? 'urgent' : ''}">
                <div class="reset-header">
                    <div class="reset-user-info">
                        <div class="reset-user-name">${escapeHtml(reset.user_name || 'N/A')}</div>
                        <div class="reset-user-details">
                            <strong>Email:</strong> ${escapeHtml(reset.user_email || 'N/A')}
                        </div>
                        <div class="reset-user-details">
                            <strong>Role:</strong> ${escapeHtml((reset.user_role || 'Unknown').toUpperCase())}
                        </div>
                        <div class="reset-timestamp">
                            Requested: ${escapeHtml(reset.created_at || 'N/A')}
                        </div>
                    </div>
                </div>
                <div class="reset-actions">
                    <button class="btn-action btn-reject" onclick="confirmReject(${reset.id})">
                        Reject
                    </button>
                    <button class="btn-action btn-approve" onclick="confirmApprove(${reset.id})">
                        Approve & Send Reset
                    </button>
                </div>
            </div>
        `).join('')}</div>`;
    }


    function approveReset(resetId) {
        fetch(`<?php echo e(url('principal/password-resets')); ?>/${resetId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Failed to approve reset');
        })
        .then(data => {
            if (data.success) {
                showMessage(`Reset approved. Temporary password: ${data.data.temp_password} (User: ${data.data.user_email})`, 'success');
                loadPasswordResets();
            } else {
                showMessage(data.message || 'Failed to approve reset', 'error');
            }
        })
        .catch(error => {
            showMessage(error.message, 'error');
        });
    }

    function rejectReset(resetId) {
        fetch(`<?php echo e(url('principal/password-resets')); ?>/${resetId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Failed to reject reset');
        })
        .then(data => {
            if (data.success) {
                showMessage('Reset request rejected.', 'success');
                loadPasswordResets();
            } else {
                showMessage(data.message || 'Failed to reject reset', 'error');
            }
        })
        .catch(error => {
            showMessage(error.message, 'error');
        });
    }

    function updateBadge(count) {
        const badge = document.querySelector('.toolbar span[style*="background"]');
        if (badge) {
            badge.textContent = `${count} Pending`;
        }
    }

    function showEmptyState() {
        const container = document.getElementById('resetsContainer');
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">✓</div>
                <div class="empty-state-title">No Pending Requests</div>
                <div class="empty-state-text">All password reset requests have been processed.</div>
            </div>
        `;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showMessage(message, type) {
        const banner = document.getElementById('resetMessage');
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

    function confirmApprove(resetId) {
        openConfirmModal(
            'Approve Reset',
            'Approve this request? A temporary password will be sent to the user.',
            () => approveReset(resetId)
        );
    }

    function confirmReject(resetId) {
        openConfirmModal(
            'Reject Request',
            'Reject this request?',
            () => rejectReset(resetId)
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
