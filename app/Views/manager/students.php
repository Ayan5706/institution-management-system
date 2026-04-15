<?php
/** @var array $programs */
$activeNav = 'students';
$programs = $programs ?? [];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Student Management</h2>
            <div style="color:#64748b;">View and manage all students</div>
        </div>
        <div>
            <button class="btn btn-primary" onclick="openAddStudentDrawer()">+ Add Student</button>
        </div>
    </div>

    <style>
        .filter-bar {
            display: flex;
            gap: 12px;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            cursor: pointer;
        }

        .search-input {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            width: 200px;
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

        .student-link {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .student-link:hover {
            text-decoration: underline;
        }

        .program-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            background: #eff6ff;
            color: #1e3a8a;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-badge.active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 10px;
            font-size: 0.85rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            cursor: pointer;
            background: #fff;
            text-decoration: none;
            font-weight: 600;
        }

        .action-btn:hover {
            background: #f8fafc;
        }

        .action-btn.danger {
            color: #dc2626;
        }

        .empty-message {
            text-align: center;
            padding: 40px 20px;
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

        /* Side Drawer */
        .drawer {
            display: none;
            position: fixed;
            top: 0;
            right: 0;
            width: 400px;
            height: 100vh;
            background: #fff;
            box-shadow: -4px 0 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
        }

        .drawer.open {
            display: flex;
            flex-direction: column;
        }

        .drawer-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .drawer-header h2 {
            margin: 0;
            font-size: 1.3rem;
        }

        .drawer-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
        }

        .drawer-content {
            padding: 20px;
            flex: 1;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #0f172a;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2563eb;
            background: #eff6ff;
        }

        .error-message {
            color: #dc2626;
            font-size: 0.85rem;
            margin-top: 4px;
        }

        .drawer-footer {
            padding: 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            flex: 1;
        }

        .btn-primary {
            background: #2563eb;
            color: #fff;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-ghost {
            background: #f1f5f9;
            color: #0f172a;
        }

        .btn-ghost:hover {
            background: #e2e8f0;
        }

        @media (max-width: 768px) {
            .drawer {
                width: 100%;
            }

            .action-buttons {
                flex-direction: column;
            }

            .search-input {
                width: 100%;
            }
        }
    </style>

    <div id="studentsMessage" class="notice-banner"></div>

    <div class="table-view-header">
        <div class="filter-bar table-view-controls">
        <input type="text" id="searchInput" class="search-input table-view-field" placeholder="Search by name or registration...">
        <select class="filter-select table-view-field" id="programFilter">
            <option value="">All Programs</option>
            <?php foreach ($programs as $program): ?>
                <option value="<?php echo e($program['program_code'] ?? ''); ?>"><?php echo e($program['program_name'] ?? ''); ?></option>
            <?php endforeach; ?>
        </select>
        <select class="filter-select table-view-field" id="statusFilter">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        </div>
        <div class="table-view-meta" id="studentsMeta"></div>
    </div>

    <div class="table-container">
        <table id="studentsTable">
            <thead>
                <tr>
                    <th>Registration Number</th>
                    <th>Name</th>
                    <th>Program</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="studentsTableBody">
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #64748b;">
                        Loading students...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="table-view-pagination" id="studentsPager" style="margin-top: 14px; display: none;">
        <div class="pagination-info" id="studentsPageInfo"></div>
        <div class="pagination-actions">
            <button type="button" class="btn btn-ghost" id="studentsPrev">Previous</button>
            <button type="button" class="btn btn-ghost" id="studentsNext">Next</button>
        </div>
    </div>
</div>

<div id="confirmModal" class="modal-backdrop" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
        <h3 class="modal-title" id="confirmTitle">Confirm Action</h3>
        <p class="modal-text" id="confirmText">Are you sure you want to continue?</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-ghost" onclick="closeConfirmModal()">Cancel</button>
            <button type="button" class="btn btn-primary" id="confirmButton">Confirm</button>
        </div>
    </div>
</div>

<!-- Add Student Drawer -->
<div class="drawer" id="addStudentDrawer">
    <div class="drawer-header">
        <h2>Add Student</h2>
        <button class="drawer-close" onclick="closeAddStudentDrawer()">×</button>
    </div>
    <div class="drawer-content">
        <form id="addStudentForm">
            <div class="form-group">
                <label for="fullName">Full Name *</label>
                <input type="text" id="fullName" name="full_name" required>
                <div class="error-message" id="fullNameError"></div>
            </div>
            <div class="form-group">
                <label for="registrationNumber">Registration Number *</label>
                <input type="text" id="registrationNumber" name="registration_number" required>
                <div class="error-message" id="registrationNumberError"></div>
            </div>
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
                <div class="error-message" id="emailError"></div>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone">
            </div>
            <div class="form-group">
                <label for="dateOfBirth">Date of Birth *</label>
                <input type="date" id="dateOfBirth" name="date_of_birth" required>
                <div class="error-message" id="dateOfBirthError"></div>
            </div>
            <div class="form-group">
                <label for="programId">Program *</label>
                <select id="programId" name="program_id" required>
                    <option value="">Select Program</option>
                    <?php foreach ($programs as $program): ?>
                        <option value="<?php echo e($program['id'] ?? ''); ?>"><?php echo e($program['program_name'] ?? ''); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="error-message" id="programIdError"></div>
            </div>
        </form>
    </div>
    <div class="drawer-footer">
        <button class="btn btn-ghost" onclick="closeAddStudentDrawer()">Cancel</button>
        <button class="btn btn-primary" onclick="submitAddStudent()">Create Student</button>
    </div>
</div>

<script>
    let allStudents = [];
    let tableView = null;

    document.addEventListener('DOMContentLoaded', function() {
        loadStudents();
    });

    function loadStudents() {
        fetch('<?php echo e(url('api/manager/students')); ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                allStudents = data.data;
                displayStudents(allStudents);

                if (!tableView) {
                    tableView = window.IMS?.initTableView({
                        tbodyId: 'studentsTableBody',
                        searchInputId: 'searchInput',
                        filters: [
                            { id: 'programFilter', rowDatasetKey: 'program' },
                            { id: 'statusFilter', rowDatasetKey: 'status' },
                        ],
                        metaId: 'studentsMeta',
                        pagerId: 'studentsPager',
                        pageInfoId: 'studentsPageInfo',
                        prevId: 'studentsPrev',
                        nextId: 'studentsNext',
                        pageSize: 10,
                        noResultsColSpan: 5,
                        noResultsText: 'No matching students found.',
                    });
                } else {
                    tableView.reset && tableView.reset();
                }
            } else {
                showError('Failed to load students');
            }
        })
        .catch(() => showError('Error loading students'));
    }

    function displayStudents(students) {
        const tbody = document.getElementById('studentsTableBody');
        
        if (students.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="empty-message">No students found</td></tr>';
            return;
        }

        tbody.innerHTML = students.map(student => `
            <tr data-program="${escapeHtml(String(student.program_code || ''))}"
                data-status="${escapeHtml(String(student.status || ''))}"
                data-search="${escapeHtml(`${student.name || ''} ${student.registration_number || ''}`.trim())}">
                <td>${escapeHtml(student.registration_number)}</td>
                <td>
                    <a class="student-link" onclick="viewStudent(${student.id})">${escapeHtml(student.name)}</a>
                </td>
                <td><span class="program-badge">${escapeHtml(student.program || 'N/A')}</span></td>
                <td>
                    <span class="status-badge ${student.status}">
                        ${student.status === 'active' ? '✓ Active' : '○ Inactive'}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="action-btn" onclick="toggleStudentStatus(${student.id}, '${student.status}')">
                            ${student.status === 'active' ? 'Deactivate' : 'Activate'}
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function viewStudent(id) {
        window.location.href = '<?php echo e(url('manager/students')); ?>/' + id;
    }

    function toggleStudentStatus(id, currentStatus) {
        const newStatus = currentStatus === 'active' ? 'Deactivate' : 'Activate';
        openConfirmModal(
            `${newStatus} Student`,
            `Are you sure you want to ${newStatus.toLowerCase()} this student?`,
            () => {
                fetch('<?php echo e(url('api/manager/students')); ?>/' + id + '/toggle', {
                    method: 'PATCH',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('Student status updated successfully.', 'success');
                        loadStudents();
                    } else {
                        showMessage(data.message || 'Failed to update student status', 'error');
                    }
                })
                .catch(() => showMessage('Error updating student', 'error'));
            }
        );
    }

    function openAddStudentDrawer() {
        document.getElementById('addStudentDrawer').classList.add('open');
    }

    function closeAddStudentDrawer() {
        document.getElementById('addStudentDrawer').classList.remove('open');
        document.getElementById('addStudentForm').reset();
        clearErrors();
    }

    function submitAddStudent() {
        const formData = new FormData(document.getElementById('addStudentForm'));
        const data = Object.fromEntries(formData);

        if (data.registration_number) {
            data.login_id = data.registration_number;
        }

        clearErrors();

        fetch('<?php echo e(url('api/manager/students')); ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message || 'Student created successfully. Activation email sent.', 'success');
                closeAddStudentDrawer();
                loadStudents();
            } else if (data.errors) {
                displayErrors(data.errors);
            } else {
                showMessage(data.message || 'Failed to create student', 'error');
            }
        })
        .catch(() => showMessage('Error creating student', 'error'));
    }

    function clearErrors() {
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
    }

    function displayErrors(errors) {
        for (const [field, message] of Object.entries(errors)) {
            const el = document.getElementById(field + 'Error');
            if (el) {
                el.textContent = message;
            }
        }
    }

    function showError(message) {
        showMessage(message, 'error');
    }

    function showMessage(message, type) {
        const banner = document.getElementById('studentsMessage');
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
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
