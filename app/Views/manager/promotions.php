<?php
/** @var array $active_semesters */
$activeNav = 'manager/promotions';
$active_semesters = $active_semesters ?? [];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Semester Promotions</h2>
            <div style="color:#64748b;">Review approved and pending students for active semesters</div>
        </div>
    </div>

    <style>
        .promotions-header {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin-bottom: 18px;
        }

        .promotions-header .status-message {
            color: #ef4444;
            font-weight: 600;
            width: 100%;
        }

        .promotions-header label {
            font-weight: 600;
            color: #0f172a;
        }

        .promotions-header select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
        }

        .promotions-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 16px;
        }

        .promo-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px;
            background: #fff;
        }

        .promo-switch {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            gap: 12px;
        }

        .promo-switch-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .promo-switch .switch-btn {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .promo-switch .switch-btn:hover {
            background: #e2e8f0;
            border-color: #cbd5e1;
        }

        .promo-panel {
            display: none;
        }

        .promo-panel.active {
            display: block;
        }

        .promo-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 4px;
        }

        .promo-subtitle {
            color: #64748b;
            margin: 0 0 12px;
            font-size: 0.92rem;
        }

        .promo-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 12px;
        }

        .promo-actions .btn {
            padding: 8px 12px;
            font-size: 0.88rem;
            border-radius: 10px;
        }

        .promo-actions label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.88rem;
            color: #475569;
        }

        .promo-table {
            width: 100%;
            border-collapse: collapse;
        }

        .promo-table th {
            text-align: left;
            padding: 10px 8px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.85rem;
            color: #475569;
        }

        .promo-table th:first-child,
        .promo-table td:first-child {
            width: 36px;
            text-align: center;
        }

        .promo-table th:nth-child(4),
        .promo-table td:nth-child(4) {
            text-align: right;
            width: 110px;
        }

        .promo-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #eef2f7;
            font-size: 0.88rem;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge.paid {
            background: #d1fae5;
            color: #065f46;
        }

        .badge.pending {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty-state {
            text-align: center;
            color: #64748b;
            padding: 16px 0;
        }

        .notice-banner {
            padding: 12px 16px;
            border-radius: 8px;
            margin: 12px 0;
            font-size: 0.9rem;
            display: none;
        }

        .notice-banner.success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .notice-banner.error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        @media (max-width: 980px) {
            .promotions-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Confirmation Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-dialog {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            margin-bottom: 16px;
        }

        .modal-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .modal-body {
            margin: 16px 0;
            color: #475569;
            line-height: 1.6;
        }

        .modal-info {
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 12px;
            border-radius: 6px;
            font-size: 0.9rem;
            margin: 12px 0;
        }

        .modal-info strong {
            display: block;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .modal-footer .btn {
            padding: 8px 16px;
            font-size: 0.9rem;
            border-radius: 8px;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .modal-footer .btn-confirm {
            background: #3b82f6;
            color: #fff;
        }

        .modal-footer .btn-confirm:hover {
            background: #2563eb;
        }

        .modal-footer .btn-cancel {
            background: #e2e8f0;
            color: #475569;
        }

        .modal-footer .btn-cancel:hover {
            background: #cbd5e1;
        }

        .modal-footer .btn-danger {
            background: #ef4444;
            color: #fff;
        }

        .modal-footer .btn-danger:hover {
            background: #dc2626;
        }
    </style>

    <div class="promotions-header">
        <label for="semesterSelect">Active Semester</label>
        <select id="semesterSelect">
            <option value="">Select semester</option>
            <?php foreach ($active_semesters as $semester): ?>
                <option value="<?php echo e($semester['id']); ?>">
                    <?php echo e($semester['label'] ?? 'Semester'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (empty($active_semesters)): ?>
            <span class="status-message">No active semesters found.</span>
        <?php endif; ?>
    </div>

    <div id="promotionMessage" class="notice-banner"></div>

    <div class="promotions-grid">
        <div class="promo-card">
            <div class="promo-switch">
                <div class="promo-switch-title">
                    <h3 class="promo-title" id="promoTitle">Approved Students</h3>
                    <span id="promoCount" style="color:#64748b; font-size:0.85rem;"></span>
                </div>
                <div class="promo-switch-title">
                    <button class="switch-btn" id="promoPrev" type="button" aria-label="Show approved students">&#8592;</button>
                    <button class="switch-btn" id="promoNext" type="button" aria-label="Show pending students">&#8594;</button>
                </div>
            </div>

            <div class="promo-panel active" id="approvedPanel">
                <p class="promo-subtitle">Paid students ready for promotion.</p>
                <div class="promo-actions">
                    <button class="btn btn-primary" id="promoteBtn">Promote Selected</button>
                    <label><input type="checkbox" id="selectAllApproved"> Select all</label>
                    <span id="approvedCount" style="color:#64748b; font-size:0.85rem;"></span>
                </div>
                <table class="promo-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Student</th>
                            <th>Registration</th>
                            <th>Paid</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="approvedTable">
                        <tr><td colspan="5" class="empty-state">Select a semester to view students.</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="promo-panel" id="pendingPanel">
                <p class="promo-subtitle">Fees pending, reminder required.</p>
                <div class="promo-actions">
                    <button class="btn btn-ghost" id="remindBtn">Send Reminders</button>
                    <label><input type="checkbox" id="selectAllPending"> Select all</label>
                    <span id="pendingCount" style="color:#64748b; font-size:0.85rem;"></span>
                </div>
                <table class="promo-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Student</th>
                            <th>Registration</th>
                            <th>Pending</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="pendingTable">
                        <tr><td colspan="5" class="empty-state">Select a semester to view students.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modals -->
<div class="modal-overlay" id="confirmationModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Confirm Action</h3>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content will be injected here -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel" id="modalCancel">Cancel</button>
            <button type="button" class="btn btn-confirm" id="modalConfirm">Confirm</button>
        </div>
    </div>
</div>

<script>
    const semesterSelect = document.getElementById('semesterSelect');
    const approvedTable = document.getElementById('approvedTable');
    const pendingTable = document.getElementById('pendingTable');
    const approvedCount = document.getElementById('approvedCount');
    const pendingCount = document.getElementById('pendingCount');
    const promoTitle = document.getElementById('promoTitle');
    const promoCount = document.getElementById('promoCount');
    const promoPrev = document.getElementById('promoPrev');
    const promoNext = document.getElementById('promoNext');
    const approvedPanel = document.getElementById('approvedPanel');
    const pendingPanel = document.getElementById('pendingPanel');
    const selectAllApproved = document.getElementById('selectAllApproved');
    const selectAllPending = document.getElementById('selectAllPending');
    const promoteBtn = document.getElementById('promoteBtn');
    const remindBtn = document.getElementById('remindBtn');
    const confirmationModal = document.getElementById('confirmationModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const modalConfirm = document.getElementById('modalConfirm');
    const modalCancel = document.getElementById('modalCancel');

    let pendingAction = null;
    let pendingData = null;
    let activePanel = 'approved';

    function updatePanelView() {
        const showingApproved = activePanel === 'approved';
        approvedPanel.classList.toggle('active', showingApproved);
        pendingPanel.classList.toggle('active', !showingApproved);
        promoTitle.textContent = showingApproved ? 'Approved Students' : 'Pending Students';
        const countText = showingApproved ? approvedCount.textContent : pendingCount.textContent;
        promoCount.textContent = countText ? `(${countText})` : '';
        promoPrev.setAttribute('aria-label', showingApproved ? 'Show pending students' : 'Show approved students');
        promoNext.setAttribute('aria-label', showingApproved ? 'Show pending students' : 'Show approved students');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showMessage(message, type) {
        const banner = document.getElementById('promotionMessage');
        if (!banner) return;

        banner.textContent = message;
        banner.classList.remove('success', 'error');
        banner.classList.add(type === 'error' ? 'error' : 'success');
        banner.style.display = 'block';

        if (type !== 'error') {
            setTimeout(() => {
                banner.style.display = 'none';
            }, 3000);
        }
    }

    function showModal(title, body, confirmCallback) {
        modalTitle.textContent = title;
        modalBody.innerHTML = body;
        confirmationModal.classList.add('active');

        pendingAction = confirmCallback;
    }

    function hideModal() {
        confirmationModal.classList.remove('active');
        pendingAction = null;
        pendingData = null;
    }

    function renderTable(tbody, rows, type) {
        if (!rows || rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="empty-state">No ${type} students.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(row => {
            const statusClass = (row.fee_status || '').toLowerCase() === 'paid' ? 'paid' : 'pending';
            const amount = type === 'approved'
                ? Number(row.amount_paid || 0).toFixed(2)
                : (row.pending_amount === null ? 'N/A' : Number(row.pending_amount || 0).toFixed(2));
            return `
                <tr>
                    <td><input type="checkbox" class="${type}-check" data-id="${row.student_id}"></td>
                    <td>${escapeHtml(row.full_name || 'N/A')}</td>
                    <td>${escapeHtml(row.registration_number || row.login_id || 'N/A')}</td>
                    <td>${amount}</td>
                    <td><span class="badge ${statusClass}">${escapeHtml(row.fee_status || 'Pending')}</span></td>
                </tr>
            `;
        }).join('');
    }

    function setCounts(approved, pending) {
        approvedCount.textContent = `${approved} students`;
        pendingCount.textContent = `${pending} students`;
        updatePanelView();
    }

    function getSelectedIds(selector) {
        return Array.from(document.querySelectorAll(selector))
            .filter(el => el.checked)
            .map(el => Number(el.getAttribute('data-id')))
            .filter(id => id > 0);
    }

    function toggleAll(selector, checked) {
        document.querySelectorAll(selector).forEach(el => {
            el.checked = checked;
        });
    }

    function loadPromotions() {
        const semesterId = semesterSelect.value;
        if (!semesterId) {
            renderTable(approvedTable, [], 'approved');
            renderTable(pendingTable, [], 'pending');
            setCounts(0, 0);
            return;
        }

        fetch(`<?php echo e(url('api/manager/promotions')); ?>?semester_id=${semesterId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to load promotions');
            }
            const approved = data.data.approved || [];
            const pending = data.data.pending || [];
            renderTable(approvedTable, approved, 'approved');
            renderTable(pendingTable, pending, 'pending');
            setCounts(approved.length, pending.length);
            selectAllApproved.checked = false;
            selectAllPending.checked = false;
        })
        .catch(error => {
            showMessage(error.message, 'error');
        });
    }

    function executePromotion() {
        if (!pendingData) return;

        fetch('<?php echo e(url('api/manager/promotions/promote')); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(pendingData)
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Promotion failed');
            }
            showMessage('Promotion completed and emails sent.', 'success');
            loadPromotions();
        })
        .catch(error => showMessage(error.message, 'error'));
    }

    function executeReminder() {
        if (!pendingData) return;

        fetch('<?php echo e(url('api/manager/promotions/remind')); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(pendingData)
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Reminder send failed');
            }
            showMessage('Reminder emails sent.', 'success');
            loadPromotions();
        })
        .catch(error => showMessage(error.message, 'error'));
    }

    semesterSelect.addEventListener('change', loadPromotions);

    selectAllApproved.addEventListener('change', (e) => {
        toggleAll('.approved-check', e.target.checked);
    });

    selectAllPending.addEventListener('change', (e) => {
        toggleAll('.pending-check', e.target.checked);
    });

    promoPrev.addEventListener('click', () => {
        activePanel = activePanel === 'approved' ? 'pending' : 'approved';
        updatePanelView();
    });

    promoNext.addEventListener('click', () => {
        activePanel = activePanel === 'approved' ? 'pending' : 'approved';
        updatePanelView();
    });

    promoteBtn.addEventListener('click', () => {
        const semesterId = Number(semesterSelect.value || 0);
        const studentIds = getSelectedIds('.approved-check');

        if (!semesterId || studentIds.length === 0) {
            showMessage('Select at least one approved student.', 'error');
            return;
        }

        // Store pending data and show confirmation modal
        pendingData = { semester_id: semesterId, student_ids: studentIds };

        const body = `
            <p>You are about to promote <strong>${studentIds.length}</strong> student${studentIds.length !== 1 ? 's' : ''} to the next semester.</p>
            <div class="modal-info">
                <strong>Action Details:</strong>
                Students will be enrolled in the next semester and promotion emails will be sent to their registered email addresses.
            </div>
            <p style="margin-bottom:0;"><strong>Do you want to proceed?</strong></p>
        `;

        showModal('Confirm Promotions', body, executePromotion);
    });

    remindBtn.addEventListener('click', () => {
        const semesterId = Number(semesterSelect.value || 0);
        const studentIds = getSelectedIds('.pending-check');

        if (!semesterId || studentIds.length === 0) {
            showMessage('Select at least one pending student.', 'error');
            return;
        }

        // Store pending data and show confirmation modal
        pendingData = { semester_id: semesterId, student_ids: studentIds };

        const body = `
            <p>You are about to send fee reminders to <strong>${studentIds.length}</strong> student${studentIds.length !== 1 ? 's' : ''}.</p>
            <div class="modal-info">
                <strong>Action Details:</strong>
                Reminder emails will be sent to their registered email addresses with details about pending fees.
            </div>
            <p style="margin-bottom:0;"><strong>Do you want to proceed?</strong></p>
        `;

        showModal('Confirm Reminders', body, executeReminder);
    });

    modalCancel.addEventListener('click', hideModal);
    
    modalConfirm.addEventListener('click', () => {
        if (pendingAction) {
            pendingAction();
        }
        hideModal();
    });

    // Close modal when clicking outside of it
    confirmationModal.addEventListener('click', (e) => {
        if (e.target === confirmationModal) {
            hideModal();
        }
    });

    if (!semesterSelect.value && semesterSelect.options.length > 1) {
        semesterSelect.selectedIndex = 1;
    }

    if (semesterSelect.value) {
        loadPromotions();
    }

    updatePanelView();
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
