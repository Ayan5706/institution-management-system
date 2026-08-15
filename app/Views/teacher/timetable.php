<?php
/** @var array<int, array<string, mixed>> $timetable */
$activeNav = 'teacher/timetable';
$timetable = $timetable ?? [];
?>
<?php ob_start(); ?>
<style>
    .timetable-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        margin-top: 12px;
    }

    .timetable-table th {
        background: #f8fafc;
        padding: 12px;
        text-align: left;
        font-weight: 700;
        border-bottom: 2px solid #e2e8f0;
        color: #475569;
    }

    .timetable-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #0f172a;
    }

    .timetable-table tr:hover {
        background: #f8fafc;
    }

    .subject-code {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 4px;
    }

    @media (max-width: 768px) {
        .timetable-table th,
        .timetable-table td {
            padding: 10px;
            font-size: 0.9rem;
        }
    }

    @media (max-width: 640px) {
        .timetable-table th,
        .timetable-table td {
            padding: 8px;
            font-size: 0.85rem;
        }
    }
</style>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">View Timetable</h2>
            <div style="color:#6c7b86;">Your weekly teaching schedule</div>
        </div>
        <a href="<?php echo e(url('teacher/dashboard')); ?>" class="btn btn-ghost">Back to Dashboard</a>
    </div>

    <?php if (!empty($timetable)): ?>
        <div style="overflow-x: auto;">
            <table class="timetable-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Subject</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Program</th>
                        <th>Semester</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($timetable as $slot): ?>
                        <tr>
                            <td><?php echo e($slot['day'] ?? ''); ?></td>
                            <td>
                                <div><?php echo e($slot['subject_name'] ?? ''); ?></div>
                                <div class="subject-code"><?php echo e($slot['subject_code'] ?? ''); ?></div>
                            </td>
                            <td><?php echo e(date('H:i A', strtotime($slot['start_time']))); ?></td>
                            <td><?php echo e(date('H:i A', strtotime($slot['end_time']))); ?></td>
                            <td><?php echo e($slot['program_code'] ?? ''); ?></td>
                            <td>
                                <?php if (!empty($slot['academic_year'])): ?>
                                    <?php echo e($slot['academic_year']); ?> - Semester <?php echo e($slot['semester_number'] ?? ''); ?>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state" style="padding: 16px; text-align: center;">
            No timetable slots found for your account.
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
