<?php
/** @var string $user_name */
/** @var array<string, mixed> $stats */
/** @var array<int, array<string, mixed>> $programs */
/** @var array<int, array<string, mixed>> $semester_fees */
$activeNav = 'dashboard';
$user_name = $user_name ?? 'Accountant';
$stats = $stats ?? [
    'total_collected' => '0.00',
    'total_pending' => '0.00',
    'active_semesters' => 0,
    'total_students' => 0,
];
$programs = $programs ?? [];
$semester_fees = $semester_fees ?? [];
$selected_program_id = !empty($programs) ? (int) ($programs[0]['id'] ?? 0) : 0;
?>
<?php ob_start(); ?>
<style>
    .finance-layout {
        display: grid;
        gap: 18px;
    }

    .finance-section {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 10px 24px rgba(31, 42, 55, 0.08);
    }

    .finance-section-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
    }

    .finance-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .finance-subtitle {
        margin: 6px 0 0;
        color: #6c7b86;
        font-size: 0.92rem;
    }

    .fees-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .fees-field label {
        display: block;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 6px;
        font-size: 0.9rem;
    }

    .fees-field select,
    .fees-field input {
        width: 100%;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        font-size: 0.95rem;
    }

    .btn-primary {
        padding: 10px 16px;
        border-radius: 10px;
        border: 0;
        background: #2563eb;
        color: #ffffff;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
    }

    .btn-primary:hover {
        background: #1d4ed8;
    }

    .fees-status {
        margin-top: 10px;
        font-size: 0.9rem;
        color: #1e293b;
    }

    .fees-table {
        width: 100%;
        border-collapse: collapse;
    }

    .fees-table thead {
        background: #f8fbff;
    }

    .fees-table th,
    .fees-table td {
        padding: 12px 14px;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.92rem;
    }

    .table-container {
        overflow-x: auto;
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-active {
        background: #dcfce7;
        color: #166534;
    }

    .badge-inactive {
        background: #fef3c7;
        color: #854d0e;
    }

    .empty-state {
        padding: 24px;
        text-align: center;
        color: #64748b;
    }

    .detail-card {
        margin-top: 16px;
        padding: 16px;
        background: #f8fafc;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        display: grid;
        gap: 12px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .detail-item span {
        display: block;
        color: #64748b;
        font-size: 0.82rem;
    }

    .detail-item strong {
        font-size: 0.96rem;
        color: #0f172a;
    }

    @media (max-width: 960px) {
        .fees-grid {
            grid-template-columns: 1fr 1fr;
        }

        .detail-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 640px) {
        .fees-grid {
            grid-template-columns: 1fr;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Accountant Dashboard</h2>
            <div style="color:#6c7b86;">Welcome, <?php echo e($user_name); ?>. Financial overview and fee management.</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">₹</div>
            <div>
                <div class="stat-label">Total Collected Fees</div>
                <div class="stat-value">₹<?php echo number_format((float) ($stats['total_collected'] ?? 0), 2); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div>
                <div class="stat-label">Total Pending Fees</div>
                <div class="stat-value">₹<?php echo number_format((float) ($stats['total_pending'] ?? 0), 2); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🗓️</div>
            <div>
                <div class="stat-label">Active Semesters</div>
                <div class="stat-value"><?php echo (int) ($stats['active_semesters'] ?? 0); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div>
                <div class="stat-label">Total Students</div>
                <div class="stat-value"><?php echo (int) ($stats['total_students'] ?? 0); ?></div>
            </div>
        </div>
    </div>

    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
