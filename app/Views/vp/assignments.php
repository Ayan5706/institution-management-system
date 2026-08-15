<?php
/** @var array $assignments */
/** @var array $teachers */
/** @var array $subjects */
$activeNav = 'assignments';
$assignments = $assignments ?? [];
$teachers = $teachers ?? [];
$subjects = $subjects ?? [];

$assignmentPrograms = [];
$assignmentSemesters = [];
foreach ($assignments as $assignment) {
    if (!empty($assignment['program_name'])) {
        $assignmentPrograms[] = (string) $assignment['program_name'];
    }
    if (!empty($assignment['semester_number'])) {
        $assignmentSemesters[] = (string) $assignment['semester_number'];
    }
}
$assignmentPrograms = array_values(array_unique($assignmentPrograms));
$assignmentSemesters = array_values(array_unique($assignmentSemesters));
sort($assignmentPrograms);
sort($assignmentSemesters);

// Build unique programs and semesters from subjects
$programsList = [];
$semestersList = [];
foreach ($subjects as $subject) {
    if (!empty($subject['program_id']) && !empty($subject['program_name'])) {
        if (!isset($programsList[$subject['program_id']])) {
            $programsList[$subject['program_id']] = $subject['program_name'];
        }
    }
    if (!empty($subject['semester_id']) && !empty($subject['semester_number'])) {
        if (!isset($semestersList[$subject['semester_id']])) {
            $semestersList[$subject['semester_id']] = [
                'number' => $subject['semester_number'],
                'year' => $subject['academic_year'] ?? ''
            ];
        }
    }
}
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Subject Assignments</h2>
            <div style="color:#64748b;">Manage teacher-subject assignments</div>
        </div>
        <button onclick="toggleAssignForm()" class="btn btn-primary">+ Assign Teacher</button>
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

        .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
        }

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

        .btn-danger {
            background: #dc2626;
            color: #fff;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .btn-danger:hover {
            background: #b91c1c;
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

    <div id="assignmentMessage" class="notice-banner"></div>

    <div class="table-view-header">
        <div class="filter-bar table-view-controls">
        <input type="text" id="assignmentSearch" class="filter-input table-view-field" placeholder="Search teacher or subject...">
        <select id="programFilter" class="filter-select table-view-field">
            <option value="">All Programs</option>
            <?php foreach ($assignmentPrograms as $programName): ?>
                <option value="<?php echo e($programName); ?>"><?php echo e($programName); ?></option>
            <?php endforeach; ?>
        </select>
        <select id="semesterFilter" class="filter-select table-view-field">
            <option value="">All Semesters</option>
            <?php foreach ($assignmentSemesters as $semesterNumber): ?>
                <option value="<?php echo e($semesterNumber); ?>">Sem <?php echo e($semesterNumber); ?></option>
            <?php endforeach; ?>
        </select>
        </div>
        <div class="table-view-meta" id="assignmentsMeta"></div>
    </div>

    <!-- Assign Teacher Form -->
    <div id="assignForm" class="form-container">
        <h3 style="margin-top: 0;">Assign Teacher to Subject</h3>
        <form onsubmit="handleAssign(event)">
            <div class="form-group">
                <label for="assignProgram">Program *</label>
                <select id="assignProgram" name="program_id" required>
                    <option value="">Select a program</option>
                    <?php foreach ($programsList as $progId => $progName): ?>
                        <option value="<?php echo e($progId); ?>"><?php echo e($progName); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="assignSemester">Semester *</label>
                <select id="assignSemester" name="semester_id" required>
                    <option value="">Select a semester</option>
                    <?php foreach ($semestersList as $semId => $semData): ?>
                        <option value="<?php echo e($semId); ?>">Sem <?php echo e($semData['number']); ?> (<?php echo e($semData['year']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="assignSubject">Subject *</label>
                <select id="assignSubject" name="subject_id" required>
                    <option value="">Select a subject</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo e($subject['id']); ?>" 
                                data-program="<?php echo e($subject['program_id']); ?>"
                                data-semester="<?php echo e($subject['semester_id']); ?>">
                            <?php echo e($subject['subject_name']); ?> (<?php echo e($subject['subject_code']); ?>) - <?php echo e($subject['program_name']); ?> Sem <?php echo e($subject['semester_number']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="assignTeacher">Teacher *</label>
                <select id="assignTeacher" name="teacher_id" required>
                    <option value="">Select a teacher</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo e($teacher['id']); ?>"><?php echo e($teacher['full_name']); ?> (<?php echo e($teacher['login_id']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="formMessage"></div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Create Assignment</button>
                <button type="button" class="btn-cancel" onclick="toggleAssignForm()">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Assignments Table -->
    <div class="table-container">
        <?php if (empty($assignments)): ?>
            <div class="empty-message">
                <p>No assignments found. <a href="#" onclick="toggleAssignForm(); return false;" style="color:#2563eb;text-decoration:underline;">Create one now</a></p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Teacher Name</th>
                        <th>Subject Name</th>
                        <th>Program</th>
                        <th>Semester</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="assignmentsTableBody">
                    <?php foreach ($assignments as $assignment): ?>
                        <tr data-program="<?php echo e($assignment['program_name'] ?? ''); ?>"
                            data-semester="<?php echo e($assignment['semester_number'] ?? ''); ?>"
                            data-teacher="<?php echo e($assignment['teacher_name'] ?? ''); ?>"
                            data-subject="<?php echo e($assignment['subject_name'] ?? ''); ?>"
                            data-search="<?php echo e(trim(($assignment['teacher_name'] ?? '') . ' ' . ($assignment['subject_name'] ?? '') . ' ' . ($assignment['subject_code'] ?? '') . ' ' . ($assignment['program_name'] ?? '') . ' ' . ($assignment['semester_number'] ?? '') . ' ' . ($assignment['academic_year'] ?? ''))); ?>">
                            <td><?php echo e($assignment['teacher_name'] ?? 'N/A'); ?></td>
                            <td><?php echo e($assignment['subject_name'] ?? 'N/A'); ?> (<?php echo e($assignment['subject_code'] ?? 'N/A'); ?>)</td>
                            <td><?php echo e($assignment['program_name'] ?? 'N/A'); ?></td>
                            <td><?php echo e($assignment['semester_number'] ?? 'N/A'); ?> - <?php echo e($assignment['academic_year'] ?? 'N/A'); ?></td>
                            <td>
                                <button onclick="removeAssignment(<?php echo e($assignment['id']); ?>, '<?php echo e($assignment['teacher_name']); ?>', '<?php echo e($assignment['subject_name']); ?>')" class="btn-danger">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if (!empty($assignments)): ?>
        <div class="table-view-pagination" id="assignmentsPager" style="margin-top: 14px;">
            <div class="pagination-info" id="assignmentsPageInfo"></div>
            <div class="pagination-actions">
                <button type="button" class="btn btn-ghost" id="assignmentsPrev">Previous</button>
                <button type="button" class="btn btn-ghost" id="assignmentsNext">Next</button>
            </div>
        </div>
    <?php endif; ?>

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
        function toggleAssignForm() {
            const form = document.getElementById('assignForm');
            form.classList.toggle('active');
            if (form.classList.contains('active')) {
                document.getElementById('assignProgram').focus();
            }
        }

        function handleAssign(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            fetch('<?php echo e(url('vp/assignments')); ?>', {
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
                    messageDiv.innerHTML = '<div class="success-message">Assignment created successfully. Refreshing...</div>';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    messageDiv.innerHTML = '<div class="error-message">' + (data.message || 'Error creating assignment') + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('formMessage').innerHTML = '<div class="error-message">Error: ' + error.message + '</div>';
            });
        }

        function removeAssignment(id, teacher, subject) {
            openConfirmModal(
                'Remove Assignment',
                'Remove assignment of ' + subject + ' from ' + teacher + '?',
                () => {
                    fetch('<?php echo e(url('vp/assignments')); ?>/' + id, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage('Assignment removed successfully.', 'success');
                            location.reload();
                        } else {
                            showMessage('Error: ' + (data.message || 'Failed to remove assignment'), 'error');
                        }
                    });
                }
            );
        }

        function showMessage(message, type) {
            const banner = document.getElementById('assignmentMessage');
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

        window.IMS?.initTableView({
            tbodyId: 'assignmentsTableBody',
            searchInputId: 'assignmentSearch',
            filters: [
                { id: 'programFilter', rowDatasetKey: 'program' },
                { id: 'semesterFilter', rowDatasetKey: 'semester' },
            ],
            metaId: 'assignmentsMeta',
            pagerId: 'assignmentsPager',
            pageInfoId: 'assignmentsPageInfo',
            prevId: 'assignmentsPrev',
            nextId: 'assignmentsNext',
            pageSize: 10,
            noResultsColSpan: 5,
            noResultsText: 'No matching assignments found.',
        });
    </script>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
