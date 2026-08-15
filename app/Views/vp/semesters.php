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
<!-- Flatpickr CSS and JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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

        .date-picker-wrapper {
            position: relative;
            display: block;
        }

        .date-picker-wrapper input[type="date"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
            background: #fff;
            color: #0f172a;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .date-picker-wrapper input[type="date"]:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .date-picker-wrapper input[type="date"]:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .date-picker-wrapper input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            border-radius: 4px;
            margin-right: 2px;
            opacity: 0.6;
            filter: invert(0.8);
        }

        .date-picker-wrapper input[type="date"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }

        .date-picker-wrapper input[type="date"].error {
            border-color: #dc2626;
            background-color: #fef2f2;
        }

        .date-picker-wrapper input[type="date"].error:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .date-display {
            display: none;
        }

        .form-hint {
            display: block;
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 4px;
        }

        .form-hint.error {
            color: #dc2626;
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

        .quick-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 8px 0 16px;
            flex-wrap: wrap;
        }

        .quick-toggle .label {
            font-weight: 600;
            color: #0f172a;
        }

        .toggle-btn {
            padding: 8px 14px;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #0f172a;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .toggle-btn:hover {
            background: #f8fafc;
        }

        .toggle-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .toggle-btn.active {
            background: linear-gradient(135deg, #2f7f87, #6aa3a8);
            color: #fff;
            border-color: #2f7f87;
        }

        .toggle-btn.active:hover {
            background: linear-gradient(135deg, #1f5459, #2f7f87);
            border-color: #1f5459;
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

        .success-message {
            color: #065f46;
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 12px;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .error-message {
            color: #991b1b;
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 12px;
            border-radius: 4px;
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
                    <select id="semNumber" name="semester_number" required disabled>
                        <option value="">Select program first</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="startAcademicYear">Program Start Year</label>
                    <input type="number" id="startAcademicYear" name="start_academic_year" required min="2000" max="2099" placeholder="e.g., 2025">
                </div>
                <div class="form-group">
                    <label for="endAcademicYear">Program End Year</label>
                    <input type="number" id="endAcademicYear" name="end_academic_year" required min="2000" max="2099" placeholder="e.g., 2028">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="startDate">Semester Start Date</label>
                    <div class="date-picker-wrapper">
                        <input type="text" id="startDate" name="start_date" class="flatpickr-input" required placeholder="Select date...">
                    </div>
                    <small class="form-hint">Date must be between 2000 and 2099</small>
                </div>
                <div class="form-group">
                    <label for="endDate">Semester End Date</label>
                    <div class="date-picker-wrapper">
                        <input type="text" id="endDate" name="end_date" class="flatpickr-input" required placeholder="Select date...">
                    </div>
                    <small class="form-hint">Date must be between 2000 and 2099</small>
                </div>
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
        <input type="text" id="semesterSearch" placeholder="Search program, year, status..." oninput="filterSemesters()">

        <select id="programFilter" onchange="filterSemesters()">
            <option value="">All Programs</option>
            <?php foreach ($programs as $prog): ?>
                <option value="<?php echo e($prog['program_name']); ?>" data-program-id="<?php echo e($prog['id']); ?>"><?php echo e($prog['program_name']); ?></option>
            <?php endforeach; ?>
        </select>

        <select id="semesterNumberFilter" onchange="filterSemesters()">
            <option value="">All Semesters</option>
            <?php foreach ($semester_numbers as $number): ?>
                <option value="<?php echo e($number); ?>">Sem <?php echo e($number); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="quick-toggle">
        <button type="button" class="toggle-btn" id="toggleOddBtn" onclick="requestTermToggle('odd')">Odd Semesters</button>
        <button type="button" class="toggle-btn" id="toggleEvenBtn" onclick="requestTermToggle('even')">Even Semesters</button>
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
                        <th>Semester Start Date</th>
                        <th>Semester End Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="semestersTable">
                    <?php foreach ($semesters as $sem): ?>
                        <?php $semNum = (int) ($sem['semester_number'] ?? 0); ?>
                        <tr class="semester-row"
                            data-semester-id="<?php echo e($sem['id']); ?>"
                            data-program="<?php echo e($sem['program_name'] ?? 'N/A'); ?>"
                            data-program-id="<?php echo e($sem['program_id'] ?? 0); ?>"
                            data-program-name="<?php echo e($sem['program_name'] ?? 'N/A'); ?>"
                            data-semester-number="<?php echo e($sem['semester_number'] ?? ''); ?>"
                            data-term="<?php echo ($semNum % 2 === 0) ? 'even' : 'odd'; ?>"
                            data-search="<?php echo e(strtolower(trim(
                                ($sem['program_name'] ?? '') . ' ' .
                                ($sem['start_academic_year'] ?? '') . '-' . ($sem['end_academic_year'] ?? '') . ' ' .
                                ($sem['start_date'] ?? '') . ' ' .
                                ($sem['end_date'] ?? '') . ' ' .
                                ($sem['semester_number'] ?? '') . ' ' .
                                (((int) ($sem['is_current'] ?? 0) === 1) ? 'current active' : 'inactive')
                            ))); ?>">
                            <td><?php echo e($sem['program_name'] ?? 'N/A'); ?></td>
                            <td><?php echo e($sem['semester_number'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="term-badge">
                                    <?php 
                                        echo ($semNum % 2 === 0) ? 'Even' : 'Odd';
                                    ?>
                                </span>
                            </td>
                            <td><?php echo e($sem['academic_year'] ?? 'N/A'); ?></td>
                            <td><?php 
                                $startDate = isset($sem['start_date']) ? DateTime::createFromFormat('Y-m-d', $sem['start_date']) : null;
                                echo $startDate ? $startDate->format('d-m-Y') : 'N/A'; 
                            ?></td>
                            <td><?php 
                                $endDate = isset($sem['end_date']) ? DateTime::createFromFormat('Y-m-d', $sem['end_date']) : null;
                                echo $endDate ? $endDate->format('d-m-Y') : 'N/A'; 
                            ?></td>
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

    <!-- Semester Creation Confirmation Modal -->
    <div id="createSemesterModal" class="modal-backdrop" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true">
            <h3 class="modal-title">Confirm Semester Details</h3>
            <p class="modal-text">Please review the semester information before creating:</p>
            <div style="background: #f8fafc; padding: 14px; border-radius: 8px; margin-bottom: 16px; font-size: 0.9rem;">
                <div style="margin-bottom: 10px;"><strong>Program:</strong> <span id="confirmProgramName"></span></div>
                <div style="margin-bottom: 10px;"><strong>Semester Number:</strong> <span id="confirmSemesterNumber"></span></div>
                <div style="margin-bottom: 10px;"><strong>Academic Year:</strong> <span id="confirmProgramPeriod"></span></div>
                <div style="margin-bottom: 10px;"><strong>Semester Start Date:</strong> <span id="confirmStartDate"></span></div>
                <div><strong>Semester End Date:</strong> <span id="confirmEndDate"></span></div>
            </div>
            <div id="confirmCreateMessage"></div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeCreateSemesterModal()">Cancel</button>
                <button type="button" class="btn-submit" onclick="submitSemesterCreation()">Create Semester</button>
            </div>
        </div>
    </div>

    <script>
        // Date formatting utility for display
        const formatDateDisplay = (dateStr) => {
            if (!dateStr) return 'N/A';
            const [year, month, day] = dateStr.split('-');
            return `${day}-${month}-${year}`;
        };

        const getSelectedProgramInfo = () => {
            const select = document.getElementById('programFilter');
            if (!select || !select.value) return null;
            const option = select.options[select.selectedIndex];
            const programId = parseInt(option?.dataset?.programId || '0');
            if (!programId) return null;
            return {
                id: programId,
                name: option?.text || ''
            };
        };

        const updateQuickToggleState = () => {
            const info = getSelectedProgramInfo();
            const oddBtn = document.getElementById('toggleOddBtn');
            const evenBtn = document.getElementById('toggleEvenBtn');

            if (oddBtn) oddBtn.disabled = false;
            if (evenBtn) evenBtn.disabled = false;

            const noProgram = !info;
            if (oddBtn) oddBtn.dataset.requireProgram = noProgram ? '1' : '0';
            if (evenBtn) evenBtn.dataset.requireProgram = noProgram ? '1' : '0';

            // Determine which term is currently active for the selected program
            if (info && info.id) {
                const allRows = document.querySelectorAll('.semester-row');
                let activeOdd = false;
                let activeEven = false;

                allRows.forEach(row => {
                    const progId = parseInt(row.dataset.programId);
                    const term = row.dataset.term;
                    const isCurrent = row.querySelector('.status-current') !== null;

                    if (progId === info.id && isCurrent) {
                        if (term === 'odd') {
                            activeOdd = true;
                        } else if (term === 'even') {
                            activeEven = true;
                        }
                    }
                });

                // Update button states
                if (oddBtn) {
                    if (activeOdd) {
                        oddBtn.classList.add('active');
                    } else {
                        oddBtn.classList.remove('active');
                    }
                }

                if (evenBtn) {
                    if (activeEven) {
                        evenBtn.classList.add('active');
                    } else {
                        evenBtn.classList.remove('active');
                    }
                }
            } else {
                // Remove active class if no program selected
                if (oddBtn) oddBtn.classList.remove('active');
                if (evenBtn) evenBtn.classList.remove('active');
            }
        };

        // Store programs data for semester population
        const programsData = <?php echo json_encode($programs); ?>;

        // Populate semester number dropdown based on selected program
        const populateSemesterNumbers = (programId) => {
            const semesterSelect = document.getElementById('semNumber');
            
            // Find the selected program
            const selectedProgram = programsData.find(p => p.id == programId);
            
            if (!selectedProgram) {
                semesterSelect.innerHTML = '<option value="">Select program first</option>';
                semesterSelect.disabled = true;
                return;
            }
            
            // Get the duration in semesters
            const duration = parseInt(selectedProgram.duration_semesters) || 0;
            
            // Populate dropdown with 1 to duration
            let options = '<option value="">Select semester</option>';
            for (let i = 1; i <= duration; i++) {
                options += `<option value="${i}">Semester ${i}</option>`;
            }
            
            semesterSelect.innerHTML = options;
            semesterSelect.disabled = false;
            semesterSelect.value = ''; // Reset selection
        };

        // Prevent arrow key navigation in semester select after selection
        document.addEventListener('DOMContentLoaded', function() {
            const programSelect = document.getElementById('semProgram');
            const semesterSelect = document.getElementById('semNumber');
            
            // When program is selected, populate semesters
            if (programSelect) {
                programSelect.addEventListener('change', function() {
                    if (this.value) {
                        populateSemesterNumbers(this.value);
                    } else {
                        // Reset semester select if program is cleared
                        semesterSelect.innerHTML = '<option value="">Select program first</option>';
                        semesterSelect.disabled = true;
                    }
                });
                
                // Prevent arrow key navigation in program select if a program is selected
                programSelect.addEventListener('keydown', function(event) {
                    if (this.value && (event.key === 'ArrowUp' || event.key === 'ArrowDown')) {
                        event.preventDefault();
                    }
                });
            }
            
            // Prevent arrow key navigation in semester select if a semester is selected
            if (semesterSelect) {
                semesterSelect.addEventListener('keydown', function(event) {
                    if (this.value && (event.key === 'ArrowUp' || event.key === 'ArrowDown')) {
                        event.preventDefault();
                    }
                });
            }
        });

        document.getElementById('programFilter')?.addEventListener('change', updateQuickToggleState);

        // Calculate days in month
        const getDaysInMonth = (month, year) => {
            const daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            if (month === 2 && year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0)) {
                return 29; // Leap year
            }
            return daysInMonth[month - 1];
        };

        // Validate date input
        const validateDateInput = (inputElement) => {
            const value = inputElement.value;
            if (!value) return true; // Empty is OK (will be caught by required)

            // Check if it matches YYYY-MM-DD format
            if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                return false;
            }

            const [year, month, day] = value.split('-').map(Number);

            // Validate year is 4 digits and within range
            if (year < 2000 || year > 2099) {
                return false;
            }

            // Validate month
            if (month < 1 || month > 12) {
                return false;
            }

            // Validate day
            if (day < 1 || day > 31) {
                return false;
            }

            // Basic date validation (month-specific)
            const daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            if (year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0)) {
                daysInMonth[1] = 29; // Leap year
            }

            if (day > daysInMonth[month - 1]) {
                return false;
            }

            return true;
        };

        // Setup date input validation
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Flatpickr for start date
            flatpickr('#startDate', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd-m-Y',
                minDate: '2000-01-01',
                maxDate: '2099-12-31',
                monthSelectorType: 'static',
                yearSelectableTo: 2099,
                yearSelectableFrom: 2000,
                disableMobile: true,
                onClose: function(selectedDates, dateStr) {
                    if (dateStr) {
                        const startDateInput = document.getElementById('startDate');
                        validateAndUpdateField(startDateInput);
                    }
                }
            });
            
            // Initialize Flatpickr for end date
            flatpickr('#endDate', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd-m-Y',
                minDate: '2000-01-01',
                maxDate: '2099-12-31',
                monthSelectorType: 'static',
                yearSelectableTo: 2099,
                yearSelectableFrom: 2000,
                disableMobile: true,
                onClose: function(selectedDates, dateStr) {
                    if (dateStr) {
                        const endDateInput = document.getElementById('endDate');
                        validateAndUpdateField(endDateInput);
                    }
                }
            });

            const validateAndUpdateField = (input) => {
                const isValid = validateDateInput(input);
                if (!isValid && input.value) {
                    input.classList.add('error');
                    input.setCustomValidity('Please enter a valid date between 2000-01-01 and 2099-12-31');
                } else {
                    input.classList.remove('error');
                    input.setCustomValidity('');
                }
            };
        });

        function toggleAddSemesterForm() {
            const form = document.getElementById('addSemesterForm');
            form.classList.toggle('active');
            if (form.classList.contains('active')) {
                // Reset all form fields
                document.getElementById('semProgram').value = '';
                document.getElementById('semNumber').innerHTML = '<option value="">Select program first</option>';
                document.getElementById('semNumber').disabled = true;
                document.getElementById('startAcademicYear').value = '';
                document.getElementById('endAcademicYear').value = '';
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';
                document.getElementById('formMessage').innerHTML = '';
                
                document.getElementById('semProgram').focus();
            }
        }

        function handleAddSemester(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            
            // Convert FormData to object with proper type conversions
            const data = {
                program_id: parseInt(formData.get('program_id')),
                semester_number: parseInt(formData.get('semester_number')),
                start_date: formData.get('start_date'),
                end_date: formData.get('end_date'),
                start_academic_year: parseInt(formData.get('start_academic_year')),
                end_academic_year: parseInt(formData.get('end_academic_year'))
            };

            // Validate all required fields
            if (!data.program_id || !data.semester_number || !data.start_date || !data.end_date || !data.start_academic_year || !data.end_academic_year) {
                document.getElementById('formMessage').innerHTML = '<div class="error-message">All fields are required</div>';
                return;
            }

            // Validate date inputs
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            
            if (!validateDateInput(startDateInput)) {
                document.getElementById('formMessage').innerHTML = '<div class="error-message">Semester start date must be valid and between 2000-01-01 and 2099-12-31</div>';
                startDateInput.focus();
                return;
            }

            if (!validateDateInput(endDateInput)) {
                document.getElementById('formMessage').innerHTML = '<div class="error-message">Semester end date must be valid and between 2000-01-01 and 2099-12-31</div>';
                endDateInput.focus();
                return;
            }

            // Validate end date is after start date
            if (data.start_date >= data.end_date) {
                document.getElementById('formMessage').innerHTML = '<div class="error-message">End date must be after start date</div>';
                return;
            }

            // Find program name
            const programSelect = document.getElementById('semProgram');
            const programName = programSelect.options[programSelect.selectedIndex]?.text || 'Unknown Program';

            // Populate confirmation modal
            document.getElementById('confirmProgramName').textContent = programName;
            document.getElementById('confirmSemesterNumber').textContent = data.semester_number;
            document.getElementById('confirmProgramPeriod').textContent = data.start_academic_year + ' - ' + data.end_academic_year;
            document.getElementById('confirmStartDate').textContent = formatDateDisplay(data.start_date);
            document.getElementById('confirmEndDate').textContent = formatDateDisplay(data.end_date);
            document.getElementById('confirmCreateMessage').innerHTML = '';

            // Store form data for submission
            window.pendingSemesterData = data;

            // Show confirmation modal
            document.getElementById('createSemesterModal').classList.add('show');
            document.getElementById('createSemesterModal').setAttribute('aria-hidden', 'false');
        }

        function closeCreateSemesterModal() {
            document.getElementById('createSemesterModal').classList.remove('show');
            document.getElementById('createSemesterModal').setAttribute('aria-hidden', 'true');
            window.pendingSemesterData = null;
        }

        function submitSemesterCreation() {
            const data = window.pendingSemesterData;
            if (!data) {
                document.getElementById('confirmCreateMessage').innerHTML = '<div class="error-message">No data to submit</div>';
                return;
            }

            // Validate data
            if (!data.program_id || !data.semester_number || !data.start_date || !data.end_date || !data.start_academic_year || !data.end_academic_year) {
                document.getElementById('confirmCreateMessage').innerHTML = '<div class="error-message">Missing required fields</div>';
                return;
            }

            const jsonData = JSON.stringify(data);
            console.log('Sending data:', jsonData);

            fetch('<?php echo e(url('vp/semesters')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: jsonData
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                const messageDiv = document.getElementById('confirmCreateMessage');
                if (data.success) {
                    messageDiv.innerHTML = '<div class="success-message">Semester created successfully. Refreshing...</div>';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    messageDiv.innerHTML = '<div class="error-message">' + (data.message || 'Error creating semester') + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('confirmCreateMessage').innerHTML = '<div class="error-message">Error: ' + error.message + '</div>';
            });
        }

        // Close modal when clicking outside of it
        document.getElementById('createSemesterModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'createSemesterModal') {
                closeCreateSemesterModal();
            }
        });

        function activateSemester(id) {
            const row = document.querySelector(`.semester-row[data-semester-id="${id}"]`);
            if (!row) {
                showMessage('Unable to find selected semester.', 'error');
                return;
            }

            const programId = parseInt(row.dataset.programId || '0');
            const term = (row.dataset.term || '').toLowerCase();
            const programName = row.dataset.programName || 'this program';

            if (!programId || (term !== 'odd' && term !== 'even')) {
                showMessage('Missing program or term details for this semester.', 'error');
                return;
            }

            const termLabel = term === 'odd' ? 'Odd' : 'Even';
            const oppositeLabel = term === 'odd' ? 'Even' : 'Odd';

            openConfirmModal(
                `Activate ${termLabel} Semesters`,
                `Activate all ${termLabel.toLowerCase()} semesters for ${programName}? This will deactivate all ${oppositeLabel.toLowerCase()} semesters.`,
                () => {
                    toggleTermForProgram(programId, term);
                }
            );
        }

        function requestTermToggle(term) {
            const programInfo = getSelectedProgramInfo();
            if (!programInfo) {
                showMessage('Select a program to use quick toggle.', 'error');
                return;
            }

            const termLabel = term === 'odd' ? 'Odd' : 'Even';
            const oppositeLabel = term === 'odd' ? 'Even' : 'Odd';

            openConfirmModal(
                `Activate ${termLabel} Semesters`,
                `Activate all ${termLabel.toLowerCase()} semesters for ${programInfo.name}? This will deactivate all ${oppositeLabel.toLowerCase()} semesters.`,
                () => {
                    toggleTermForProgram(programInfo.id, term);
                }
            );
        }

        function toggleTermForProgram(programId, term) {
            fetch('<?php echo e(url('vp/semesters/toggle-term')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    program_id: programId,
                    term: term
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message || 'Semesters updated successfully.', 'success');
                    location.reload();
                } else {
                    showMessage('Error: ' + (data.message || 'Failed to update semesters'), 'error');
                }
            })
            .catch(error => {
                showMessage('Error: ' + error.message, 'error');
            });
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
            row.innerHTML = '<td colspan="8" class="empty-message">No matching semesters found.</td>';
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

        const programFilter = document.getElementById('programFilter');
        if (programFilter && programFilter.options.length === 2 && programFilter.selectedIndex === 0) {
            programFilter.selectedIndex = 1;
            filterSemesters();
        }

        updateQuickToggleState();

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
