<?php
$activeNav = 'password-resets';
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Student Password Reset Approvals</h2>
            <div style="color:#64748b;">Review and approve student password reset requests</div>
        </div>
        <div>
            <span class="widget-pill">
                <span id="pendingBadge">0</span> Pending
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
            margin-top: 8px;
        }

        .reset-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .btn-approve {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .btn-approve:hover {
            background: #a7f3d0;
        }

        .btn-reject {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .btn-reject:hover {
            background: #fca5a5;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .temp-password-section {
            padding: 12px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            margin-top: 12px;
            display: none;
        }

        .temp-password-section.show {
            display: block;
        }

        .temp-password-label {
            font-size: 0.9rem;
            color: #065f46;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .temp-password-value {
            font-family: 'Courier New', monospace;
            background: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #bbf7d0;
            color: #0f172a;
            font-weight: 700;
            font-size: 1.1rem;
            word-break: break-all;
        }

        .notes {
            margin-top: 8px;
            padding: 8px;
            background: #f0fdf4;
            border-radius: 6px;
            color: #065f46;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        .loader {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #065f46;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
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

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .reset-header {
                flex-direction: column;
                gap: 12px;
            }

            .reset-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>

    <div id="resetMessage" class="notice-banner"></div>

    <div class="filter-bar" style="display:flex; gap:12px; margin: 10px 0 18px; flex-wrap: wrap;">
        <input type="text" id="resetSearch" class="filter-input" placeholder="Search student or login..." style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
    </div>

    <div id="resetsContainer">
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <div class="empty-state-title">Loading requests...</div>
        </div>
    </div>
</div>

<div id="confirmModal" class="modal-backdrop" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
        <h3 class="modal-title" id="confirmTitle">Confirm Action</h3>
        <p class="modal-text" id="confirmText">Are you sure you want to continue?</p>
        <div class="modal-actions">
            <button type="button" class="btn" onclick="closeConfirmModal()">Cancel</button>
            <button type="button" class="btn btn-approve" id="confirmButton">Confirm</button>
        </div>
    </div>
</div>

<script>
    let allResets = [];

    document.addEventListener('DOMContentLoaded', loadResetRequests);
    document.getElementById('resetSearch')?.addEventListener('input', filterResets);

    function loadResetRequests() {
        fetch('<?php echo e(url('api/manager/reset-requests')); ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                allResets = data.data;
                filterResets();
            } else {
                showEmptyState();
            }
        })
        .catch(() => showEmptyState());
    }

    function displayResets(resets) {
        const container = document.getElementById('resetsContainer');
        
        if (resets.length === 0) {
            showEmptyState();
            return;
        }

        container.innerHTML = resets.map((reset, idx) => `
            <div class="reset-card urgent">
                <div class="reset-header">
                    <div class="reset-user-info">
                        <div class="reset-user-name">${escapeHtml(reset.full_name)}</div>
                        <div class="reset-user-details">
                            📧 Login: <strong>${escapeHtml(reset.login_id)}</strong>
                        </div>
                        <div class="reset-timestamp">
                            Requested: ${formatDate(reset.created_at)}
                        </div>
                    </div>
                    <div class="reset-actions">
                        <button class="btn btn-approve" onclick="approveReset(${reset.id}, ${idx}, this)">
                            ✓ Approve
                        </button>
                        <button class="btn btn-reject" onclick="rejectReset(${reset.id}, ${idx}, this)">
                            ✗ Reject
                        </button>
                    </div>
                </div>
                <div class="temp-password-section" id="tempPassword-${idx}">
                    <div class="temp-password-label">✓ Temporary Password Generated:</div>
                    <div class="temp-password-value" id="tempPasswordValue-${idx}"></div>
                    <div class="notes">
                        📝 Share this temporary password with the student. They must change it upon first login.
                    </div>
                </div>
            </div>
        `).join('');
    }

    function filterResets() {
        const search = (document.getElementById('resetSearch')?.value || '').toLowerCase();
        const filtered = allResets.filter(reset => {
            const text = `${reset.full_name || ''} ${reset.login_id || ''}`.toLowerCase();
            return search === '' || text.includes(search);
        });
        displayResets(filtered);
        document.getElementById('pendingBadge').textContent = filtered.length;
    }

    function approveReset(id, idx, btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="loader"></span>';

        fetch('<?php echo e(url('api/manager/reset-requests')); ?>/' + id + '/approve', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('tempPasswordValue-' + idx).textContent = data.temporary_password;
                document.getElementById('tempPassword-' + idx).classList.add('show');
                btn.innerHTML = '✓ Approved';
                btn.style.opacity = '0.6';
                // Disable both buttons for this card
                document.querySelectorAll(`#tempPassword-${idx}`).forEach(el => {
                    const card = el.closest('.reset-card');
                    card.querySelectorAll('.btn').forEach(b => b.disabled = true);
                });
            } else {
                showMessage('Error: ' + data.error, 'error');
                btn.disabled = false;
                btn.innerHTML = '✓ Approve';
            }
        })
        .catch(err => {
            showMessage('Error: ' + err.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '✓ Approve';
        });
    }

    function rejectReset(id, idx, btn) {
        openConfirmModal(
            'Reject Request',
            'Are you sure you want to reject this password reset request?',
            () => {
                btn.disabled = true;
                btn.innerHTML = '<span class="loader"></span>';

                fetch('<?php echo e(url('api/manager/reset-requests')); ?>/' + id + '/reject', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        event.target.closest('.reset-card').style.opacity = '0.5';
                        event.target.closest('.reset-card').style.textDecoration = 'line-through';
                        btn.innerHTML = '✗ Rejected';
                        const card = event.target.closest('.reset-card');
                        card.querySelectorAll('.btn').forEach(b => b.disabled = true);
                        showMessage('Reset request rejected.', 'success');
                    } else {
                        showMessage('Error: ' + data.error, 'error');
                        btn.disabled = false;
                        btn.innerHTML = '✗ Reject';
                    }
                })
                .catch(err => {
                    showMessage('Error: ' + err.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = '✗ Reject';
                });
            }
        );
    }

    function showEmptyState() {
        document.getElementById('resetsContainer').innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">✓</div>
                <div class="empty-state-title">All Set!</div>
                <div class="empty-state-text">No pending student password reset requests at this time.</div>
            </div>
        `;
        document.getElementById('pendingBadge').textContent = '0';
    }

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
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
