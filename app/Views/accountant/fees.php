<?php
/** @var array<int, array<string, mixed>> $programs */
/** @var int $selected_program_id */
/** @var int $selected_semester_id */
$activeNav = 'fees';
$programs = $programs ?? [];
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

    .fees-divider {
        height: 1px;
        background: #f1f5f9;
        margin: 16px 0;
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

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-paid {
        background: #dcfce7;
        color: #166534;
    }

    .badge-pending {
        background: #fef3c7;
        color: #854d0e;
    }

    .student-detail {
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


    .empty-state {
        padding: 24px;
        text-align: center;
        color: #64748b;
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
    <div class="fees-page">
    <div class="fees-section">
        <div class="fees-section-header">
            <div>
                <h3 class="fees-section-title">Semester</h3>
                <p class="fees-section-subtitle">Choose a program, then set the fee for the active semester.</p>
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
                <label for="semesterSelect">Active Semester</label>
                <select id="semesterSelect">
                    <option value="">Select a program first</option>
                </select>
            </div>
            <div class="fees-field">
                <label for="semesterFeeInput">Semester Fee (₹)</label>
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
                <h3 class="fees-section-title">Student Fees</h3>
                <p class="fees-section-subtitle">Filter students, review payment history, and update records.</p>
            </div>
        </div>

        <div class="fees-grid">
            <div class="fees-field">
                <label for="studentProgramSelect">Program</label>
                <select id="studentProgramSelect">
                    <option value="">All programs</option>
                    <?php foreach ($programs as $program): ?>
                        <option value="<?php echo (int) $program['id']; ?>" <?php echo (int) $program['id'] === $selected_program_id ? 'selected' : ''; ?>>
                            <?php echo e($program['program_name'] ?? 'Program'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fees-field">
                <label for="studentSemesterSelect">Semester</label>
                <select id="studentSemesterSelect">
                    <option value="">Select a program first</option>
                </select>
            </div>
            <div class="fees-field">
                <label for="registrationSearch">Registration</label>
                <input type="text" id="registrationSearch" placeholder="Search registration number">
            </div>
            <div class="fees-field" style="display: flex; align-items: flex-end;">
                <button class="btn-primary" id="applyStudentFilters">Apply Filters</button>
            </div>
        </div>

        <div class="fees-divider"></div>

        <div style="overflow-x: auto;">
            <table class="fees-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Registration</th>
                        <th>Semester</th>
                        <th>Fee</th>
                        <th>Paid</th>
                        <th>Pending</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody">
                    <tr>
                        <td colspan="8" class="empty-state">Select filters to view student fees.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="student-detail" id="studentDetailCard" style="display: none;">
            <div class="detail-grid" id="studentDetailGrid"></div>
            <div class="fees-grid">
                <div class="fees-field">
                    <label for="paymentAmountInput">Payment Amount (₹)</label>
                    <input type="number" id="paymentAmountInput" min="0" step="0.01" placeholder="Enter new payment">
                </div>
                <div class="fees-field" style="display: flex; align-items: flex-end;">
                    <button class="btn-primary" id="updatePaymentButton">Update Payment</button>
                </div>
            </div>
            <div class="fees-status" id="paymentStatus"></div>
        </div>

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

const studentProgramSelect = document.getElementById('studentProgramSelect');
const studentSemesterSelect = document.getElementById('studentSemesterSelect');
const registrationSearch = document.getElementById('registrationSearch');
const applyStudentFilters = document.getElementById('applyStudentFilters');
const studentTableBody = document.getElementById('studentTableBody');
const studentDetailCard = document.getElementById('studentDetailCard');
const studentDetailGrid = document.getElementById('studentDetailGrid');
const paymentAmountInput = document.getElementById('paymentAmountInput');
const updatePaymentButton = document.getElementById('updatePaymentButton');
const paymentStatus = document.getElementById('paymentStatus');

let studentFeeCache = [];
let selectedFee = null;

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

function loadSemesters(programId, activeOnly, selectEl, selectedId) {
    if (!programId) {
        selectEl.innerHTML = '<option value="">Select a program first</option>';
        return;
    }

    const activeParam = activeOnly ? '?active=1' : '';
    fetchJson(`<?php echo e(url('api/accountant/program')); ?>/${programId}/semesters${activeParam}`)
        .then(data => {
            if (!data.success) {
                renderSemesterOptions(selectEl, [], null);
                return;
            }

            renderSemesterOptions(selectEl, data.data || [], selectedId);

            if (selectEl === semesterSelect) {
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                semesterFeeInput.value = selectedOption && selectedOption.dataset.fee ? selectedOption.dataset.fee : '';
            }
        })
        .catch(() => renderSemesterOptions(selectEl, [], null));
}

function renderStudentTable(rows) {
    studentTableBody.innerHTML = '';
    if (!rows.length) {
        studentTableBody.innerHTML = '<tr><td colspan="8" class="empty-state">No student fee records found.</td></tr>';
        return;
    }

    rows.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.student_name}</td>
            <td>${row.registration_number}</td>
            <td>${row.semester_label}</td>
            <td>₹${Number(row.fee_amount).toFixed(2)}</td>
            <td>₹${Number(row.amount_paid).toFixed(2)}</td>
            <td>₹${Number(row.pending).toFixed(2)}</td>
            <td><span class="badge ${row.status === 'PAID' ? 'badge-paid' : 'badge-pending'}">${row.status}</span></td>
            <td><button class="btn-primary" data-fee-id="${row.fee_id}">Select</button></td>
        `;
        tr.querySelector('button').addEventListener('click', () => selectStudentFee(row.fee_id));
        studentTableBody.appendChild(tr);
    });
}

function selectStudentFee(feeId) {
    selectedFee = studentFeeCache.find(item => Number(item.fee_id) === Number(feeId)) || null;
    if (!selectedFee) {
        return;
    }

    studentDetailCard.style.display = 'grid';
    studentDetailGrid.innerHTML = `
        <div class="detail-item"><span>Student</span><strong>${selectedFee.student_name}</strong></div>
        <div class="detail-item"><span>Registration</span><strong>${selectedFee.registration_number}</strong></div>
        <div class="detail-item"><span>Semester</span><strong>${selectedFee.semester_label}</strong></div>
        <div class="detail-item"><span>Status</span><strong>${selectedFee.status}</strong></div>
        <div class="detail-item"><span>Semester Fee</span><strong>₹${Number(selectedFee.fee_amount).toFixed(2)}</strong></div>
        <div class="detail-item"><span>Paid</span><strong>₹${Number(selectedFee.amount_paid).toFixed(2)}</strong></div>
        <div class="detail-item"><span>Pending</span><strong>₹${Number(selectedFee.pending).toFixed(2)}</strong></div>
    `;

    paymentAmountInput.value = '';
    paymentStatus.textContent = '';
}
}

function refreshStudentFees() {
    const programId = studentProgramSelect.value;
    const semesterId = studentSemesterSelect.value;
    const registration = registrationSearch.value.trim();

    const params = new URLSearchParams();
    if (programId) params.append('program_id', programId);
    if (semesterId) params.append('semester_id', semesterId);
    if (registration) params.append('registration', registration);

    fetchJson(`<?php echo e(url('api/accountant/student-fees')); ?>?${params.toString()}`)
        .then(data => {
            studentFeeCache = data.data || [];
            renderStudentTable(studentFeeCache);
        })
        .catch(() => {
            studentTableBody.innerHTML = '<tr><td colspan="8" class="empty-state">Unable to load student fees.</td></tr>';
        });
}

saveSemesterFee.addEventListener('click', () => {
    const semesterId = semesterSelect.value;
    const feeAmount = parseFloat(semesterFeeInput.value);

    if (!semesterId) {
        setStatus(semesterFeeStatus, 'Select an active semester first.', true);
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
    loadSemesters(programSelect.value, true, semesterSelect, null);
    semesterFeeInput.value = '';
    semesterFeeStatus.textContent = '';
});

studentProgramSelect.addEventListener('change', () => {
    loadSemesters(studentProgramSelect.value, false, studentSemesterSelect, null);
});

applyStudentFilters.addEventListener('click', () => {
    refreshStudentFees();
});

updatePaymentButton.addEventListener('click', () => {
    if (!selectedFee) {
        setStatus(paymentStatus, 'Select a student first.', true);
        return;
    }

    const paymentAmount = parseFloat(paymentAmountInput.value);
    if (isNaN(paymentAmount) || paymentAmount <= 0) {
        setStatus(paymentStatus, 'Enter a valid payment amount.', true);
        return;
    }

    const newTotal = Number(selectedFee.amount_paid) + paymentAmount;
    if (newTotal > Number(selectedFee.fee_amount)) {
        setStatus(paymentStatus, 'Payment exceeds the semester fee amount.', true);
        return;
    }

    fetchJson(`<?php echo e(url('api/accountant/fee')); ?>/${selectedFee.fee_id}/payment`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ amount_paid: newTotal })
    }).then(data => {
        if (data.success) {
            selectedFee.amount_paid = data.data.amount_paid;
            selectedFee.pending = data.data.pending;
            selectedFee.status = data.data.status;
            setStatus(paymentStatus, 'Payment updated successfully.');
            renderStudentTable(studentFeeCache);
            selectStudentFee(selectedFee.fee_id);
        } else {
            setStatus(paymentStatus, data.message || 'Unable to update payment.', true);
        }
    }).catch(() => setStatus(paymentStatus, 'Unable to update payment.', true));
});

if (initialProgramId) {
    loadSemesters(initialProgramId, true, semesterSelect, initialSemesterId);
    loadSemesters(initialProgramId, false, studentSemesterSelect, initialSemesterId);
}
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
