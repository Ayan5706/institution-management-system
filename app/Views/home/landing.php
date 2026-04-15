<?php
/** @var string|null $title */
/** @var array $features */
$title = $title ?? 'IMS - Institution Management System';
$features = $features ?? [];

if ($features === []) {
    $features = [
        [
            'title' => 'Student Management',
            'description' => 'Manage profiles and enrollments.',
            'icon' => 'SM',
        ],
        [
            'title' => 'Attendance Tracking',
            'description' => 'Track attendance and summaries.',
            'icon' => 'AT',
        ],
        [
            'title' => 'Fee Management',
            'description' => 'Monitor fees and payments.',
            'icon' => 'FM',
        ],
        [
            'title' => 'Class Scheduling',
            'description' => 'Organize timetables quickly.',
            'icon' => 'CS',
        ],
        [
            'title' => 'Teacher Management',
            'description' => 'Assign roles and workloads.',
            'icon' => 'TM',
        ],
        [
            'title' => 'Reporting & Analytics',
            'description' => 'Generate clear insights fast.',
            'icon' => 'RA',
        ],
    ];
}

if (!function_exists('renderFeatureCard')) {
    /** @param array<string, mixed> $feature */
    function renderFeatureCard(array $feature): void
    {
        $icon = (string) ($feature['icon'] ?? '');
        $title = (string) ($feature['title'] ?? '');
        $description = (string) ($feature['description'] ?? '');
        ?>
        <div class="feature-card">
            <div class="feature-icon"><?php echo e($icon); ?></div>
            <div class="feature-text">
                <h3 class="feature-title"><?php echo e($title); ?></h3>
                <p class="feature-description"><?php echo e($description); ?></p>
            </div>
        </div>
        <?php
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --bg: #d9e2e6;
            --panel: #f5f8fa;
            --panel-2: #eef3f6;
            --text: #1f2a37;
            --muted: #6c7b86;
            --accent: #2f7f87;
            --accent-2: #6aa3a8;
            --border: rgba(160, 176, 185, 0.55);
            --shadow: 0 28px 60px rgba(31, 42, 55, 0.18);
            --radius: 22px;
            --font: 'Poppins', 'Segoe UI', sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100%;
            font-family: var(--font);
            color: var(--text);
            background:
                radial-gradient(circle at 12% 16%, rgba(47, 127, 135, 0.14), transparent 40%),
                radial-gradient(circle at 84% 22%, rgba(106, 163, 168, 0.22), transparent 36%),
                linear-gradient(135deg, #d3dde2, #e6edf1);
        }

        .page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 28px 24px 80px;
        }

        .nav-shell {
            display: flex;
            justify-content: center;
        }

        .nav {
            width: min(980px, 100%);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            background: rgba(248, 251, 253, 0.85);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(14px);
        }

        .nav-right {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 18px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            text-decoration: none;
            color: var(--text);
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

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .nav-links {
            display: flex;
            gap: 22px;
            font-size: 0.92rem;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--muted);
            font-weight: 600;
        }

        .nav-links a:hover {
            color: var(--text);
        }

        .login-btn {
            padding: 8px 18px;
            border-radius: 12px;
            background: #2f7f87;
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 12px 20px rgba(47, 127, 135, 0.2);
        }

        .content {
            width: min(980px, 100%);
            margin: 22px auto 0;
            display: grid;
            gap: 22px;
        }

        .hero {
            background: var(--panel);
            border-radius: 26px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            display: grid;
            grid-template-columns: 1.2fr 0.9fr;
            gap: 26px;
            padding: 28px 28px 32px;
            position: relative;
            overflow: hidden;
        }

        .hero::after {
            content: '';
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            right: -120px;
            top: -120px;
            background: rgba(106, 163, 168, 0.18);
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid rgba(160, 176, 185, 0.6);
            background: #ffffff;
            color: var(--muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .hero h1 {
            margin: 18px 0 14px;
            font-size: clamp(2rem, 3.4vw, 3.1rem);
            line-height: 1.1;
            letter-spacing: -0.03em;
        }

        .hero p {
            color: var(--muted);
            line-height: 1.7;
            margin: 0 0 20px;
        }

        .hero-slogan {
            margin: 0 0 18px;
            color: var(--text);
            font-weight: 600;
            line-height: 1.5;
        }

        .hero-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-primary {
            padding: 12px 22px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
        }

        .hero-illustration {
            align-self: center;
            background: linear-gradient(165deg, #dae4e9, #f6f9fb);
            border-radius: 22px;
            padding: 26px;
            border: 1px solid rgba(160, 176, 185, 0.45);
            display: grid;
            gap: 16px;
            position: relative;
            overflow: hidden;
        }

        .hero-illustration::before {
            content: '';
            position: absolute;
            inset: 16px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.45);
            border: 1px solid rgba(160, 176, 185, 0.25);
            pointer-events: none;
        }

        .hero-illustration img {
            width: 100%;
            height: 100%;
            min-height: 260px;
            border-radius: 18px;
            object-fit: cover;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(160, 176, 185, 0.35);
        }

        .people-row {
            display: flex;
            gap: 10px;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        .people-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #d7e3e8;
            border: 1px solid rgba(160, 176, 185, 0.4);
        }

        .glass-panel {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.75);
            border-radius: 16px;
            padding: 16px;
            border: 1px solid rgba(160, 176, 185, 0.35);
            display: grid;
            gap: 10px;
        }

        .glass-title {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .glass-line {
            height: 10px;
            border-radius: 999px;
            background: #e6edf2;
        }

        .glass-line.short {
            width: 70%;
        }

        .features {
            background: var(--panel-2);
            border-radius: 26px;
            border: 1px solid var(--border);
            padding: 18px;
            box-shadow: var(--shadow);
        }

        .features h2 {
            text-align: center;
            margin: 0 0 8px;
        }

        .features > p {
            text-align: center;
            color: var(--muted);
            margin: 0 0 28px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            align-items: stretch;
        }

        .feature-card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid rgba(160, 176, 185, 0.4);
            padding: 10px 12px;
            display: grid;
            grid-template-columns: 42px 1fr;
            gap: 6px;
            align-items: start;
            align-content: start;
            height: 100%;
            text-align: left;
        }

        .feature-icon {
            align-self: start;
        }

        .feature-text {
            width: 100%;
            max-width: 100%;
            display: grid;
            gap: 3px;
            align-content: start;
            text-align: left;
            justify-items: start;
        }

        .feature-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #edf2f5;
            border: 1px solid rgba(160, 176, 185, 0.4);
            display: grid;
            place-items: center;
            font-weight: 700;
            color: #2f5861;
            font-size: 0.85rem;
        }

        .feature-title {
            font-size: 1.05rem;
            font-weight: 600;
            margin: 0;
            line-height: 1.25;
            text-align: left;
            justify-self: start;
        }

        .feature-description {
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.5;
            margin: 0;
            max-width: 100%;
            text-align: left;
            justify-self: start;
        }

        .footer {
            text-align: center;
            color: var(--muted);
            margin-top: 30px;
            font-size: 0.9rem;
        }

        @media (max-width: 960px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .nav-links {
                display: none;
            }
        }

        @media (max-width: 640px) {
            .page {
                padding: 18px 14px 60px;
            }

            .hero,
            .features {
                padding: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="nav-shell">
            <header class="nav">
                <a href="<?php echo e(url('/')); ?>" class="brand">
                    <div class="brand-mark">
                        <img src="/assets/images/logo-eduhub.svg" alt="IMS logo">
                    </div>
                    <span>IMS</span>
                </a>
                <div class="nav-right">
                    <nav class="nav-links" aria-label="Top navigation">
                        <a href="<?php echo e(url('/')); ?>">Home</a>
                        <a href="#features">Features</a>
                    </nav>
                    <a href="<?php echo e(url('login')); ?>" class="login-btn">Login</a>
                </div>
            </header>
        </div>

        <div class="content">
            <section class="hero" id="about">
                <div>
                    <span class="hero-badge">IMS</span>
                    <h1>Institution Management System</h1>
                    <p class="hero-slogan">Empowering Education with Seamless Organization</p>
                    <div class="hero-actions">
                        <a href="<?php echo e(url('login')); ?>" class="btn-primary">Get Started</a>
                    </div>
                </div>
                <div class="hero-illustration">
                    <img src="/assets/images/illustrations/college-students.svg" alt="College students collaborating">
                </div>
            </section>

            <section class="features" id="features">
                <h2>Features</h2>
                <p>All-in-one tools for institutional administration.</p>
                <div class="features-grid">
                    <?php foreach ($features as $feature): ?>
                        <?php renderFeatureCard((array) $feature); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <footer class="footer" id="support">
            <p>IMS Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
