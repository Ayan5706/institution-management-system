<?php
$activeNav = 'reports';
$pageSubtitle = 'Academic structure summary across programs, semesters, subjects, and users.';
$filters = [
    'program' => (string) ($_GET['program'] ?? 'all'),
    'semester' => (string) ($_GET['semester'] ?? 'all'),
    'year' => (string) ($_GET['year'] ?? date('Y')),
];
$academicSummary = $academicSummary ?? [
    'users' => 148,
    'programs' => 6,
    'semesters' => 12,
    'subjects' => 42,
];

$programBreakdown = $programBreakdown ?? [
    ['program' => 'BSIT', 'students' => 420, 'subjects' => 14],
    ['program' => 'BSBA', 'students' => 280, 'subjects' => 10],
    ['program' => 'BSED', 'students' => 350, 'subjects' => 9],
    ['program' => 'BSCRIM', 'students' => 234, 'subjects' => 9],
];

$highlights = $highlights ?? [
    ['title' => 'User Accounts', 'detail' => 'Administrators, teachers, and student records are grouped under one identity layer.'],
    ['title' => 'Programs', 'detail' => 'Degree tracks define the academic structure for related semesters and subjects.'],
    ['title' => 'Timetables', 'detail' => 'Class schedules connect teacher assignments with active days and time slots.'],
    ['title' => 'Fees & Profiles', 'detail' => 'Student fee records and profiles support student lifecycle reporting.'],
];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Academic Summary</h2>
            <div style="color:#64748b;">High-level school structure and record counts.</div>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a class="btn btn-ghost" href="<?php echo e(url('reports')); ?>">Back to Reports</a>
            <a class="btn btn-ghost" href="<?php echo e(url('reports/academic') . '?export=csv'); ?>">Export CSV</a>
            <a class="btn btn-primary" href="<?php echo e(url('reports/academic') . '?export=pdf'); ?>">Export PDF</a>
        </div>
    </div>

    <form method="get" action="<?php echo e(url('reports/academic')); ?>" class="card" style="padding:16px; margin-bottom:18px; border-radius:16px;">
        <style>
            .filter-grid { display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
            .filter-grid label { display:block; font-size:0.85rem; color:#64748b; margin-bottom:6px; }
            .filter-grid select, .filter-grid input {
                width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px; background:#fff;
            }
            @media (max-width: 960px) { .filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
            @media (max-width: 640px) { .filter-grid { grid-template-columns: 1fr; } }
        </style>
        <div class="filter-grid">
            <div>
                <label for="program">Program</label>
                <select id="program" name="program">
                    <option value="all" <?php echo $filters['program'] === 'all' ? 'selected' : ''; ?>>All Programs</option>
                    <option value="BSIT" <?php echo $filters['program'] === 'BSIT' ? 'selected' : ''; ?>>BSIT</option>
                    <option value="BSBA" <?php echo $filters['program'] === 'BSBA' ? 'selected' : ''; ?>>BSBA</option>
                    <option value="BSED" <?php echo $filters['program'] === 'BSED' ? 'selected' : ''; ?>>BSED</option>
                    <option value="BSCRIM" <?php echo $filters['program'] === 'BSCRIM' ? 'selected' : ''; ?>>BSCRIM</option>
                </select>
            </div>
            <div>
                <label for="semester">Semester</label>
                <select id="semester" name="semester">
                    <option value="all" <?php echo $filters['semester'] === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="1" <?php echo $filters['semester'] === '1' ? 'selected' : ''; ?>>1st Semester</option>
                    <option value="2" <?php echo $filters['semester'] === '2' ? 'selected' : ''; ?>>2nd Semester</option>
                </select>
            </div>
            <div>
                <label for="year">Academic Year</label>
                <input id="year" name="year" value="<?php echo e($filters['year']); ?>" placeholder="2026">
            </div>
            <div style="display:flex; align-items:flex-end; gap:10px;">
                <button class="btn btn-primary" type="submit">Apply Filters</button>
                <a class="btn btn-ghost" href="<?php echo e(url('reports/academic')); ?>">Reset</a>
            </div>
        </div>
    </form>

    <style>
        .kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 20px; }
        .kpi { padding: 18px; border-radius: 18px; background: linear-gradient(180deg, #fff, #f8fbff); border: 1px solid #e2e8f0; }
        .label { color: #64748b; font-size: 0.9rem; margin-bottom: 10px; }
        .value { font-size: 1.8rem; font-weight: 800; letter-spacing: -0.04em; }
        .grid { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 18px; }
        .panel { padding: 20px; border-radius: 18px; background: #fff; border: 1px solid #e2e8f0; }
        .highlight { padding: 16px; border-radius: 14px; background: #f8fbff; border: 1px solid #e2e8f0; }
        .highlight h3 { margin: 0 0 6px; font-size: 1rem; }
        .highlight p { margin: 0; color: #64748b; line-height: 1.6; }
        .table-wrap { overflow-x:auto; }
        table { width:100%; border-collapse: collapse; min-width: 620px; }
        th, td { padding: 12px 10px; border-bottom: 1px solid #e2e8f0; text-align:left; }
        th { color:#475569; font-size:0.86rem; text-transform:uppercase; letter-spacing:0.04em; }
        .bar { height: 8px; background:#e2e8f0; border-radius:999px; overflow:hidden; margin-top: 6px; }
        .bar > span { display:block; height:100%; background: linear-gradient(135deg, #2563eb, #14b8a6); }
        @media (max-width: 1100px) { .kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } .grid { grid-template-columns: 1fr; } }
        @media (max-width: 720px) { .kpis { grid-template-columns: 1fr; } }
    </style>

    <div class="kpis">
        <div class="kpi"><div class="label">Users</div><div class="value"><?php echo e((string) $academicSummary['users']); ?></div></div>
        <div class="kpi"><div class="label">Programs</div><div class="value"><?php echo e((string) $academicSummary['programs']); ?></div></div>
        <div class="kpi"><div class="label">Semesters</div><div class="value"><?php echo e((string) $academicSummary['semesters']); ?></div></div>
        <div class="kpi"><div class="label">Subjects</div><div class="value"><?php echo e((string) $academicSummary['subjects']); ?></div></div>
    </div>

    <div class="grid">
        <section class="panel">
            <h3 style="margin:0 0 16px;">Program Breakdown</h3>
            <div class="table-wrap" style="margin-bottom:16px;">
                <table>
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Students</th>
                            <th>Subjects</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $maxStudents = max(array_column($programBreakdown, 'students')); ?>
                        <?php foreach ($programBreakdown as $row): ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($row['program']); ?></strong>
                                    <div class="bar"><span style="width: <?php echo e((string) max(5, (int) (($row['students'] / $maxStudents) * 100))); ?>%"></span></div>
                                </td>
                                <td><?php echo e((string) $row['students']); ?></td>
                                <td><?php echo e((string) $row['subjects']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <h3 style="margin:0 0 16px;">Structure Highlights</h3>
            <div style="display:grid; gap:12px;">
                <?php foreach ($highlights as $highlight): ?>
                    <div class="highlight">
                        <h3><?php echo e($highlight['title']); ?></h3>
                        <p><?php echo e($highlight['detail']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <aside class="panel">
            <h3 style="margin:0 0 16px;">Summary Notes</h3>
            <p style="margin:0; color:#64748b; line-height:1.7;">
                This academic report is ready to consume live aggregates from the users, programs,
                semesters, and subjects tables once the corresponding dashboard queries are added.
            </p>
            <div style="margin-top:18px; display:grid; gap:10px; color:#0f172a;">
                <div><strong>Primary scope:</strong> academic records</div>
                <div><strong>Future filters:</strong> program and semester</div>
                <div><strong>Exports:</strong> PDF and CSV</div>
            </div>
        </aside>
    </div>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
