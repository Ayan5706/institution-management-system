<?php
/** @var array $timetable */
/** @var array|null $current_semester */
/** @var bool $profile_not_found */
$activeNav = 'timetable';
$timetable = $timetable ?? [];
$current_semester = $current_semester ?? null;
$profile_not_found = $profile_not_found ?? false;
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Timetable</h2>
            <div style="color:#64748b;">Your weekly class schedule</div>
        </div>
        <a href="<?php echo e(url('student/dashboard')); ?>" class="btn btn-ghost">Back to Dashboard</a>
    </div>

    <div style="display:flex; gap:12px; margin: 10px 0 18px; flex-wrap: wrap;">
        <input type="text" id="timetableSearch" class="filter-input" placeholder="Search subject..." style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
        <select id="timetableDay" class="filter-select" style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
            <option value="">All Days</option>
            <?php foreach (array_keys($timetable) as $day): ?>
                <option value="<?php echo e(strtolower($day)); ?>"><?php echo e($day); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Profile Not Found Warning -->
    <?php if ($profile_not_found): ?>
        <div class="notice-warning">
            <strong>⚠️ Profile Setup Needed</strong><br>
            Your student profile hasn't been set up yet. Please contact your institution to complete your enrollment. Your timetable will appear here once your profile is created.
        </div>
    <?php endif; ?>

    <style>
        .semester-info {
            padding: 12px 16px;
            background: #eff6ff;
            border-left: 4px solid #2563eb;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            color: #0f172a;
        }

        .timetable-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .day-column {
            background: #f8fafc;
            border: 1px solid #dbe4f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .day-header {
            background: linear-gradient(135deg, #2563eb, #0d9488);
            color: #fff;
            padding: 12px;
            font-weight: 700;
            text-align: center;
        }

        .day-slots {
            padding: 12px;
            display: grid;
            gap: 8px;
        }

        .slot {
            padding: 12px;
            background: #fff;
            border-left: 4px solid #2563eb;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .slot-subject {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .slot-code {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 6px;
        }

        .slot-time {
            font-size: 0.85rem;
            color: #2563eb;
            font-weight: 600;
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: #64748b;
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 12px;
        }

        @media (max-width: 768px) {
            .timetable-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <?php if ($current_semester): ?>
        <div class="semester-info">
            <strong>Semester:</strong> <?php echo e($current_semester['academic_year']); ?> - Semester <?php echo e($current_semester['semester_number']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($timetable) && array_filter($timetable)): ?>
        <div class="timetable-grid">
            <?php foreach ($timetable as $day => $slots): ?>
                <?php if (!empty($slots)): ?>
                    <div class="day-column" data-day="<?php echo e(strtolower($day)); ?>">
                        <div class="day-header"><?php echo e($day); ?></div>
                        <div class="day-slots">
                            <?php foreach ($slots as $slot): ?>
                                <div class="slot" data-subject="<?php echo e($slot['subject_name']); ?>" data-code="<?php echo e($slot['subject_code']); ?>">
                                    <div class="slot-code"><?php echo e($slot['subject_code']); ?></div>
                                    <div class="slot-subject"><?php echo e($slot['subject_name']); ?></div>
                                    <div class="slot-time"><?php echo e($slot['start_time']); ?> - <?php echo e($slot['end_time']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📅</div>
            <p>No classes scheduled</p>
            <p style="font-size: 0.9rem; margin-top: 8px;">Your timetable will appear here once it's published.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    function filterTimetable() {
        const search = (document.getElementById('timetableSearch')?.value || '').toLowerCase();
        const dayFilter = document.getElementById('timetableDay')?.value || '';

        document.querySelectorAll('.day-column').forEach(column => {
            const matchesDay = dayFilter === '' || column.dataset.day === dayFilter;
            let visibleSlots = 0;

            column.querySelectorAll('.slot').forEach(slot => {
                const text = `${slot.dataset.subject} ${slot.dataset.code}`.toLowerCase();
                const matchesSearch = search === '' || text.includes(search);
                const showSlot = matchesSearch && matchesDay;
                slot.style.display = showSlot ? '' : 'none';
                if (showSlot) {
                    visibleSlots += 1;
                }
            });

            column.style.display = matchesDay && (search === '' || visibleSlots > 0) ? '' : 'none';
        });
    }

    document.getElementById('timetableSearch')?.addEventListener('input', filterTimetable);
    document.getElementById('timetableDay')?.addEventListener('change', filterTimetable);
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
