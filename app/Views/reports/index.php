<?php
$activeNav = 'reports';
$pageSubtitle = 'Browse available reports for the school management system.';
$reportMeta = [
    'last_generated' => '2026-04-11 22:30',
    'timezone' => 'Asia/Manila',
];
$reportCards = [
    [
        'title' => 'Academic Summary',
        'description' => 'System summary for users, programs, semesters, and subjects.',
        'link' => url('reports/academic'),
        'tone' => 'blue',
    ],
];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Reports Center</h2>
            <div style="color:#64748b;">Choose a report to review current system information.</div>
        </div>
        <div>
            <a class="btn btn-ghost" href="<?php echo e(url('reports/academic')); ?>">Generate Now</a>
        </div>
    </div>

    <style>
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .meta {
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            color: #64748b;
            font-size: 0.92rem;
        }

        .report-card {
            padding: 22px;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            border: 1px solid #e2e8f0;
            display: grid;
            gap: 14px;
        }

        .report-card h3 { margin: 0; font-size: 1.1rem; }
        .report-card p { margin: 0; color: #64748b; line-height: 1.6; }
        .report-link {
            display: inline-flex;
            width: fit-content;
            text-decoration: none;
            color: #fff;
            font-weight: 700;
            padding: 11px 14px;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb, #14b8a6);
        }

        .tone-blue { border-top: 4px solid #2563eb; }
        .tone-emerald { border-top: 4px solid #10b981; }
        .tone-violet { border-top: 4px solid #8b5cf6; }

        @media (max-width: 960px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>

    <div class="meta">
        <div><strong>Last Generated:</strong> <?php echo e($reportMeta['last_generated']); ?></div>
        <div><strong>Timezone:</strong> <?php echo e($reportMeta['timezone']); ?></div>
    </div>

    <div class="grid">
        <?php foreach ($reportCards as $card): ?>
            <section class="report-card tone-<?php echo e($card['tone']); ?>">
                <h3><?php echo e($card['title']); ?></h3>
                <p><?php echo e($card['description']); ?></p>
                <a class="report-link" href="<?php echo e($card['link']); ?>">Open Report</a>
            </section>
        <?php endforeach; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
