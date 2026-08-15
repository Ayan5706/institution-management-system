<?php
/** @var array $assignments */
$activeNav = 'teacher/attendance';
$assignments = $assignments ?? [];
?>
<?php ob_start(); ?>
<style>
    .schedule-table td {
        vertical-align: middle;
    }

    .schedule-time {
        font-weight: 700;
        color: #2f7f87;
        font-size: 1.05rem;
    }

    .schedule-code {
        background: rgba(47, 127, 135, 0.12);
        color: #2f5861;
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .schedule-name {
        font-weight: 700;
        color: #0f172a;
    }

    .schedule-meta {
        color: #64748b;
        font-size: 0.9rem;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .schedule-action {
        display: flex;
        justify-content: flex-end;
    }

    @media (max-width: 640px) {
        .schedule-action {
            justify-content: flex-start;
        }
    }
</style>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Mark Attendance</h2>
            <div style="color:#6c7b86;">Select a class session to mark attendance</div>
        </div>
        <a href="<?php echo e(url('teacher/dashboard')); ?>" class="btn btn-ghost">
            Back to Dashboard
        </a>
    </div>

    <div class="widget" style="margin-top: 12px;">
        <div class="widget-header">
            <div>
                <h3 class="widget-title">Today's Sessions</h3>
                <p class="widget-meta">Only your scheduled classes for today</p>
            </div>
            <span class="widget-pill"><?php echo e(count($assignments)); ?> sessions</span>
        </div>

        <?php if (!empty($assignments)): ?>
            <div class="table-container">
                <table class="table-view-table schedule-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Code</th>
                            <th>Subject</th>
                            <th>Academic Year</th>
                            <th>Semester</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $slot): ?>
                            <tr>
                                <td class="schedule-time">
                                    <?php echo e(date('H:i A', strtotime($slot['start_time']))); ?> -
                                    <?php echo e(date('H:i A', strtotime($slot['end_time']))); ?>
                                </td>
                                <td>
                                    <span class="schedule-code"><?php echo e($slot['subject_code'] ?? '-'); ?></span>
                                </td>
                                <td>
                                    <span class="schedule-name"><?php echo e($slot['subject_name'] ?? ''); ?></span>
                                </td>
                                <td><span class="schedule-meta"><?php echo e($slot['academic_year'] ?? ''); ?></span></td>
                                <td><span class="schedule-meta">Semester <?php echo e($slot['semester_number'] ?? ''); ?></span></td>
                                <td>
                                    <div class="schedule-action">
                                        <?php if (!empty($slot['attendance_marked'])): ?>
                                            <button class="btn btn-ghost" disabled>Attendance Completed</button>
                                        <?php elseif (empty($slot['is_enabled'])): ?>
                                            <button class="btn btn-ghost" disabled>Window Closed</button>
                                        <?php else: ?>
                                            <a class="btn btn-primary" href="<?php echo e(url("teacher/attendance/mark/{$slot['id']}")); ?>">
                                                Mark Attendance
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state" style="padding: 16px; text-align: center;">
                No classes scheduled for today.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
