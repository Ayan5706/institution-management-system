<?php
/** @var array $teacher */
/** @var array $assignments */
$activeNav = 'teachers';
$teacher = $teacher ?? [
    'id' => 0,
    'role' => 'TEACHER',
    'login_id' => 'teacher001',
    'full_name' => 'Prof. Jane Smith',
    'email' => 'jane@example.test',
    'phone' => '09170000002',
    'is_active' => 1,
    'created_at' => '2026-04-11 10:00:00',
];
$assignments = $assignments ?? [];
$title = 'Teacher Details';
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Teacher Details</h2>
            <div style="color:#64748b;">View faculty account information (read-only)</div>
        </div>
        <a class="btn-back" href="<?php echo e(url('principal/teachers')); ?>">← Back to Teachers</a>
    </div>

    <style>
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            gap: 20px;
        }

        .btn-back {
            padding: 10px 16px;
            background: #f8fafc;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-back:hover {
            background: #e2e8f0;
        }

        .detail-header {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: #fff;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .detail-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
        }

        .detail-header-info h3 {
            margin: 0 0 5px;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .detail-header-info p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.2);
            color: #065f46;
        }

        .status-inactive {
            background: rgba(239, 68, 68, 0.2);
            color: #991b1b;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }

        .detail-item {
            padding: 16px;
            border-radius: 12px;
            background: #f8fbff;
            border: 1px solid #e2e8f0;
        }

        .detail-label {
            display: block;
            color: #64748b;
            font-size: 0.88rem;
            margin-bottom: 6px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            color: #0f172a;
            font-size: 1.02rem;
            font-weight: 600;
            word-break: break-all;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }

        .info-box {
            padding: 16px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            color: #1e40af;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        @media (max-width: 720px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .detail-header {
                flex-direction: column;
                text-align: center;
            }

            .toolbar {
                flex-direction: column;
            }

            .btn-back {
                width: 100%;
                text-align: center;
            }
        }
    </style>

    <!-- Header Section -->
    <div class="detail-header">
        <div class="detail-avatar">
            <?php 
            $initials = '';
            $nameParts = explode(' ', $teacher['full_name'] ?? '');
            foreach (array_slice($nameParts, 0, 2) as $part) {
                $initials .= substr($part, 0, 1);
            }
            echo e(strtoupper($initials ?: 'T'));
            ?>
        </div>
        <div class="detail-header-info">
            <h3><?php echo e($teacher['full_name'] ?? 'N/A'); ?></h3>
            <p>
                <strong>Login ID:</strong> <?php echo e($teacher['login_id'] ?? 'N/A'); ?>
            </p>
            <p>
                <span class="status-badge <?php echo ((int) ($teacher['is_active'] ?? 0)) === 1 ? 'status-active' : 'status-inactive'; ?>">
                    <?php echo ((int) ($teacher['is_active'] ?? 0)) === 1 ? '✓ Active' : '✗ Inactive'; ?>
                </span>
            </p>
        </div>
    </div>

    <!-- Contact Information -->
    <h2 class="section-title">Contact Information</h2>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Email Address</span>
            <div class="detail-value">
                <a href="mailto:<?php echo e($teacher['email'] ?? ''); ?>" style="color: #2563eb; text-decoration: none;">
                    <?php echo e($teacher['email'] ?? 'N/A'); ?>
                </a>
            </div>
        </div>
        <div class="detail-item">
            <span class="detail-label">Phone Number</span>
            <div class="detail-value">
                <?php echo e($teacher['phone'] ?? '-'); ?>
            </div>
        </div>
    </div>

    <!-- Account Information -->
    <h2 class="section-title">Account Information</h2>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Teacher ID</span>
            <div class="detail-value"><?php echo e((string) ($teacher['id'] ?? '')); ?></div>
        </div>
        <div class="detail-item">
            <span class="detail-label">Account Type</span>
            <div class="detail-value"><?php echo e($teacher['role'] ?? 'TEACHER'); ?></div>
        </div>
        <div class="detail-item">
            <span class="detail-label">Account Created</span>
            <div class="detail-value"><?php echo e($teacher['created_at'] ?? 'N/A'); ?></div>
        </div>
        <div class="detail-item">
            <span class="detail-label">Last Updated</span>
            <div class="detail-value"><?php echo e($teacher['updated_at'] ?? $teacher['created_at'] ?? 'N/A'); ?></div>
        </div>
    </div>

    <!-- Assigned Classes -->
    <h2 class="section-title">Assigned Classes</h2>
    <?php if (empty($assignments)): ?>
        <div class="info-box">No subject assignments found for this teacher.</div>
    <?php else: ?>
        <div class="detail-grid">
            <?php foreach ($assignments as $assignment): ?>
                <div class="detail-item">
                    <span class="detail-label"><?php echo e($assignment['subject_code'] ?? 'N/A'); ?></span>
                    <div class="detail-value">
                        <?php echo e($assignment['subject_name'] ?? 'N/A'); ?>
                    </div>
                    <div style="margin-top: 8px; color: #64748b; font-size: 0.88rem;">
                        <?php echo e($assignment['program_name'] ?? 'N/A'); ?>
                        <?php if (!empty($assignment['program_code'])): ?>
                            (<?php echo e($assignment['program_code']); ?>)
                        <?php endif; ?>
                    </div>
                    <div style="margin-top: 4px; color: #64748b; font-size: 0.88rem;">
                        Semester <?php echo e($assignment['semester_number'] ?? 'N/A'); ?>
                        <?php if (!empty($assignment['academic_year'])): ?>
                            · <?php echo e($assignment['academic_year']); ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Info Box -->
    <div class="info-box" style="margin-top: 30px;">
        <strong>ℹ️ Note:</strong> This is a read-only view of the teacher's account information. For additional details like subject assignments, class schedules, or performance metrics, please refer to the faculty management section in the academic system.
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
