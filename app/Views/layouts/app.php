<?php
/** @var string|null $title */
/** @var string|null $content */
$title = $title ?? 'IMS Final';
$pageSubtitle = $pageSubtitle ?? 'Manage school records and related actions.';
$activeNav = $activeNav ?? '';
$currentPath = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$isActive = static function (string $path) use ($currentPath, $activeNav): string {
    $normalized = trim((string) parse_url($path, PHP_URL_PATH), '/');

    if ($activeNav !== '' && $activeNav === $normalized) {
        return 'active';
    }

    return $currentPath === $normalized ? 'active' : '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?></title>
    <script>
        // Allow pages to register table views before the main helper script is loaded.
        window.IMS = window.IMS || {};
        window.IMS._tableViewQueue = window.IMS._tableViewQueue || [];
        window.IMS.initTableView = window.IMS.initTableView || function (config) {
            window.IMS._tableViewQueue.push(config);
        };
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --bg: #e6ecef;
            --panel: #f8fbfd;
            --panel-2: #eef3f6;
            --text: #1f2a37;
            --muted: #6c7b86;
            --accent: #2f7f87;
            --accent-2: #6aa3a8;
            --border: #d6e0e6;
            --shadow: 0 18px 40px rgba(31, 42, 55, 0.12);
            --radius: 20px;
            --radius-lg: 26px;
            --font: 'Poppins', 'Segoe UI', sans-serif;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            min-height: 100%;
            font-family: var(--font);
            background:
                radial-gradient(circle at 12% 18%, rgba(106, 163, 168, 0.25), transparent 36%),
                radial-gradient(circle at 80% 8%, rgba(47, 127, 135, 0.18), transparent 32%),
                url('/assets/images/backgrounds/eduhub-motif.svg') no-repeat 70% 120px,
                linear-gradient(180deg, #d7e1e6 0%, #e8eef2 40%, #eef3f6 100%);
            color: var(--text);
        }

        a { color: inherit; }

        .shell {
            min-height: calc(100vh - 48px);
            margin: 24px;
            display: grid;
            grid-template-columns: 250px 1fr;
            background: var(--panel);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .sidebar {
            padding: 26px 18px;
            background: #f1f5f7;
            color: var(--text);
            border-right: 1px solid var(--border);
        }

        .brand {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 26px;
        }

        .brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #cfe3e6, #77aeb2);
            color: #1f2a37;
            font-weight: 700;
            overflow: hidden;
        }

        .brand-mark svg {
            width: 70%;
            height: 70%;
            display: block;
            fill: currentColor;
        }

        .brand-title {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 600;
        }

        .brand-subtitle {
            margin: 3px 0 0;
            color: var(--muted);
            font-size: 0.82rem;
        }

        .nav {
            display: grid;
            gap: 8px;
        }

        .nav a,
        .nav button {
            text-decoration: none;
            padding: 11px 14px;
            border-radius: 14px;
            color: #3b4a55;
            background: #ffffff;
            border: 1px solid transparent;
            cursor: pointer;
            font: inherit;
            text-align: left;
            width: 100%;
            box-shadow: 0 6px 16px rgba(31, 42, 55, 0.06);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav a:hover,
        .nav a.active,
        .nav button:hover {
            border-color: rgba(47, 127, 135, 0.25);
            background: rgba(47, 127, 135, 0.12);
            color: #1f2a37;
        }

        .sidebar-footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid rgba(108, 123, 134, 0.18);
            color: var(--muted);
            font-size: 0.86rem;
            line-height: 1.6;
        }

        .main {
            padding: 26px 28px 32px;
            background: linear-gradient(180deg, #f8fbfd 0%, #f2f6f8 100%);
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            margin-bottom: 20px;
            padding: 18px 20px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 10px 24px rgba(31, 42, 55, 0.08);
        }

        .page-title {
            margin: 0;
            font-size: clamp(1.35rem, 2vw, 2rem);
            font-weight: 600;
        }

        .page-subtitle {
            margin: 6px 0 0;
            color: var(--muted);
        }

        .card {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: 0 12px 30px rgba(31, 42, 55, 0.08);
        }

        .content-card {
            padding: 22px;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 16px;
            border-radius: 12px;
            border: 0;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary {
            color: #ffffff;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            box-shadow: 0 12px 24px rgba(47, 127, 135, 0.22);
        }

        .btn-ghost {
            color: var(--text);
            background: #f4f7f9;
            border: 1px solid var(--border);
        }

        .flash {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 14px;
            background: #eaf3f4;
            border: 1px solid #cde0e4;
            color: #2f5861;
        }

        /* Theme overrides for role pages */
        .content-card h2,
        .content-card h3,
        .content-card h4 {
            color: var(--text) !important;
        }

        .content-card .toolbar div > div {
            color: var(--muted) !important;
        }

        .content-card table {
            background: #ffffff !important;
            border-collapse: collapse;
        }

        .content-card th {
            background: #f4f7f9 !important;
            color: #4c5b66 !important;
            border-bottom: 2px solid var(--border) !important;
        }

        .content-card td {
            border-bottom: 1px solid var(--border) !important;
        }

        .content-card tr:hover {
            background: #f4f7f9 !important;
        }

        .content-card input,
        .content-card select,
        .content-card textarea,
        .content-card .filter-input,
        .content-card .filter-select,
        .content-card .form-input,
        .content-card .form-select {
            background: #ffffff !important;
            border: 1px solid var(--border) !important;
            border-radius: 10px;
            color: var(--text) !important;
        }

        .content-card input:focus,
        .content-card select:focus,
        .content-card textarea:focus,
        .content-card .filter-input:focus,
        .content-card .filter-select:focus,
        .content-card .form-input:focus,
        .content-card .form-select:focus {
            outline: none;
            border-color: rgba(47, 127, 135, 0.6) !important;
            box-shadow: 0 0 0 3px rgba(47, 127, 135, 0.12) !important;
        }

        .content-card .btn,
        .content-card .btn-submit,
        .content-card .add-btn,
        .content-card .action-btn,
        .content-card .btn-back,
        .content-card .btn-save,
        .content-card .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--accent-2)) !important;
            color: #ffffff !important;
            border: 0 !important;
            box-shadow: 0 12px 24px rgba(47, 127, 135, 0.22);
        }

        .content-card .btn-ghost,
        .content-card .btn-secondary,
        .content-card .btn-cancel {
            background: #f4f7f9 !important;
            color: var(--text) !important;
            border: 1px solid var(--border) !important;
        }

        .content-card .btn-danger,
        .content-card .btn-reject {
            background: #c96a6a !important;
            color: #ffffff !important;
        }

        .content-card .btn-approve {
            background: #2f7f87 !important;
            color: #ffffff !important;
        }

        .content-card .status-badge,
        .content-card .role-badge,
        .content-card .program-badge,
        .content-card .assignment-badge,
        .content-card .day-badge {
            background: rgba(47, 127, 135, 0.12) !important;
            color: #2f5861 !important;
        }

        .content-card .status-badge.active,
        .content-card .status-active {
            background: rgba(47, 127, 135, 0.18) !important;
            color: #2f5861 !important;
        }

        .content-card .status-badge.inactive,
        .content-card .status-inactive {
            background: rgba(201, 106, 106, 0.18) !important;
            color: #8b3a3a !important;
        }

        .content-card .empty-message,
        .content-card .empty-state {
            background: #f4f7f9 !important;
            border: 1px solid var(--border) !important;
            color: var(--muted) !important;
            border-radius: 12px;
        }

        /* Standard table/list viewing pattern */
        .content-card .table-view-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 12px;
            flex-wrap: wrap;
            margin: 12px 0 12px;
        }

        .content-card .table-view-controls {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
            flex: 1;
            min-width: min(520px, 100%);
        }

        .content-card .table-view-field {
            min-width: min(220px, 100%);
        }

        .content-card .table-view-field label {
            display: block;
            font-weight: 600;
            color: var(--text) !important;
            margin-bottom: 6px;
            font-size: 0.9rem;
        }

        .content-card .table-view-meta {
            color: var(--muted) !important;
            font-size: 0.9rem;
            white-space: nowrap;
            padding-bottom: 2px;
        }

        .content-card .table-container,
        .content-card .table-view-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #ffffff;
        }

        .content-card .pagination,
        .content-card .table-view-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .content-card .pagination-info,
        .content-card .table-view-pageinfo {
            color: var(--muted) !important;
            font-size: 0.9rem;
        }

        .content-card .pagination-actions {
            display: flex;
            gap: 10px;
        }

        .content-card .btn:disabled,
        .content-card button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .notice-warning {
            padding: 16px;
            background: #f4efe2;
            border-left: 4px solid #caa669;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #6d4d22;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .stat-card {
            padding: 16px 18px;
            border-radius: 16px;
            background: #ffffff;
            border: 1px solid var(--border);
            box-shadow: 0 10px 24px rgba(31, 42, 55, 0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(47, 127, 135, 0.12);
            color: #1f2a37;
            font-weight: 600;
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--muted);
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: -0.02em;
            margin-top: 2px;
        }

        .dashboard-layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            gap: 18px;
        }

        .dashboard-layout.single-column {
            grid-template-columns: minmax(0, 1fr);
        }

        .dashboard-layout.two-column {
            grid-template-columns: 1fr;
        }

        .dashboard-requests-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .dashboard-column {
            display: grid;
            gap: 18px;
        }

        .widget {
            padding: 18px;
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid var(--border);
            box-shadow: 0 10px 24px rgba(31, 42, 55, 0.08);
        }

        .widget-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }

        .widget-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 600;
        }

        .widget-meta {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 0.86rem;
        }

        .widget-pill {
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(47, 127, 135, 0.12);
            color: #1f2a37;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .chart-bars {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            align-items: end;
            gap: 10px;
            height: 140px;
        }

        .chart-bars .bar {
            height: calc(var(--value) * 100%);
            border-radius: 10px;
            background: linear-gradient(180deg, rgba(47, 127, 135, 0.9), rgba(106, 163, 168, 0.5));
        }

        .chart-bars .bar::after {
            content: '';
            display: block;
            width: 100%;
            height: 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.6);
            margin-top: 6px;
        }

        .chart-donut {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 8px auto 0;
            background: conic-gradient(#2f7f87 calc(var(--value) * 100%), #dbe6ea 0);
            display: grid;
            place-items: center;
        }

        .chart-donut span {
            width: 92px;
            height: 92px;
            border-radius: 50%;
            background: #ffffff;
            display: grid;
            place-items: center;
            font-weight: 600;
            color: var(--text);
        }

        .content-card table {
            width: 100%;
            min-width: 720px;
            border-collapse: collapse;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .content-card .table-container table,
        .content-card .table-view-container table {
            background: transparent;
            border: none;
            border-radius: 0;
        }

        .content-card table thead {
            background: #f4f7f9;
        }

        .content-card table th,
        .content-card table td {
            padding: 12px 14px;
            text-align: center;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
            vertical-align: middle;
        }

        .content-card table th {
            white-space: nowrap;
        }

        .content-card table th {
            font-weight: 600;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-size: 0.78rem;
        }

        .content-card table tbody tr:hover {
            background: rgba(47, 127, 135, 0.08);
        }

        .content-card table tbody tr:last-child td {
            border-bottom: none;
        }

        .content-card .view-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #0f172a;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
        }

        .content-card .view-btn:hover {
            background: #e2e8f0;
        }

        .activity-meta {
            color: var(--muted);
            font-size: 0.82rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .quick-action {
            padding: 12px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: #f4f7f9;
            text-decoration: none;
            color: var(--text);
            font-weight: 600;
            font-size: 0.88rem;
            text-align: center;
        }

        .quick-action:hover {
            background: rgba(47, 127, 135, 0.12);
        }

        @media (max-width: 1200px) {
            .shell {
                margin: 16px;
                grid-template-columns: 220px 1fr;
            }

            .main {
                padding: 22px 24px 28px;
            }

            .sidebar {
                padding: 22px 16px;
            }
        }

        @media (max-width: 960px) {
            .shell {
                grid-template-columns: 1fr;
                margin: 16px;
            }

            .sidebar {
                border-right: none;
                border-bottom: 1px solid var(--border);
            }

            .nav {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .toolbar {
                align-items: flex-start;
            }

            .dashboard-layout {
                grid-template-columns: 1fr;
            }

            .dashboard-requests-grid {
                grid-template-columns: 1fr;
            }

            .content-card table {
                min-width: 640px;
            }
        }

        @media (max-width: 640px) {
            .shell { margin: 12px; }
            .main { padding: 18px; }
            .content-card { padding: 18px; }
            .sidebar { padding: 18px 14px; }
            .topbar { padding: 16px; }
            .quick-actions { grid-template-columns: 1fr; }
            .nav { grid-template-columns: 1fr; }
            .page-title { font-size: clamp(1.2rem, 5vw, 1.6rem); }

            .content-card .table-view-header {
                align-items: flex-start;
            }

            .content-card .table-view-controls {
                min-width: 100%;
            }

            .content-card .table-view-meta {
                width: 100%;
            }

            .content-card table {
                min-width: 520px;
            }

            .content-card table th,
            .content-card table td {
                padding: 10px 12px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">
                    <svg viewBox="0 0 64 64" role="img" aria-label="Bank">
                        <path d="M4 26l28-14 28 14v6H4v-6zm6 12h6v18h-6V38zm12 0h6v18h-6V38zm12 0h6v18h-6V38zm12 0h6v18h-6V38zM6 58h52v4H6v-4z"/>
                    </svg>
                </div>
                <div>
                    <p class="brand-title">IMS</p>
                    <p class="brand-subtitle">Institution Management System</p>
                </div>
            </div>

            <nav class="nav">
                <?php
                $userRole = strtoupper((string) ($_SESSION['user_role'] ?? 'STUDENT'));
                $profileUrl = match ($userRole) {
                    'PRINCIPAL' => url('principal/profile'),
                    'VP'        => url('vp/profile'),
                    'MANAGER'   => url('manager/profile'),
                    'TEACHER'   => url('teacher/profile'),
                    'ACCOUNTANT'=> url('accountant/profile'),
                    'STUDENT'   => url('student/profile'),
                    default     => url('profile')
                };
                ?>

                <!-- Common Navigation -->
                <a href="<?php echo e(match($userRole) {
                    'PRINCIPAL' => url('principal/dashboard'),
                    'VP'        => url('vp/dashboard'),
                    'MANAGER'   => url('manager/dashboard'),
                    'TEACHER'   => url('teacher/dashboard'),
                    'ACCOUNTANT'=> url('accountant/dashboard'),
                    'STUDENT'   => url('student/dashboard'),
                    default     => url('dashboard')
                }); ?>" class="<?php echo e($isActive(match($userRole) {
                    'PRINCIPAL' => url('principal/dashboard'),
                    'VP'        => url('vp/dashboard'),
                    'MANAGER'   => url('manager/dashboard'),
                    'TEACHER'   => url('teacher/dashboard'),
                    'ACCOUNTANT'=> url('accountant/dashboard'),
                    'STUDENT'   => url('student/dashboard'),
                    default     => url('dashboard')
                })); ?>">
                    Dashboard
                </a>

                <!-- Principal Navigation -->
                <?php if ($userRole === 'PRINCIPAL'): ?>
                    <a href="<?php echo e(url('principal/accounts')); ?>" class="<?php echo e($isActive(url('principal/accounts'))); ?>">
                        Accounts
                    </a>
                    <a href="<?php echo e(url('principal/students')); ?>" class="<?php echo e($isActive(url('principal/students'))); ?>">
                        Students
                    </a>
                    <a href="<?php echo e(url('principal/teachers')); ?>" class="<?php echo e($isActive(url('principal/teachers'))); ?>">
                        Teachers
                    </a>
                    <a href="<?php echo e(url('principal/config')); ?>" class="<?php echo e($isActive(url('principal/config'))); ?>">
                        Config
                    </a>
                    <a href="<?php echo e(url('principal/password-resets')); ?>" class="<?php echo e($isActive(url('principal/password-resets'))); ?>">
                        Password Requests
                    </a>
                    <a href="<?php echo e(url('principal/email-requests')); ?>" class="<?php echo e($isActive(url('principal/email-requests'))); ?>">
                        Email Requests
                    </a>
                <?php endif; ?>

                <!-- VP Navigation -->
                <?php if ($userRole === 'VP'): ?>
                    <a href="<?php echo e(url('vp/programs')); ?>" class="<?php echo e($isActive(url('vp/programs'))); ?>">
                        Programs
                    </a>
                    <a href="<?php echo e(url('vp/semesters')); ?>" class="<?php echo e($isActive(url('vp/semesters'))); ?>">
                        Semesters
                    </a>
                    <a href="<?php echo e(url('vp/subjects')); ?>" class="<?php echo e($isActive(url('vp/subjects'))); ?>">
                        Subjects
                    </a>
                    <a href="<?php echo e(url('vp/teachers')); ?>" class="<?php echo e($isActive(url('vp/teachers'))); ?>">
                        Teachers
                    </a>
                    <a href="<?php echo e(url('vp/assignments')); ?>" class="<?php echo e($isActive(url('vp/assignments'))); ?>">
                        Assignments
                    </a>
                    <a href="<?php echo e(url('vp/timetable')); ?>" class="<?php echo e($isActive(url('vp/timetable'))); ?>">
                        Timetable
                    </a>
                    <a href="<?php echo e(url('vp/password-requests')); ?>" class="<?php echo e($isActive(url('vp/password-requests'))); ?>">
                        Password Requests
                    </a>
                    <a href="<?php echo e(url('vp/email-requests')); ?>" class="<?php echo e($isActive(url('vp/email-requests'))); ?>">
                        Email Requests
                    </a>
                <?php endif; ?>

                <!-- Manager Navigation -->
                <?php if ($userRole === 'MANAGER'): ?>
                    <a href="<?php echo e(url('manager/students')); ?>" class="<?php echo e($isActive(url('manager/students'))); ?>">
                        Students
                    </a>
                    <a href="<?php echo e(url('manager/promotions')); ?>" class="<?php echo e($isActive(url('manager/promotions'))); ?>">
                        Promotions
                    </a>
                    <a href="<?php echo e(url('manager/students/csv-upload')); ?>" class="<?php echo e($isActive(url('manager/students/csv-upload'))); ?>">
                        CSV Upload
                    </a>
                    <a href="<?php echo e(url('manager/password-resets')); ?>" class="<?php echo e($isActive(url('manager/password-resets'))); ?>">
                        Password Requests
                    </a>
                    <a href="<?php echo e(url('manager/email-requests')); ?>" class="<?php echo e($isActive(url('manager/email-requests'))); ?>">
                        Email Requests
                    </a>
                <?php endif; ?>

                <!-- Teacher Navigation -->
                <?php if ($userRole === 'TEACHER'): ?>
                    <a href="<?php echo e(url('teacher/attendance')); ?>" class="<?php echo e($isActive(url('teacher/attendance'))); ?>">
                        Mark Attendance
                    </a>
                    <a href="<?php echo e(url('teacher/timetable')); ?>" class="<?php echo e($isActive(url('teacher/timetable'))); ?>">
                        View Timetable
                    </a>
                    <a href="<?php echo e(url('teacher/students')); ?>" class="<?php echo e($isActive(url('teacher/students'))); ?>">
                        View Students
                    </a>
                    <a href="<?php echo e(url('teacher/attendance/history')); ?>" class="<?php echo e($isActive(url('teacher/attendance/history'))); ?>">
                        Attendance History
                    </a>
                <?php endif; ?>

                <!-- Student Navigation -->
                <?php if ($userRole === 'STUDENT'): ?>
                    <a href="<?php echo e(url('student/timetable')); ?>" class="<?php echo e($isActive(url('student/timetable'))); ?>">
                        Timetable
                    </a>
                    <a href="<?php echo e(url('student/subjects')); ?>" class="<?php echo e($isActive(url('student/subjects'))); ?>">
                        Subjects
                    </a>
                    <a href="<?php echo e(url('student/attendance')); ?>" class="<?php echo e($isActive(url('student/attendance'))); ?>">
                        Attendance
                    </a>
                    <a href="<?php echo e(url('student/fees')); ?>" class="<?php echo e($isActive(url('student/fees'))); ?>">
                        Fees
                    </a>
                <?php endif; ?>

                <!-- Accountant Navigation -->
                <?php if ($userRole === 'ACCOUNTANT'): ?>
                    <a href="<?php echo e(url('accountant/semester-fees')); ?>" class="<?php echo e($isActive(url('accountant/semester-fees'))); ?>">
                        Semester Fees
                    </a>
                    <a href="<?php echo e(url('accountant/student-fees')); ?>" class="<?php echo e($isActive(url('accountant/student-fees'))); ?>">
                        Student Fees
                    </a>
                <?php endif; ?>

                <!-- Admin Navigation (legacy - not used in spec) -->
                <?php if ($userRole === 'ADMIN'): ?>
                    <a href="<?php echo e(url('users')); ?>" class="<?php echo e($isActive(url('users'))); ?>">
                        Users
                    </a>
                <?php endif; ?>

                <a href="<?php echo e($profileUrl); ?>" class="<?php echo e($isActive($profileUrl)); ?>">
                    My Profile
                </a>

                <!-- Logout (Always shown) -->
                <form action="<?php echo e(url('logout')); ?>" method="POST" style="margin: 0;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" style="margin: 0;">
                        Logout
                    </button>
                </form>
            </nav>

            <div class="sidebar-footer">
                <div><strong><?php echo e((string) ($_SESSION['user_email'] ?? 'Guest')); ?></strong></div>
                <div><?php echo e((string) ($_SESSION['user_role'] ?? 'visitor')); ?></div>
            </div>
        </aside>

        <main class="main">
            <?php echo $content ?? ''; ?>
        </main>
    </div>

    <script>
        (function () {
            function getRows(tbody) {
                if (!tbody) return [];
                return Array.from(tbody.querySelectorAll('tr')).filter(function (row) {
                    return !row.querySelector('.empty-state') && !row.querySelector('.empty-message');
                });
            }

            function ensureNoResultsRow(tbody, id, colSpan, message) {
                if (!tbody) return null;
                var existing = document.getElementById(id);
                if (existing) return existing;

                var row = document.createElement('tr');
                row.id = id;
                row.innerHTML = '<td colspan="' + (colSpan || 1) + '" class="empty-state" style="text-align:center; padding: 24px;">' + (message || 'No matching records found.') + '</td>';
                tbody.appendChild(row);
                return row;
            }

            function initTableView(config) {
                if (!config || !config.tbodyId) return;

                var tbody = document.getElementById(config.tbodyId);
                if (!tbody) return;

                var searchInput = config.searchInputId ? document.getElementById(config.searchInputId) : null;
                var metaEl = config.metaId ? document.getElementById(config.metaId) : null;
                var pageInfoEl = config.pageInfoId ? document.getElementById(config.pageInfoId) : null;
                var prevBtn = config.prevId ? document.getElementById(config.prevId) : null;
                var nextBtn = config.nextId ? document.getElementById(config.nextId) : null;
                var pagerEl = config.pagerId ? document.getElementById(config.pagerId) : null;

                var pageSize = Number(config.pageSize || 10);
                var currentPage = 1;

                function matchesFilter(rowValue, filterValue, mode, separator) {
                    var raw = String(rowValue || '');
                    var wanted = String(filterValue || '');

                    if (!wanted) return true;

                    if (mode === 'csv-includes') {
                        var sep = separator || ',';
                        return raw.split(sep).map(function (v) { return v.trim(); }).filter(Boolean).indexOf(wanted) !== -1;
                    }

                    return raw === wanted;
                }

                var filters = (config.filters || []).map(function (f) {
                    return {
                        el: document.getElementById(f.id),
                        key: f.rowDatasetKey,
                        mode: f.mode || 'equals',
                        separator: f.separator || ',',
                    };
                }).filter(function (f) { return !!f.el && !!f.key; });

                function filteredRows() {
                    var rows = getRows(tbody);
                    var query = (searchInput && searchInput.value ? String(searchInput.value) : '').trim().toLowerCase();

                    return rows.filter(function (row) {
                        for (var i = 0; i < filters.length; i++) {
                            var value = String(filters[i].el.value || '').trim();
                            if (!matchesFilter(row.dataset[filters[i].key], value, filters[i].mode, filters[i].separator)) {
                                return false;
                            }
                        }

                        if (!query) return true;

                        var haystack = String(row.dataset.search || row.textContent || '').toLowerCase();
                        return haystack.indexOf(query) !== -1;
                    });
                }

                function render() {
                    var rows = getRows(tbody);
                    var filtered = filteredRows();
                    var total = rows.length;
                    var shownTotal = filtered.length;

                    var pageCount = Math.max(1, Math.ceil(shownTotal / pageSize));
                    currentPage = Math.min(Math.max(1, currentPage), pageCount);

                    var start = (currentPage - 1) * pageSize;
                    var end = start + pageSize;
                    var visible = filtered.slice(start, end);

                    rows.forEach(function (row) { row.style.display = 'none'; });
                    visible.forEach(function (row) { row.style.display = ''; });

                    var noResultsRow = ensureNoResultsRow(tbody, config.noResultsRowId || (config.tbodyId + '-no-results'), config.noResultsColSpan || 1, config.noResultsText);
                    if (noResultsRow) {
                        noResultsRow.style.display = shownTotal === 0 ? '' : 'none';
                    }

                    if (metaEl) {
                        metaEl.textContent = (shownTotal === total) ? (total + ' total') : (shownTotal + ' of ' + total);
                    }

                    if (pagerEl) {
                        pagerEl.style.display = shownTotal > 0 ? '' : 'none';
                    }

                    if (pageInfoEl) {
                        pageInfoEl.textContent = 'Page ' + currentPage + ' of ' + pageCount;
                    }

                    if (prevBtn) prevBtn.disabled = currentPage <= 1;
                    if (nextBtn) nextBtn.disabled = currentPage >= pageCount;
                }

                function resetAndRender() {
                    currentPage = 1;
                    render();
                }

                if (searchInput) {
                    searchInput.addEventListener('input', resetAndRender);
                }

                filters.forEach(function (f) {
                    f.el.addEventListener('change', resetAndRender);
                });

                if (prevBtn) {
                    prevBtn.addEventListener('click', function () {
                        currentPage = Math.max(1, currentPage - 1);
                        render();
                    });
                }

                if (nextBtn) {
                    nextBtn.addEventListener('click', function () {
                        currentPage = currentPage + 1;
                        render();
                    });
                }

                render();
                return { render: render, reset: resetAndRender };
            }

            window.IMS = window.IMS || {};
            window.IMS.initTableView = initTableView;

            // Initialize any table views queued before the helper loaded.
            var queue = window.IMS._tableViewQueue || [];
            if (queue && queue.length) {
                queue.forEach(function (cfg) {
                    try {
                        initTableView(cfg);
                    } catch (e) {
                        // Swallow init errors to avoid breaking pages.
                    }
                });
                window.IMS._tableViewQueue = [];
            }
        })();
    </script>
</body>
</html>
