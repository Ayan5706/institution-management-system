<?php
/** @var array $semesters */
/** @var array $programs */
$activeNav = 'semesters';
$semesters = $semesters ?? [];
$programs = $programs ?? [];

$semester_numbers = [];
foreach ($semesters as $sem) {
    if (!empty($sem['semester_number'])) {
        $semester_numbers[] = (string) $sem['semester_number'];
    }
}
$semester_numbers = array_values(array_unique($semester_numbers));
sort($semester_numbers);
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Semesters</h2>
            <div style="color:#64748b;">Manage academic semesters</div>
        </div>
        <button onclick="toggleAddSemesterForm()" class="btn btn-primary">+ Add Semester</button>
    </div>

    <style>
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .filter-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #0f172a;
        }

        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .filter-group input {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
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

        .status-current {
            background: #3b82f6;
            color: #fff;
        }

        .status-other {
            background: #e5e7eb;
            color: #374151;
        }

        .term-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            background: #f3e8ff;
            color: #6b21a8;
        }

        .action-btn {
            padding: 6px 12px;
            background: #2563eb;
            color: #fff;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .action-btn:hover {
            background: #1d4ed8;
        }

        .action-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .action-btn.secondary {
            background: #64748b;
        }

        .action-btn.secondary:hover {
            background: #475569;
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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .form-group input:focus,
        .form-group select:focus {
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

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
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

    <div id="semesterMessage" class="notice-banner"></div>

    <!-- Add Semester Form -->
    <div id="addSemesterForm" class="form-container">
        <h3 style="margin-top: 0;">Add New Semester</h3>
        <form onsubmit="handleAddSemester(event)">
            <div class="form-row">
                <div class="form-group">
                    <label for="semProgram">Program</label>
                    <select id="semProgram" name="program_id" required>
                        <option value="">Select a program</option>
                        <?php foreach ($programs as $prog): ?>
                            <option value="<?php echo e($prog['id']); ?>"><?php echo e($prog['program_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="semNumber">Semester Number</label>
                    <input type="number" id="semNumber" name="semester_number" required min="1" max="12" placeholder="e.g., 1">
                </div>
                <div class="form-group">
                    <label for="academicYear">Academic Year</label>
                    <input type="text" id="academicYear" name="academic_year" required placeholder="e.g., 2024-2025" pattern="^\d{4}-\d{4}$">
                </div>
            </div>
            <div class="form-group">
                <label for="feeAmount">Fee Amount (Optional)</label>
                <input type="number" id="feeAmount" name="fee_amount" step="0.01" min="0" placeholder="e.g., 5000">
            </div>
            <div id="formMessage"></div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Save Semester</button>
                <button type="button" class="btn-cancel" onclick="toggleAddSemesterForm()">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Filters -->
    <div class="filter-group">
        <label for="programFilter">Filter by Program:</label>
        <select id="programFilter" onchange="filterSemesters()">
            <option value="">All Programs</option>
            <?php foreach ($programs as $prog): ?>
                <option value="<?php echo e($prog['program_name']); ?>"><?php echo e($prog['program_name']); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="semesterNumberFilter">Semester Number:</label>
        <select id="semesterNumberFilter" onchange="filterSemesters()">
            <option value="">All Semesters</option>
            <?php foreach ($semester_numbers as $number): ?>
                <option value="<?php echo e($number); ?>">Sem <?php echo e($number); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="semesterSearch">Search:</label>
        <input type="text" id="semesterSearch" placeholder="Search program, year, status..." oninput="filterSemesters()">
    </div>

    <!-- Semesters Table -->
    <div class="table-container">
        <?php if (empty($semesters)): ?>
            <div class="empty-message">
                <p>No semesters found. <a href="#" onclick="toggleAddSemesterForm(); return false;" style="color:#2563eb;text-decoration:underline;">Create one now</a></p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>Semester Number</th>
                        <th>Term</th>
                        <th>Academic Year</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="semestersTable">
                    <?php foreach ($semesters as $sem): ?>
                        <tr class="semester-row"
                            data-program="<?php echo e($sem['program_name'] ?? 'N/A'); ?>"
                            data-semester-number="<?php echo e($sem['semester_number'] ?? ''); ?>"
                            data-search="<?php echo e(strtolower(trim(
                                ($sem['program_name'] ?? '') . ' ' .
                                ($sem['academic_year'] ?? '') . ' ' .
                                ($sem['semester_number'] ?? '') . ' ' .
                                (((int) ($sem['is_current'] ?? 0) === 1) ? 'current active' : 'inactive')
                            ))); ?>">
                            <td><?php echo e($sem['program_name'] ?? 'N/A'); ?></td>
                            <td><?php echo e($sem['semester_number'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="term-badge">
                                    <?php 
                                        $semNum = (int)($sem['semester_number'] ?? 0);
                                        echo ($semNum % 2 === 0) ? 'Even' : 'Odd';
                                    ?>
                                </span>
                            </td>
                            <td><?php echo e($sem['academic_year'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="status-badge <?php echo ($sem['is_current'] ?? 0) ? 'status-current' : 'status-other'; ?>">
                                    <?php echo ($sem['is_current'] ?? 0) ? 'Current' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!($sem['is_current'] ?? 0)): ?>
                                    <button onclick="activateSemester(<?php echo e($sem['id']); ?>)" class="action-btn secondary">Activate</button>
                                <?php else: ?>
                                    <span style="color: #10b981; font-weight: 600;">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div id="semestersPagination" style="display:flex; justify-content: space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-top: 14px;">
                <div id="semestersPageInfo" style="color:#64748b; font-size:0.9rem;"></div>
                <div style="display:flex; gap:10px;">
                    <button type="button" class="action-btn secondary" id="semestersPrev">Previous</button>
                    <button type="button" class="action-btn secondary" id="semestersNext">Next</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="confirmModal" class="modal-backdrop" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
            <h3 class="modal-title" id="confirmTitle">Confirm Action</h3>
            <p class="modal-text" id="confirmText">Are you sure you want to continue?</p>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeConfirmModal()">Cancel</button>
                <button type="button" class="btn-submit" id="confirmButton">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        function toggleAddSemesterForm() {
            const form = document.getElementById('addSemesterForm');
            form.classList.toggle('active');
            if (form.classList.contains('active')) {
                document.getElementById('semProgram').focus();
            }
        }

        function handleAddSemester(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            fetch('<?php echo e(url('vp/semesters')); ?>', {
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
                    messageDiv.innerHTML = '<div class="success-message">Semester added successfully. Refreshing...</div>';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    messageDiv.innerHTML = '<div class="error-message">' + (data.message || 'Error adding semester') + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('formMessage').innerHTML = '<div class="error-message">Error: ' + error.message + '</div>';
            });
        }

        function activateSemester(id) {
            openConfirmModal(
                'Activate Semester',
                'Activate this semester? This will deactivate other semesters from the same program.',
                () => {
                    fetch('<?php echo e(url('vp/semesters')); ?>/' + id + '/activate', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage('Semester activated successfully.', 'success');
                            location.reload();
                        } else {
                            showMessage('Error: ' + (data.message || 'Failed to activate semester'), 'error');
                        }
                    });
                }
            );
        }

        let semestersPage = 1;
        const semestersPageSize = 10;

        function getAllSemesterRows() {
            return Array.from(document.querySelectorAll('.semester-row'));
        }

        function getFilteredSemesterRows() {
            const program = document.getElementById('programFilter')?.value || '';
            const semesterNumber = document.getElementById('semesterNumberFilter')?.value || '';
            const search = (document.getElementById('semesterSearch')?.value || '').trim().toLowerCase();

            return getAllSemesterRows().filter(row => {
                const matchesProgram = program === '' || (row.dataset.program || '') === program;
                const matchesSemester = semesterNumber === '' || (row.dataset.semesterNumber || '') === semesterNumber;
                if (!matchesProgram || !matchesSemester) return false;

                if (!search) return true;
                const haystack = ((row.dataset.search || row.textContent || '') + '').toLowerCase();
                return haystack.includes(search);
            });
        }

        function ensureNoResultsRow() {
            const tbody = document.getElementById('semestersTable');
            if (!tbody) return null;

            let row = document.getElementById('semestersNoResults');
            if (row) return row;

            row = document.createElement('tr');
            row.id = 'semestersNoResults';
            row.innerHTML = '<td colspan="6" class="empty-message">No matching semesters found.</td>';
            tbody.appendChild(row);
            return row;
        }

        function renderSemesterTable() {
            const allRows = getAllSemesterRows();
            const filteredRows = getFilteredSemesterRows();

            const pageCount = Math.max(1, Math.ceil(filteredRows.length / semestersPageSize));
            semestersPage = Math.min(Math.max(1, semestersPage), pageCount);

            const start = (semestersPage - 1) * semestersPageSize;
            const end = start + semestersPageSize;
            const visible = filteredRows.slice(start, end);

            allRows.forEach(row => {
                row.style.display = 'none';
            });

            visible.forEach(row => {
                row.style.display = '';
            });

            const noResultsRow = ensureNoResultsRow();
            if (noResultsRow) {
                noResultsRow.style.display = filteredRows.length === 0 ? '' : 'none';
            }

            const pager = document.getElementById('semestersPagination');
            if (pager) {
                pager.style.display = filteredRows.length > 0 ? '' : 'none';
            }

            const pageInfo = document.getElementById('semestersPageInfo');
            if (pageInfo) {
                pageInfo.textContent = `Page ${semestersPage} of ${pageCount}`;
            }

            const prev = document.getElementById('semestersPrev');
            const next = document.getElementById('semestersNext');
            if (prev) prev.disabled = semestersPage <= 1;
            if (next) next.disabled = semestersPage >= pageCount;
        }

        function filterSemesters() {
            semestersPage = 1;
            renderSemesterTable();
        }

        document.getElementById('semestersPrev')?.addEventListener('click', () => {
            semestersPage = Math.max(1, semestersPage - 1);
            renderSemesterTable();
        });

        document.getElementById('semestersNext')?.addEventListener('click', () => {
            semestersPage = semestersPage + 1;
            renderSemesterTable();
        });

        renderSemesterTable();

        function showMessage(message, type) {
            const banner = document.getElementById('semesterMessage');
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
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
