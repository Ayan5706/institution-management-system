<?php
/** @var array $timetable */
/** @var array|null $program */
/** @var array|null $current_semester */
/** @var bool $profile_not_found */
$activeNav = 'timetable';
$timetable = $timetable ?? [];
$program = $program ?? null;
$current_semester = $current_semester ?? null;
$profile_not_found = $profile_not_found ?? false;
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Timetable</h2>
            <div style="color:#64748b;">Your class schedule</div>
        </div>
        <a href="<?php echo e(url('student/dashboard')); ?>" class="btn btn-ghost">Back to Dashboard</a>
    </div>

    <style>
        .view-toggle {
            display: inline-flex;
            gap: 8px;
            padding: 6px;
            background: #f1f5f9;
            border-radius: 999px;
            margin-bottom: 16px;
        }

        .view-toggle button {
            border: 0;
            background: transparent;
            padding: 8px 14px;
            border-radius: 999px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
        }

        .view-toggle button.active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.1);
        }

        .daily-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .daily-controls label {
            font-weight: 600;
            color: #334155;
        }

        .daily-controls select {
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .timetable-view {
            display: none;
        }

        .timetable-view.active {
            display: block;
        }

        .semester-info {
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            color: #0f172a;
        }

        .timetable-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
        }

        .timetable-table th {
            background: #f8fafc;
            padding: 12px;
            text-align: left;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .timetable-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #0f172a;
        }

        .timetable-table tr:hover {
            background: #f8fafc;
        }

        .subject-code {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 4px;
        }

        .daily-list {
            display: grid;
            gap: 12px;
        }

        .daily-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            background: #ffffff;
        }

        .daily-card-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 8px;
        }

        .daily-time {
            font-weight: 700;
            color: #0f172a;
        }

        .daily-subject {
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
        }

        .daily-meta {
            color: #64748b;
            font-size: 0.9rem;
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
            .timetable-table th,
            .timetable-table td {
                padding: 10px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 640px) {
            .timetable-table th,
            .timetable-table td {
                padding: 8px;
                font-size: 0.85rem;
            }
        }
    </style>

    <!-- Profile Not Found Warning -->
    <?php if ($profile_not_found): ?>
        <div class="notice-warning">
            <strong>⚠️ Profile Setup Needed</strong><br>
            Your student profile hasn't been set up yet. Please contact your institution to complete your enrollment. Your timetable will appear here once your profile is created.
        </div>
    <?php endif; ?>

    <!-- Program & Semester Info -->
    <div class="semester-info">
        <?php if ($program): ?>
            <strong>Program:</strong> <?php echo e($program['program_name'] ?? 'N/A'); ?>
        <?php endif; ?>
        <?php if ($current_semester): ?>
            <span style="margin-left: 20px;"><strong>Current Semester:</strong> Semester <?php echo e($current_semester['semester_number']); ?></span>
        <?php else: ?>
            <span style="margin-left: 20px;"><strong>Current Semester:</strong> N/A</span>
        <?php endif; ?>
    </div>

    <?php if (!empty($timetable)): ?>
        <div class="view-toggle" role="tablist" aria-label="Timetable view">
            <button type="button" class="active" data-view="weekly" aria-pressed="true">Weekly view</button>
            <button type="button" data-view="daily" aria-pressed="false">Daily view</button>
        </div>

        <div id="weeklyView" class="timetable-view active">
            <div class="daily-controls" style="margin-bottom: 10px;">
                <label for="weeklyDaySelect">Day</label>
                <select id="weeklyDaySelect"></select>
            </div>
            <div style="overflow-x: auto;">
                <table class="timetable-table">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Subject</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Teacher</th>
                        </tr>
                    </thead>
                    <tbody id="weeklyTableBody">
                        <?php foreach ($timetable as $slot): ?>
                            <tr data-day-code="<?php echo e($slot['day_code'] ?? ''); ?>">
                                <td><?php echo e($slot['day'] ?? ''); ?></td>
                                <td>
                                    <div><?php echo e($slot['subject_name'] ?? ''); ?></div>
                                    <div class="subject-code"><?php echo e($slot['subject_code'] ?? ''); ?></div>
                                </td>
                                <td><?php echo e($slot['start_time'] ?? ''); ?></td>
                                <td><?php echo e($slot['end_time'] ?? ''); ?></td>
                                <td><?php echo e($slot['teacher_name'] ?? 'TBA'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div id="weeklyEmpty" class="empty-state" style="display:none;">
                <div class="empty-state-icon">📚</div>
                <p>No classes scheduled for this day</p>
                <p style="font-size: 0.9rem; margin-top: 8px;">Pick another day to view your schedule.</p>
            </div>
        </div>

        <div id="dailyView" class="timetable-view">
            <div class="daily-controls">
                <label for="daySelect">Day</label>
                <select id="daySelect"></select>
            </div>
            <div id="dailyList" class="daily-list"></div>
            <div id="dailyEmpty" class="empty-state" style="display:none;">
                <div class="empty-state-icon">📚</div>
                <p>No classes scheduled for this day</p>
                <p style="font-size: 0.9rem; margin-top: 8px;">Pick another day to view your schedule.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📚</div>
            <p>No classes scheduled</p>
            <p style="font-size: 0.9rem; margin-top: 8px;">Your timetable will appear here once it's published.</p>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($timetable)): ?>
<script>
    (function () {
        const timetableData = <?php echo json_encode($timetable, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?> || [];
        const viewButtons = document.querySelectorAll('.view-toggle button');
        const weeklyView = document.getElementById('weeklyView');
        const dailyView = document.getElementById('dailyView');
        const daySelect = document.getElementById('daySelect');
        const weeklyDaySelect = document.getElementById('weeklyDaySelect');
        const weeklyTableBody = document.getElementById('weeklyTableBody');
        const weeklyEmpty = document.getElementById('weeklyEmpty');
        const dailyList = document.getElementById('dailyList');
        const dailyEmpty = document.getElementById('dailyEmpty');

        const dayOrder = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
        const dayLabelMap = {
            MON: 'Monday',
            TUE: 'Tuesday',
            WED: 'Wednesday',
            THU: 'Thursday',
            FRI: 'Friday',
            SAT: 'Saturday'
        };

        const uniqueDays = [];
        dayOrder.forEach((code) => {
            if (timetableData.some((slot) => (slot.day_code || '').toUpperCase() === code)) {
                uniqueDays.push(code);
            }
        });

        if (uniqueDays.length === 0) {
            Object.keys(dayLabelMap).forEach((code) => uniqueDays.push(code));
        }

        uniqueDays.forEach((code) => {
            const option = document.createElement('option');
            option.value = code;
            option.textContent = dayLabelMap[code] || code;
            daySelect.appendChild(option);
        });

        uniqueDays.forEach((code) => {
            const option = document.createElement('option');
            option.value = code;
            option.textContent = dayLabelMap[code] || code;
            weeklyDaySelect.appendChild(option);
        });

        function formatTime(value) {
            if (!value) {
                return '';
            }
            const [h, m] = value.split(':');
            if (h === undefined || m === undefined) {
                return value;
            }
            const hour = parseInt(h, 10);
            if (Number.isNaN(hour)) {
                return value;
            }
            const suffix = hour >= 12 ? 'PM' : 'AM';
            const displayHour = ((hour + 11) % 12) + 1;
            return displayHour + ':' + m + ' ' + suffix;
        }

        function renderDaily(dayCode) {
            const slots = timetableData.filter((slot) => (slot.day_code || '').toUpperCase() === dayCode);
            dailyList.innerHTML = '';

            if (slots.length === 0) {
                dailyEmpty.style.display = 'block';
                return;
            }

            dailyEmpty.style.display = 'none';

            slots.forEach((slot) => {
                const card = document.createElement('div');
                card.className = 'daily-card';

                const header = document.createElement('div');
                header.className = 'daily-card-header';

                const time = document.createElement('div');
                time.className = 'daily-time';
                time.textContent = formatTime(slot.start_time) + ' - ' + formatTime(slot.end_time);

                const teacher = document.createElement('div');
                teacher.className = 'daily-meta';
                teacher.textContent = slot.teacher_name || 'TBA';

                const subject = document.createElement('div');
                subject.className = 'daily-subject';
                subject.textContent = slot.subject_name || '';

                const meta = document.createElement('div');
                meta.className = 'daily-meta';
                meta.textContent = slot.subject_code || '';

                header.appendChild(time);
                header.appendChild(teacher);
                card.appendChild(header);
                card.appendChild(subject);
                card.appendChild(meta);
                dailyList.appendChild(card);
            });
        }

        function renderWeekly(dayCode) {
            const rows = weeklyTableBody ? Array.from(weeklyTableBody.querySelectorAll('tr')) : [];
            let visibleCount = 0;

            rows.forEach((row) => {
                const rowDay = (row.getAttribute('data-day-code') || '').toUpperCase();
                const isVisible = rowDay === dayCode;
                row.style.display = isVisible ? '' : 'none';
                if (isVisible) {
                    visibleCount += 1;
                }
            });

            if (weeklyEmpty) {
                weeklyEmpty.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        function setActiveView(view) {
            viewButtons.forEach((button) => {
                const isActive = button.getAttribute('data-view') === view;
                button.classList.toggle('active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            weeklyView.classList.toggle('active', view === 'weekly');
            dailyView.classList.toggle('active', view === 'daily');
        }

        viewButtons.forEach((button) => {
            button.addEventListener('click', () => {
                setActiveView(button.getAttribute('data-view'));
            });
        });

        const todayIndex = new Date().getDay();
        const jsDayCode = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'][todayIndex] || 'MON';
        const defaultDay = uniqueDays.includes(jsDayCode) ? jsDayCode : uniqueDays[0];
        daySelect.value = defaultDay;
        weeklyDaySelect.value = defaultDay;
        renderDaily(defaultDay);
        renderWeekly(defaultDay);

        daySelect.addEventListener('change', (event) => {
            renderDaily(event.target.value);
        });

        weeklyDaySelect.addEventListener('change', (event) => {
            renderWeekly(event.target.value);
        });
    })();
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
