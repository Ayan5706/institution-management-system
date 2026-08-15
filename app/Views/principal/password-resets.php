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
        .table-panel {
            width: 100%;
            margin-top: 18px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
        }

        .table-view-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }

        .table-summary {
            color: #475569;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .filter-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            width: 100%;
        }

        .filter-input {
            width: 100%;
            max-width: 420px;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #0f172a;
            font-size: 0.95rem;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 10px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        th,
        td {
            padding: 16px 18px;
            border-bottom: 1px solid #edf2f7;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 0.9rem;
        }

        td {
            color: #334155;
            font-size: 0.93rem;
        }

        tr:hover {
            background: #f8fafc;
        }

        .login-email {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .login-email strong {
            color: #0f172a;
        }

        .date-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: #eef2ff;
            color: #4338ca;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-approve,
        .btn-reject {
            border: none;
            border-radius: 12px;
            padding: 10px 16px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.88rem;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .btn-approve {
            background: #10b981;
            color: #fff;
        }

        .btn-approve:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.18);
        }

        .btn-reject {
            background: #ef4444;
            color: #fff;
        }

        .btn-reject:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.18);
        }

        .notice-banner {
            padding: 14px 16px;
            border-radius: 12px;
            margin: 16px 0;
            font-size: 0.95rem;
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

        .empty-message {
            padding: 56px 24px;
            text-align: center;
        }

        .empty-message p {
            margin: 8px 0;
            color: #64748b;
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
            width: min(520px, 92vw);
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 24px 40px rgba(15, 23, 42, 0.14);
        }

        .modal-title {
            margin: 0 0 12px;
            font-size: 1.1rem;
            color: #0f172a;
        }

        .modal-text {
            margin: 0 0 18px;
            color: #475569;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
    </style>

    <div class="table-panel">
        <div class="table-view-header">
            <div class="filter-bar">
                <input id="resetSearch" class="filter-input" type="text" placeholder="Search user, email, or role...">
            </div>
            <div class="table-summary" id="requestTotalCount"><?php echo e(count($pending_resets)); ?> total</div>
        </div>

        <div id="resetMessage" class="notice-banner"></div>

        <div class="table-container" id="resetsContainer">
        <div class="empty-message">
            <div style="font-size: 1.9rem; margin-bottom: 12px;">📋</div>
            <p style="font-weight: 700; color: #0f172a;">Loading requests...</p>
            <p>No pending password reset requests found.</p>
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

<div id="tempPasswordModal" class="modal-backdrop" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="tempTitle">
        <h3 class="modal-title" id="tempTitle">Temporary Password</h3>
        <p class="modal-text" id="tempText">Use this temporary password to notify the user.</p>
        <div style="display:flex; gap:8px; align-items:center; margin-bottom:12px;">
            <input id="tempPasswordField" type="text" readonly style="flex:1; padding:8px; border:1px solid #e2e8f0; border-radius:6px; font-weight:700;" />
            <button type="button" class="btn-action btn-approve" id="copyTempBtn">Copy</button>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-action btn-reject" onclick="closeTempModal()">Close</button>
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
            renderTable(allResets);
        }

        loadPasswordResets();
        document.getElementById('resetSearch')?.addEventListener('input', filterResets);
    });

    function loadPasswordResets() {
        fetch('<?php echo e(url('api/principal/password-resets')); ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                allResets = data.data;
                renderTable(allResets);
            } else {
                renderEmpty();
            }
        })
        .catch(() => renderEmpty());
    }

    function renderTable(resets) {
        const container = document.getElementById('resetsContainer');
        const totalCount = document.getElementById('requestTotalCount');
        if (!resets || resets.length === 0) {
            renderEmpty();
            return;
        }

        updateBadge(resets.length);
        if (totalCount) {
            totalCount.textContent = `${resets.length} total`;
        }
        container.innerHTML = `
            <table>
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Login / Email</th>
                        <th>Role</th>
                        <th>Requested Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="resetTableBody">
                    ${resets.map(reset => `
                        <tr id="row-${reset.id}" data-search="${escapeHtml((reset.user_name || '') + ' ' + (reset.user_email || '') + ' ' + (reset.user_role || ''))}">
                            <td>${escapeHtml(reset.user_name || 'N/A')}</td>
                            <td class="login-email">
                                <strong>${escapeHtml(reset.login_id || reset.user_email || 'N/A')}</strong>
                                <span>${escapeHtml(reset.user_email || 'N/A')}</span>
                            </td>
                            <td>${escapeHtml((reset.user_role || 'Unknown').toUpperCase())}</td>
                            <td><span class="date-badge">${formatDate(reset.created_at)}</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" class="btn-approve" onclick="confirmApprove(${reset.id})">Approve</button>
                                    <button type="button" class="btn-reject" onclick="confirmReject(${reset.id})">Reject</button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }

    function renderEmpty() {
        updateBadge(0);
        const container = document.getElementById('resetsContainer');
        container.innerHTML = `
            <div class="empty-message">
                <div style="font-size: 1.9rem; margin-bottom: 12px;">✓</div>
                <p style="font-weight: 700; color: #0f172a;">No Pending Requests</p>
                <p>All password reset requests have been processed.</p>
            </div>
        `;
    }

    function filterResets() {
        const query = (document.getElementById('resetSearch')?.value || '').toLowerCase().trim();
        const filtered = allResets.filter(reset => {
            const text = `${reset.user_name || ''} ${reset.login_id || reset.user_email || ''} ${reset.user_email || ''} ${reset.user_role || ''}`.toLowerCase();
            return query === '' || text.includes(query);
        });

        renderTable(filtered);
    }

    function showNoResultsRow() {
        const tbody = document.getElementById('resetTableBody');
        if (!tbody || tbody.querySelector('.no-results-row')) {
            return;
        }
        const row = document.createElement('tr');
        row.className = 'no-results-row';
        row.innerHTML = `
            <td colspan="5" style="padding: 24px 18px; text-align: center; color: #64748b;">
                No matching requests found.
            </td>
        `;
        tbody.appendChild(row);
    }

    function clearNoResultsRow() {
        const row = document.querySelector('#resetTableBody .no-results-row');
        if (row) {
            row.remove();
        }
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
                // Show persistent modal with temporary password so principal can copy it
                const temp = (data.data && data.data.temp_password) ? data.data.temp_password : (data.temporary_password || '');
                const email = (data.data && data.data.user_email) ? data.data.user_email : (data.user_email || '');
                if (temp !== '') {
                    showTempPasswordModal(temp, email);
                } else {
                    showMessage('Reset approved.', 'success');
                }
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
        const badge = document.querySelector('.toolbar .widget-pill');
        if (badge) {
            badge.textContent = `${count} Pending`;
        }
    }

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        if (Number.isNaN(date.getTime())) {
            return 'Unknown';
        }
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function showEmptyState() {
        const container = document.getElementById('resetsContainer');
        container.innerHTML = `
            <div class="empty-message">
                <div style="font-size: 1.9rem; margin-bottom: 12px;">✓</div>
                <p style="font-weight: 700; color: #0f172a;">No Pending Requests</p>
                <p>All password reset requests have been processed.</p>
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

    // Temporary password modal helpers
    function showTempPasswordModal(password, email) {
        const modal = document.getElementById('tempPasswordModal');
        const field = document.getElementById('tempPasswordField');
        const text = document.getElementById('tempText');
        const copyBtn = document.getElementById('copyTempBtn');

        field.value = password;
        text.textContent = email ? `Temporary password for ${email}:` : 'Use this temporary password to notify the user.';
        copyBtn.onclick = () => {
            try {
                field.select();
                document.execCommand('copy');
                copyBtn.textContent = 'Copied';
                setTimeout(() => { copyBtn.textContent = 'Copy'; }, 2000);
            } catch (e) {
                // fallback
                navigator.clipboard?.writeText(password).then(() => {
                    copyBtn.textContent = 'Copied';
                    setTimeout(() => { copyBtn.textContent = 'Copy'; }, 2000);
                }).catch(() => {});
            }
        };

        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeTempModal() {
        const modal = document.getElementById('tempPasswordModal');
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
    }
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
