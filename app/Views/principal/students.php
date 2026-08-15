<?php
/** @var array $students */
/** @var array $semesters */
$activeNav = 'students';
$students = $students ?? [];
$semesters = $semesters ?? [];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Student Records</h2>
            <div style="color:#64748b;">View all enrolled students (read-only)</div>
        </div>
    </div>

    <style>
        .filter-bar {
            display: flex;
            gap: 12px;
            margin: 20px 0;
            flex-wrap: nowrap;
            align-items: center;
            overflow-x: auto;
        }

        .filter-bar input[type="text"],
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            cursor: pointer;
            flex: 1;
            min-width: 150px;
            font-size: 0.9rem;
        }

        .filter-bar input[type="text"] {
            cursor: text;
        }

        .filter-select {
            cursor: pointer;
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

        #studentsTable th {
            background: #f8fafc;
            padding: 14px;
            text-align: center;
            font-weight: 700;
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
            vertical-align: middle;
        }

        #studentsTable td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
            vertical-align: middle;
        }

        tr:hover {
            background: #f8fafc;
        }

        .student-link {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
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

        .empty-message {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }

        .view-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #0f172a;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
        }

        .view-btn:hover {
            background: #e2e8f0;
        }

        .action-col {
            text-align: center;
            white-space: nowrap;
        }
    </style>

    <div class="table-view-header">
        <div class="filter-bar table-view-controls">
        <input type="text" id="searchInput" class="table-view-field" placeholder="Search students...">
        <select class="filter-select table-view-field" id="programFilter">
            <option value="">All Programs</option>
            <?php foreach ($programs ?? [] as $program): ?>
                <option value="<?php echo e($program['program_code'] ?? ''); ?>"><?php echo e($program['program_name'] ?? ''); ?></option>
            <?php endforeach; ?>
        </select>
        <select class="filter-select table-view-field" id="semesterFilter">
            <option value="">All Semesters</option>
            <?php foreach ($semesters as $semester): ?>
                <option value="<?php echo (int) ($semester['id'] ?? 0); ?>">
                    <?php echo e(($semester['program_name'] ?? 'Program') . ' - ' . ($semester['academic_year'] ?? '') . ' S' . (int) ($semester['semester_number'] ?? 0)); ?>
                </option>
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
                    <th>Semester</th>
                    <th>Status</th>
                    <th class="action-col">Action</th>
                </tr>
            </thead>
            <tbody id="studentsTableBody">
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #64748b;">
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

<script>
    let allStudents = [];
    let tableView = null;

    // Load students on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadStudents();
    });

    function loadStudents() {
        fetch('<?php echo e(url('api/principal/students')); ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                allStudents = data.data;
                displayStudents(allStudents);
                tableView = window.IMS?.initTableView({
                    tbodyId: 'studentsTableBody',
                    searchInputId: 'searchInput',
                    filters: [
                        { id: 'programFilter', rowDatasetKey: 'program' },
                        { id: 'semesterFilter', rowDatasetKey: 'semester' },
                        { id: 'statusFilter', rowDatasetKey: 'status' },
                    ],
                    metaId: 'studentsMeta',
                    pagerId: 'studentsPager',
                    pageInfoId: 'studentsPageInfo',
                    prevId: 'studentsPrev',
                    nextId: 'studentsNext',
                    pageSize: 10,
                    noResultsColSpan: 6,
                    noResultsText: 'No matching students found.',
                });
            } else {
                showEmptyState();
            }
        })
        .catch(error => {
            console.error('Error loading students:', error);
            showEmptyState();
        });
    }

    function displayStudents(students) {
        const tbody = document.getElementById('studentsTableBody');
        const detailBase = '<?php echo e(url('principal/students')); ?>';
        
        if (!students || students.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #64748b;">No students found.</td></tr>';
            return;
        }

        tbody.innerHTML = students.map(student => `
            <tr data-program="${escapeHtml(String(student.program_code || ''))}"
                data-semester="${escapeHtml(String(student.semester_id || ''))}"
                data-status="${student.is_active ? 'active' : 'inactive'}"
                data-search="${escapeHtml(`${student.registration_number || ''} ${student.full_name || ''} ${student.program_code || ''} ${student.semester_label || ''}`.trim())}">
                <td>${escapeHtml(student.registration_number || 'N/A')}</td>
                <td>${escapeHtml(student.full_name || 'N/A')}</td>
                <td><span class="program-badge">${escapeHtml(student.program_code || 'N/A')}</span></td>
                <td>${escapeHtml(student.semester_label || 'N/A')}</td>
                <td>
                    <span class="status-badge" style="padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; background: ${student.is_active ? '#d1fae5' : '#fee2e2'}; color: ${student.is_active ? '#065f46' : '#991b1b'};">
                        ${student.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td class="action-col">
                    <a class="view-btn" href="${detailBase}/${student.id}">View</a>
                </td>
            </tr>
        `).join('');
    }

    function showEmptyState() {
        const tbody = document.getElementById('studentsTableBody');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px 20px; color: #64748b;"><p>No students found.</p></td></tr>';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
