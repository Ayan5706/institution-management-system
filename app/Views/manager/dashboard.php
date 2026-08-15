<?php
/** @var array $stats */
/** @var array $pending_resets */
/** @var string $user_name */
$activeNav = 'dashboard';
$stats = $stats ?? [];
$pending_resets = $pending_resets ?? [];
$pending_email_changes = $pending_email_changes ?? [];
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

    <style>
        .pending-alert {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid rgba(245, 158, 11, 0.4);
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.18), rgba(245, 158, 11, 0.06));
            color: #92400e;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .pending-alert strong {
            color: #7c2d12;
        }
    </style>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div>
                <div class="stat-label">Total Students</div>
                <div class="stat-value" id="statTotalStudents"><?php echo e($stats['total_students'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div>
                <div class="stat-label">Active Students</div>
                <div class="stat-value" id="statActiveStudents"><?php echo e($stats['active_students'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏸️</div>
            <div>
                <div class="stat-label">Inactive Students</div>
                <div class="stat-value" id="statInactiveStudents"><?php echo e($stats['inactive_students'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🔐</div>
            <div>
                <div class="stat-label">Pending Resets</div>
                <div class="stat-value" id="statPendingResets"><?php echo e($stats['pending_resets'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✉️</div>
            <div>
                <div class="stat-label">Pending Email Changes</div>
                <div class="stat-value" id="statPendingEmailChanges"><?php echo e($stats['pending_email_changes'] ?? 0); ?></div>
            </div>
        </div>
    </div>

    <div class="dashboard-layout two-column">
        <div class="dashboard-requests-grid">
            <div class="widget" style="display: flex; flex-direction: column;">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Pending Requests</h3>
                        <p class="widget-meta">Student password reset approvals</p>
                    </div>
                    <span class="widget-pill" id="pendingResetsPill"><?php echo e(count($pending_resets)); ?> pending</span>
                </div>
                <div class="empty-state" id="pendingResetsEmpty" style="padding: 16px; text-align: center;">
                    <?php if (!empty($pending_resets)): ?>
                        <div class="pending-alert">⚠️ <strong>Action needed:</strong> Pending reset requests require review.</div>
                    <?php else: ?>
                        No pending requests.
                    <?php endif; ?>
                </div>
                <div style="display: flex; justify-content: flex-start; margin-top: auto; padding-top: 12px;">
                    <a class="btn btn-primary" href="<?php echo e(url('manager/password-resets')); ?>" style="padding: 8px 14px; font-size: 0.85rem;">Review Requests</a>
                </div>
            </div>
            <div class="widget" style="display: flex; flex-direction: column;">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Email Change Requests</h3>
                        <p class="widget-meta">Approve student email updates</p>
                    </div>
                    <span class="widget-pill" id="pendingEmailPill"><?php echo e($stats['pending_email_changes'] ?? 0); ?> pending</span>
                </div>
                <div class="empty-state" id="pendingEmailEmpty" style="padding: 16px; text-align: center;">
                    <?php if (!empty($pending_email_changes)): ?>
                        <div class="pending-alert">⚠️ <strong>Action needed:</strong> Email change requests need your review.</div>
                    <?php else: ?>
                        No pending requests.
                    <?php endif; ?>
                </div>
                <div style="display: flex; justify-content: flex-start; margin-top: auto; padding-top: 12px;">
                    <a class="btn btn-primary" href="<?php echo e(url('manager/email-requests')); ?>" style="padding: 8px 14px; font-size: 0.85rem;">Review Requests</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = value;
        }
    }

    function setHtml(id, value) {
        const el = document.getElementById(id);
        if (el) {
            el.innerHTML = value;
        }
    }

    async function refreshDashboard() {
        try {
            const response = await fetch(`<?php echo e(url('api/manager/dashboard')); ?>?t=${Date.now()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store',
            });
            const payload = await response.json();

            if (payload.success && payload.data) {
                const data = payload.data;
                setText('statTotalStudents', data.total_students ?? 0);
                setText('statActiveStudents', data.active_students ?? 0);
                setText('statInactiveStudents', data.inactive_students ?? 0);
                setText('statPendingResets', data.pending_resets ?? 0);
                setText('statPendingEmailChanges', data.pending_email_changes ?? 0);
                setText('pendingEmailPill', `${data.pending_email_changes ?? 0} pending`);

                const emailEmpty = (data.pending_email_changes ?? 0) > 0
                    ? '<div class="pending-alert">⚠️ <strong>Action needed:</strong> Email change requests need your review.</div>'
                    : 'No pending requests.';
                setHtml('pendingEmailEmpty', emailEmpty);
            }
        } catch (error) {
            console.log('[Manager Dashboard] API error:', error);
        }

        try {
            const response = await fetch(`<?php echo e(url('api/manager/reset-requests')); ?>?t=${Date.now()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store',
            });
            const payload = await response.json();

            if (payload.success && Array.isArray(payload.data)) {
                const count = payload.data.length;
                setText('pendingResetsPill', `${count} pending`);
                setText('statPendingResets', count);

                const resetEmpty = count > 0
                    ? '<div class="pending-alert">⚠️ <strong>Action needed:</strong> Pending reset requests require review.</div>'
                    : 'No pending requests.';
                setHtml('pendingResetsEmpty', resetEmpty);
            }
        } catch (error) {
            console.log('[Manager Dashboard] Reset API error:', error);
        }
    }

    refreshDashboard();
    setInterval(refreshDashboard, 15000);
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
