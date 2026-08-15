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
            max-width: 360px;
            padding: 12px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            color: #0f172a;
            font-size: 0.95rem;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            min-width: 720px;
        }

        th,
        td {
            padding: 16px 18px;
            text-align: left;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
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

        .date-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: #ede9fe;
            color: #6b21a8;
            font-size: 0.84rem;
            font-weight: 700;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn,
        .btn-approve,
        .btn-reject {
            border: none;
            border-radius: 10px;
            padding: 10px 16px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.88rem;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .btn {
            background: #ef4444;
            color: #fff;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.18);
        }

        .btn-approve {
            background: #047857;
            color: #fff;
        }

        .btn-approve:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(5, 150, 105, 0.18);
        }

        .btn-reject {
            background: #ef4444;
            color: #fff;
        }

        .btn-reject:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.18);
        }

        .empty-state {
            text-align: center;
            padding: 56px 24px;
        }

        .empty-state-icon {
            font-size: 2.5rem;
            margin-bottom: 12px;
        }

        .empty-state-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #0f172a;
        }

        .empty-state-text {
            color: #64748b;
            font-size: 0.95rem;
        }

        .notice-banner {
            padding: 12px 16px;
            border-radius: 10px;
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

        @media (max-width: 900px) {
            .table-view-header {
                flex-direction: column;
                align-items: stretch;
            }

            th,
            td {
                padding: 12px 10px;
            }

            .filter-input {
                max-width: 100%;
            }
        }
    </style>

    <div id="resetMessage" class="notice-banner"></div>

    <div class="table-panel">
        <div class="table-view-header">
            <div class="filter-bar">
                <input type="text" id="resetSearch" class="filter-input" placeholder="Search student or login...">
            </div>
            <div class="table-summary" id="requestTotalCount">0 total</div>
        </div>

        <div class="table-container" id="resetsContainer">
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <div class="empty-state-title">Loading requests...</div>
            </div>
        </div>
    </div>
</div>

<div id="confirmModal" class="modal-backdrop" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
        <h3 class="modal-title" id="confirmTitle">Confirm Action</h3>
        <p class="modal-text" id="confirmText">Are you sure you want to continue?</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-reject" onclick="closeConfirmModal()">Cancel</button>
            <button type="button" class="btn btn-approve" id="confirmButton">Confirm</button>
        </div>
    </div>
</div>

<script>
    let allResets = [];
    let pendingAction = null;

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('resetSearch')?.addEventListener('input', filterResets);
        loadResetRequests();
    });

    function loadResetRequests() {
        fetch('<?php echo e(url('api/manager/reset-requests')); ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && Array.isArray(data.data)) {
                allResets = data.data;
                filterResets();
            } else {
                renderEmpty();
            }
        })
        .catch(() => renderEmpty());
    }

    function renderTable(resets) {
        const container = document.getElementById('resetsContainer');
        const totalCount = document.getElementById('requestTotalCount');
        const count = Array.isArray(resets) ? resets.length : 0;

        if (count === 0) {
            renderEmpty();
            return;
        }

        totalCount.textContent = `${count} total`;
        updateBadge(count);

        container.innerHTML = `
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Email</th>
                        <th>Requested Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${resets.map(reset => `
                        <tr id="row-${reset.id}" data-search="${escapeHtml((reset.user_name || '') + ' ' + (reset.login_id || '') + ' ' + (reset.user_email || ''))}">
                            <td>${escapeHtml(reset.user_name || 'N/A')}</td>
                            <td>${escapeHtml(reset.login_id || 'N/A')}</td>
                            <td>${escapeHtml(reset.user_email || 'N/A')}</td>
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

    function filterResets() {
        const query = (document.getElementById('resetSearch')?.value || '').toLowerCase().trim();
        const filtered = allResets.filter(reset => {
            const text = `${reset.user_name || ''} ${reset.login_id || ''} ${reset.user_email || ''}`.toLowerCase();
            return query === '' || text.includes(query);
        });

        renderTable(filtered);
    }

    function approveReset(resetId) {
        fetch('<?php echo e(url('api/manager/reset-requests')); ?>/' + resetId + '/approve', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message || 'Password reset approved.', 'success');
                loadResetRequests();
            } else {
                showMessage(data.error || data.message || 'Failed to approve reset.', 'error');
            }
        })
        .catch(error => showMessage(error.message, 'error'));
    }

    function rejectReset(resetId) {
        fetch('<?php echo e(url('api/manager/reset-requests')); ?>/' + resetId + '/reject', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message || 'Password reset request dismissed.', 'success');
                loadResetRequests();
            } else {
                showMessage(data.error || data.message || 'Failed to reject reset.', 'error');
            }
        })
        .catch(error => showMessage(error.message, 'error'));
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

    function renderEmpty() {
        document.getElementById('resetsContainer').innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">✓</div>
                <div class="empty-state-title">No Pending Requests</div>
                <div class="empty-state-text">All password reset requests have been processed.</div>
            </div>
        `;
        document.getElementById('requestTotalCount').textContent = '0 total';
        updateBadge(0);
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
            setTimeout(() => banner.style.display = 'none', 3000);
        }
    }

    function confirmApprove(resetId) {
        openConfirmModal('Approve Reset', 'Approve this reset request? A temporary password will be generated.', () => approveReset(resetId));
    }

    function confirmReject(resetId) {
        openConfirmModal('Reject Request', 'Reject this reset request?', () => rejectReset(resetId));
    }

    function openConfirmModal(title, text, action) {
        const modal = document.getElementById('confirmModal');
        const titleEl = document.getElementById('confirmTitle');
        const textEl = document.getElementById('confirmText');
        const confirmBtn = document.getElementById('confirmButton');

        pendingAction = action;
        titleEl.textContent = title;
        textEl.textContent = text;
        confirmBtn.onclick = () => {
            if (pendingAction) pendingAction();
            closeConfirmModal();
        };
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeConfirmModal() {
        const modal = document.getElementById('confirmModal');
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        pendingAction = null;
    }
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
