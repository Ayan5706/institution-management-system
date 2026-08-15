<?php
/** @var array $teachers */
/** @var array $programs */
$activeNav = 'teachers';
$teachers = $teachers ?? [];
$programs = $programs ?? [];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Faculty Records</h2>
            <div style="color:#64748b;">View all teaching staff (read-only)</div>
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

        .teacher-name {
            font-weight: 600;
            color: #0f172a;
        }

        .department-badge {
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

        .stats-bar {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin: 20px 0;
        }

        .stat-item {
            padding: 16px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0f172a;
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
    </style>

    <div class="table-view-header">
        <div class="filter-bar table-view-controls">
        <input type="text" id="searchInput" class="table-view-field" placeholder="Search teachers...">
        <select class="filter-select table-view-field" id="statusFilter">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <select class="filter-select table-view-field" id="programFilter">
            <option value="">All Programs</option>
            <?php foreach ($programs as $program): ?>
                <option value="<?php echo (int) ($program['id'] ?? 0); ?>"><?php echo e($program['program_name'] ?? ''); ?></option>
            <?php endforeach; ?>
        </select>
        </div>
        <div class="table-view-meta" id="teachersMeta"></div>
    </div>

    <div class="table-container">
        <table id="teachersTable">
            <thead>
                <tr>
                    <th>Staff ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Program</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="teachersTableBody">
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #64748b;">
                        Loading teachers...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="table-view-pagination" id="teachersPager" style="margin-top: 14px; display: none;">
        <div class="pagination-info" id="teachersPageInfo"></div>
        <div class="pagination-actions">
            <button type="button" class="btn btn-ghost" id="teachersPrev">Previous</button>
            <button type="button" class="btn btn-ghost" id="teachersNext">Next</button>
        </div>
    </div>
</div>

<script>
    let allTeachers = [];
    let tableView = null;

    // Load teachers on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadTeachers();
    });

    function loadTeachers() {
        fetch('<?php echo e(url('api/principal/teachers')); ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                allTeachers = data.data;
                displayTeachers(allTeachers);
                tableView = window.IMS?.initTableView({
                    tbodyId: 'teachersTableBody',
                    searchInputId: 'searchInput',
                    filters: [
                        { id: 'statusFilter', rowDatasetKey: 'status' },
                        { id: 'programFilter', rowDatasetKey: 'programIds', mode: 'csv-includes' },
                    ],
                    metaId: 'teachersMeta',
                    pagerId: 'teachersPager',
                    pageInfoId: 'teachersPageInfo',
                    prevId: 'teachersPrev',
                    nextId: 'teachersNext',
                    pageSize: 10,
                    noResultsColSpan: 6,
                    noResultsText: 'No matching teachers found.',
                });
            } else {
                showEmptyState();
            }
        })
        .catch(error => {
            console.error('Error loading teachers:', error);
            showEmptyState();
        });
    }

    function displayTeachers(teachers) {
        const tbody = document.getElementById('teachersTableBody');
        const detailBase = '<?php echo e(url('principal/teachers')); ?>';
        
        if (!teachers || teachers.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #64748b;">No teachers found.</td></tr>';
            return;
        }

        tbody.innerHTML = teachers.map(teacher => `
            <tr data-status="${teacher.is_active ? 'active' : 'inactive'}"
                data-program-ids="${escapeHtml(String(teacher.program_ids || ''))}"
                data-search="${escapeHtml(`${teacher.login_id || ''} ${teacher.full_name || ''} ${teacher.email || ''} ${teacher.program_names || ''}`.trim())}">
                <td>${escapeHtml(teacher.login_id || 'N/A')}</td>
                <td><span class="teacher-name">${escapeHtml(teacher.full_name || 'N/A')}</span></td>
                <td>${escapeHtml(teacher.email || 'N/A')}</td>
                <td>${escapeHtml(teacher.program_names || 'Unassigned')}</td>
                <td>
                    <span class="status-badge ${teacher.is_active ? 'status-active' : 'status-inactive'}">
                        ${teacher.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td>
                    <a class="view-btn" href="${detailBase}/${teacher.id}">View</a>
                </td>
            </tr>
        `).join('');
    }

    function showEmptyState() {
        const tbody = document.getElementById('teachersTableBody');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px 20px; color: #64748b;"><p>No teachers found.</p></td></tr>';
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
