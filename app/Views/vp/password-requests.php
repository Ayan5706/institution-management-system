<?php
/** @var array $pending_resets */
$activeNav = 'password-requests';
$pending_resets = $pending_resets ?? [];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Password Requests</h2>
            <div style="color:#64748b;">Manage teacher password reset requests</div>
        </div>
    </div>

    <div class="table-view-header">
        <div class="filter-bar table-view-controls">
            <input type="text" id="requestSearch" class="filter-input table-view-field" placeholder="Search teacher or login...">
        </div>
        <div class="table-view-meta" id="requestsMeta"></div>
    </div>

    <style>
        .filter-bar {
            display: flex;
            gap: 12px;
            margin: 10px 0 18px;
            flex-wrap: nowrap;
            align-items: center;
            overflow-x: auto;
        }

        .filter-input {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
            background: #fff;
            flex: 1;
            min-width: 140px;
            cursor: text;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        th {
            background: #f8fafc;
            padding: 14px;
            font-weight: 700;
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
        }

        tr:hover {
            background: #f8fafc;
        }

        .empty-message {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-approve {
            background: #10b981;
            color: #fff;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .btn-approve:hover {
            background: #059669;
        }

        .btn-reject {
            background: #ef4444;
            color: #fff;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .btn-reject:hover {
            background: #dc2626;
        }

        .success-notification {
            margin-bottom: 15px;
            padding: 12px 16px;
            background: #d1fae5;
            border-left: 4px solid #10b981;
            border-radius: 4px;
            color: #065f46;
            font-weight: 500;
        }

        .error-notification {
            margin-bottom: 15px;
            padding: 12px 16px;
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            border-radius: 4px;
            color: #991b1b;
            font-weight: 500;
        }

        .date-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            background: #ede9fe;
            color: #6b21a8;
        }

        .notice-banner {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 15px;
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

    <div id="notification" class="notice-banner"></div>

    <div class="table-container">
        <?php if (empty($pending_resets)): ?>
            <div class="empty-message">
                <p>✓ No pending password reset requests.</p>
                <p style="font-size: 0.9rem; color: #94a3b8;">All teacher password resets have been processed.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Teacher Name</th>
                        <th>Staff/Login ID</th>
                        <th>Email</th>
                        <th>Requested Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="requestsTableBody">
                    <?php foreach ($pending_resets as $request): ?>
                        <tr id="row-<?php echo e($request['id']); ?>"
                            data-name="<?php echo e($request['full_name'] ?? ''); ?>"
                            data-login="<?php echo e($request['login_id'] ?? ''); ?>"
                            data-email="<?php echo e($request['email'] ?? ''); ?>"
                            data-search="<?php echo e(trim(($request['full_name'] ?? '') . ' ' . ($request['login_id'] ?? '') . ' ' . ($request['email'] ?? ''))); ?>">
                            <td><?php echo e($request['full_name'] ?? 'N/A'); ?></td>
                            <td><?php echo e($request['login_id'] ?? 'N/A'); ?></td>
                            <td><?php echo e($request['email'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="date-badge"><?php echo e(date('M d, Y H:i', strtotime($request['created_at'] ?? 'now'))); ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button onclick="approveRequest(<?php echo (int) ($request['id'] ?? 0); ?>, <?php echo json_encode($request['full_name'] ?? ''); ?>)" class="btn-approve">Approve</button>
                                    <button onclick="rejectRequest(<?php echo (int) ($request['id'] ?? 0); ?>, <?php echo json_encode($request['full_name'] ?? ''); ?>)" class="btn-reject">Reject</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if (!empty($pending_resets)): ?>
        <div class="table-view-pagination" id="requestsPager" style="margin-top: 14px;">
            <div class="pagination-info" id="requestsPageInfo"></div>
            <div class="pagination-actions">
                <button type="button" class="btn btn-ghost" id="requestsPrev">Previous</button>
                <button type="button" class="btn btn-ghost" id="requestsNext">Next</button>
            </div>
        </div>
    <?php endif; ?>

    <div id="confirmModal" class="modal-backdrop" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
            <h3 class="modal-title" id="confirmTitle">Confirm Action</h3>
            <p class="modal-text" id="confirmText">Are you sure you want to continue?</p>
            <div class="modal-actions">
                <button type="button" class="btn-reject" onclick="closeConfirmModal()">Cancel</button>
                <button type="button" class="btn-approve" id="confirmButton">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        window.IMS?.initTableView({
            tbodyId: 'requestsTableBody',
            searchInputId: 'requestSearch',
            metaId: 'requestsMeta',
            pagerId: 'requestsPager',
            pageInfoId: 'requestsPageInfo',
            prevId: 'requestsPrev',
            nextId: 'requestsNext',
            pageSize: 10,
            noResultsColSpan: 5,
            noResultsText: 'No matching requests found.',
        });

        function approveRequest(id, name) {
            openConfirmModal(
                'Approve Request',
                'Approve password reset request for ' + name + '? A temporary password will be generated.',
                () => {
                    fetch('<?php echo e(url('vp/password-requests')); ?>/' + id + '/approve', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage('✓ ' + (data.message || 'Password reset approved') + (data.temp_password ? ' Temporary password: ' + data.temp_password : ''), 'success');
                            const row = document.getElementById('row-' + id);
                            if (row) setTimeout(() => row.remove(), 2000);
                            setTimeout(() => location.reload(), 2500);
                        } else {
                            showMessage('✗ ' + (data.message || 'Error approving request'), 'error');
                        }
                    });
                }
            );
        }

        function rejectRequest(id, name) {
            openConfirmModal(
                'Reject Request',
                'Reject password reset request for ' + name + '?',
                () => {
                    fetch('<?php echo e(url('vp/password-requests')); ?>/' + id + '/reject', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage('✓ ' + (data.message || 'Password reset rejected'), 'success');
                            const row = document.getElementById('row-' + id);
                            if (row) setTimeout(() => row.remove(), 2000);
                            setTimeout(() => location.reload(), 2500);
                        } else {
                            showMessage('✗ ' + (data.message || 'Error rejecting request'), 'error');
                        }
                    });
                }
            );
        }

        function showMessage(message, type) {
            const notif = document.getElementById('notification');
            if (!notif) return;

            notif.textContent = message;
            notif.classList.remove('success', 'error');
            notif.classList.add(type === 'error' ? 'error' : 'success');
            notif.style.display = 'block';

            if (type !== 'error') {
                setTimeout(() => {
                    notif.style.display = 'none';
                }, 3000);
            }
            notif.scrollIntoView({ behavior: 'smooth' });
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

        filterRequests();
    </script>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
