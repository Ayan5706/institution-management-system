<?php
/** @var array $stats */
/** @var array $pending_resets */
/** @var string $user_name */
$activeNav = 'dashboard';
$stats = $stats ?? [];
$pending_resets = $pending_resets ?? [];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Principal Dashboard</h2>
            <div style="color:#6c7b86;">Overview of institutional operations</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🎓</div>
            <div>
                <div class="stat-label">Total Students</div>
                <div class="stat-value"><?php echo e($stats['total_students'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👩‍🏫</div>
            <div>
                <div class="stat-label">Staff Members</div>
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
            <div class="stat-icon">🔐</div>
            <div>
                <div class="stat-label">Pending Resets</div>
                <div class="stat-value"><?php echo e($stats['pending_resets'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✉️</div>
            <div>
                <div class="stat-label">Pending Email Changes</div>
                <div class="stat-value"><?php echo e($stats['pending_email_changes'] ?? 0); ?></div>
            </div>
        </div>
    </div>

    <div class="dashboard-layout single-column">
        <div class="dashboard-column">
            <div class="widget" style="display: flex; flex-direction: column;">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Pending Requests</h3>
                        <p class="widget-meta">Password reset approvals</p>
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
                    <a class="btn btn-primary" href="<?php echo e(url('principal/password-resets')); ?>" style="padding: 8px 14px; font-size: 0.85rem;">Review Requests</a>
                </div>
            </div>
            <div class="widget" style="display: flex; flex-direction: column; margin-top: 18px;">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Email Change Requests</h3>
                        <p class="widget-meta">Approve admin email updates</p>
                    </div>
                    <span class="widget-pill"><?php echo e($stats['pending_email_changes'] ?? 0); ?> pending</span>
                </div>
                <div class="empty-state" style="padding: 16px; text-align: center;">
                    <?php if (!empty($stats['pending_email_changes'])): ?>
                        You have email change requests to review.
                    <?php else: ?>
                        No pending requests.
                    <?php endif; ?>
                </div>
                <div style="display: flex; justify-content: flex-start; margin-top: auto; padding-top: 12px;">
                    <a class="btn btn-primary" href="<?php echo e(url('principal/email-requests')); ?>" style="padding: 8px 14px; font-size: 0.85rem;">Review Requests</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Optional: Auto-refresh dashboard every 30 seconds
    // Uncomment to enable
    // setInterval(function() { location.reload(); }, 30000);
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
