<?php
/** @var array $slot */
/** @var array $students */
/** @var bool $isWithinWindow */
/** @var bool $attendanceCompleted */
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

    .filter-input {
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 0.9rem;
        min-width: 220px;
    }

    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
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
        justify-content: center;
        flex-wrap: wrap;
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

    .attendance-table {
        width: 100%;
        border-collapse: collapse;
    }

    .attendance-table th,
    .attendance-table td {
        padding: 12px;
        text-align: left;
        font-size: 0.9rem;
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
        <a href="<?php echo e(url('teacher/attendance/history')); ?>" class="btn btn-ghost">
            View History
        </a>
    </div>

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
                <div class="header-value"><?php echo e($slot['program_name'] ?? 'N/A'); ?></div>
                <div style="font-size: 0.8rem; color: #6c7b86; margin-top: 4px;">
                    <?php echo e($slot['program_code'] ?? ''); ?> • Semester <?php echo e($slot['semester_number'] ?? '0'); ?>
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

    <?php if (!empty($attendanceCompleted)): ?>
        <div class="status-banner status-success">
            ✓ Attendance Completed. Editing is not available for this session.
        </div>
    <?php elseif ($isWithinWindow): ?>
        <div class="status-banner status-success">
            ✓ Attendance window is open. You can mark attendance now.
        </div>
    <?php else: ?>
        <div class="status-banner status-warning">
            ⚠ Attendance can only be marked during class hours.
        </div>
    <?php endif; ?>

    <div id="formMessage" class="form-message"></div>

    <?php if (!empty($students)): ?>
        <form id="attendanceForm">
            <div class="table-view-header">
                <div class="table-view-controls">
                    <input type="text" id="studentSearch" class="filter-input table-view-field" placeholder="Search students...">
                    <button type="button" class="btn btn-ghost" id="markAllPresent"
                        <?php echo (!$isWithinWindow || !empty($attendanceCompleted)) ? 'disabled' : ''; ?>>
                        Mark All Present
                    </button>
                    <button type="button" class="btn btn-ghost" id="markAllAbsent"
                        <?php echo (!$isWithinWindow || !empty($attendanceCompleted)) ? 'disabled' : ''; ?>>
                        Mark All Absent
                    </button>
                </div>
                <div class="table-view-meta"><?php echo e(count($students)); ?> total</div>
            </div>

            <div class="table-container">
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
                                                   <?php echo (!$isWithinWindow || !empty($attendanceCompleted)) ? 'disabled' : ''; ?>>
                                            <span>Present</span>
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="attendance[<?php echo e($student['id']); ?>]"
                                                   value="ABSENT"
                                                   <?php echo (!$isWithinWindow || !empty($attendanceCompleted)) ? 'disabled' : ''; ?>>
                                            <span>Absent</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="submit-group">
                <a href="<?php echo e(url('teacher/attendance/history')); ?>" class="btn btn-ghost">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn"
                    <?php echo (!$isWithinWindow || !empty($attendanceCompleted)) ? 'disabled' : ''; ?>>
                    Save Attendance
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
                const attendanceCompleted = <?php echo json_encode(!empty($attendanceCompleted)); ?>;
                const studentSearch = document.getElementById('studentSearch');

                markAllPresent.addEventListener('click', function() {
                    document.querySelectorAll('input[type="radio"][value="PRESENT"]').forEach(radio => {
                        radio.checked = true;
                    });
                });

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

                form.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    if (!isWithinWindow) {
                        showMessage('Attendance can only be marked during class hours', 'error');
                        return;
                    }

                    if (attendanceCompleted) {
                        showMessage('Attendance is already completed for this session', 'error');
                        return;
                    }

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
                    submitBtn.textContent = 'Saving...';

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
                            submitBtn.textContent = 'Save Attendance';
                        }
                    } catch (error) {
                        showMessage('Error submitting attendance: ' + error.message, 'error');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Save Attendance';
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
