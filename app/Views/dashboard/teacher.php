<?php
/** @var string $user_name */
/** @var string $user_role */
/** @var array<string, int> $summary */
$activeNav = 'dashboard';
$user_name = $user_name ?? 'Teacher';
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Teacher Dashboard</h2>
            <div style="color:#64748b;">Welcome, <?php echo e($user_name); ?>. Class and attendance management.</div>
        </div>
        <div>
            <a class="btn btn-primary" href="<?php echo e(url('attendance')); ?>">Mark Attendance</a>
        </div>
    </div>

    <style>
        .section-title {
            margin: 20px 0 15px;
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .kpis {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .kpi {
            padding: 18px;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            border: 1px solid #e2e8f0;
        }

        .kpi .label {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .kpi .value {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            margin-bottom: 8px;
        }

        .kpi .trend {
            font-size: 0.9rem;
            color: #475569;
        }

        .panel {
            padding: 20px;
            border-radius: 18px;
            background: #fff;
            border: 1px solid #e2e8f0;
        }

        .panel h3 {
            margin: 0 0 18px;
            font-size: 1.05rem;
        }

        .quick-links {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 20px;
        }

        .quick-link {
            padding: 14px;
            border-radius: 14px;
            text-decoration: none;
            background: linear-gradient(135deg, #d1fae5, #f8fafc);
            border: 1px solid #a7f3d0;
            font-weight: 700;
            color: #065f46;
        }

        .tone-green { border-top: 4px solid #16a34a; }
        .tone-teal { border-top: 4px solid #0d9488; }

        @media (max-width: 1100px) {
            .kpis { grid-template-columns: 1fr; }
        }

        @media (max-width: 720px) {
            .quick-links { grid-template-columns: 1fr; }
        }
    </style>

    <div style="margin-bottom: 30px;">
        <h3 class="section-title">Teaching Overview</h3>
        <div class="kpis">
            <div class="kpi tone-green">
                <div class="label">Subjects</div>
                <div class="value"><?php echo e($summary['total_subjects'] ?? 42); ?></div>
                <div class="trend">Assigned subjects and classes</div>
            </div>
            <div class="kpi tone-teal">
                <div class="label">Attendance</div>
                <div class="value">Manage</div>
                <div class="trend">Track student attendance records</div>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 30px;">
        <h3 class="section-title">Teaching Tools</h3>
        <div class="quick-links">
            <a class="quick-link" href="<?php echo e(url('attendance')); ?>">Mark Attendance</a>
            <a class="quick-link" href="<?php echo e(url('timetables')); ?>">View Timetable</a>
            <a class="quick-link" href="<?php echo e(url('reports/academic')); ?>">Academic Reports</a>
            <a class="quick-link" href="<?php echo e(url('reports/attendance')); ?>">Attendance Reports</a>
            <a class="quick-link" href="<?php echo e(url('reports')); ?>">All Reports</a>
            <a class="quick-link" href="<?php echo e(url('teacher-assignments')); ?>">View Assignments</a>
        </div>
    </div>

    <div class="panel">
        <h3>Your Responsibilities</h3>
        <p>As a Teacher, you manage classroom attendance, view your assignments, check your timetable, and generate academic reports. You can track student attendance and access educational materials.</p>
        <p style="color: #64748b; margin-bottom: 0;">Use the teaching tools to manage your classes and student records.</p>
    </div>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
