<?php
/** @var array $programs */
$activeNav = 'programs';
$programs = $programs ?? [];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Programs</h2>
            <div style="color:#64748b;">Manage academic programs</div>
        </div>
        <button onclick="toggleAddProgramForm()" class="btn btn-primary">+ Add Program</button>
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

        .success-message {
            color: #10b981;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .filter-bar {
            display: flex;
            gap: 12px;
            margin: 10px 0 18px;
            flex-wrap: wrap;
        }

        .filter-input,
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
            background: #fff;
        }

        @media (max-width: 768px) {
            .toolbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>

    <div class="table-view-header">
        <div class="filter-bar table-view-controls">
            <input type="text" id="programSearch" class="filter-input table-view-field" placeholder="Search programs...">
            <select id="programStatus" class="filter-select table-view-field">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="table-view-meta" id="programsMeta"></div>
    </div>

    <!-- Add Program Form -->
    <div id="addProgramForm" class="form-container">
        <h3 style="margin-top: 0;">Add New Program</h3>
        <form onsubmit="handleAddProgram(event)">
            <div class="form-group">
                <label for="programName">Program Name</label>
                <input type="text" id="programName" name="program_name" required placeholder="e.g., Bachelor of Science in Computer Science">
            </div>
            <div class="form-group">
                <label for="programCode">Program Code</label>
                <input type="text" id="programCode" name="program_code" required placeholder="e.g., BS-CS" pattern="^[A-Z0-9-]+$" title="Use uppercase letters, numbers, and hyphens only">
            </div>
            <div class="form-group">
                <label for="duration">Duration (Semesters)</label>
                <input type="number" id="duration" name="duration_semesters" required min="1" max="12" placeholder="e.g., 8">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="is_active">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div id="formMessage"></div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Save Program</button>
                <button type="button" class="btn-cancel" onclick="toggleAddProgramForm()">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Programs Table -->
    <div class="table-container">
        <?php if (empty($programs)): ?>
            <div class="empty-message">
                <p>No programs found. <a href="#" onclick="toggleAddProgramForm(); return false;" style="color:#2563eb;text-decoration:underline;">Create one now</a></p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Program Name</th>
                        <th>Program Code</th>
                        <th>Duration (Semesters)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="programsTableBody">
                    <?php foreach ($programs as $program): ?>
                        <tr data-status="<?php echo ($program['is_active'] ?? false) ? 'active' : 'inactive'; ?>"
                            data-name="<?php echo e($program['program_name'] ?? ''); ?>"
                            data-code="<?php echo e($program['program_code'] ?? ''); ?>"
                            data-search="<?php echo e(trim(($program['program_name'] ?? '') . ' ' . ($program['program_code'] ?? ''))); ?>">
                            <td><?php echo e($program['program_name'] ?? 'N/A'); ?></td>
                            <td><?php echo e($program['program_code'] ?? 'N/A'); ?></td>
                            <td><?php echo e($program['duration_semesters'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="status-badge <?php echo ($program['is_active'] ?? false) ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo ($program['is_active'] ?? false) ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if (!empty($programs)): ?>
        <div class="table-view-pagination" id="programsPager" style="margin-top: 14px;">
            <div class="pagination-info" id="programsPageInfo"></div>
            <div class="pagination-actions">
                <button type="button" class="btn btn-ghost" id="programsPrev">Previous</button>
                <button type="button" class="btn btn-ghost" id="programsNext">Next</button>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function toggleAddProgramForm() {
            const form = document.getElementById('addProgramForm');
            form.classList.toggle('active');
            if (form.classList.contains('active')) {
                document.getElementById('programName').focus();
            }
        }

        function handleAddProgram(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            fetch('<?php echo e(url('vp/programs')); ?>', {
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
                    messageDiv.innerHTML = '<div class="success-message">Program added successfully. Refreshing...</div>';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    messageDiv.innerHTML = '<div class="error-message">' + (data.message || 'Error adding program') + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('formMessage').innerHTML = '<div class="error-message">Error: ' + error.message + '</div>';
            });
        }

        window.IMS?.initTableView({
            tbodyId: 'programsTableBody',
            searchInputId: 'programSearch',
            filters: [
                { id: 'programStatus', rowDatasetKey: 'status' },
            ],
            metaId: 'programsMeta',
            pagerId: 'programsPager',
            pageInfoId: 'programsPageInfo',
            prevId: 'programsPrev',
            nextId: 'programsNext',
            pageSize: 10,
            noResultsColSpan: 4,
            noResultsText: 'No matching programs found.',
        });
    </script>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
