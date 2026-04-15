<?php
/** @var array $slot */
/** @var array $students */
/** @var bool $isWithinWindow */
/** @var int $slot_id */
?>
<?php ob_start(); ?>
<style>
    .attendance-header {
        background: linear-gradient(135deg, #f1f5f9, #e0e7ff);
        padding: 20px;
        border-radius: 14px;
        margin-bottom: 20px;
        border: 1px solid #dbe4f0;
    }

    .header-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .header-item {
        padding: 12px;
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }

    .header-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 4px;
    }

    .header-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: #0f172a;
    }

    .status-banner {
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 16px;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .status-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .status-warning {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }

    .controls-group {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-input {
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #dbe4f0;
        font-size: 0.9rem;
        min-width: 220px;
    }

    .btn {
        padding: 10px 14px;
        border-radius: 10px;
        border: 1px solid transparent;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .btn-primary {
        background: linear-gradient(135deg, #2563eb, #0d9488);
        color: #fff;
    }

    .btn-primary:hover {
        opacity: 0.9;
    }

    .btn-secondary {
        background: #f1f5f9;
        color: #0f172a;
        border: 1px solid #dbe4f0;
    }

    .btn-secondary:hover {
        background: #e0e7ff;
    }

    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .attendance-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .attendance-table thead {
        background: #f1f5f9;
        border-bottom: 2px solid #dbe4f0;
    }

    .attendance-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #0f172a;
        font-size: 0.9rem;
    }

    .attendance-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
    }

    .attendance-table tbody tr:hover {
        background: #f8fbff;
    }

    .student-name {
        font-weight: 600;
        color: #0f172a;
    }

    .reg-number {
        font-size: 0.85rem;
        color: #64748b;
    }

    .radio-group {
        display: flex;
        gap: 16px;
        align-items: center;
    }

    .radio-label {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .radio-label input[type="radio"] {
        cursor: pointer;
        width: 18px;
        height: 18px;
    }

    .empty-state {
        padding: 40px;
        text-align: center;
        color: #64748b;
    }

    .empty-state-icon {
        font-size: 3rem;
        margin-bottom: 12px;
    }

    .submit-group {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }

    .form-message {
        padding: 12px 14px;
        border-radius: 10px;
        margin-bottom: 16px;
        display: none;
    }

    .form-message.success {
        display: block;
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .form-message.error {
        display: block;
        background: #fee2e2;
        color: #7f1d1d;
        border: 1px solid #fecaca;
    }

    @media (max-width: 960px) {
        .header-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .attendance-table {
            font-size: 0.85rem;
        }
        .attendance-table th,
        .attendance-table td {
            padding: 8px;
        }
    }

    @media (max-width: 640px) {
        .header-grid {
            grid-template-columns: 1fr;
        }
        .radio-group {
            gap: 12px;
        }
        .submit-group {
            flex-direction: column-reverse;
        }
        .submit-group .btn {
            width: 100%;
        }
    }
</style>

<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Mark Attendance</h2>
            <div style="color:#64748b;">Record attendance for this session</div>
        </div>
        <a href="<?php echo e(url('teacher/attendance/history')); ?>" class="btn btn-secondary">
            View History
        </a>
    </div>

    <!-- Header Info -->
    <div class="attendance-header">
        <div class="header-grid">
            <div class="header-item">
                <div class="header-label">Subject</div>
                <div class="header-value"><?php echo e($slot['subject_name'] ?? 'N/A'); ?></div>
                <div style="font-size: 0.8rem; color: #6c7b86; margin-top: 4px;">
                    <?php echo e($slot['subject_code'] ?? ''); ?>
                </div>
            </div>
            <div class="header-item">
                <div class="header-label">Class</div>
                <div class="header-value"><?php echo e($slot['academic_year'] ?? 'N/A'); ?></div>
                <div style="font-size: 0.8rem; color: #6c7b86; margin-top: 4px;">
                    Semester <?php echo e($slot['semester_number'] ?? '0'); ?>
                </div>
            </div>
            <div class="header-item">
                <div class="header-label">Date & Time</div>
                <div class="header-value"><?php echo e(date('M d, Y')); ?></div>
                <div style="font-size: 0.8rem; color: #6c7b86; margin-top: 4px;">
                    <?php echo e(date('H:i A', strtotime($slot['start_time']))); ?> - 
                    <?php echo e(date('H:i A', strtotime($slot['end_time']))); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Message -->
    <?php if ($isWithinWindow): ?>
        <div class="status-banner status-success">
            ✓ Attendance window is open. You can mark attendance now.
        </div>
    <?php else: ?>
        <div class="status-banner status-warning">
            ⚠ Attendance window is closed. Marking is disabled.
        </div>
    <?php endif; ?>

    <!-- Form Message -->
    <div id="formMessage" class="form-message"></div>

    <!-- Student List -->
    <?php if (!empty($students)): ?>
        <form id="attendanceForm">
            <!-- Control Buttons -->
            <div class="controls-group">
                <input type="text" id="studentSearch" class="filter-input" placeholder="Search students...">
                <button type="button" class="btn btn-secondary" id="markAllPresent" 
                    <?php echo !$isWithinWindow ? 'disabled' : ''; ?>>
                    Mark All Present
                </button>
                <button type="button" class="btn btn-secondary" id="markAllAbsent"
                    <?php echo !$isWithinWindow ? 'disabled' : ''; ?>>
                    Mark All Absent
                </button>
            </div>

            <!-- Attendance Table -->
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Registration Number</th>
                        <th style="text-align: center;">Attendance Status</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody">
                    <?php foreach ($students as $student): ?>
                        <tr data-name="<?php echo e($student['full_name']); ?>" data-reg="<?php echo e($student['registration_number']); ?>">
                            <td>
                                <div class="student-name"><?php echo e($student['full_name']); ?></div>
                            </td>
                            <td>
                                <div class="reg-number"><?php echo e($student['registration_number']); ?></div>
                            </td>
                            <td style="text-align: center;">
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="attendance[<?php echo e($student['id']); ?>]" 
                                               value="PRESENT"
                                               <?php echo !$isWithinWindow ? 'disabled' : ''; ?>>
                                        <span>Present</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="attendance[<?php echo e($student['id']); ?>]" 
                                               value="ABSENT"
                                               <?php echo !$isWithinWindow ? 'disabled' : ''; ?>>
                                        <span>Absent</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Submit Group -->
            <div class="submit-group">
                <a href="<?php echo e(url('teacher/attendance/history')); ?>" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn"
                    <?php echo !$isWithinWindow ? 'disabled' : ''; ?>>
                    Submit Attendance
                </button>
            </div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('attendanceForm');
                const markAllPresent = document.getElementById('markAllPresent');
                const markAllAbsent = document.getElementById('markAllAbsent');
                const formMessage = document.getElementById('formMessage');
                const submitBtn = document.getElementById('submitBtn');
                const isWithinWindow = <?php echo json_encode($isWithinWindow); ?>;
                const studentSearch = document.getElementById('studentSearch');

                // Mark all present
                markAllPresent.addEventListener('click', function() {
                    document.querySelectorAll('input[type="radio"][value="PRESENT"]').forEach(radio => {
                        radio.checked = true;
                    });
                });

                // Mark all absent
                markAllAbsent.addEventListener('click', function() {
                    document.querySelectorAll('input[type="radio"][value="ABSENT"]').forEach(radio => {
                        radio.checked = true;
                    });
                });

                studentSearch?.addEventListener('input', function() {
                    const search = studentSearch.value.toLowerCase();
                    document.querySelectorAll('#attendanceTableBody tr').forEach(row => {
                        const text = `${row.dataset.name} ${row.dataset.reg}`.toLowerCase();
                        row.style.display = search === '' || text.includes(search) ? '' : 'none';
                    });
                });

                // Form submission
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    if (!isWithinWindow) {
                        showMessage('Attendance window is closed', 'error');
                        return;
                    }

                    // Collect attendance data
                    const attendance = {};
                    document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
                        const name = radio.name;
                        const studentId = name.match(/\d+/)[0];
                        attendance[studentId] = radio.value;
                    });

                    if (Object.keys(attendance).length === 0) {
                        showMessage('Please select attendance status for at least one student', 'error');
                        return;
                    }

                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Submitting...';

                    try {
                        const response = await fetch('<?php echo e(url("api/teacher/attendance/{$slot_id}/submit")); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            },
                            body: JSON.stringify({ attendance })
                        });

                        const data = await response.json();

                        if (data.success) {
                            showMessage('✓ Attendance submitted successfully!', 'success');
                            setTimeout(() => {
                                window.location.href = '<?php echo e(url('teacher/attendance/history')); ?>';
                            }, 1500);
                        } else {
                            showMessage(data.message || 'Failed to submit attendance', 'error');
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Submit Attendance';
                        }
                    } catch (error) {
                        showMessage('Error submitting attendance: ' + error.message, 'error');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Submit Attendance';
                    }
                });

                function showMessage(message, type) {
                    formMessage.textContent = message;
                    formMessage.className = 'form-message ' + type;
                    formMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        </script>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">👥</div>
            <p>No students found for this class</p>
            <p style="font-size: 0.9rem;">There are no enrolled students to mark attendance for.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
