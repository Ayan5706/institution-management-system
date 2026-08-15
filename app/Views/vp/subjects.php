<?php
/** @var array $subjects */
/** @var array $semesters */
/** @var array $subject_types */
/** @var array $departments */
/** @var array $semester_numbers */
$activeNav = 'subjects';
$subjects = $subjects ?? [];
$semesters = $semesters ?? [];
$subject_types = $subject_types ?? [];
$departments = $departments ?? [];
$semester_numbers = $semester_numbers ?? [];

sort($subject_types);
sort($departments);
sort($semester_numbers);
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Subjects</h2>
            <div style="color:#64748b;">Manage course subjects</div>
        </div>
        <button onclick="toggleAddSubjectForm()" class="btn btn-primary">+ Add Subject</button>
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
            gap: 12px;
            margin-bottom: 15px;
            flex-wrap: nowrap;
            align-items: center;
            overflow: hidden;
        }

        .filter-item {
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .filter-group label {
            font-weight: 600;
            color: #0f172a;
            white-space: nowrap;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
        }
        .filter-group .filter-search {
            min-width: 160px;
        }

        .filter-group select {
            min-width: 160px;
        }

        @media (max-width: 1100px) {
            .filter-group {
                flex-wrap: nowrap;
            }
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

        .assignment-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .assigned {
            background: #d1fae5;
            color: #065f46;
        }

        .unassigned {
            background: #fed7aa;
            color: #92400e;
        }

        .assignment-link {
            text-decoration: none;
            cursor: pointer;
        }

        .assignment-link:focus-visible {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
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
    </style>

    <!-- Add Subject Form -->
    <div id="addSubjectForm" class="form-container">
        <h3 style="margin-top: 0;">Add New Subject</h3>
        <form onsubmit="handleAddSubject(event)">
            <!-- Step 1: Program & Semester Selection -->
            <div id="step1-selection">
                <div class="form-row">
                    <div class="form-group">
                        <label for="subjectProgram">Select Program *</label>
                        <select id="subjectProgram" onchange="updateSemesterOptions()" required>
                            <option value="">Choose a program</option>
                            <?php 
                            $programs_added = [];
                            foreach ($semesters as $sem):
                                if (!in_array($sem['program_name'], $programs_added)):
                                    $programs_added[] = $sem['program_name'];
                            ?>
                                <option value="<?php echo e($sem['program_id']); ?>" data-program="<?php echo e($sem['program_name']); ?>">
                                    <?php echo e($sem['program_name']); ?>
                                </option>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subjectSemester">Select Semester *</label>
                        <select id="subjectSemester" name="semester_id" required onchange="enableSubjectDetails()">
                            <option value="">First select a program</option>
                        </select>
                    </div>
                </div>
                <div style="display:flex; gap:10px; margin-top:15px;">
                    <button type="button" class="btn-submit" onclick="proceedToDetails()" style="opacity:0.6; cursor:not-allowed;" id="proceedBtn" disabled>Proceed to Subject Details</button>
                    <button type="button" class="btn-cancel" onclick="toggleAddSubjectForm()">Cancel</button>
                </div>
            </div>

            <!-- Step 2: Subject Details -->
            <div id="step2-details" style="display:none;">
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:12px; margin-bottom:15px;">
                    <p style="margin:0; color:#166534; font-size:0.9rem;">
                        <strong id="selectedProgramText"></strong> → <strong id="selectedSemesterText"></strong>
                    </p>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="subjectName">Subject Name *</label>
                        <input type="text" id="subjectName" name="subject_name" required placeholder="e.g., Database Management">
                    </div>
                    <div class="form-group">
                        <label for="subjectCode">Subject Code *</label>
                        <input type="text" id="subjectCode" name="subject_code" required placeholder="e.g., CS-201" pattern="^[A-Z0-9-]+$">
                    </div>
                </div>
                
                <div id="formMessage"></div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Save Subject</button>
                    <button type="button" class="btn-cancel" onclick="backToSelection()">Back</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function updateSemesterOptions() {
            const programId = document.getElementById('subjectProgram').value;
            const semesterSelect = document.getElementById('subjectSemester');
            const proceedBtn = document.getElementById('proceedBtn');
            
            semesterSelect.innerHTML = '<option value="">Choose a semester</option>';
            
            if (programId) {
                const semesters = <?php echo json_encode($semesters); ?>;
                const filtered = semesters.filter(s => s.program_id == programId);
                
                filtered.forEach(sem => {
                    const option = document.createElement('option');
                    option.value = sem.id;
                    option.textContent = `Sem ${sem.semester_number} (${sem.academic_year})`;
                    semesterSelect.appendChild(option);
                });
                
                proceedBtn.disabled = true;
                proceedBtn.style.opacity = '0.6';
                proceedBtn.style.cursor = 'not-allowed';
            }
        }
        
        function enableSubjectDetails() {
            const proceedBtn = document.getElementById('proceedBtn');
            const programId = document.getElementById('subjectProgram').value;
            const semesterId = document.getElementById('subjectSemester').value;
            
            if (programId && semesterId) {
                proceedBtn.disabled = false;
                proceedBtn.style.opacity = '1';
                proceedBtn.style.cursor = 'pointer';
            } else {
                proceedBtn.disabled = true;
                proceedBtn.style.opacity = '0.6';
                proceedBtn.style.cursor = 'not-allowed';
            }
        }
        
        function proceedToDetails() {
            const programSelect = document.getElementById('subjectProgram');
            const semesterSelect = document.getElementById('subjectSemester');
            const programText = programSelect.options[programSelect.selectedIndex].text;
            const semesterText = semesterSelect.options[semesterSelect.selectedIndex].text;
            
            document.getElementById('selectedProgramText').textContent = programText;
            document.getElementById('selectedSemesterText').textContent = semesterText;
            
            document.getElementById('step1-selection').style.display = 'none';
            document.getElementById('step2-details').style.display = 'block';
        }
        
        function backToSelection() {
            document.getElementById('step1-selection').style.display = 'block';
            document.getElementById('step2-details').style.display = 'none';
            document.getElementById('subjectName').value = '';
            document.getElementById('subjectCode').value = '';
        }
    </script>

    <div class="table-view-header">
        <div class="filter-group table-view-controls">
            <input type="text" id="subjectSearch" class="table-view-field filter-search" placeholder="Search subjects...">

            <div class="filter-item">
                <select id="departmentFilter" class="table-view-field">
                <option value="">All Departments</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?php echo e($department); ?>"><?php echo e($department); ?></option>
                <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-item">
                <select id="semesterNumberFilter" class="table-view-field">
                <option value="">All Semesters</option>
                <?php foreach ($semester_numbers as $number): ?>
                    <option value="<?php echo e($number); ?>">Sem <?php echo e($number); ?></option>
                <?php endforeach; ?>
                </select>
            </div>

        </div>
        <div class="table-view-meta" id="subjectsMeta"></div>
    </div>

    <!-- Subjects Table -->
    <div class="table-container">
        <?php if (empty($subjects)): ?>
            <div class="empty-message">
                <p>No subjects found. <a href="#" onclick="toggleAddSubjectForm(); return false;" style="color:#2563eb;text-decoration:underline;">Create one now</a></p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Subject Name</th>
                        <th>Subject Code</th>
                        <th>Program</th>
                        <th>Semester</th>
                        <th>Assigned Teacher</th>
                    </tr>
                </thead>
                <tbody id="subjectsTable">
                    <?php foreach ($subjects as $subject): ?>
                        <tr class="subject-row"
                            data-program="<?php echo e($subject['program_name'] ?? 'N/A'); ?>"
                            data-semester-number="<?php echo e($subject['semester_number'] ?? ''); ?>"
                            data-subject-type="<?php echo e($subject['subject_type'] ?? ''); ?>"
                            data-search="<?php echo e(trim(($subject['subject_name'] ?? '') . ' ' . ($subject['subject_code'] ?? '') . ' ' . ($subject['program_name'] ?? '') . ' ' . ($subject['semester_number'] ?? '') . ' ' . ($subject['academic_year'] ?? '') . ' ' . ($subject['teacher_name'] ?? ''))); ?>">
                            <td><?php echo e($subject['subject_name'] ?? 'N/A'); ?></td>
                            <td><?php echo e($subject['subject_code'] ?? 'N/A'); ?></td>
                            <td><?php echo e($subject['program_name'] ?? 'N/A'); ?></td>
                            <td><?php echo e($subject['academic_year'] ?? 'N/A') . ' (Sem ' . ($subject['semester_number'] ?? 'N/A') . ')'; ?></td>
                            <td>
                                <?php if (!empty($subject['teacher_name'])): ?>
                                    <span class="assignment-badge assigned"><?php echo e($subject['teacher_name']); ?></span>
                                <?php else: ?>
                                    <a class="assignment-badge unassigned assignment-link"
                                       href="<?php echo e(url('vp/assignments')); ?>"
                                       title="Assign a teacher">Unassigned</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if (!empty($subjects)): ?>
        <div class="table-view-pagination" id="subjectsPager" style="margin-top: 14px;">
            <div class="pagination-info" id="subjectsPageInfo"></div>
            <div class="pagination-actions">
                <button type="button" class="btn btn-ghost" id="subjectsPrev">Previous</button>
                <button type="button" class="btn btn-ghost" id="subjectsNext">Next</button>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function toggleAddSubjectForm() {
            const form = document.getElementById('addSubjectForm');
            form.classList.toggle('active');
            if (form.classList.contains('active')) {
                document.getElementById('subjectName').focus();
            }
        }

        function handleAddSubject(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            fetch('<?php echo e(url('vp/subjects')); ?>', {
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
                    messageDiv.innerHTML = '<div class="success-message">Subject added successfully. Refreshing...</div>';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    messageDiv.innerHTML = '<div class="error-message">' + (data.message || 'Error adding subject') + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('formMessage').innerHTML = '<div class="error-message">Error: ' + error.message + '</div>';
            });
        }

        window.IMS?.initTableView({
            tbodyId: 'subjectsTable',
            searchInputId: 'subjectSearch',
            filters: [
                { id: 'departmentFilter', rowDatasetKey: 'program' },
                { id: 'semesterNumberFilter', rowDatasetKey: 'semesterNumber' },
            ],
            metaId: 'subjectsMeta',
            pagerId: 'subjectsPager',
            pageInfoId: 'subjectsPageInfo',
            prevId: 'subjectsPrev',
            nextId: 'subjectsNext',
            pageSize: 10,
            noResultsColSpan: 5,
            noResultsText: 'No matching subjects found.',
        });
    </script>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
