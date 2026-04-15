<?php
/** @var array<int, array<string, mixed>> $programs */
/** @var array<int, array<string, mixed>> $semester_fees */
/** @var int $selected_program_id */
/** @var int $selected_semester_id */
$activeNav = 'semester-fees';
$programs = $programs ?? [];
$semester_fees = $semester_fees ?? [];
$selected_program_id = $selected_program_id ?? 0;
$selected_semester_id = $selected_semester_id ?? 0;

if ($selected_program_id === 0 && !empty($programs)) {
    $selected_program_id = (int) $programs[0]['id'];
}
?>
<?php ob_start(); ?>
<style>
    .fees-page {
        display: grid;
        gap: 20px;
    }

    .fees-section {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
    }

    .fees-section-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
    }

    .fees-section-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .fees-section-subtitle {
        margin: 6px 0 0;
        color: #64748b;
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

    .fees-table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 12px;
    }

    .fees-table-controls {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        flex: 1;
        min-width: min(520px, 100%);
    }

    .fees-table-filter {
        min-width: min(220px, 100%);
    }

    .fees-table-filter label {
        display: block;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 6px;
        font-size: 0.9rem;
    }

    .fees-table-filter select {
        width: 100%;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        font-size: 0.95rem;
    }

    .fees-table-search {
        flex: 1;
        min-width: min(320px, 100%);
    }

    .fees-table-search label {
        display: block;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 6px;
        font-size: 0.9rem;
    }

    .fees-table-search input {
        width: 100%;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        font-size: 0.95rem;
    }

    .fees-table-summary {
        color: #64748b;
        font-size: 0.9rem;
        padding-bottom: 2px;
        white-space: nowrap;
    }

    .fees-table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .fees-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 14px;
    }

    .fees-pagination .page-info {
        color: #64748b;
        font-size: 0.9rem;
    }

    .fees-pagination .page-actions {
        display: flex;
        gap: 10px;
    }

    .btn-secondary {
        padding: 10px 16px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #0f172a;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-secondary:disabled {
        cursor: not-allowed;
        opacity: 0.6;
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

    @media (max-width: 960px) {
        .fees-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 640px) {
        .fees-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="card content-card">
    <div class="fees-page">
        <div class="fees-section">
            <div class="fees-section-header">
                <div>
                    <h3 class="fees-section-title">Semester Fees</h3>
                    <p class="fees-section-subtitle">Select a program and semester to update fee amounts.</p>
                </div>
            </div>

            <div class="fees-grid">
                <div class="fees-field">
                    <label for="programSelect">Program</label>
                    <select id="programSelect">
                        <?php foreach ($programs as $program): ?>
                            <option value="<?php echo (int) $program['id']; ?>" <?php echo (int) $program['id'] === $selected_program_id ? 'selected' : ''; ?>>
                                <?php echo e($program['program_name'] ?? 'Program'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="fees-field">
                    <label for="semesterSelect">Semester</label>
                    <select id="semesterSelect">
                        <option value="">Select a program first</option>
                    </select>
                </div>
                <div class="fees-field">
                    <label for="semesterFeeInput">Semester Fee (₱)</label>
                    <input type="number" id="semesterFeeInput" min="0" step="0.01" placeholder="Enter fee amount">
                </div>
                <div class="fees-field" style="display: flex; align-items: flex-end;">
                    <button class="btn-primary" id="saveSemesterFee">Save Fee</button>
                </div>
            </div>
            <div class="fees-status" id="semesterFeeStatus"></div>
        </div>

        <div class="fees-section">
            <div class="fees-section-header">
                <div>
                    <h3 class="fees-section-title">All Semester Fees</h3>
                    <p class="fees-section-subtitle">Review every program and semester fee setting.</p>
                </div>
            </div>

            <div class="fees-table-toolbar">
                <div class="fees-table-controls">
                    <div class="fees-table-filter">
                        <label for="semesterFeesProgramFilter">Program</label>
                        <select id="semesterFeesProgramFilter">
                            <option value="">All programs</option>
                            <?php foreach ($programs as $program): ?>
                                <option value="<?php echo (int) $program['id']; ?>">
                                    <?php echo e($program['program_name'] ?? 'Program'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="fees-table-filter">
                        <label for="semesterFeesSemesterFilter">Semester</label>
                        <select id="semesterFeesSemesterFilter">
                            <option value="">All semesters</option>
                        </select>
                    </div>

                    <div class="fees-table-search">
                        <label for="semesterFeesSearch">Search</label>
                        <input type="text" id="semesterFeesSearch" placeholder="Search program, academic year, semester, fee, status...">
                    </div>
                </div>
                <div class="fees-table-summary" id="semesterFeesSummary"></div>
            </div>

            <div class="fees-table-container">
                <table class="fees-table" id="semesterFeesTable">
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Semester</th>
                            <th>Academic Year</th>
                            <th>Fee</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="semesterFeesTableBody">
                        <?php if (empty($semester_fees)): ?>
                            <tr>
                                <td colspan="6" class="empty-state">No semester fees found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($semester_fees as $semester): ?>
                                <tr
                                    data-program-id="<?php echo (int) ($semester['program_id'] ?? 0); ?>"
                                    data-semester-id="<?php echo (int) ($semester['id'] ?? 0); ?>"
                                    data-semester-label="<?php echo e(trim(($semester['academic_year'] ?? '') . ' - S' . (int) ($semester['semester_number'] ?? 0))); ?>"
                                    data-search="<?php echo e(strtolower(trim(($semester['program_name'] ?? '') . ' ' . ($semester['academic_year'] ?? '') . ' S' . (int) ($semester['semester_number'] ?? 0) . ' ' . number_format((float) ($semester['fee_amount'] ?? 0), 2) . ' ' . (((int) ($semester['is_current'] ?? 0) === 1) ? 'active' : 'inactive')))); ?>">
                                    <td><?php echo e($semester['program_name'] ?? 'Program'); ?></td>
                                    <td><?php echo 'S' . (int) ($semester['semester_number'] ?? 0); ?></td>
                                    <td><?php echo e($semester['academic_year'] ?? ''); ?></td>
                                    <td>₱<?php echo number_format((float) ($semester['fee_amount'] ?? 0), 2); ?></td>
                                    <td>
                                        <?php if ((int) ($semester['is_current'] ?? 0) === 1): ?>
                                            <span class="badge badge-active">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-inactive">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button
                                            class="btn-primary edit-semester-fee"
                                            data-program-id="<?php echo (int) ($semester['program_id'] ?? 0); ?>"
                                            data-semester-id="<?php echo (int) ($semester['id'] ?? 0); ?>"
                                            data-fee-amount="<?php echo e((string) ($semester['fee_amount'] ?? '0.00')); ?>">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($semester_fees)): ?>
                <div class="fees-pagination" id="semesterFeesPagination">
                    <div class="page-info" id="semesterFeesPageInfo"></div>
                    <div class="page-actions">
                        <button type="button" class="btn-secondary" id="semesterFeesPrev">Previous</button>
                        <button type="button" class="btn-secondary" id="semesterFeesNext">Next</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const programs = <?php echo json_encode($programs); ?>;
const initialProgramId = <?php echo (int) $selected_program_id; ?>;
const initialSemesterId = <?php echo (int) $selected_semester_id; ?>;

const programSelect = document.getElementById('programSelect');
const semesterSelect = document.getElementById('semesterSelect');
const semesterFeeInput = document.getElementById('semesterFeeInput');
const semesterFeeStatus = document.getElementById('semesterFeeStatus');
const saveSemesterFee = document.getElementById('saveSemesterFee');

function fetchJson(url, options = {}) {
    return fetch(url, options).then(response => response.json());
}

function setStatus(el, message, isError = false) {
    el.textContent = message;
    el.style.color = isError ? '#dc2626' : '#1e293b';
}

function renderSemesterOptions(selectEl, semesters, selectedId) {
    selectEl.innerHTML = '';
    if (!semesters.length) {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'No semesters available';
        selectEl.appendChild(option);
        return;
    }

    semesters.forEach(semester => {
        const option = document.createElement('option');
        option.value = semester.id;
        option.textContent = `${semester.academic_year} - S${semester.semester_number}` + (semester.is_current == 1 ? ' (Active)' : '');
        option.dataset.fee = semester.fee_amount || '';
        if (selectedId && Number(selectedId) === Number(semester.id)) {
            option.selected = true;
        }
        selectEl.appendChild(option);
    });
}

function loadSemesters(programId, selectEl, selectedId) {
    if (!programId) {
        selectEl.innerHTML = '<option value="">Select a program first</option>';
        return;
    }

    fetchJson(`<?php echo e(url('api/accountant/program')); ?>/${programId}/semesters`)
        .then(data => {
            if (!data.success) {
                renderSemesterOptions(selectEl, [], null);
                return;
            }

            renderSemesterOptions(selectEl, data.data || [], selectedId);

            const selectedOption = selectEl.options[selectEl.selectedIndex];
            semesterFeeInput.value = selectedOption && selectedOption.dataset.fee ? selectedOption.dataset.fee : '';
        })
        .catch(() => renderSemesterOptions(selectEl, [], null));
}

saveSemesterFee.addEventListener('click', () => {
    const semesterId = semesterSelect.value;
    const feeAmount = parseFloat(semesterFeeInput.value);

    if (!semesterId) {
        setStatus(semesterFeeStatus, 'Select a semester first.', true);
        return;
    }

    if (isNaN(feeAmount) || feeAmount <= 0) {
        setStatus(semesterFeeStatus, 'Enter a valid fee amount.', true);
        return;
    }

    fetchJson(`<?php echo e(url('api/accountant/semester')); ?>/${semesterId}/fee-amount`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ fee_amount: feeAmount })
    }).then(data => {
        if (data.success) {
            setStatus(semesterFeeStatus, 'Fee amount updated successfully.');
            const selectedOption = semesterSelect.options[semesterSelect.selectedIndex];
            if (selectedOption) {
                selectedOption.dataset.fee = feeAmount.toFixed(2);
            }
        } else {
            setStatus(semesterFeeStatus, data.message || 'Unable to update fee.', true);
        }
    }).catch(() => setStatus(semesterFeeStatus, 'Unable to update fee.', true));
});

semesterSelect.addEventListener('change', () => {
    const selectedOption = semesterSelect.options[semesterSelect.selectedIndex];
    semesterFeeInput.value = selectedOption && selectedOption.dataset.fee ? selectedOption.dataset.fee : '';
});

programSelect.addEventListener('change', () => {
    loadSemesters(programSelect.value, semesterSelect, null);
    semesterFeeInput.value = '';
    semesterFeeStatus.textContent = '';
});

Array.from(document.querySelectorAll('.edit-semester-fee')).forEach(button => {
    button.addEventListener('click', () => {
        const programId = button.dataset.programId || '';
        const semesterId = button.dataset.semesterId || '';
        const feeAmount = button.dataset.feeAmount || '';

        if (programId) {
            programSelect.value = programId;
            loadSemesters(programId, semesterSelect, semesterId);
        }

        semesterFeeInput.value = feeAmount;
        semesterFeeStatus.textContent = '';
    });
});

if (initialProgramId) {
    loadSemesters(initialProgramId, semesterSelect, initialSemesterId);
}

// --- All Semester Fees table: search + pagination ---
const semesterFeesSearch = document.getElementById('semesterFeesSearch');
const semesterFeesProgramFilter = document.getElementById('semesterFeesProgramFilter');
const semesterFeesSemesterFilter = document.getElementById('semesterFeesSemesterFilter');
const semesterFeesSummary = document.getElementById('semesterFeesSummary');
const semesterFeesTbody = document.getElementById('semesterFeesTableBody');
const semesterFeesPagination = document.getElementById('semesterFeesPagination');
const semesterFeesPageInfo = document.getElementById('semesterFeesPageInfo');
const semesterFeesPrev = document.getElementById('semesterFeesPrev');
const semesterFeesNext = document.getElementById('semesterFeesNext');

let semesterFeesPage = 1;
const semesterFeesPageSize = 10;

function getSemesterFeeRows() {
    if (!semesterFeesTbody) return [];
    return Array.from(semesterFeesTbody.querySelectorAll('tr'))
        .filter(row => !row.querySelector('.empty-state'));
}

function getSemesterFeesFilteredRows() {
    const rows = getSemesterFeeRows();
    const query = (semesterFeesSearch?.value || '').trim().toLowerCase();

    const selectedProgramId = (semesterFeesProgramFilter?.value || '').trim();
    const selectedSemesterId = (semesterFeesSemesterFilter?.value || '').trim();

    return rows.filter(row => {
        if (selectedProgramId && String(row.dataset.programId || '') !== selectedProgramId) {
            return false;
        }

        if (selectedSemesterId && String(row.dataset.semesterId || '') !== selectedSemesterId) {
            return false;
        }

        if (!query) return true;

        const haystack = (row.dataset.search || row.textContent || '').toLowerCase();
        return haystack.includes(query);
    });
}

function rebuildSemesterFeesSemesterOptions() {
    if (!semesterFeesSemesterFilter) return;

    const selectedProgramId = (semesterFeesProgramFilter?.value || '').trim();
    const currentSelection = (semesterFeesSemesterFilter.value || '').trim();

    const rows = getSemesterFeeRows().filter(row => {
        if (!selectedProgramId) return true;
        return String(row.dataset.programId || '') === selectedProgramId;
    });

    const options = new Map();
    rows.forEach(row => {
        const id = String(row.dataset.semesterId || '').trim();
        if (!id) return;
        const label = String(row.dataset.semesterLabel || '').trim() || `Semester ${id}`;
        options.set(id, label);
    });

    semesterFeesSemesterFilter.innerHTML = '<option value="">All semesters</option>';
    Array.from(options.entries())
        .sort((a, b) => a[1].localeCompare(b[1]))
        .forEach(([id, label]) => {
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = label;
            semesterFeesSemesterFilter.appendChild(opt);
        });

    if (currentSelection && options.has(currentSelection)) {
        semesterFeesSemesterFilter.value = currentSelection;
    } else {
        semesterFeesSemesterFilter.value = '';
    }
}

function ensureNoResultsRow() {
    if (!semesterFeesTbody) return null;
    let row = document.getElementById('semesterFeesNoResults');
    if (row) return row;

    row = document.createElement('tr');
    row.id = 'semesterFeesNoResults';
    row.innerHTML = '<td colspan="6" class="empty-state">No matching semester fees found.</td>';
    semesterFeesTbody.appendChild(row);
    return row;
}

function renderSemesterFeesTable() {
    const rows = getSemesterFeeRows();
    const filteredRows = getSemesterFeesFilteredRows();
    const total = rows.length;
    const filteredTotal = filteredRows.length;

    const pageCount = Math.max(1, Math.ceil(filteredTotal / semesterFeesPageSize));
    semesterFeesPage = Math.min(Math.max(1, semesterFeesPage), pageCount);

    const start = (semesterFeesPage - 1) * semesterFeesPageSize;
    const end = start + semesterFeesPageSize;
    const visible = filteredRows.slice(start, end);

    rows.forEach(row => {
        row.style.display = 'none';
    });

    visible.forEach(row => {
        row.style.display = '';
    });

    const noResultsRow = ensureNoResultsRow();
    if (noResultsRow) {
        noResultsRow.style.display = filteredTotal === 0 ? '' : 'none';
    }

    if (semesterFeesSummary) {
        semesterFeesSummary.textContent = filteredTotal === total
            ? `${total} total`
            : `${filteredTotal} of ${total}`;
    }

    if (semesterFeesPagination) {
        semesterFeesPagination.style.display = filteredTotal > 0 ? '' : 'none';
    }

    if (semesterFeesPageInfo) {
        semesterFeesPageInfo.textContent = `Page ${semesterFeesPage} of ${pageCount}`;
    }

    if (semesterFeesPrev) semesterFeesPrev.disabled = semesterFeesPage <= 1;
    if (semesterFeesNext) semesterFeesNext.disabled = semesterFeesPage >= pageCount;
}

semesterFeesSearch?.addEventListener('input', () => {
    semesterFeesPage = 1;
    renderSemesterFeesTable();
});

semesterFeesProgramFilter?.addEventListener('change', () => {
    semesterFeesPage = 1;
    rebuildSemesterFeesSemesterOptions();
    renderSemesterFeesTable();
});

semesterFeesSemesterFilter?.addEventListener('change', () => {
    semesterFeesPage = 1;
    renderSemesterFeesTable();
});

semesterFeesPrev?.addEventListener('click', () => {
    semesterFeesPage = Math.max(1, semesterFeesPage - 1);
    renderSemesterFeesTable();
});

semesterFeesNext?.addEventListener('click', () => {
    semesterFeesPage = semesterFeesPage + 1;
    renderSemesterFeesTable();
});

rebuildSemesterFeesSemesterOptions();
renderSemesterFeesTable();
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
