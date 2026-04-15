    <style>
    </div>

    <style>
        .dashboard-grid {
            color: #1f2a37;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .dashboard-column {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .section-title {
            margin: 0 0 14px;
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        .schedule-card {
            padding: 16px;
            border-radius: 14px;
            background: #ffffff;
            border: 1px solid #d6e0e6;
            display: flex;
            flex-direction: column;
            transition: all 0.2s;
        }

        .schedule-card:hover {
            border-color: rgba(47, 127, 135, 0.4);
            box-shadow: 0 8px 18px rgba(31, 42, 55, 0.12);
        }

        .schedule-card.disabled {
            opacity: 0.65;
            background: #f4f7f9;
        }

        .schedule-card.disabled:hover {
            border-color: #d6e0e6;
            box-shadow: none;
        }

        .subject-code {
            font-size: 0.8rem;
            color: #6c7b86;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .subject-name {
            font-weight: 700;
            color: #1f2a37;
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .class-info {
            font-size: 0.85rem;
            color: #6c7b86;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .time-slot {
            font-size: 0.9rem;
            color: #2f7f87;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .action-button {
            padding: 10px 14px;
            border-radius: 10px;
            border: 0;
            cursor: pointer;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
            transition: all 0.2s;
            margin-top: auto;
        }

        .btn-mark {
            background: linear-gradient(135deg, #2f7f87, #6aa3a8);
            color: #fff;
        }

        .btn-mark:hover:not(:disabled) {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .btn-mark:disabled {
            background: #cbd5cf;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: #6c7b86;
            background: #f4f7f9;
            border-radius: 14px;
            border: 1px solid #d6e0e6;
        }

        .empty-state-icon {
            font-size: 2.5rem;
            margin-bottom: 12px;
        }

        .subjects-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
        }

        .subject-item {
            padding: 12px;
            border-radius: 10px;
            background: #f4f7f9;
            border: 1px solid #d6e0e6;
            font-size: 0.85rem;
        }

        .subject-item-code {
            font-weight: 700;
            color: #2f7f87;
            margin-bottom: 4px;
        }

        .subject-item-name {
            color: #1f2a37;
            margin-bottom: 4px;
            line-height: 1.3;
            font-size: 0.9rem;
        }

        .subject-item-meta {
            font-size: 0.75rem;
            color: #6c7b86;
        }

        .card-section {
            margin-bottom: 24px;
        }

        @media (max-width: 1200px) {
            .schedule-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            }
        }

        @media (max-width: 640px) {
            .schedule-grid {
                grid-template-columns: 1fr;
            }
            .subjects-list {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <!-- Stats Section -->
    <div class="dashboard-layout">
        <!-- Schedule Section -->
        <div class="dashboard-column">
            <div class="card-section">
                <h3 class="section-title">Today's Schedule</h3>
                <?php if (!empty($assignments)): ?>
                    <div class="schedule-grid">
                        <?php foreach ($assignments as $slot): ?>
                            <div class="schedule-card <?php echo !$slot['is_enabled'] ? 'disabled' : ''; ?>">
                                <div class="subject-code"><?php echo e($slot['subject_code']); ?></div>
                                <div class="subject-name"><?php echo e($slot['subject_name']); ?></div>
                                <div class="class-info">
                                    <?php echo e($slot['academic_year']); ?><br>
                                    Semester <?php echo e($slot['semester_number']); ?>
                                </div>
                                <div class="time-slot">
                                    <?php echo e(date('H:i A', strtotime($slot['start_time']))); ?> - 
                                    <?php echo e(date('H:i A', strtotime($slot['end_time']))); ?>
                                </div>
                                <a href="<?php echo e(url("teacher/attendance/mark/{$slot['id']}")); ?>" 
                                   class="action-button btn-mark"
                                   <?php echo !$slot['is_enabled'] ? 'style="pointer-events: none;" title="Attendance window is closed"' : ''; ?>>
                                    Mark Attendance
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📅</div>
                        <p><strong>No classes scheduled for today</strong></p>
                        <p>Your timetable shows no sessions on this day.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Subjects Section -->
        <div class="dashboard-column">
            <div class="card-section">
                <h3 class="section-title">My Subjects</h3>
                <?php if (!empty($all_subjects)): ?>
                    <div class="subjects-list">
                        <?php foreach ($all_subjects as $subject): ?>
                            <div class="subject-item">
                                <div class="subject-item-code"><?php echo e($subject['subject_code']); ?></div>
                                <div class="subject-item-name"><?php echo e($subject['subject_name']); ?></div>
                                <div class="subject-item-meta">
                                    <?php echo e($subject['academic_year']); ?> | 
                                    S<?php echo e($subject['semester_number']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📚</div>
                        <p><strong>No subjects assigned yet</strong></p>
                        <p>You don't have any subject assignments.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
