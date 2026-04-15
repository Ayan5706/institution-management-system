<?php
/** @var array $attendance_summary */
/** @var bool $profile_not_found */
$activeNav = 'attendance';
$attendance_summary = $attendance_summary ?? [];
$profile_not_found = $profile_not_found ?? false;
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Attendance</h2>
            <div style="color:#64748b;">Your attendance record by subject</div>
        </div>
        <a href="<?php echo e(url('student/dashboard')); ?>" class="btn btn-ghost">Back to Dashboard</a>
    </div>

    <div class="table-view-header" style="margin-top: 6px;">
        <div class="filter-bar table-view-controls" style="margin: 10px 0 18px;">
            <input type="text" id="attendanceSearch" class="filter-input table-view-field" placeholder="Search subject or code..." style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
        </div>
        <div class="table-view-meta" id="attendanceMeta"></div>
    </div>

    <!-- Profile Not Found Warning -->
    <?php if ($profile_not_found): ?>
        <div class="notice-warning">
            <strong>⚠️ Profile Setup Needed</strong><br>
            Your student profile hasn't been set up yet. Please contact your institution to complete your enrollment. Your attendance records will appear here once your profile is created.
        </div>
    <?php endif; ?>

    <style>
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .attendance-table thead {
            background: #f1f5f9;
            border-bottom: 2px solid #dbe4f0;
        }

        .attendance-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #0f172a;
            font-size: 0.9rem;
        }

        .attendance-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .attendance-table tbody tr:hover {
            background: #f8fbff;
        }

        .subject-cell {
            color: #0f172a;
            font-weight: 600;
        }

        .subject-code {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 2px;
        }

        .count-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
            min-width: 40px;
        }

        .count-total {
            background: #e0e7ff;
            color: #0d47a1;
        }

        .count-present {
            background: #d1fae5;
            color: #065f46;
        }

        .count-absent {
            background: #fee2e2;
            color: #7f1d1d;
        }

        .percent-text {
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            background: #f1f5f9;
            display: inline-block;
        }

        .percent-great {
            background: #d1fae5;
            color: #065f46;
        }

        .percent-good {
            background: #dbeafe;
            color: #0c4a6e;
        }

        .percent-warning {
            background: #fed7aa;
            color: #92400e;
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

        @media (max-width: 960px) {
            .attendance-table {
                font-size: 0.85rem;
            }

            .attendance-table th,
            .attendance-table td {
                padding: 8px;
            }
        }

        @media (max-width: 640px) {
            .attendance-table {
                font-size: 0.75rem;
            }

            .attendance-table th,
            .attendance-table td {
                padding: 6px;
            }
        }
    </style>

    <?php if (!empty($attendance_summary)): ?>
        <div style="overflow-x: auto;">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th style="text-align: center;">Total Sessions</th>
                        <th style="text-align: center;">Present</th>
                        <th style="text-align: center;">Absent</th>
                        <th style="text-align: center;">Attendance %</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody">
                    <?php foreach ($attendance_summary as $record): ?>
                        <tr data-subject="<?php echo e($record['subject_name']); ?>"
                            data-code="<?php echo e($record['subject_code']); ?>"
                            data-search="<?php echo e(trim(($record['subject_name'] ?? '') . ' ' . ($record['subject_code'] ?? ''))); ?>">
                            <td>
                                <div class="subject-cell"><?php echo e($record['subject_name']); ?></div>
                                <div class="subject-code"><?php echo e($record['subject_code']); ?></div>
                            </td>
                            <td style="text-align: center;">
                                <span class="count-badge count-total">
                                    <?php echo e($record['total_sessions']); ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="count-badge count-present">
                                    <?php echo e($record['present_count']); ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="count-badge count-absent">
                                    <?php echo e($record['absent_count']); ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="percent-text <?php
                                    $p = $record['attendance_percent'];
                                    echo $p >= 80 ? 'percent-great' : ($p >= 70 ? 'percent-good' : 'percent-warning');
                                ?>">
                                    <?php echo e($record['attendance_percent']); ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-view-pagination" id="attendancePager" style="margin-top: 14px;">
            <div class="pagination-info" id="attendancePageInfo"></div>
            <div class="pagination-actions">
                <button type="button" class="btn btn-ghost" id="attendancePrev">Previous</button>
                <button type="button" class="btn btn-ghost" id="attendanceNext">Next</button>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📊</div>
            <p>No attendance records</p>
            <p style="font-size: 0.9rem; margin-top: 8px;">Attendance records will appear here once they're marked.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    window.IMS = window.IMS || {};
    window.IMS.initTableView({
        tbodyId: 'attendanceTableBody',
        searchInputId: 'attendanceSearch',
        metaId: 'attendanceMeta',
        pagerId: 'attendancePager',
        pageInfoId: 'attendancePageInfo',
        prevId: 'attendancePrev',
        nextId: 'attendanceNext',
        pageSize: 10,
        noResultsColSpan: 5
    });
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
