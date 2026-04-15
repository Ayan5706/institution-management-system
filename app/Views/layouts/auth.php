<?php
/** @var string|null $title */
/** @var string|null $content */
$title = $title ?? 'Log in to IMS';
$heroBadge = $heroBadge ?? 'IMS Final System';
$heroTitle = $heroTitle ?? 'Manage students, records, and operations from one place.';
$heroDescription = $heroDescription ?? 'A focused administration interface for programs, subjects, attendance, fees, and system control. Sign in to continue to the dashboard.';
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
            --bg-1: #95b5bb;
            --bg-2: #7aa6ad;
            --panel: rgba(255, 255, 255, 0.55);
            --panel-border: rgba(255, 255, 255, 0.28);
            --text: #1f2a37;
            --muted: #6c7b86;
            --accent: #2f7f87;
            --accent-2: #6aa3a8;
            --danger: #ef7a7a;
            --shadow: 0 26px 70px rgba(31, 42, 55, 0.26);
            --radius: 24px;
            --font: 'Poppins', 'Segoe UI', sans-serif;
            --field: rgba(255, 255, 255, 0.7);
            --field-border: rgba(255, 255, 255, 0.5);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            min-height: 100%;
            font-family: var(--font);
            color: var(--text);
            background: linear-gradient(145deg, #8fb6bb, #6f9fa6);
        }

        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.18), transparent 42%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.12), transparent 40%),
                url('/assets/images/backgrounds/eduhub-motif.svg') no-repeat 50% 60%;
            opacity: 0.55;
            pointer-events: none;
        }

        .auth-shell {
            width: min(480px, 100%);
            display: grid;
            gap: 18px;
            position: relative;
            z-index: 1;
        }

        .auth-card {
            padding: 36px 34px 30px;
            border: 1px solid var(--panel-border);
            border-radius: 26px;
            background: var(--panel);
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
            animation: floatIn 0.8s ease-out both;
        }

        .auth-card .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--muted);
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 14px;
        }

        .auth-card .back-link:hover {
            color: var(--text);
        }

        .brand {
            display: grid;
            place-items: center;
            gap: 10px;
            margin-bottom: 22px;
            text-align: center;
        }

        .brand-mark {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            font-weight: 700;
            color: #1f2a37;
            background: linear-gradient(135deg, #cfe3e6, #77aeb2);
            overflow: hidden;
            box-shadow: 0 14px 30px rgba(31, 42, 55, 0.2);
        }

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand h2 {
            margin: 0;
            font-size: 1.35rem;
        }

        .brand small {
            color: var(--muted);
            display: block;
            margin-top: 4px;
        }

        .flash {
            display: none;
            padding: 14px 16px;
            border-radius: 14px;
            margin: 18px 0;
            font-size: 0.95rem;
            line-height: 1.5;
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(160, 176, 185, 0.4);
        }

        .provider-row {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .provider-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            font-size: 0.78rem;
            font-weight: 600;
            color: #1f2a37;
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.45);
            box-shadow: 0 10px 20px rgba(31, 42, 55, 0.15);
        }

        .flash.success {
            display: block;
            border-color: rgba(47, 127, 135, 0.35);
            color: #2f5861;
        }

        .flash.error {
            display: block;
            border-color: rgba(239, 122, 122, 0.4);
            color: #9c2b2b;
        }

        @keyframes floatIn {
            from {
                opacity: 0;
                transform: translateY(24px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 12px;
            }

            .auth-card {
                padding: 28px 22px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <section class="auth-card">
            <a class="back-link" href="<?php echo e(url('/')); ?>">← Back to Home</a>
            <div class="brand">
                <div class="brand-mark">
                    <img src="/assets/images/logo-eduhub.svg" alt="IMS logo">
                </div>
                <div>
                    <h2><?php echo e($title); ?></h2>
                    <small>Use your login ID to continue</small>
                </div>
            </div>

            <?php if (!empty($error ?? '')): ?>
                <div class="flash error" id="authFlash"><?php echo e((string) $error); ?></div>
            <?php elseif (!empty($message ?? '')): ?>
                <div class="flash success" id="authFlash"><?php echo e((string) $message); ?></div>
            <?php else: ?>
                <div class="flash" id="authFlash"></div>
            <?php endif; ?>

            <?php echo $content ?? ''; ?>
        </section>

    </div>
</body>
</html>
