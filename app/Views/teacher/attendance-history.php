<?php
/** @var array $subjects */
/** @var array $attendance_records */
/** @var array $filters */
$subjects = $subjects ?? [];
$attendance_records = $attendance_records ?? [];
$filters = $filters ?? ['subject_id' => 0, 'from_date' => '', 'to_date' => ''];
$activeNav = 'teacher/attendance/history';
?>
<?php ob_start(); ?>
<style>
    .filter-section {
        background: #f4f7f9;
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        border: 1px solid #d6e0e6;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        margin-bottom: 12px;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .filter-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #0f172a;
    }

    .filter-input,
    .filter-select {
        padding: 8px 10px;
        border-radius: 10px;
        font-size: 0.9rem;
        font-family: inherit;
    }

    .filter-buttons {
        display: flex;
        gap: 8px;
    }

    .filter-buttons .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 14px;
        border-radius: 10px;
        font-weight: 600;
        min-height: 38px;
    }

    .filter-buttons .btn-ghost {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #0f172a;
    }

    .filter-buttons .btn-ghost:hover {
        background: #f8fafc;
    }

    .date-cell {
        font-weight: 600;
        color: #0f172a;
    }

    .subject-cell {
        color: #0f172a;
    }

    .subject-meta {
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

    .count-late {
        background: #ffedd5;
        color: #9a3412;
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
        .filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }
        .filter-buttons {
            flex-direction: column;
        }
        .filter-buttons .btn {
            width: 100%;
        }
    }
</style>

<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Attendance History</h2>
            <div style="color:#64748b;">View and filter past attendance records</div>
        </div>
        <a href="<?php echo e(url('teacher/dashboard')); ?>" class="btn btn-ghost">
            Back to Dashboard
        </a>
    </div>

    <div class="table-view-header" style="margin-top: 6px;">
        <div class="filter-bar table-view-controls" style="margin: 10px 0 18px;">
            <input type="text" id="attendanceHistorySearch" class="filter-input table-view-field" placeholder="Search date, subject, or class...">
        </div>
        <div class="table-view-meta" id="attendanceHistoryMeta"></div>
    </div>

    <div class="filter-section">
        <form method="GET" action="<?php echo e(url('teacher/attendance/history')); ?>">
            <div class="filter-grid">
                <div class="filter-group">
                    <label class="filter-label">Subject</label>
                    <select name="subject_id" class="filter-select">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo e($subject['id']); ?>"
                                    <?php echo (string) $filters['subject_id'] === (string) $subject['id'] ? 'selected' : ''; ?>>
                                <?php echo e($subject['subject_code']); ?> - <?php echo e($subject['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">From Date</label>
                    <input type="date" name="from_date" class="filter-input"
                           value="<?php echo e($filters['from_date']); ?>">
                </div>

                <div class="filter-group">
                    <label class="filter-label">To Date</label>
                    <input type="date" name="to_date" class="filter-input"
                           value="<?php echo e($filters['to_date']); ?>">
                </div>

                <div class="filter-group">
                    <label class="filter-label">&nbsp;</label>
                    <div class="filter-buttons">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="<?php echo e(url('teacher/attendance/history')); ?>" class="btn btn-ghost" style="text-align: center;">
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php if (!empty($attendance_records)): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Class</th>
                        <th style="text-align: center;">Total Students</th>
                        <th style="text-align: center;">Present</th>
                        <th style="text-align: center;">Absent</th>
                        <th style="text-align: center;">Late</th>
                    </tr>
                </thead>
                <tbody id="attendanceHistoryTableBody">
                    <?php foreach ($attendance_records as $record): ?>
                        <tr data-search="<?php echo e(trim(
                            date('M d, Y', strtotime($record['date'])) . ' ' .
                            ($record['subject_name'] ?? '') . ' ' .
                            ($record['subject_code'] ?? '') . ' ' .
                            ($record['academic_year'] ?? '') . ' semester ' . ($record['semester_number'] ?? '')
                        )); ?>">
                            <td>
                                <div class="date-cell">
                                    <?php echo e(date('M d, Y', strtotime($record['date']))); ?>
                                </div>
                            </td>
                            <td>
                                <div class="subject-cell"><?php echo e($record['subject_name']); ?></div>
                                <div class="subject-meta"><?php echo e($record['subject_code']); ?></div>
                            </td>
                            <td>
                                <div class="subject-cell"><?php echo e($record['academic_year']); ?></div>
                                <div class="subject-meta">Semester <?php echo e($record['semester_number']); ?></div>
                            </td>
                            <td style="text-align: center;">
                                <span class="count-badge count-total">
                                    <?php echo e($record['total_students']); ?>
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
                                <span class="count-badge count-late">
                                    <?php echo e($record['late_count'] ?? 0); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-view-pagination" id="attendanceHistoryPager" style="margin-top: 14px;">
            <div class="pagination-info" id="attendanceHistoryPageInfo"></div>
            <div class="pagination-actions">
                <button type="button" class="btn btn-ghost" id="attendanceHistoryPrev">Previous</button>
                <button type="button" class="btn btn-ghost" id="attendanceHistoryNext">Next</button>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📊</div>
            <p>No attendance records found</p>
            <p style="font-size: 0.9rem;">
                <?php
                if (!empty($filters['subject_id']) || !empty($filters['from_date']) || !empty($filters['to_date'])) {
                    echo 'Try adjusting your filter criteria.';
                } else {
                    echo 'Attendance records will appear here once you mark them.';
                }
                ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
    window.IMS = window.IMS || {};
    window.IMS.initTableView({
        tbodyId: 'attendanceHistoryTableBody',
        searchInputId: 'attendanceHistorySearch',
        metaId: 'attendanceHistoryMeta',
        pagerId: 'attendanceHistoryPager',
        pageInfoId: 'attendanceHistoryPageInfo',
        prevId: 'attendanceHistoryPrev',
        nextId: 'attendanceHistoryNext',
        pageSize: 10,
        noResultsColSpan: 7
    });
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
