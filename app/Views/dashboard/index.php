<?php
/** @var array<string, int> $summary */
$activeNav = 'dashboard';
$summary = $summary ?? [
    'total_users' => 148,
    'total_programs' => 6,
    'total_semesters' => 12,
    'total_subjects' => 42,
];

$overviewCards = [
    [
        'label' => 'Total Users',
        'value' => number_format((int) ($summary['total_users'] ?? 0)),
        'trend' => 'Administrators, teachers, and students',
        'icon' => 'US',
        'tone' => 'blue',
    ],
    [
        'label' => 'Programs',
        'value' => number_format((int) ($summary['total_programs'] ?? 0)),
        'trend' => 'Academic tracks defined',
        'icon' => 'PR',
        'tone' => 'emerald',
    ],
    [
        'label' => 'Semesters',
        'value' => number_format((int) ($summary['total_semesters'] ?? 0)),
        'trend' => 'Current and archived terms',
        'icon' => 'SM',
        'tone' => 'violet',
    ],
    [
        'label' => 'Subjects',
        'value' => number_format((int) ($summary['total_subjects'] ?? 0)),
        'trend' => 'Active subject catalog',
        'icon' => 'SB',
        'tone' => 'amber',
    ],
];

$recentActivities = [
    ['time' => '08:45', 'title' => 'Attendance logged', 'detail' => 'Morning attendance was captured for the first class block.'],
    ['time' => '09:20', 'title' => 'Student profile updated', 'detail' => 'A student record was updated with new program details.'],
    ['time' => '10:05', 'title' => 'Subject assignment changed', 'detail' => 'Teacher assignment was updated for a class schedule.'],
    ['time' => '11:10', 'title' => 'Reset request received', 'detail' => 'One user requested a password reset.'],
];

$systemChecks = [
    ['label' => 'Database connection', 'status' => 'Healthy'],
    ['label' => 'Authentication', 'status' => 'Protected'],
    ['label' => 'Session store', 'status' => 'Active'],
    ['label' => 'Backup schedule', 'status' => 'Pending setup'],
];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Overview</h2>
            <div style="color:#64748b;">A snapshot of the school management system at a glance.</div>
        </div>
        <div>
            <a class="btn btn-primary" href="<?php echo e(url('reports/academic')); ?>">Open Academic Summary</a>
        </div>
    </div>

    <div class="stats-grid">
        <?php foreach ($overviewCards as $card): ?>
            <div class="stat-card">
                <div class="stat-icon"><?php echo e($card['icon']); ?></div>
                <div>
                    <div class="stat-label"><?php echo e($card['label']); ?></div>
                    <div class="stat-value"><?php echo e($card['value']); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="dashboard-layout">
        <div class="dashboard-column">
            <div class="widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Recent Activity</h3>
                        <p class="widget-meta">Latest system events</p>
                    </div>
                </div>
                <ul class="activity-list">
                    <?php foreach ($recentActivities as $activity): ?>
                        <li class="activity-item">
                            <div>
                                <strong><?php echo e($activity['title']); ?></strong>
                                <div class="activity-meta"><?php echo e($activity['detail']); ?></div>
                            </div>
                            <span class="activity-meta"><?php echo e($activity['time']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Quick Actions</h3>
                        <p class="widget-meta">Jump to key reports</p>
                    </div>
                </div>
                <div class="quick-actions">
                    <a class="quick-action" href="<?php echo e(url('users')); ?>">Open Users</a>
                    <a class="quick-action" href="<?php echo e(url('reports/academic')); ?>">Academic Summary</a>
                    <a class="quick-action" href="<?php echo e(url('reports')); ?>">All Reports</a>
                </div>
            </div>
        </div>

        <div class="dashboard-column">
            <div class="widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">System Status</h3>
                        <p class="widget-meta">Health checks</p>
                    </div>
                </div>
                <ul class="activity-list">
                    <?php foreach ($systemChecks as $check): ?>
                        <li class="activity-item">
                            <div>
                                <strong><?php echo e($check['label']); ?></strong>
                            </div>
                            <span class="activity-meta"><?php echo e($check['status']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Operations Note</h3>
                        <p class="widget-meta">Live data ready</p>
                    </div>
                </div>
                <div style="color:#64748b; line-height:1.6;">
                    The dashboard is scaffolded for live school operations and ready to be connected to real aggregates from your models.
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
