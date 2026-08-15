<?php
/** @var array $stats */
/** @var array $assignments */
/** @var string $user_name */
$activeNav = 'dashboard';
$stats = $stats ?? [];
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
            <h2 style="margin:0 0 6px;">Teacher Dashboard</h2>
            <div style="color:#6c7b86;">Your classes and attendance for today</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="20" height="20" role="img" aria-label="Subjects">
                    <path d="M4 6h10a3 3 0 0 1 3 3v9H7a3 3 0 0 0-3 3V6z" fill="none" stroke="currentColor" stroke-width="1.6"/>
                    <path d="M7 6v12" fill="none" stroke="currentColor" stroke-width="1.6"/>
                    <path d="M17 18h3V9a3 3 0 0 0-3-3h-3" fill="none" stroke="currentColor" stroke-width="1.6"/>
                </svg>
            </div>
            <div>
                <div class="stat-label">Assigned Subjects</div>
                <div class="stat-value"><?php echo e($stats['assigned_subjects'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="20" height="20" role="img" aria-label="Classes">
                    <path d="M3 7h18v10H3z" fill="none" stroke="currentColor" stroke-width="1.6"/>
                    <path d="M7 17v2h10v-2" fill="none" stroke="currentColor" stroke-width="1.6"/>
                    <path d="M8 11h8" fill="none" stroke="currentColor" stroke-width="1.6"/>
                </svg>
            </div>
            <div>
                <div class="stat-label">Assigned Classes</div>
                <div class="stat-value"><?php echo e($stats['assigned_classes'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="20" height="20" role="img" aria-label="Today">
                    <path d="M7 3v4M17 3v4" fill="none" stroke="currentColor" stroke-width="1.6"/>
                    <rect x="4" y="5" width="16" height="15" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/>
                    <path d="M4 9h16" fill="none" stroke="currentColor" stroke-width="1.6"/>
                </svg>
            </div>
            <div>
                <div class="stat-label">Today's Classes</div>
                <div class="stat-value"><?php echo e($stats['today_classes'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="20" height="20" role="img" aria-label="Pending">
                    <circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.6"/>
                    <path d="M12 8v4l3 2" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div>
                <div class="stat-label">Pending Attendance</div>
                <div class="stat-value"><?php echo e($stats['pending_attendance'] ?? 0); ?></div>
            </div>
        </div>
    </div>

    <div class="dashboard-layout single-column">
        <div class="dashboard-column">
            <div class="widget" id="today-schedule">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Today's Schedule</h3>
                        <p class="widget-meta">Only your classes for today</p>
                    </div>
                    <span class="widget-pill"><?php echo e($stats['today_classes'] ?? 0); ?> sessions</span>
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
                                                    <button class="btn btn-ghost" disabled>Completed</button>
                                                <?php elseif (empty($slot['is_enabled'])): ?>
                                                    <button class="btn btn-ghost" disabled>Window Closed</button>
                                                <?php else: ?>
                                                    <a class="btn btn-primary" href="<?php echo e(url("teacher/attendance/mark/{$slot['id']}")); ?>">Mark Attendance</a>
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
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
