<?php
/** @var array<int, array<string, mixed>> $users */
$activeNav = 'users';
$pageSubtitle = 'Manage administrators, teachers, and students.';
$users = $users ?? [
    ['id' => 1, 'role' => 'admin', 'login_id' => 'admin', 'full_name' => 'System Admin', 'email' => 'admin@example.test', 'status' => 'Active'],
    ['id' => 2, 'role' => 'teacher', 'login_id' => 'teacher01', 'full_name' => 'Juan Dela Cruz', 'email' => 'juan@example.test', 'status' => 'Active'],
    ['id' => 3, 'role' => 'student', 'login_id' => 'student01', 'full_name' => 'Maria Santos', 'email' => 'maria@example.test', 'status' => 'Inactive'],
];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">User Accounts</h2>
            <div style="color:#64748b;">Browse system accounts and access roles.</div>
        </div>
        <div>
            <a class="btn btn-primary" href="<?php echo e(url('users/create')); ?>">Create User</a>
        </div>
    </div>

    <style>
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 980px; }
        th, td { padding: 14px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { color: #475569; font-size: 0.92rem; text-transform: uppercase; letter-spacing: 0.04em; }
        td { color: #0f172a; }
        .pill { display: inline-flex; align-items: center; padding: 6px 10px; border-radius: 999px; font-size: 0.82rem; font-weight: 700; }
        .pill.active { background: #dcfce7; color: #166534; }
        .pill.inactive { background: #fee2e2; color: #991b1b; }
        .role { text-transform: uppercase; font-size: 0.82rem; letter-spacing: 0.06em; color: #2563eb; font-weight: 800; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .action-link { color: #2563eb; text-decoration: none; font-weight: 700; }
    </style>

    <div class="table-view-header" style="margin-top: 6px;">
        <div class="filter-bar table-view-controls">
            <input type="text" id="userSearch" class="filter-input table-view-field" placeholder="Search users...">
        </div>
        <div class="table-view-meta" id="usersMeta"></div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Role</th>
                    <th>Login ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="usersTableBody">
                <?php foreach ($users as $user): ?>
                    <tr data-search="<?php echo e(trim(
                        (string) ($user['id'] ?? '') . ' ' .
                        (string) ($user['role'] ?? '') . ' ' .
                        (string) ($user['login_id'] ?? '') . ' ' .
                        (string) ($user['full_name'] ?? '') . ' ' .
                        (string) ($user['email'] ?? '') . ' ' .
                        (string) ($user['status'] ?? '')
                    )); ?>">
                        <td><?php echo e((string) ($user['id'] ?? '')); ?></td>
                        <td><span class="role"><?php echo e((string) ($user['role'] ?? '')); ?></span></td>
                        <td><?php echo e((string) ($user['login_id'] ?? '')); ?></td>
                        <td><?php echo e((string) ($user['full_name'] ?? '')); ?></td>
                        <td><?php echo e((string) ($user['email'] ?? '')); ?></td>
                        <td><span class="pill <?php echo strtolower((string) ($user['status'] ?? 'inactive')); ?>"><?php echo e((string) ($user['status'] ?? 'Inactive')); ?></span></td>
                        <td>
                            <div class="actions">
                                <a class="view-btn" href="<?php echo e(url('users/' . (string) ($user['id'] ?? 0))); ?>">View</a>
                                <a class="action-link" href="<?php echo e(url('users/' . (string) ($user['id'] ?? 0) . '/edit')); ?>">Edit</a>
                                <?php if (!empty($user['can_resend_activation'])): ?>
                                    <button
                                        type="button"
                                        class="action-link resend-activation"
                                        data-user-id="<?php echo e((string) ($user['id'] ?? '')); ?>">
                                        Resend Activation
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="table-view-pagination" id="usersPager" style="margin-top: 14px;">
        <div class="pagination-info" id="usersPageInfo"></div>
        <div class="pagination-actions">
            <button type="button" class="btn btn-ghost" id="usersPrev">Previous</button>
            <button type="button" class="btn btn-ghost" id="usersNext">Next</button>
        </div>
    </div>

    <script>
        const resendBaseUrl = '<?php echo e(url('users')); ?>';

        document.querySelectorAll('.resend-activation').forEach((button) => {
            button.addEventListener('click', async () => {
                if (!confirm('Send a new activation email for this principal?')) {
                    return;
                }

                const userId = button.dataset.userId;
                if (!userId) {
                    alert('Missing user id.');
                    return;
                }

                button.disabled = true;

                try {
                    const response = await fetch(`${resendBaseUrl}/${userId}/resend-activation`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json().catch(() => ({}));
                    alert(data.message || 'Activation email sent.');
                } catch (error) {
                    alert('Failed to send activation email.');
                } finally {
                    button.disabled = false;
                }
            });
        });

        window.IMS = window.IMS || {};
        window.IMS.initTableView({
            tbodyId: 'usersTableBody',
            searchInputId: 'userSearch',
            metaId: 'usersMeta',
            pagerId: 'usersPager',
            pageInfoId: 'usersPageInfo',
            prevId: 'usersPrev',
            nextId: 'usersNext',
            pageSize: 10,
            noResultsColSpan: 7
        });
    </script>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
