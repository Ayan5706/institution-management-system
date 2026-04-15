<?php
/** @var array $fee_records */
/** @var bool $profile_not_found */
$activeNav = 'fees';
$fee_records = $fee_records ?? [];
$profile_not_found = $profile_not_found ?? false;
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Fees</h2>
            <div style="color:#64748b;">Your fee payment details</div>
        </div>
        <a href="<?php echo e(url('student/dashboard')); ?>" class="btn btn-ghost">Back to Dashboard</a>
    </div>

    <div class="table-view-header" style="margin-top: 6px;">
        <div class="filter-bar table-view-controls" style="margin: 10px 0 18px;">
            <input type="text" id="feeSearch" class="filter-input table-view-field" placeholder="Search semester or year..." style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
            <select id="feeStatus" class="filter-select table-view-field" style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
                <option value="">All Status</option>
                <option value="PAID">Paid</option>
                <option value="PENDING">Pending</option>
            </select>
        </div>
        <div class="table-view-meta" id="feesMeta"></div>
    </div>

    <!-- Profile Not Found Warning -->
    <?php if ($profile_not_found): ?>
        <div class="notice-warning">
            <strong>⚠️ Profile Setup Needed</strong><br>
            Your student profile hasn't been set up yet. Please contact your institution to complete your enrollment. Your fee information will appear here once your profile is created.
        </div>
    <?php endif; ?>

    <style>
        .fees-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .fees-table thead {
            background: #f1f5f9;
            border-bottom: 2px solid #dbe4f0;
        }

        .fees-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #0f172a;
            font-size: 0.9rem;
        }

        .fees-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .fees-table tbody tr:hover {
            background: #f8fbff;
        }

        .semester-cell {
            color: #0f172a;
            font-weight: 600;
        }

        .semester-detail {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 2px;
        }

        .amount-text {
            font-weight: 600;
            color: #0f172a;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }

        .status-pending {
            background: #fee2e2;
            color: #7f1d1d;
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
            .fees-table {
                font-size: 0.85rem;
            }

            .fees-table th,
            .fees-table td {
                padding: 8px;
            }
        }

        @media (max-width: 640px) {
            .fees-table {
                font-size: 0.75rem;
            }

            .fees-table th,
            .fees-table td {
                padding: 6px;
            }
        }
    </style>

    <?php if (!empty($fee_records)): ?>
        <div style="overflow-x: auto;">
            <table class="fees-table">
                <thead>
                    <tr>
                        <th>Semester</th>
                        <th style="text-align: right;">Fee Amount</th>
                        <th style="text-align: right;">Amount Paid</th>
                        <th style="text-align: right;">Pending</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody id="feesTableBody">
                    <?php foreach ($fee_records as $record): ?>
                        <tr data-status="<?php echo e($record['status']); ?>"
                            data-semester="<?php echo e($record['academic_year']); ?>"
                            data-number="<?php echo e($record['semester_number']); ?>"
                            data-search="<?php echo e(trim(($record['academic_year'] ?? '') . ' semester ' . ($record['semester_number'] ?? '') . ' ' . ($record['status'] ?? ''))); ?>">
                            <td>
                                <div class="semester-cell"><?php echo e($record['academic_year']); ?></div>
                                <div class="semester-detail">Semester <?php echo e($record['semester_number']); ?></div>
                            </td>
                            <td style="text-align: right;">
                                <div class="amount-text">₨<?php echo e(number_format($record['fee_amount'])); ?></div>
                            </td>
                            <td style="text-align: right;">
                                <div class="amount-text">₨<?php echo e(number_format($record['amount_paid'])); ?></div>
                            </td>
                            <td style="text-align: right;">
                                <div class="amount-text">₨<?php echo e(number_format($record['pending_amount'])); ?></div>
                            </td>
                            <td style="text-align: center;">
                                <span class="status-badge <?php echo e($record['status'] === 'PAID' ? 'status-paid' : 'status-pending'); ?>">
                                    <?php echo e($record['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-view-pagination" id="feesPager" style="margin-top: 14px;">
            <div class="pagination-info" id="feesPageInfo"></div>
            <div class="pagination-actions">
                <button type="button" class="btn btn-ghost" id="feesPrev">Previous</button>
                <button type="button" class="btn btn-ghost" id="feesNext">Next</button>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">💳</div>
            <p>No fee records</p>
            <p style="font-size: 0.9rem; margin-top: 8px;">Your fee information will appear here once you're enrolled in a semester.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    window.IMS = window.IMS || {};
    window.IMS.initTableView({
        tbodyId: 'feesTableBody',
        searchInputId: 'feeSearch',
        metaId: 'feesMeta',
        pagerId: 'feesPager',
        pageInfoId: 'feesPageInfo',
        prevId: 'feesPrev',
        nextId: 'feesNext',
        pageSize: 10,
        noResultsColSpan: 5,
        filters: [
            { id: 'feeStatus', rowDatasetKey: 'status' }
        ]
    });
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
