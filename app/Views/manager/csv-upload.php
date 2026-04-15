<?php
$activeNav = 'csv-upload';
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Import Students via CSV</h2>
            <div style="color:#64748b;">4-step import process: Upload → Validate → Confirm → Complete</div>
        </div>
        <div>
            <a href="<?php echo e(url('manager/students')); ?>" class="btn btn-ghost">← Back to Students</a>
        </div>
    </div>

    <style>
        .steps {
            display: flex;
            gap: 20px;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .step {
            flex: 1;
            min-width: 200px;
            padding: 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-align: center;
            background: #fff;
        }

        .step.active {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .step.completed {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #0f172a;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .step.active .step-number {
            background: #2563eb;
            color: #fff;
        }

        .step.completed .step-number {
            background: #10b981;
            color: #fff;
        }

        .step-title {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .step-description {
            font-size: 0.9rem;
            color: #64748b;
        }

        .section {
            padding: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .notice-banner {
            padding: 12px 16px;
            border-radius: 8px;
            margin: 16px 0;
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

        .section h3 {
            margin: 0 0 16px;
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .section.hidden {
            display: none;
        }

        .file-upload {
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            background: #fff;
        }

        .file-upload input {
            display: none;
        }

        .file-upload-button {
            display: inline-block;
            padding: 12px 24px;
            background: #2563eb;
            color: #fff;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .file-upload-button:hover {
            background: #1d4ed8;
        }

        .file-name {
            margin-top: 12px;
            color: #64748b;
            font-size: 0.9rem;
        }

        .validation-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .validation-card {
            padding: 16px;
            border-radius: 10px;
            text-align: center;
        }

        .validation-card.valid {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        .validation-card.invalid {
            background: #fef2f2;
            border: 1px solid #fecaca;
        }

        .validation-label {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .validation-value {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .validation-card.valid .validation-value {
            color: #10b981;
        }

        .validation-card.invalid .validation-value {
            color: #dc2626;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            font-size: 0.9rem;
        }

        th {
            background: #f8fafc;
            padding: 12px;
            text-align: left;
            font-weight: 700;
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        tr:hover {
            background: #f8fafc;
        }

        .error-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .error-list li {
            padding: 6px 0;
            color: #dc2626;
            font-size: 0.85rem;
        }

        .buttons {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #2563eb;
            color: #fff;
            flex: 1;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-primary:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
        }

        .btn-ghost {
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            flex: 1;
        }

        .btn-ghost:hover {
            background: #e2e8f0;
        }

        .completion-icon {
            font-size: 3rem;
            margin-bottom: 16px;
        }

        .completion-message {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .completion-details {
            color: #64748b;
            font-size: 1rem;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #e2e8f0;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .steps {
                flex-direction: column;
            }

            .validation-summary {
                grid-template-columns: 1fr;
            }

            .file-upload {
                padding: 20px;
            }
        }
    </style>

    <div id="csvMessage" class="notice-banner"></div>

    <!-- Step Indicator -->
    <div class="steps">
        <div class="step active" id="step1-indicator">
            <div class="step-number">1</div>
            <div class="step-title">Upload</div>
            <div class="step-description">Select CSV file</div>
        </div>
        <div class="step" id="step2-indicator">
            <div class="step-number">2</div>
            <div class="step-title">Validate</div>
            <div class="step-description">Review data</div>
        </div>
        <div class="step" id="step3-indicator">
            <div class="step-number">3</div>
            <div class="step-title">Confirm</div>
            <div class="step-description">Verify import</div>
        </div>
        <div class="step" id="step4-indicator">
            <div class="step-number">4</div>
            <div class="step-title">Complete</div>
            <div class="step-description">Summary</div>
        </div>
    </div>

    <!-- Step 1: Upload -->
    <div class="section" id="step1">
        <h3>Step 1: Upload CSV File</h3>
        <div class="file-upload">
            <div>
                <label for="csvFile" class="file-upload-button">
                    📁 Choose CSV File
                </label>
                <input type="file" id="csvFile" accept=".csv" onchange="handleFileSelect()">
            </div>
            <div class="file-name">
                <small>Expected columns: Full Name, Login ID (must match Registration Number), Registration Number, Date of Birth, Program Code, Email, Phone</small>
            </div>
        </div>
        <div class="buttons">
            <button class="btn btn-primary" onclick="proceedToValidation()" disabled id="uploadButton">
                Next: Validate →
            </button>
        </div>
    </div>

    <!-- Step 2: Validate -->
    <div class="section hidden" id="step2">
        <h3>Step 2: Validation Preview</h3>
        <div class="validation-summary">
            <div class="validation-card valid">
                <div class="validation-label">Valid Rows</div>
                <div class="validation-value" id="validCount">0</div>
            </div>
            <div class="validation-card invalid">
                <div class="validation-label">Invalid Rows</div>
                <div class="validation-value" id="invalidCount">0</div>
            </div>
        </div>
        
        <div id="validRowsSection">
            <h4 style="color: #10b981; font-weight: 700; margin-bottom: 12px;">✓ Valid Rows</h4>
            <div class="table-view-header">
                <div class="table-view-controls">
                    <input type="text" id="validRowsSearch" class="table-view-field" placeholder="Search valid rows...">
                </div>
                <div class="table-view-meta" id="validRowsMeta"></div>
            </div>
            <div class="table-container">
                <table id="validRowsTable">
                    <thead>
                        <tr>
                            <th>Row</th>
                            <th>Full Name</th>
                            <th>Login ID</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody id="validRowsTableBody">
                    </tbody>
                </table>
            </div>
            <div class="table-view-pagination" id="validRowsPager" style="margin-top: 14px; display:none;">
                <div class="pagination-info" id="validRowsPageInfo"></div>
                <div class="pagination-actions">
                    <button type="button" class="btn btn-ghost" id="validRowsPrev">Previous</button>
                    <button type="button" class="btn btn-ghost" id="validRowsNext">Next</button>
                </div>
            </div>
        </div>

        <div id="invalidRowsSection" style="margin-top: 30px;">
            <h4 style="color: #dc2626; font-weight: 700; margin-bottom: 12px;">✗ Invalid Rows</h4>
            <div class="table-view-header">
                <div class="table-view-controls">
                    <input type="text" id="invalidRowsSearch" class="table-view-field" placeholder="Search invalid rows...">
                </div>
                <div class="table-view-meta" id="invalidRowsMeta"></div>
            </div>
            <div class="table-container">
                <table id="invalidRowsTable">
                    <thead>
                        <tr>
                            <th>Row</th>
                            <th>Issues</th>
                        </tr>
                    </thead>
                    <tbody id="invalidRowsTableBody">
                    </tbody>
                </table>
            </div>
            <div class="table-view-pagination" id="invalidRowsPager" style="margin-top: 14px; display:none;">
                <div class="pagination-info" id="invalidRowsPageInfo"></div>
                <div class="pagination-actions">
                    <button type="button" class="btn btn-ghost" id="invalidRowsPrev">Previous</button>
                    <button type="button" class="btn btn-ghost" id="invalidRowsNext">Next</button>
                </div>
            </div>
        </div>

        <div class="buttons" style="margin-top: 20px;">
            <button class="btn btn-ghost" onclick="resetProcess()">← Start Over</button>
            <button class="btn btn-primary" onclick="proceedToConfirm()" disabled id="validateButton">
                Next: Confirm →
            </button>
        </div>
    </div>

    <!-- Step 3: Confirm -->
    <div class="section hidden" id="step3">
        <h3>Step 3: Confirm Import</h3>
        <p style="color: #64748b; margin-bottom: 16px;">
            You are about to import <strong id="confirmCount">0</strong> students into the system. This action cannot be undone.
        </p>
        <div class="buttons">
            <button class="btn btn-ghost" onclick="backToValidation()">← Back</button>
            <button class="btn btn-primary" onclick="processImport()">
                Complete Import →
            </button>
        </div>
    </div>

    <!-- Step 4: Complete -->
    <div class="section hidden" id="step4">
        <div style="text-align: center; padding: 40px 0;">
            <div class="completion-icon">✓</div>
            <div class="completion-message">Import Completed!</div>
            <div class="completion-details">
                <div style="margin-bottom: 12px;">
                    Imported: <strong id="completionImported">0</strong> students
                </div>
                <div style="margin-bottom: 24px;">
                    Failed: <strong id="completionRejected">0</strong> students
                </div>
                <div id="completionMessage" style="color: #10b981; font-weight: 600;">
                    All students imported successfully!
                </div>
            </div>
        </div>
        <div class="buttons">
            <button class="btn btn-primary" onclick="window.location.href='<?php echo e(url('manager/students')); ?>'">
                Go to Student Management
            </button>
        </div>
    </div>
</div>

<script>
    let firstValidRow = null;
    let uploadedFile = null;

    function handleFileSelect() {
        const fileInput = document.getElementById('csvFile');
        uploadedFile = fileInput.files[0];
        document.getElementById('uploadButton').disabled = !uploadedFile;
        if (uploadedFile) {
            document.querySelector('.file-name').innerHTML = '✓ ' + uploadedFile.name;
        }
    }

    function proceedToValidation() {
        if (!uploadedFile) return;
        
        const formData = new FormData();
        formData.append('csv_file', uploadedFile);

        const uploadButton = document.getElementById('uploadButton');
        uploadButton.innerHTML = '<span class="spinner"></span> Processing...';
        uploadButton.disabled = true;

        fetch('<?php echo e(url('api/manager/csv-upload')); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            uploadButton.innerHTML = 'Next: Validate →';
            uploadButton.disabled = false;

            if (!data.success) {
                showMessage('Error: ' + data.message, 'error');
                return;
            }

            document.getElementById('validCount').textContent = data.valid_count;
            document.getElementById('invalidCount').textContent = data.invalid_count;

            // Display valid rows
            const validBody = document.getElementById('validRowsTableBody');
            validBody.innerHTML = data.valid_rows.map(row => `
                <tr data-search="${escapeHtml(`${row.row} ${row.data.full_name} ${row.data.login_id} ${row.data.email}`.trim())}">
                    <td>${row.row}</td>
                    <td>${escapeHtml(row.data.full_name)}</td>
                    <td>${escapeHtml(row.data.login_id)}</td>
                    <td>${escapeHtml(row.data.email)}</td>
                </tr>
            `).join('');

            // Display invalid rows
            const invalidBody = document.getElementById('invalidRowsTableBody');
            invalidBody.innerHTML = data.invalid_rows.map(row => `
                <tr data-search="${escapeHtml(`${row.row} ${(row.errors || []).join(' ')}`.trim())}">
                    <td>${row.row}</td>
                    <td><ul class="error-list">${row.errors.map(err => '<li>• ' + escapeHtml(err) + '</li>').join('')}</ul></td>
                </tr>
            `).join('');

            window.IMS?.initTableView({
                tbodyId: 'validRowsTableBody',
                searchInputId: 'validRowsSearch',
                metaId: 'validRowsMeta',
                pagerId: 'validRowsPager',
                pageInfoId: 'validRowsPageInfo',
                prevId: 'validRowsPrev',
                nextId: 'validRowsNext',
                pageSize: 10,
                noResultsColSpan: 4,
                noResultsText: 'No matching valid rows found.',
            });

            window.IMS?.initTableView({
                tbodyId: 'invalidRowsTableBody',
                searchInputId: 'invalidRowsSearch',
                metaId: 'invalidRowsMeta',
                pagerId: 'invalidRowsPager',
                pageInfoId: 'invalidRowsPageInfo',
                prevId: 'invalidRowsPrev',
                nextId: 'invalidRowsNext',
                pageSize: 10,
                noResultsColSpan: 2,
                noResultsText: 'No matching invalid rows found.',
            });

            document.getElementById('invalidRowsSection').style.display = data.invalid_count > 0 ? 'block' : 'none';
            document.getElementById('validateButton').disabled = data.valid_count === 0;

            // Store valid rows
            window.validRows = data.valid_rows;

            showStep(2);
        })
        .catch(err => {
            uploadButton.innerHTML = 'Next: Validate →';
            uploadButton.disabled = false;
            showMessage('Error uploading file: ' + err.message, 'error');
        });
    }

    function proceedToConfirm() {
        document.getElementById('confirmCount').textContent = window.validRows.length;
        showStep(3);
    }

    function backToValidation() {
        showStep(2);
    }

    function processImport() {
        const confirmButton = document.querySelector('#step3 .btn-primary');
        confirmButton.innerHTML = '<span class="spinner"></span> Importing...';
        confirmButton.disabled = true;

        fetch('<?php echo e(url('api/manager/csv-confirm')); ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ valid_rows: window.validRows })
        })
        .then(response => response.json())
        .then(data => {
            confirmButton.innerHTML = 'Complete Import →';
            confirmButton.disabled = false;

            if (!data.success) {
                showMessage('Error: ' + data.message, 'error');
                return;
            }

            document.getElementById('completionImported').textContent = data.imported;
            document.getElementById('completionRejected').textContent = data.rejected;
            document.getElementById('completionMessage').textContent = data.message;

            showStep(4);
        })
        .catch(err => {
            confirmButton.innerHTML = 'Complete Import →';
            confirmButton.disabled = false;
            showMessage('Error: ' + err.message, 'error');
        });
    }

    function resetProcess() {
        location.reload();
    }

    function showStep(step) {
        document.querySelectorAll('.section').forEach(s => s.classList.add('hidden'));
        document.getElementById('step' + step).classList.remove('hidden');

        document.querySelectorAll('.step').forEach(s => {
            s.classList.remove('active', 'completed');
        });

        for (let i = 1; i < step; i++) {
            document.getElementById('step' + i + '-indicator').classList.add('completed');
        }
        document.getElementById('step' + step + '-indicator').classList.add('active');
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    function showMessage(message, type) {
        const banner = document.getElementById('csvMessage');
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
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
