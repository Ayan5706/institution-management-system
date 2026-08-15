<?php
/** @var array $teachers */
$activeNav = 'teachers';
$teachers = $teachers ?? [];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Teachers</h2>
            <div style="color:#64748b;">Manage teaching staff</div>
        </div>
        <button onclick="toggleAddTeacherForm()" class="btn btn-primary">+ Add Teacher</button>
    </div>

    <style>
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
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
            cursor: pointer;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty-message {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }

        .form-container {
            display: none;
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2563eb;
        }

        .form-container.active {
            display: block;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #0f172a;
        }

        .form-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-submit {
            background: #2563eb;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-submit:hover {
            background: #1d4ed8;
        }

        .btn-cancel {
            background: #e2e8f0;
            color: #0f172a;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-cancel:hover {
            background: #cbd5e1;
        }

        .error-message {
            color: #dc2626;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .success-message {
            color: #10b981;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .note {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            border-radius: 4px;
            color: #92400e;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        .filter-bar {
            display: flex;
            gap: 12px;
            margin: 10px 0 18px;
            flex-wrap: nowrap;
            align-items: center;
            overflow-x: auto;
        }

        .filter-input,
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
            background: #fff;
            flex: 1;
            min-width: 140px;
        }

        .filter-input {
            cursor: text;
        }

        .filter-select {
            cursor: pointer;
        }
    </style>

    <!-- Add Teacher Form -->
    <div id="addTeacherForm" class="form-container">
        <h3 style="margin-top: 0;">Add New Teacher</h3>
        <form onsubmit="handleAddTeacher(event)">
            <div class="form-row">
                <div class="form-group">
                    <label for="staffId">Staff ID</label>
                    <input type="text" id="staffId" name="login_id" required placeholder="Loading..." readonly>
                </div>
                <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="full_name" required placeholder="e.g., John Doe">
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="e.g., john@example.com">
            </div>
            <div id="formMessage"></div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Add Teacher</button>
                <button type="button" class="btn-cancel" onclick="toggleAddTeacherForm()">Cancel</button>
            </div>
        </form>
    </div>

    <div class="table-view-header">
        <div class="filter-bar table-view-controls">
            <input type="text" id="teacherSearch" class="filter-input table-view-field" placeholder="Search teachers...">
            <select id="teacherStatus" class="filter-select table-view-field">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="table-view-meta" id="teachersMeta"></div>
    </div>

    <!-- Teachers Table -->
    <div class="table-container">
        <?php if (empty($teachers)): ?>
            <div class="empty-message">
                <p>No teachers found. <a href="#" onclick="toggleAddTeacherForm(); return false;" style="color:#2563eb;text-decoration:underline;">Add one now</a></p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Staff ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="teachersTableBody">
                    <?php foreach ($teachers as $teacher): ?>
                        <tr data-status="<?php echo ($teacher['is_active'] ?? false) ? 'active' : 'inactive'; ?>"
                            data-name="<?php echo e($teacher['full_name'] ?? ''); ?>"
                            data-email="<?php echo e($teacher['email'] ?? ''); ?>"
                            data-login="<?php echo e($teacher['login_id'] ?? ''); ?>"
                            data-search="<?php echo e(trim(($teacher['full_name'] ?? '') . ' ' . ($teacher['email'] ?? '') . ' ' . ($teacher['login_id'] ?? ''))); ?>">
                            <td><?php echo e($teacher['login_id'] ?? $teacher['id'] ?? 'N/A'); ?></td>
                            <td><?php echo e($teacher['full_name'] ?? 'N/A'); ?></td>
                            <td><?php echo e($teacher['email'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="status-badge <?php echo ($teacher['is_active'] ?? false) ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo ($teacher['is_active'] ?? false) ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <a class="view-btn" href="<?php echo e(url('vp/teachers/' . ($teacher['id'] ?? 0))); ?>">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if (!empty($teachers)): ?>
        <div class="table-view-pagination" id="teachersPager" style="margin-top: 14px;">
            <div class="pagination-info" id="teachersPageInfo"></div>
            <div class="pagination-actions">
                <button type="button" class="btn btn-ghost" id="teachersPrev">Previous</button>
                <button type="button" class="btn btn-ghost" id="teachersNext">Next</button>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function toggleAddTeacherForm() {
            const form = document.getElementById('addTeacherForm');
            form.classList.toggle('active');
            if (form.classList.contains('active')) {
                refreshTeacherLoginId();
                document.getElementById('fullName').focus();
            }
        }

        function refreshTeacherLoginId() {
            const loginIdInput = document.getElementById('staffId');
            if (!loginIdInput) return;

            loginIdInput.value = 'Loading...';

            fetch('<?php echo e(url('vp/teachers/next-login-id')); ?>', {
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
                    document.getElementById('formMessage').innerHTML =
                        '<div class="error-message">' + (data.message || 'Unable to generate Staff ID.') + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                loginIdInput.value = '';
                document.getElementById('formMessage').innerHTML =
                    '<div class="error-message">Error generating Staff ID: ' + error.message + '</div>';
            });
        }

        function handleAddTeacher(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            fetch('<?php echo e(url('vp/teachers')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(Object.fromEntries(formData))
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('formMessage');
                if (data.success) {
                    messageDiv.innerHTML = '<div class="success-message"><strong>✓ Teacher added successfully!</strong><br>' + (data.message || '') + '<br>Refreshing...</div>';
                    setTimeout(() => location.reload(), 2000);
                } else {
                    messageDiv.innerHTML = '<div class="error-message">' + (data.message || 'Error adding teacher') + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('formMessage').innerHTML = '<div class="error-message">Error: ' + error.message + '</div>';
            });
        }

        window.IMS?.initTableView({
            tbodyId: 'teachersTableBody',
            searchInputId: 'teacherSearch',
            filters: [
                { id: 'teacherStatus', rowDatasetKey: 'status' },
            ],
            metaId: 'teachersMeta',
            pagerId: 'teachersPager',
            pageInfoId: 'teachersPageInfo',
            prevId: 'teachersPrev',
            nextId: 'teachersNext',
            pageSize: 10,
            noResultsColSpan: 4,
            noResultsText: 'No matching teachers found.',
        });
    </script>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
