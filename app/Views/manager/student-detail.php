<?php
/** @var array $student */
/** @var array $profile */
/** @var array $program */
/** @var array $fee_summary */
/** @var array $attendance_summary */
$activeNav = 'students';
$student = $student ?? [];
$profile = $profile ?? [];
$program = $program ?? [];
$fee_summary = $fee_summary ?? [];
$attendance_summary = $attendance_summary ?? [];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;"><?php echo e($student['full_name'] ?? 'Student Details'); ?></h2>
            <div style="color:#64748b;">View student profile and academic information</div>
        </div>
        <div>
            <a href="<?php echo e(url('manager/students')); ?>" class="btn btn-ghost">← Back to Students</a>
        </div>
    </div>

    <style>
        .profile-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .profile-section {
            padding: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }

        .profile-section h3 {
            margin: 0 0 16px;
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .profile-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .profile-row:last-child {
            border-bottom: none;
        }

        .profile-label {
            font-weight: 600;
            color: #475569;
        }

        .profile-value {
            color: #0f172a;
            font-weight: 500;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-badge.active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .summary-card {
            padding: 16px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            text-align: center;
        }

        .summary-label {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .summary-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0f172a;
        }

        .section {
            margin-top: 30px;
            padding: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }

        .section h3 {
            margin: 0 0 16px;
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
        }

        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }

            .profile-row {
                flex-direction: column;
                gap: 8px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <!-- Student Basic Information -->
    <div class="profile-container">
        <!-- Personal Information -->
        <div class="profile-section">
            <h3>Personal Information</h3>
            <div class="profile-row">
                <span class="profile-label">Full Name</span>
                <span class="profile-value"><?php echo e($student['full_name'] ?? 'N/A'); ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Email</span>
                <span class="profile-value"><?php echo e($student['email'] ?? 'N/A'); ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Phone</span>
                <span class="profile-value"><?php echo e($student['phone'] ?? 'N/A'); ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Login ID</span>
                <span class="profile-value"><?php echo e($student['login_id'] ?? 'N/A'); ?></span>
            </div>
        </div>

        <!-- Academic Information -->
        <div class="profile-section">
            <h3>Academic Information</h3>
            <div class="profile-row">
                <span class="profile-label">Registration Number</span>
                <span class="profile-value"><?php echo e($profile['registration_number'] ?? 'N/A'); ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Date of Birth</span>
                <span class="profile-value"><?php echo e($profile['date_of_birth'] ? date('M d, Y', strtotime($profile['date_of_birth'])) : 'N/A'); ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Program</span>
                <span class="profile-value"><?php echo e($program['program_name'] ?? 'N/A'); ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Account Status</span>
                <span class="profile-value">
                    <span class="status-badge <?php echo e($student['is_active'] ? 'active' : 'inactive'); ?>">
                        <?php echo e($student['is_active'] ? '✓ Active' : '○ Inactive'); ?>
                    </span>
                </span>
            </div>
        </div>
    </div>

    <!-- Fee Summary Section -->
    <div class="section">
        <h3>Fee Summary</h3>
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Total Fees</div>
                <div class="summary-value">$<?php echo e(number_format($fee_summary['total_fees'] ?? 0, 2)); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Amount Paid</div>
                <div class="summary-value" style="color: #10b981;">$<?php echo e(number_format($fee_summary['paid_amount'] ?? 0, 2)); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Amount Due</div>
                <div class="summary-value" style="color: <?php echo e(($fee_summary['due_amount'] ?? 0) > 0 ? '#dc2626' : '#10b981'); ?>;">
                    $<?php echo e(number_format($fee_summary['due_amount'] ?? 0, 2)); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Summary Section -->
    <div class="section">
        <h3>Attendance Summary</h3>
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Total Classes</div>
                <div class="summary-value"><?php echo e($attendance_summary['total_classes'] ?? 0); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Present</div>
                <div class="summary-value" style="color: #10b981;"><?php echo e($attendance_summary['present'] ?? 0); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Absent</div>
                <div class="summary-value" style="color: #dc2626;"><?php echo e($attendance_summary['absent'] ?? 0); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Late</div>
                <div class="summary-value" style="color: #f59e0b;"><?php echo e($attendance_summary['late'] ?? 0); ?></div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
