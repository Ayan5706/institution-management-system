<?php
/** @var array $subjects */
/** @var array|null $current_semester */
/** @var array|null $program */
/** @var bool $profile_not_found */
$activeNav = 'subjects';
$subjects = $subjects ?? [];
$current_semester = $current_semester ?? null;
$program = $program ?? null;
$profile_not_found = $profile_not_found ?? false;
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Subjects</h2>
            <div style="color:#64748b;">Your current semester subjects and teachers</div>
        </div>
        <a href="<?php echo e(url('student/dashboard')); ?>" class="btn btn-ghost">Back to Dashboard</a>
    </div>

    <style>
        .semester-info {
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            color: #0f172a;
        }

        .subjects-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .subjects-table thead {
            background: #f1f5f9;
            border-bottom: 2px solid #dbe4f0;
        }

        .subjects-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #0f172a;
            font-size: 0.9rem;
        }

        .subjects-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .subjects-table tbody tr:hover {
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

        .teacher-name {
            font-weight: 600;
            color: #0f172a;
        }

        .teacher-meta {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 2px;
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: #64748b;
        }

        @media (max-width: 960px) {
            .subjects-table {
                font-size: 0.85rem;
            }

            .subjects-table th,
            .subjects-table td {
                padding: 8px;
            }
        }

        @media (max-width: 640px) {
            .subjects-table {
                font-size: 0.75rem;
            }

            .subjects-table th,
            .subjects-table td {
                padding: 6px;
            }
        }
    </style>

    <!-- Profile Not Found Warning -->
    <?php if ($profile_not_found): ?>
        <div class="notice-warning">
            <strong>Profile Setup Needed</strong><br>
            Your student profile hasn't been set up yet. Please contact your institution to complete your enrollment. Your subject list will appear here once your profile is created.
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

    <div class="table-view-header" style="margin-top: 6px;">
        <div class="filter-bar table-view-controls" style="margin: 10px 0 18px;">
            <input type="text" id="subjectSearch" class="filter-input table-view-field" placeholder="Search subject, code, or teacher..." style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
        </div>
        <div class="table-view-meta" id="subjectsMeta"></div>
    </div>

    <?php if (!empty($subjects)): ?>
        <div style="overflow-x: auto;">
            <table class="subjects-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Assigned Teacher</th>
                        <th>Semester</th>
                    </tr>
                </thead>
                <tbody id="subjectsTableBody">
                    <?php foreach ($subjects as $subject): ?>
                        <tr data-subject="<?php echo e($subject['subject_name']); ?>"
                            data-code="<?php echo e($subject['subject_code']); ?>"
                            data-teacher="<?php echo e($subject['teacher_display']); ?>"
                            data-search="<?php echo e(trim(($subject['subject_name'] ?? '') . ' ' . ($subject['subject_code'] ?? '') . ' ' . ($subject['teacher_display'] ?? ''))); ?>">
                            <td>
                                <div class="subject-cell"><?php echo e($subject['subject_name']); ?></div>
                                <div class="subject-code"><?php echo e($subject['subject_code']); ?></div>
                            </td>
                            <td>
                                <div class="teacher-name"><?php echo e($subject['teacher_display']); ?></div>
                                <div class="teacher-meta">Instructor</div>
                            </td>
                            <td>
                                <?php if ($current_semester): ?>
                                    Semester <?php echo e($current_semester['semester_number']); ?>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-view-pagination" id="subjectsPager" style="margin-top: 14px;">
            <div class="pagination-info" id="subjectsPageInfo"></div>
            <div class="pagination-actions">
                <button type="button" class="btn btn-ghost" id="subjectsPrev">Previous</button>
                <button type="button" class="btn btn-ghost" id="subjectsNext">Next</button>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>No subjects found</p>
            <p style="font-size: 0.9rem; margin-top: 8px;">Your subject list will appear here once assignments are available.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    window.IMS = window.IMS || {};
    window.IMS.initTableView({
        tbodyId: 'subjectsTableBody',
        searchInputId: 'subjectSearch',
        metaId: 'subjectsMeta',
        pagerId: 'subjectsPager',
        pageInfoId: 'subjectsPageInfo',
        prevId: 'subjectsPrev',
        nextId: 'subjectsNext',
        pageSize: 10,
        noResultsColSpan: 3
    });
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
