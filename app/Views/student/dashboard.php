<?php
/** @var array $stats */
/** @var string $user_name */
/** @var array|null $current_semester */
/** @var bool $profile_not_found */
$activeNav = 'dashboard';
$stats = $stats ?? [];
$profile_not_found = $profile_not_found ?? false;
$attendance_series = $stats['attendance_series'] ?? [];
$attendance_today = (int) ($stats['attendance_percent'] ?? ($attendance_series !== [] ? $attendance_series[array_key_last($attendance_series)] : 0));
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Dashboard</h2>
            <div style="color:#6c7b86;">Your academic overview</div>
        </div>
    </div>

    <!-- Profile Not Found Warning -->
    <?php if ($profile_not_found): ?>
        <div class="notice-warning">
            <strong>⚠️ Profile Setup Needed</strong><br>
            Your student profile hasn't been set up yet. Please contact your institution to complete your enrollment. Once your profile is created, your semester, attendance, and fee information will appear here.
        </div>
    <?php endif; ?>

    <!-- Dashboard Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📆</div>
            <div>
                <div class="stat-label">Current Semester</div>
                <div class="stat-value"><?php echo e($stats['current_semester'] ?? 'N/A'); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div>
                <div class="stat-label">Attendance %</div>
                <div class="stat-value"><?php echo e($stats['attendance_percent'] ?? 0); ?>%</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💳</div>
            <div>
                <div class="stat-label">Fee Status</div>
                <div class="stat-value"><?php echo e($stats['fee_status'] ?? 'N/A'); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div>
                <div class="stat-label">Pending Amount</div>
                <div class="stat-value">₨<?php echo e(number_format($stats['pending_amount'] ?? 0)); ?></div>
            </div>
        </div>
    </div>

    <div class="dashboard-layout">
        <div class="dashboard-column">
            <div class="widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Attendance Overview</h3>
                        <p class="widget-meta">Last 7 days</p>
                    </div>
                    <span class="widget-pill"><?php echo e($attendance_today); ?>% today</span>
                </div>
                <div class="chart-bars">
                    <?php foreach ($attendance_series as $value): ?>
                        <div class="bar" style="--value: <?php echo e($value / 100); ?>;"></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="dashboard-column">
            <div class="widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Quick Access</h3>
                        <p class="widget-meta">Student tools</p>
                    </div>
                </div>
                <div class="quick-actions">
                    <a class="quick-action" href="<?php echo e(url('student/profile')); ?>">Profile</a>
                    <a class="quick-action" href="<?php echo e(url('student/attendance')); ?>">Attendance</a>
                    <a class="quick-action" href="<?php echo e(url('student/fees')); ?>">Fees</a>
                    <a class="quick-action" href="<?php echo e(url('student/timetable')); ?>">Timetable</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
