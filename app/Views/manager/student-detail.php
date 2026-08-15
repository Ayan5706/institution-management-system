<?php
/** @var array $student */
/** @var array $student_profile */
/** @var array|null $program */
/** @var array|null $current_semester */
/** @var array $attendance_summary */
/** @var array|null $created_by */
/** @var string $fee_status */
/** @var float $fee_paid */
$activeNav = 'students';
$student = $student ?? [
    'id' => 0,
    'role' => 'STUDENT',
    'login_id' => 'student001',
    'full_name' => 'John Student',
    'email' => 'john@example.test',
    'phone' => '09170000001',
    'is_active' => 1,
    'created_at' => '2026-04-11 10:00:00',
];
$student_profile = $student_profile ?? [];
$program = $program ?? null;
$current_semester = $current_semester ?? null;
$attendance_summary = $attendance_summary ?? ['total' => 0, 'present' => 0, 'rate' => 0];
$title = 'Student Details';
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Student Details</h2>
            <div style="color:#64748b;">View student account information (read-only)</div>
        </div>
        <a class="btn-back" href="<?php echo e(url('manager/students')); ?>">← Back to Students</a>
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
            background: linear-gradient(135deg, #2f7f87 0%, #0d9488 100%);
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

    <div class="detail-header">
        <div class="detail-avatar">
            <?php
            $initials = '';
            $nameParts = explode(' ', $student['full_name'] ?? '');
            foreach (array_slice($nameParts, 0, 2) as $part) {
                $initials .= substr($part, 0, 1);
            }
            echo e(strtoupper($initials ?: 'S'));
            ?>
        </div>
        <div class="detail-header-info">
            <h3><?php echo e($student['full_name'] ?? 'N/A'); ?></h3>
            <p>
                <strong>Login ID:</strong> <?php echo e($student['login_id'] ?? 'N/A'); ?>
            </p>
            <p>
                <span class="status-badge <?php echo ((int) ($student['is_active'] ?? 0)) === 1 ? 'status-active' : 'status-inactive'; ?>">
                    <?php echo ((int) ($student['is_active'] ?? 0)) === 1 ? '✓ Active' : '✗ Inactive'; ?>
                </span>
            </p>
        </div>
    </div>

    <h2 class="section-title">Contact Information</h2>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Email Address</span>
            <div class="detail-value">
                <a href="mailto:<?php echo e($student['email'] ?? ''); ?>" style="color: #2563eb; text-decoration: none;">
                    <?php echo e($student['email'] ?? 'N/A'); ?>
                </a>
            </div>
        </div>
        <div class="detail-item">
            <span class="detail-label">Phone Number</span>
            <div class="detail-value">
                <?php echo e($student['phone'] ?? '-'); ?>
            </div>
        </div>
    </div>

    <h2 class="section-title">Account Information</h2>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Account Type</span>
            <div class="detail-value"><?php echo e($student['role'] ?? 'STUDENT'); ?></div>
        </div>
        <div class="detail-item">
            <span class="detail-label">Managed by</span>
            <div class="detail-value"><?php echo e($created_by['role'] ?? 'System'); ?></div>
        </div>
        <div class="detail-item">
            <span class="detail-label">Account Created</span>
            <div class="detail-value"><?php echo e($student['created_at'] ?? 'N/A'); ?></div>
        </div>
        <div class="detail-item">
            <span class="detail-label">Last Updated</span>
            <div class="detail-value"><?php echo e($student['updated_at'] ?? $student['created_at'] ?? 'N/A'); ?></div>
        </div>
    </div>

    <h2 class="section-title">Academic Progress</h2>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Registration Number</span>
            <div class="detail-value"><?php echo e($student_profile['registration_number'] ?? 'N/A'); ?></div>
        </div>
        <div class="detail-item">
            <span class="detail-label">Program</span>
            <div class="detail-value">
                <?php echo e($program['program_name'] ?? 'N/A'); ?>
            </div>
        </div>
        <div class="detail-item">
            <span class="detail-label">Current Semester</span>
            <div class="detail-value">
                <?php echo e($current_semester['semester_number'] ?? 'N/A'); ?>
            </div>
        </div>
        <div class="detail-item">
            <span class="detail-label">Academic Year</span>
            <div class="detail-value">
                <?php echo e($current_semester['academic_year'] ?? 'N/A'); ?>
            </div>
        </div>
        <div class="detail-item">
            <span class="detail-label">Attendance Rate</span>
            <div class="detail-value">
                <?php echo e((string) ($attendance_summary['rate'] ?? 0)); ?>%
            </div>
        </div>
        <div class="detail-item">
            <span class="detail-label">Fee Status</span>
            <div class="detail-value">
                <?php echo e($fee_status ?? 'N/A'); ?>
            </div>
        </div>
    </div>

    <div class="info-box" style="margin-top: 30px;">
        <strong>ℹ️ Note:</strong> This is a read-only view of the student's account information. For additional details like enrollment records, grades, or program information, please refer to the student's profile in the academic management system.
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
