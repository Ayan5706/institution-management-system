<?php
/** @var string $user_name */
/** @var string $user_role */
/** @var array<string, int> $summary */
$activeNav = 'dashboard';
$user_name = $user_name ?? 'Student';
$summary = $summary ?? [
    'program' => 'Active Program',
    'semester' => 'Active Semester',
    'attendance_percent' => 0,
    'fee_status' => 'N/A',
];
$attendance_series = $summary['attendance_series'] ?? [70, 66, 74, 71, 69, 76, 80];
$attendance_today = (int) ($summary['attendance_percent'] ?? ($attendance_series[array_key_last($attendance_series)] ?? 0));
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Student Dashboard</h2>
            <div style="color:#64748b;">Welcome, <?php echo e($user_name); ?>. Your academic information and profile.</div>
        </div>
        <div>
            <a class="btn btn-primary" href="<?php echo e(url('student-profile')); ?>">View Profile</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">PR</div>
            <div>
                <div class="stat-label">Program</div>
                <div class="stat-value"><?php echo e($summary['program']); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">SM</div>
            <div>
                <div class="stat-label">Current Semester</div>
                <div class="stat-value"><?php echo e($summary['semester']); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">AT</div>
            <div>
                <div class="stat-label">Attendance</div>
                <div class="stat-value"><?php echo e($summary['attendance_percent']); ?>%</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">FE</div>
            <div>
                <div class="stat-label">Fee Status</div>
                <div class="stat-value"><?php echo e($summary['fee_status']); ?></div>
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

            <div class="widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Student Functions</h3>
                        <p class="widget-meta">Quick links</p>
                    </div>
                </div>
                <div class="quick-actions">
                    <a class="quick-action" href="<?php echo e(url('student-profile')); ?>">My Profile</a>
                    <a class="quick-action" href="<?php echo e(url('student-fees')); ?>">Fee Status</a>
                    <a class="quick-action" href="<?php echo e(url('reports')); ?>">My Reports</a>
                    <a class="quick-action" href="<?php echo e(url('reports/academic')); ?>">Academic Info</a>
                </div>
            </div>
        </div>

        <div class="dashboard-column">
            <div class="widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Academic Guidance</h3>
                        <p class="widget-meta">Keep on track</p>
                    </div>
                </div>
                <div style="color: #64748b; line-height: 1.7;">
                    Review your personal profile, track fee status, and monitor attendance to stay eligible for assessments. Contact your program coordinator if any record looks incorrect.
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
