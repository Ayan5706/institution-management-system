<?php
/** @var array $stats */
/** @var array $pending_resets */
/** @var string $user_name */
/** @var array $summary */
$activeNav = 'dashboard';
$stats = $stats ?? $summary ?? [];
$pending_resets = $pending_resets ?? [];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Vice Principal Dashboard</h2>
            <div style="color:#64748b;">Academic management and oversight</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👩‍🏫</div>
            <div>
                <div class="stat-label">Total Teachers</div>
                <div class="stat-value"><?php echo e($stats['total_teachers'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div>
                <div class="stat-label">Active Programs</div>
                <div class="stat-value"><?php echo e($stats['total_programs'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🗓️</div>
            <div>
                <div class="stat-label">Current Semesters</div>
                <div class="stat-value"><?php echo e($stats['current_semesters'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🔐</div>
            <div>
                <div class="stat-label">Pending Requests</div>
                <div class="stat-value"><?php echo e($stats['pending_resets'] ?? 0); ?></div>
            </div>
        </div>
    </div>

    <div class="dashboard-layout single-column">
        <div class="dashboard-column">
            <div class="widget" style="display: flex; flex-direction: column;">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Pending Password Requests</h3>
                        <p class="widget-meta">Teacher resets</p>
                    </div>
                    <span class="widget-pill"><?php echo e(count($pending_resets)); ?> pending</span>
                </div>
                <div class="empty-state" style="padding: 16px; text-align: center;">
                    <?php if (!empty($pending_resets)): ?>
                        You have pending reset requests to review.
                    <?php else: ?>
                        No pending requests.
                    <?php endif; ?>
                </div>
                <div style="display: flex; justify-content: flex-start; margin-top: auto; padding-top: 12px;">
                    <a class="btn btn-primary" href="<?php echo e(url('vp/password-requests')); ?>" style="padding: 8px 14px; font-size: 0.85rem;">Review Requests</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
