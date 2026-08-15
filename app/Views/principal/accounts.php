<?php
/** @var string $user_name */
/** @var array $admin_accounts */
$activeNav = 'accounts';
$user_name = $user_name ?? 'Principal';
$adminAccounts = $admin_accounts ?? [];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Manage Accounts</h2>
            <div style="color:#6c7b86;">Manage admin-level accounts</div>
        </div>
        <button class="add-btn" onclick="openAddAccountDrawer()">+ Add Account</button>
    </div>

    <style>
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            gap: 20px;
        }

        .add-btn {
            padding: 10px 16px;
            background: #2563eb;
            color: #fff;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: background 0.3s ease;
        }

        .add-btn:hover {
            background: #1d4ed8;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        thead tr {
            background: #f8fafc;
        }

        th {
            padding: 14px;
            text-align: left;
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

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 8px;
            background: #2563eb;
            color: #fff;
            border: 0;
            cursor: pointer;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s ease;
        }

        .action-btn:hover {
            background: #1d4ed8;
        }

        .action-btn.danger {
            background: #dc2626;
        }

        .action-btn.danger:hover {
            background: #b91c1c;
        }

        .empty-message {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .inline-message {
            padding: 12px 14px;
            border-radius: 8px;
            margin: 12px 0 18px;
            font-size: 0.9rem;
            display: none;
        }

        .inline-message.success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .inline-message.error {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            color: #7f1d1d;
        }

        .filter-bar {
            display: flex;
            gap: 12px;
            margin: 16px 0 20px;
            flex-wrap: nowrap;
            align-items: center;
            overflow-x: auto;
        }

        .filter-input,
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            font-size: 0.9rem;
            flex: 1;
            min-width: 150px;
        }

        .filter-input {
            cursor: text;
        }

        .filter-select {
            cursor: pointer;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1100;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.2);
            width: min(420px, 92vw);
            padding: 20px;
            border: 1px solid #e2e8f0;
        }

        .modal-title {
            margin: 0 0 8px;
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
        }

        .modal-text {
            margin: 0 0 16px;
            color: #475569;
            font-size: 0.95rem;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid transparent;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .modal-btn.confirm {
            background: #dc2626;
            color: #fff;
        }

        .modal-btn.cancel {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #e2e8f0;
        }

        /* Side Drawer Styles */
        .drawer-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 999;
            animation: fadeIn 0.3s ease;
        }

        .drawer-overlay.show {
            display: block;
        }

        .drawer {
            position: fixed;
            top: 0;
            right: 0;
            width: 400px;
            height: 100%;
            background: #fff;
            box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }

        .drawer.show {
            transform: translateX(0);
        }

        .drawer-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .drawer-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
        }

        .drawer-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
        }

        .drawer-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #0f172a;
            font-size: 0.95rem;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-input:disabled {
            background: #f8fafc;
            color: #64748b;
            cursor: not-allowed;
        }

        .drawer-footer {
            padding: 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
        }

        .btn-submit {
            flex: 1;
            padding: 10px 16px;
            background: #2563eb;
            color: #fff;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .btn-submit:hover {
            background: #1d4ed8;
        }

        .btn-submit:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        .btn-cancel {
            flex: 1;
            padding: 10px 16px;
            background: #f8fafc;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
        }

        .error-message {
            color: #991b1b;
            font-size: 0.85rem;
            margin-top: 4px;
        }

        .success-message {
            padding: 12px;
            background: #d1fae5;
            border: 1px solid #10b981;
            border-radius: 8px;
            color: #065f46;
            font-weight: 600;
            margin-bottom: 15px;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 768px) {
            .drawer {
                width: 100%;
            }

            .toolbar {
                flex-direction: column;
            }

            .add-btn {
                width: 100%;
            }
        }
    </style>

    <div id="accountMessage" class="inline-message"></div>

    <div class="table-view-header" style="margin-top: 6px;">
        <div class="filter-bar table-view-controls">
            <input type="text" id="accountSearch" class="filter-input table-view-field" placeholder="Search accounts...">
            <select id="roleFilter" class="filter-select table-view-field">
                <option value="">All Roles</option>
                <option value="VP">VP</option>
                <option value="MANAGER">Manager</option>
                <option value="ACCOUNTANT">Accountant</option>
            </select>
            <select id="statusFilter" class="filter-select table-view-field">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="table-view-meta" id="accountsMeta"></div>
    </div>

    <div class="table-container">
        <?php if (empty($adminAccounts)): ?>
            <div class="empty-message">
                <p>No admin accounts found</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Login ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="accountsTableBody">
                    <?php foreach ($adminAccounts as $account): ?>
                        <tr data-role="<?php echo e($account['role'] ?? ''); ?>"
                            data-status="<?php echo ($account['is_active'] ?? false) ? 'active' : 'inactive'; ?>"
                            data-search="<?php echo e(trim(
                                ($account['login_id'] ?? '') . ' ' .
                                ($account['full_name'] ?? '') . ' ' .
                                ($account['email'] ?? '') . ' ' .
                                ($account['phone'] ?? '') . ' ' .
                                ($account['role'] ?? '') . ' ' .
                                (($account['is_active'] ?? false) ? 'Active' : 'Inactive')
                            )); ?>">
                            <td><?php echo e($account['login_id'] ?? 'N/A'); ?></td>
                            <td><?php echo e($account['full_name'] ?? 'N/A'); ?></td>
                            <td><?php echo e($account['email'] ?? 'N/A'); ?></td>
                            <td><?php echo e($account['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo e($account['role'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="status-badge <?php echo ($account['is_active'] ?? false) ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo ($account['is_active'] ?? false) ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn <?php echo ($account['is_active'] ?? false) ? '' : 'danger'; ?>" onclick="toggleAccountStatus(<?php echo e($account['id']); ?>, this)"><?php echo ($account['is_active'] ?? false) ? 'Deactivate' : 'Activate'; ?></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if (!empty($adminAccounts)): ?>
        <div class="table-view-pagination" id="accountsPager" style="margin-top: 14px;">
            <div class="pagination-info" id="accountsPageInfo"></div>
            <div class="pagination-actions">
                <button type="button" class="btn btn-ghost" id="accountsPrev">Previous</button>
                <button type="button" class="btn btn-ghost" id="accountsNext">Next</button>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="deactivateModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true">
        <h3 class="modal-title">Deactivate this account?</h3>
        <p class="modal-text">This will disable the account and prevent login access.</p>
        <div class="modal-actions">
            <button type="button" class="modal-btn cancel" onclick="closeDeactivateModal()">Cancel</button>
            <button type="button" class="modal-btn confirm" id="confirmDeactivateBtn">Yes</button>
        </div>
    </div>
</div>

<!-- Add Account Side Drawer -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeAddAccountDrawer()"></div>
<div class="drawer" id="addAccountDrawer">
    <div class="drawer-header">
        <h3 class="drawer-title">Add Account</h3>
        <button class="drawer-close" onclick="closeAddAccountDrawer()">×</button>
    </div>
    <div class="drawer-content">
        <form id="addAccountForm" onsubmit="submitAddAccount(event)">
            <div class="form-group">
                <label class="form-label" for="roleInput">Role</label>
                <select id="roleInput" name="role" class="form-input">
                    <option value="VP">VP</option>
                    <option value="MANAGER">Manager</option>
                    <option value="ACCOUNTANT">Accountant</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="loginId">Login ID *</label>
                <input type="text" id="loginId" name="login_id" class="form-input" placeholder="Loading..." readonly required>
                <div class="error-message" id="loginIdError"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="fullName">Full Name *</label>
                <input type="text" id="fullName" name="full_name" class="form-input" placeholder="Enter full name" required>
                <div class="error-message" id="fullNameError"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email *</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="Enter email" required>
                <div class="error-message" id="emailError"></div>
            </div>

        </form>
    </div>
    <div class="drawer-footer">
        <button class="btn-cancel" onclick="closeAddAccountDrawer()">Cancel</button>
        <button class="btn-submit" id="submitBtn" onclick="submitAddAccount(event)">Create Account</button>
    </div>
</div>
<script>
    function openAddAccountDrawer() {
        document.getElementById('drawerOverlay').classList.add('show');
        document.getElementById('addAccountDrawer').classList.add('show');
        // Clear form first
        document.getElementById('addAccountForm').reset();
        clearFormErrors();
        refreshLoginId();
    }

    function closeAddAccountDrawer() {
        document.getElementById('drawerOverlay').classList.remove('show');
        document.getElementById('addAccountDrawer').classList.remove('show');
        document.getElementById('addAccountForm').reset();
        clearFormErrors();
    }

    function clearFormErrors() {
        document.querySelectorAll('.error-message').forEach(el => {
            el.textContent = '';
        });
    }

    function refreshLoginId() {
        const role = document.getElementById('roleInput').value;
        const loginIdInput = document.getElementById('loginId');
        if (!role || !loginIdInput) {
            return;
        }

        loginIdInput.value = 'Loading...';

        fetch(`<?php echo e(url('principal/accounts/next-login-id')); ?>?role=${encodeURIComponent(role)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.login_id) {
                loginIdInput.value = data.data.login_id;
            } else {
                loginIdInput.value = '';
                showAccountMessage(data.message || 'Unable to generate login ID.', true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loginIdInput.value = '';
            showAccountMessage(`Error generating login ID: ${error.message}`, true);
        });
    }

    document.getElementById('roleInput')?.addEventListener('change', refreshLoginId);

    window.IMS = window.IMS || {};
    window.IMS.initTableView({
        tbodyId: 'accountsTableBody',
        searchInputId: 'accountSearch',
        metaId: 'accountsMeta',
        pagerId: 'accountsPager',
        pageInfoId: 'accountsPageInfo',
        prevId: 'accountsPrev',
        nextId: 'accountsNext',
        pageSize: 10,
        noResultsColSpan: 7,
        filters: [
            { id: 'roleFilter', rowDatasetKey: 'role' },
            { id: 'statusFilter', rowDatasetKey: 'status' }
        ]
    });

    function submitAddAccount(event) {
        event.preventDefault();
        clearFormErrors();

        const form = document.getElementById('addAccountForm');
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';

        const formData = new FormData(form);

        fetch('<?php echo e(url('principal/accounts')); ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                full_name: formData.get('full_name'),
                email: formData.get('email'),
                role: formData.get('role')
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAccountMessage('Account created successfully. An activation email has been sent to the new user.', false);
                closeAddAccountDrawer();
                // Refresh the page to show the new account
                location.reload();
            } else {
                // Handle specific validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorEl = document.getElementById(field + 'Error');
                        if (errorEl) {
                            errorEl.textContent = data.errors[field];
                        }
                    });
                } else {
                    showAccountMessage(`Error: ${data.message || 'Failed to create account'}`, true);
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Account';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAccountMessage(`Error creating account: ${error.message}`, true);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Account';
        });
    }

    const accountMessage = document.getElementById('accountMessage');
    const deactivateModal = document.getElementById('deactivateModal');
    const confirmDeactivateBtn = document.getElementById('confirmDeactivateBtn');
    let pendingDeactivate = null;

    function showAccountMessage(message, isError) {
        accountMessage.textContent = message;
        accountMessage.classList.remove('success', 'error');
        accountMessage.classList.add(isError ? 'error' : 'success');
        accountMessage.style.display = 'block';

        if (!isError) {
            setTimeout(() => {
                clearAccountMessage();
            }, 3000);
        }
    }

    function clearAccountMessage() {
        accountMessage.textContent = '';
        accountMessage.classList.remove('success', 'error');
        accountMessage.style.display = 'none';
    }

    function openDeactivateModal(accountId, button) {
        pendingDeactivate = { accountId, button };
        deactivateModal.classList.add('show');
        deactivateModal.setAttribute('aria-hidden', 'false');
    }

    function closeDeactivateModal() {
        deactivateModal.classList.remove('show');
        deactivateModal.setAttribute('aria-hidden', 'true');
        pendingDeactivate = null;
    }

    confirmDeactivateBtn.addEventListener('click', () => {
        if (!pendingDeactivate) {
            return;
        }
        const { accountId, button } = pendingDeactivate;
        closeDeactivateModal();
        toggleAccountStatus(accountId, button, 'Deactivate');
    });

    deactivateModal.addEventListener('click', (event) => {
        if (event.target === deactivateModal) {
            closeDeactivateModal();
        }
    });

    function toggleAccountStatus(accountId, button, forcedAction = null) {
        clearAccountMessage();
        const action = forcedAction || (button.textContent.trim() === 'Activate' ? 'Activate' : 'Deactivate');

        if (action === 'Deactivate' && !forcedAction) {
            openDeactivateModal(accountId, button);
            return;
        }

        button.disabled = true;
        button.textContent = 'Processing...';

        fetch(`<?php echo e(url('principal/accounts')); ?>/${accountId}/toggle`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const row = button.closest('tr');
                const statusBadge = row.querySelector('.status-badge');
                const isNowActive = data.data.status === 1;

                if (isNowActive) {
                    button.textContent = 'Deactivate';
                    button.classList.remove('danger');
                    statusBadge.classList.remove('status-inactive');
                    statusBadge.classList.add('status-active');
                    statusBadge.textContent = 'Active';
                } else {
                    button.textContent = 'Activate';
                    button.classList.add('danger');
                    statusBadge.classList.remove('status-active');
                    statusBadge.classList.add('status-inactive');
                    statusBadge.textContent = 'Inactive';
                }

                if (!isNowActive) {
                    showAccountMessage('Account deactivated successfully.', false);
                } else if (action === 'Activate') {
                    showAccountMessage('Account activated successfully.', false);
                }
                button.disabled = false;
            } else {
                showAccountMessage(data.message || 'Failed to toggle account status.', true);
                button.disabled = false;
                button.textContent = action === 'Activate' ? 'Activate' : 'Deactivate';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAccountMessage(`Error toggling account status: ${error.message}`, true);
            button.disabled = false;
            button.textContent = action === 'Activate' ? 'Activate' : 'Deactivate';
        });
    }

    // Close drawer when clicking overlay
    document.getElementById('drawerOverlay')?.addEventListener('click', closeAddAccountDrawer);

    // Close drawer with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAddAccountDrawer();
            closeDeactivateModal();
        }
    });
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
