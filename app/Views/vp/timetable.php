<?php
/** @var array $timetable */
/** @var array $assignments */
/** @var array $working_days */
/** @var string $day_start_time */
/** @var string $day_end_time */
$activeNav = 'timetable';
$timetable = $timetable ?? [];
$assignments = $assignments ?? [];
$working_days = $working_days ?? [];
$day_start_time = $day_start_time ?? '';
$day_end_time = $day_end_time ?? '';

$programsList = [];
$semestersList = [];
foreach ($assignments as $assign) {
    if (!empty($assign['program_id']) && !empty($assign['program_name'])) {
        if (!isset($programsList[$assign['program_id']])) {
            $programsList[$assign['program_id']] = $assign['program_name'];
        }
    }
    if (!empty($assign['semester_id']) && !empty($assign['semester_number'])) {
        if (!isset($semestersList[$assign['semester_id']])) {
            $semestersList[$assign['semester_id']] = [
                'number' => $assign['semester_number'],
                'year' => $assign['academic_year'] ?? ''
            ];
        }
    }
}
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Timetable</h2>
            <div style="color:#64748b;">Manage class schedules</div>
        </div>
        <button onclick="toggleAddSlotForm()" class="btn btn-primary">+ Add Slot</button>
    </div>

    <div class="table-view-header">
        <div class="filter-group table-view-controls">
            <select id="programFilter" class="table-view-field">
                <option value="">All Programs</option>
                <?php foreach ($programsList as $progId => $progName): ?>
                    <option value="<?php echo e($progName); ?>"><?php echo e($progName); ?></option>
                <?php endforeach; ?>
            </select>
            <select id="semesterFilter" class="table-view-field">
                <option value="">All Semesters</option>
                <?php foreach ($semestersList as $semId => $semData): ?>
                    <option value="<?php echo e($semData['number']); ?>">Sem <?php echo e($semData['number']); ?> (<?php echo e($semData['year']); ?>)</option>
                <?php endforeach; ?>
            </select>
            <select id="dayFilter" class="table-view-field">
                <option value="">All Days</option>
                <?php foreach ($working_days as $day): ?>
                    <option value="<?php echo e($day['code']); ?>"><?php echo e($day['label']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="table-view-meta" id="timetableMeta"></div>
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
            margin: 10px 0 18px;
            flex-wrap: nowrap;
            align-items: center;
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
            background: #fff;
        }

        .filter-group .filter-search {
            min-width: 200px;
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

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .time-field {
            position: relative;
        }

        .time-input-row {
            display: flex;
            gap: 8px;
        }

        .time-input-row input {
            flex: 1;
        }

        .time-button {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            color: #0f172a;
            min-width: 44px;
        }

        .time-button:hover {
            background: #eef2f7;
        }

        .time-hint {
            margin-top: 6px;
            font-size: 0.82rem;
            color: #64748b;
        }

        .time-picker {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 240px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.15);
            padding: 12px;
            z-index: 20;
            display: none;
        }

        .time-picker.open {
            display: block;
        }

        .time-picker-header {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .time-picker-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .time-picker-row label {
            font-size: 0.8rem;
            color: #64748b;
        }

        .time-picker-row select {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            font-size: 0.9rem;
        }

        .time-picker-actions {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .time-picker-actions button {
            flex: 1;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid transparent;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .time-picker-apply {
            background: #2563eb;
            color: #ffffff;
        }

        .time-picker-cancel {
            background: #f1f5f9;
            color: #475569;
            border-color: #e2e8f0;
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

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .day-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            background: #e0e7ff;
            color: #3730a3;
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

    <div id="timetableMessage" class="notice-banner"></div>

    <!-- Add Slot Form -->
    <div id="addSlotForm" class="form-container">
        <h3 style="margin-top: 0;">Add Timetable Slot</h3>
        <form onsubmit="handleAddSlot(event)">
            <div class="form-row">
                <div class="form-group">
                    <label for="slotDay">Day</label>
                    <select id="slotDay" name="day" required>
                        <option value="">Select day</option>
                        <?php foreach ($working_days as $day): ?>
                            <option value="<?php echo e($day['code']); ?>"><?php echo e($day['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group time-field" data-time-picker>
                    <label for="slotStart">Start Time</label>
                    <div class="time-input-row">
                        <input
                            type="text"
                            id="slotStart"
                            name="start_time"
                            required
                            placeholder="HH:MM AM"
                            inputmode="numeric"
                            pattern="^(0?[1-9]|1[0-2]):[0-5]\d\s?(AM|PM)$"
                        >
                        <button type="button" class="time-button" aria-label="Select start time">🕒</button>
                    </div>
                    <div class="time-hint">Format: HH:MM AM/PM</div>
                    <div class="time-picker" role="dialog" aria-hidden="true">
                        <div class="time-picker-header">Select Time</div>
                        <div class="time-picker-row">
                            <div>
                                <label>Hour</label>
                                <select class="time-hour"></select>
                            </div>
                            <div>
                                <label>Minute</label>
                                <select class="time-minute"></select>
                            </div>
                        </div>
                        <div class="time-picker-row">
                            <div>
                                <label>Period</label>
                                <select class="time-period">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                            </div>
                        </div>
                        <div class="time-picker-actions">
                            <button type="button" class="time-picker-cancel">Cancel</button>
                            <button type="button" class="time-picker-apply">Apply</button>
                        </div>
                    </div>
                </div>
                <div class="form-group time-field" data-time-picker>
                    <label for="slotEnd">End Time</label>
                    <div class="time-input-row">
                        <input
                            type="text"
                            id="slotEnd"
                            name="end_time"
                            required
                            placeholder="HH:MM AM"
                            inputmode="numeric"
                            pattern="^(0?[1-9]|1[0-2]):[0-5]\d\s?(AM|PM)$"
                        >
                        <button type="button" class="time-button" aria-label="Select end time">🕒</button>
                    </div>
                    <div class="time-hint">Format: HH:MM AM/PM</div>
                    <div class="time-picker" role="dialog" aria-hidden="true">
                        <div class="time-picker-header">Select Time</div>
                        <div class="time-picker-row">
                            <div>
                                <label>Hour</label>
                                <select class="time-hour"></select>
                            </div>
                            <div>
                                <label>Minute</label>
                                <select class="time-minute"></select>
                            </div>
                        </div>
                        <div class="time-picker-row">
                            <div>
                                <label>Period</label>
                                <select class="time-period">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                            </div>
                        </div>
                        <div class="time-picker-actions">
                            <button type="button" class="time-picker-cancel">Cancel</button>
                            <button type="button" class="time-picker-apply">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="slotProgram">Program</label>
                    <select id="slotProgram" required>
                        <option value="">Select program</option>
                        <?php foreach ($programsList as $progId => $progName): ?>
                            <option value="<?php echo e($progId); ?>"><?php echo e($progName); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="slotSemester">Semester</label>
                    <select id="slotSemester" required>
                        <option value="">Select semester</option>
                        <?php foreach ($semestersList as $semId => $semData): ?>
                            <option value="<?php echo e($semId); ?>">Sem <?php echo e($semData['number']); ?> (<?php echo e($semData['year']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="slotAssignment">Teacher & Subject</label>
                <select id="slotAssignment" name="teacher_assignment_id" required disabled>
                    <option value="">Select program and semester first</option>
                    <?php foreach ($assignments as $assign): ?>
                        <option
                            value="<?php echo e($assign['id']); ?>"
                            data-program="<?php echo e($assign['program_id']); ?>"
                            data-semester="<?php echo e($assign['semester_id']); ?>"
                        >
                            <?php echo e($assign['teacher_name']); ?> - <?php echo e($assign['subject_name']); ?> (<?php echo e($assign['subject_code']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="formMessage"></div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Add Slot</button>
                <button type="button" class="btn-cancel" onclick="toggleAddSlotForm()">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Timetable Table -->
    <div class="table-container">
        <?php if (empty($timetable)): ?>
            <div class="empty-message">
                <p>No timetable entries found. <a href="#" onclick="toggleAddSlotForm(); return false;" style="color:#2563eb;text-decoration:underline;">Add one now</a></p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Teacher</th>
                        <th>Subject</th>
                        <th>Program</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="timetableTableBody">
                    <?php foreach ($timetable as $slot): ?>
                        <tr data-search="<?php echo e(trim(($slot['day'] ?? '') . ' ' . ($slot['start_time'] ?? '') . ' ' . ($slot['end_time'] ?? '') . ' ' . ($slot['teacher_name'] ?? '') . ' ' . ($slot['subject_name'] ?? '') . ' ' . ($slot['subject_code'] ?? '') . ' ' . ($slot['program_name'] ?? '') . ' ' . ($slot['semester_number'] ?? ''))); ?>"
                            data-program="<?php echo e($slot['program_name'] ?? ''); ?>"
                            data-semester="<?php echo e($slot['semester_number'] ?? ''); ?>"
                            data-day="<?php echo e($slot['day'] ?? ''); ?>">
                            <td><span class="day-badge"><?php echo e(ucfirst($slot['day'] ?? 'N/A')); ?></span></td>
                            <td><?php echo e($slot['start_time'] ?? 'N/A'); ?></td>
                            <td><?php echo e($slot['end_time'] ?? 'N/A'); ?></td>
                            <td><?php echo e($slot['teacher_name'] ?? 'N/A'); ?></td>
                            <td><?php echo e($slot['subject_name'] ?? 'N/A'); ?> (<?php echo e($slot['subject_code'] ?? 'N/A'); ?>)</td>
                            <td><?php echo e($slot['program_name'] ?? 'N/A'); ?></td>
                            <td>
                                <button onclick="deleteSlot(<?php echo e($slot['id']); ?>, '<?php echo e($slot['day']); ?>', '<?php echo e($slot['start_time']); ?>')" class="btn-danger">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if (!empty($timetable)): ?>
        <div class="table-view-pagination" id="timetablePager" style="margin-top: 14px;">
            <div class="pagination-info" id="timetablePageInfo"></div>
            <div class="pagination-actions">
                <button type="button" class="btn btn-ghost" id="timetablePrev">Previous</button>
                <button type="button" class="btn btn-ghost" id="timetableNext">Next</button>
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
        window.IMS?.initTableView({
            tbodyId: 'timetableTableBody',
            filters: [
                { id: 'programFilter', rowDatasetKey: 'program' },
                { id: 'semesterFilter', rowDatasetKey: 'semester' },
                { id: 'dayFilter', rowDatasetKey: 'day' },
            ],
            metaId: 'timetableMeta',
            pagerId: 'timetablePager',
            pageInfoId: 'timetablePageInfo',
            prevId: 'timetablePrev',
            nextId: 'timetableNext',
            pageSize: 10,
            noResultsColSpan: 7,
            noResultsText: 'No matching timetable entries found.',
        });

        function syncAssignmentOptions() {
            const programSelect = document.getElementById('slotProgram');
            const semesterSelect = document.getElementById('slotSemester');
            const assignmentSelect = document.getElementById('slotAssignment');

            if (!programSelect || !semesterSelect || !assignmentSelect) {
                return;
            }

            const programId = programSelect.value;
            const semesterId = semesterSelect.value;
            const options = Array.from(assignmentSelect.options);
            let hasVisible = false;

            options.forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const matchesProgram = option.dataset.program === programId;
                const matchesSemester = option.dataset.semester === semesterId;
                const shouldShow = programId && semesterId && matchesProgram && matchesSemester;

                option.hidden = !shouldShow;
                if (shouldShow) {
                    hasVisible = true;
                }
            });

            if (programId && semesterId) {
                assignmentSelect.disabled = false;
                assignmentSelect.options[0].textContent = hasVisible
                    ? 'Select teacher-subject assignment'
                    : 'No assignments for selection';
            } else {
                assignmentSelect.disabled = true;
                assignmentSelect.options[0].textContent = 'Select program and semester first';
            }

            assignmentSelect.value = '';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const programSelect = document.getElementById('slotProgram');
            const semesterSelect = document.getElementById('slotSemester');

            programSelect?.addEventListener('change', syncAssignmentOptions);
            semesterSelect?.addEventListener('change', syncAssignmentOptions);
            syncAssignmentOptions();
        });
        function toggleAddSlotForm() {
            const form = document.getElementById('addSlotForm');
            form.classList.toggle('active');
            if (form.classList.contains('active')) {
                document.getElementById('slotProgram').value = '';
                document.getElementById('slotSemester').value = '';
                syncAssignmentOptions();
                document.getElementById('slotDay').focus();
            }
        }

        function handleAddSlot(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const messageDiv = document.getElementById('formMessage');

            const startInput = document.getElementById('slotStart').value.trim();
            const endInput = document.getElementById('slotEnd').value.trim();
            const startTime = parseTime12To24(startInput);
            const endTime = parseTime12To24(endInput);

            if (startTime.error) {
                messageDiv.innerHTML = '<div class="error-message">Start time must use HH:MM AM/PM format.</div>';
                return;
            }

            if (endTime.error) {
                messageDiv.innerHTML = '<div class="error-message">End time must use HH:MM AM/PM format.</div>';
                return;
            }

            const startMinutes = timeToMinutes(startTime.value);
            const endMinutes = timeToMinutes(endTime.value);
            if (startMinutes === null || endMinutes === null) {
                messageDiv.innerHTML = '<div class="error-message">Time must be in HH:MM format.</div>';
                return;
            }

            if (endMinutes <= startMinutes) {
                messageDiv.innerHTML = '<div class="error-message">End time must be after start time.</div>';
                return;
            }

            if ((endMinutes - startMinutes) < 60) {
                messageDiv.innerHTML = '<div class="error-message">Class duration must be at least 1 hour.</div>';
                return;
            }

            const dayStart = '<?php echo e($day_start_time); ?>';
            const dayEnd = '<?php echo e($day_end_time); ?>';
            if (!dayStart || !dayEnd) {
                messageDiv.innerHTML = '<div class="error-message">System configuration is incomplete. Please set working day hours.</div>';
                return;
            }
            const minStart = timeToMinutes(dayStart);
            const maxEnd = timeToMinutes(dayEnd);

            if (minStart !== null && startMinutes < minStart) {
                messageDiv.innerHTML = '<div class="error-message">Start time must be within configured day hours.</div>';
                return;
            }

            if (maxEnd !== null && endMinutes > maxEnd) {
                messageDiv.innerHTML = '<div class="error-message">End time must be within configured day hours.</div>';
                return;
            }

            formData.set('start_time', startTime.value);
            formData.set('end_time', endTime.value);

            fetch('<?php echo e(url('vp/timetable')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(Object.fromEntries(formData))
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.innerHTML = '<div class="success-message">Slot added successfully. Refreshing...</div>';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    messageDiv.innerHTML = '<div class="error-message">' + (data.message || 'Error adding slot') + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('formMessage').innerHTML = '<div class="error-message">Error: ' + error.message + '</div>';
            });
        }

        function parseTime12To24(value) {
            const normalized = value.trim().toUpperCase();
            const match = normalized.match(/^(0?[1-9]|1[0-2]):([0-5]\d)\s?(AM|PM)$/);
            if (!match) {
                return { error: 'Use HH:MM AM/PM format.' };
            }

            let hour = Number(match[1]);
            const minute = match[2];
            const period = match[3];

            if (period === 'AM') {
                hour = hour === 12 ? 0 : hour;
            } else {
                hour = hour === 12 ? 12 : hour + 12;
            }

            return { value: `${String(hour).padStart(2, '0')}:${minute}` };
        }

        function formatTime12(value) {
            const match = value.trim().match(/^([01]\d|2[0-3]):([0-5]\d)$/);
            if (!match) {
                return value;
            }

            let hour = Number(match[1]);
            const minute = match[2];
            const period = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12;
            if (hour === 0) {
                hour = 12;
            }
            return `${hour}:${minute} ${period}`;
        }

        function timeToMinutes(value) {
            const match = value.match(/^([01]\d|2[0-3]):([0-5]\d)$/);
            if (!match) {
                return null;
            }
            return Number(match[1]) * 60 + Number(match[2]);
        }

        function initTimePicker(wrapper) {
            const input = wrapper.querySelector('input');
            const button = wrapper.querySelector('.time-button');
            const picker = wrapper.querySelector('.time-picker');
            const hourSelect = wrapper.querySelector('.time-hour');
            const minuteSelect = wrapper.querySelector('.time-minute');
            const periodSelect = wrapper.querySelector('.time-period');
            const applyButton = wrapper.querySelector('.time-picker-apply');
            const cancelButton = wrapper.querySelector('.time-picker-cancel');

            input.value = formatTime12(input.value);

            for (let hour = 1; hour <= 12; hour += 1) {
                const option = document.createElement('option');
                const value = String(hour).padStart(2, '0');
                option.value = value;
                option.textContent = value;
                hourSelect.appendChild(option);
            }

            for (let minute = 0; minute < 60; minute += 5) {
                const option = document.createElement('option');
                const value = String(minute).padStart(2, '0');
                option.value = value;
                option.textContent = value;
                minuteSelect.appendChild(option);
            }

            function openPicker() {
                const current = input.value.trim();
                const match = current.toUpperCase().match(/^(0?[1-9]|1[0-2]):([0-5]\d)\s?(AM|PM)$/);
                if (match) {
                    hourSelect.value = String(Number(match[1])).padStart(2, '0');
                    const minute = Math.round(Number(match[2]) / 5) * 5;
                    minuteSelect.value = String(minute).padStart(2, '0');
                    periodSelect.value = match[3];
                }
                picker.classList.add('open');
                picker.setAttribute('aria-hidden', 'false');
            }

            function closePicker() {
                picker.classList.remove('open');
                picker.setAttribute('aria-hidden', 'true');
            }

            button.addEventListener('click', (event) => {
                event.stopPropagation();
                openPicker();
            });

            applyButton.addEventListener('click', () => {
                const hour = Number(hourSelect.value);
                input.value = `${hour}:${minuteSelect.value} ${periodSelect.value}`;
                closePicker();
            });

            cancelButton.addEventListener('click', closePicker);

            document.addEventListener('click', (event) => {
                if (!wrapper.contains(event.target)) {
                    closePicker();
                }
            });
        }

        document.querySelectorAll('[data-time-picker]').forEach(initTimePicker);

        function deleteSlot(id, day, time) {
            openConfirmModal(
                'Delete Slot',
                'Delete ' + day + ' at ' + time + ' slot?',
                () => {
                    fetch('<?php echo e(url('vp/timetable')); ?>/' + id, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage('Slot deleted successfully.', 'success');
                            location.reload();
                        } else {
                            showMessage('Error: ' + (data.message || 'Failed to delete slot'), 'error');
                        }
                    });
                }
            );
        }

        function showMessage(message, type) {
            const banner = document.getElementById('timetableMessage');
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
