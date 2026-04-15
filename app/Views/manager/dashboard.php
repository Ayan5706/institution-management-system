<?php
/** @var array $stats */
/** @var array $pending_resets */
/** @var string $user_name */
$activeNav = 'dashboard';
$stats = $stats ?? [];
$pending_resets = $pending_resets ?? [];
$enrollment_series = $enrollment_series ?? [32, 48, 58, 66, 54, 62, 71];
$enrollment_series = array_map(static fn($value) => max(0, min(100, (int) $value)), $enrollment_series);
$status_percent = $status_percent ?? 74;
$status_percent = max(0, min(100, (int) $status_percent));
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Manager Dashboard</h2>
            <div style="color:#6c7b86;">Student lifecycle management and oversight</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div>
                <div class="stat-label">Total Students</div>
                <div class="stat-value"><?php echo e($stats['total_students'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div>
                <div class="stat-label">Active Students</div>
                <div class="stat-value"><?php echo e($stats['active_students'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏸️</div>
            <div>
                <div class="stat-label">Inactive Students</div>
                <div class="stat-value"><?php echo e($stats['inactive_students'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🔐</div>
            <div>
                <div class="stat-label">Pending Resets</div>
                <div class="stat-value"><?php echo e($stats['pending_resets'] ?? 0); ?></div>
            </div>
        </div>
    </div>

    <div class="dashboard-layout">
        <div class="dashboard-column">
            <div class="widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Recent Requests</h3>
                        <p class="widget-meta">Pending student resets</p>
                    </div>
                </div>
                <ul class="activity-list">
                    <?php if (!empty($pending_resets)): ?>
                        <?php foreach (array_slice($pending_resets, 0, 4) as $reset): ?>
                            <li class="activity-item">
                                <div>
                                    <strong><?php echo e($reset['full_name'] ?? 'Student'); ?></strong>
                                    <div class="activity-meta">Reset requested · <?php echo e($reset['login_id'] ?? 'ID'); ?></div>
                                </div>
                                <span class="activity-meta"><?php echo e(date('M d', strtotime($reset['created_at'] ?? 'now'))); ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="activity-item">
                            <div>
                                <strong>No pending requests</strong>
                                <div class="activity-meta">All resets are processed</div>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
