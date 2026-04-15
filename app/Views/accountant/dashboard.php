<?php
/** @var string $user_name */
/** @var string $user_role */
/** @var array<string, mixed> $stats */
$activeNav = 'dashboard';
$user_name = $user_name ?? 'Accountant';
$stats = $stats ?? [
    'total_collected' => 0,
    'total_pending' => 0,
    'active_semesters' => 0,
    'total_students' => 0,
];
$collection_series = $stats['collection_series'] ?? [42, 58, 46, 63, 55, 72, 68];
$collection_total = (float) ($stats['total_collected'] ?? 0) + (float) ($stats['total_pending'] ?? 0);
$collection_percent = $collection_total > 0
    ? (int) round(((float) ($stats['total_collected'] ?? 0) / $collection_total) * 100)
    : 0;
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Accountant Dashboard</h2>
            <div style="color:#6c7b86;">Welcome, <?php echo e($user_name); ?>. Financial overview and fee management.</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">💳</div>
            <div>
                <div class="stat-label">Total Collected</div>
                <div class="stat-value">₱<?php echo number_format((float) ($stats['total_collected'] ?? 0), 2); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div>
                <div class="stat-label">Total Pending</div>
                <div class="stat-value">₱<?php echo number_format((float) ($stats['total_pending'] ?? 0), 2); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🗓️</div>
            <div>
                <div class="stat-label">Active Semesters</div>
                <div class="stat-value"><?php echo (int) ($stats['active_semesters'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div>
                <div class="stat-label">Total Students</div>
                <div class="stat-value"><?php echo (int) ($stats['total_students'] ?? 0); ?></div>
            </div>
        </div>
    </div>

    <div class="dashboard-layout">
        <div class="dashboard-column">
            <div class="widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Collections Overview</h3>
                        <p class="widget-meta">Recent semesters</p>
                    </div>
                    <span class="widget-pill"><?php echo e($collection_percent); ?>% collected</span>
                </div>
                <div class="chart-bars">
                    <?php foreach ($collection_series as $value): ?>
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
                        <p class="widget-meta">Finance actions</p>
                    </div>
                </div>
                <div class="quick-actions">
                    <a class="quick-action" href="<?php echo e(url('accountant/semester-fees')); ?>">Semester Fees</a>
                    <a class="quick-action" href="<?php echo e(url('accountant/student-fees')); ?>">Student Fees</a>
                    <a class="quick-action" href="<?php echo e(url('reports/finance')); ?>">Finance Reports</a>
                    <a class="quick-action" href="<?php echo e(url('accountant/dashboard')); ?>">Overview</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
