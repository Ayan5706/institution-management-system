<?php
/** @var array $config */
$activeNav = 'config';
$config = $config ?? [];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">System Configuration</h2>
            <div style="color:#6c7b86;">Manage institution settings</div>
        </div>
    </div>

    <style>
        .config-section {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            max-width: 600px;
        }

        .config-item {
            margin-bottom: 24px;
        }

        .config-item:last-child {
            margin-bottom: 0;
        }

        .config-label {
            display: block;
            font-weight: 700;
            margin-bottom: 8px;
            color: #0f172a;
            font-size: 0.95rem;
        }

        .config-description {
            display: block;
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 8px;
        }

        .config-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
        }

        .config-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .time-field {
            position: relative;
        }

        .time-input-row {
            display: flex;
            gap: 8px;
        }

        .time-input-row .config-input {
            flex: 1;
        }

        .time-button {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: #0f172a;
            min-width: 44px;
        }

        .time-button:hover {
            background: #eef2f7;
        }

        .time-hint {
            margin-top: 6px;
            font-size: 0.82rem;
            color: #64748b;
        }

        .time-picker {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 240px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.15);
            padding: 12px;
            z-index: 20;
            display: none;
        }

        .time-picker.open {
            display: block;
        }

        .time-picker-header {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .time-picker-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .time-picker-row label {
            font-size: 0.8rem;
            color: #64748b;
        }

        .time-picker-row select {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            font-size: 0.9rem;
        }

        .time-picker-actions {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .time-picker-actions button {
            flex: 1;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid transparent;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .time-picker-apply {
            background: #2563eb;
            color: #ffffff;
        }

        .time-picker-cancel {
            background: #f1f5f9;
            color: #475569;
            border-color: #e2e8f0;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }

        .btn-save {
            padding: 10px 20px;
            background: #10b981;
            color: #fff;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .btn-save:hover {
            background: #059669;
        }

        .btn-cancel {
            padding: 10px 20px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-block;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
        }

        .info-message {
            background: #f0f9ff;
            border-left: 4px solid #0284c7;
            padding: 14px;
            border-radius: 6px;
            margin-bottom: 24px;
            color: #0c4a6e;
            font-size: 0.9rem;
        }

        .form-message {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            display: none;
        }

        .form-message.error {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            color: #7f1d1d;
        }

        .form-message.success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }
    </style>

    <div class="info-message">
        Configure key institution settings that affect daily operations.
    </div>

    <form id="configForm" class="config-section">
        <div id="configMessage" class="form-message"></div>

        <div class="config-item">
            <label class="config-label" for="working_days">Working Days</label>
            <span class="config-description">Business days per week (e.g., 5 for Mon-Fri)</span>
            <input 
                type="number" 
                id="working_days" 
                class="config-input" 
                value="<?php echo e($config['WORKING_DAYS'] ?? ''); ?>"
                min="1"
                max="6"
                required
            >
        </div>

        <div class="config-item time-field" data-time-picker>
            <label class="config-label" for="start_time">Start Time</label>
            <span class="config-description">Daily classes start time</span>
            <div class="time-input-row">
                <input
                    type="text"
                    id="start_time"
                    class="config-input"
                    value="<?php echo e($config['DAY_START_TIME'] ?? ''); ?>"
                    placeholder="HH:MM AM"
                    inputmode="numeric"
                    pattern="^(0?[1-9]|1[0-2]):[0-5]\d\s?(AM|PM)$"
                    required
                >
                <button type="button" class="time-button" aria-label="Select start time">🕒</button>
            </div>
            <div class="time-hint">Format: HH:MM AM/PM</div>
            <div class="time-picker" role="dialog" aria-hidden="true">
                <div class="time-picker-header">Select Time</div>
                <div class="time-picker-row">
                    <div>
                        <label>Hour</label>
                        <select class="time-hour"></select>
                    </div>
                    <div>
                        <label>Minute</label>
                        <select class="time-minute"></select>
                    </div>
                </div>
                <div class="time-picker-row">
                    <div>
                        <label>Period</label>
                        <select class="time-period">
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                    </div>
                </div>
                <div class="time-picker-actions">
                    <button type="button" class="time-picker-cancel">Cancel</button>
                    <button type="button" class="time-picker-apply">Apply</button>
                </div>
            </div>
        </div>

        <div class="config-item time-field" data-time-picker>
            <label class="config-label" for="end_time">End Time</label>
            <span class="config-description">Daily classes end time</span>
            <div class="time-input-row">
                <input
                    type="text"
                    id="end_time"
                    class="config-input"
                    value="<?php echo e($config['DAY_END_TIME'] ?? ''); ?>"
                    placeholder="HH:MM AM"
                    inputmode="numeric"
                    pattern="^(0?[1-9]|1[0-2]):[0-5]\d\s?(AM|PM)$"
                    required
                >
                <button type="button" class="time-button" aria-label="Select end time">🕒</button>
            </div>
            <div class="time-hint">Format: HH:MM AM/PM</div>
            <div class="time-picker" role="dialog" aria-hidden="true">
                <div class="time-picker-header">Select Time</div>
                <div class="time-picker-row">
                    <div>
                        <label>Hour</label>
                        <select class="time-hour"></select>
                    </div>
                    <div>
                        <label>Minute</label>
                        <select class="time-minute"></select>
                    </div>
                </div>
                <div class="time-picker-row">
                    <div>
                        <label>Period</label>
                        <select class="time-period">
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                    </div>
                </div>
                <div class="time-picker-actions">
                    <button type="button" class="time-picker-cancel">Cancel</button>
                    <button type="button" class="time-picker-apply">Apply</button>
                </div>
            </div>
        </div>

        <div class="config-item">
            <label class="config-label" for="grace_minutes">Grace Minutes</label>
            <span class="config-description">Minutes allowed for late attendance (e.g., 5)</span>
            <input 
                type="number" 
                id="grace_minutes" 
                class="config-input" 
                value="<?php echo e($config['GRACE_MINUTES'] ?? ''); ?>"
                min="0"
                max="60"
                required
            >
        </div>

        <div class="button-group">
            <button type="button" class="btn-save" onclick="saveConfig()">Save Changes</button>
            <a href="<?php echo e(url('principal/dashboard')); ?>" class="btn-cancel">Cancel</a>
        </div>
    </form>

    <script>
        function showConfigMessage(message, isError) {
            const messageEl = document.getElementById('configMessage');
            messageEl.textContent = message;
            messageEl.classList.remove('error', 'success');
            messageEl.classList.add(isError ? 'error' : 'success');
            messageEl.style.display = 'block';
        }

        function clearConfigMessage() {
            const messageEl = document.getElementById('configMessage');
            messageEl.textContent = '';
            messageEl.classList.remove('error', 'success');
            messageEl.style.display = 'none';
        }

        function parseTime12To24(value) {
            const normalized = value.trim().toUpperCase();
            const match = normalized.match(/^(0?[1-9]|1[0-2]):([0-5]\d)\s?(AM|PM)$/);
            if (!match) {
                return { error: 'Use HH:MM AM/PM format.' };
            }

            let hour = Number(match[1]);
            const minute = match[2];
            const period = match[3];

            if (period === 'AM') {
                hour = hour === 12 ? 0 : hour;
            } else {
                hour = hour === 12 ? 12 : hour + 12;
            }

            return { value: `${String(hour).padStart(2, '0')}:${minute}` };
        }

        function formatTime12(value) {
            const match = value.trim().match(/^([01]\d|2[0-3]):([0-5]\d)$/);
            if (!match) {
                return value;
            }

            let hour = Number(match[1]);
            const minute = match[2];
            const period = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12;
            if (hour === 0) {
                hour = 12;
            }
            return `${hour}:${minute} ${period}`;
        }

        function saveConfig() {
            clearConfigMessage();
            const formData = {
                working_days: document.getElementById('working_days').value,
                day_start_time: document.getElementById('start_time').value.trim(),
                day_end_time: document.getElementById('end_time').value.trim(),
                grace_minutes: document.getElementById('grace_minutes').value
            };

            const workingDays = parseInt(formData.working_days, 10);
            if (!Number.isFinite(workingDays) || workingDays < 1 || workingDays > 6) {
                showConfigMessage('Working days must be between 1 and 6.', true);
                return;
            }

            const startTime = parseTime12To24(formData.day_start_time);
            if (startTime.error) {
                showConfigMessage('Start time must use HH:MM AM/PM format.', true);
                return;
            }

            const endTime = parseTime12To24(formData.day_end_time);
            if (endTime.error) {
                showConfigMessage('End time must use HH:MM AM/PM format.', true);
                return;
            }

            formData.day_start_time = startTime.value;
            formData.day_end_time = endTime.value;

            // Save each config value
            const requests = [
                fetch('<?php echo e(url('principal/config')); ?>/working_days', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ value: formData.working_days })
                }),
                fetch('<?php echo e(url('principal/config')); ?>/day_start_time', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ value: formData.day_start_time })
                }),
                fetch('<?php echo e(url('principal/config')); ?>/day_end_time', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ value: formData.day_end_time })
                }),
                fetch('<?php echo e(url('principal/config')); ?>/grace_minutes', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ value: formData.grace_minutes })
                })
            ];

            Promise.all(requests).then(async (responses) => {
                if (responses.every(r => r.ok)) {
                    showConfigMessage('Configuration saved successfully.', false);
                    return;
                }

                const failedResponse = responses.find(r => !r.ok);
                if (failedResponse) {
                    try {
                        const payload = await failedResponse.json();
                        showConfigMessage(payload.message || 'Error saving configuration. Please try again.', true);
                    } catch (error) {
                        showConfigMessage('Error saving configuration. Please try again.', true);
                    }
                }
            }).catch(error => {
                showConfigMessage('Error: ' + error.message, true);
            });
        }

        function initTimePicker(wrapper) {
            const input = wrapper.querySelector('.config-input');
            const button = wrapper.querySelector('.time-button');
            const picker = wrapper.querySelector('.time-picker');
            const hourSelect = wrapper.querySelector('.time-hour');
            const minuteSelect = wrapper.querySelector('.time-minute');
            const periodSelect = wrapper.querySelector('.time-period');
            const applyButton = wrapper.querySelector('.time-picker-apply');
            const cancelButton = wrapper.querySelector('.time-picker-cancel');

            input.value = formatTime12(input.value);

            for (let hour = 1; hour <= 12; hour += 1) {
                const option = document.createElement('option');
                const value = String(hour).padStart(2, '0');
                option.value = value;
                option.textContent = value;
                hourSelect.appendChild(option);
            }

            for (let minute = 0; minute < 60; minute += 5) {
                const option = document.createElement('option');
                const value = String(minute).padStart(2, '0');
                option.value = value;
                option.textContent = value;
                minuteSelect.appendChild(option);
            }

            function openPicker() {
                const current = input.value.trim();
                const match = current.toUpperCase().match(/^(0?[1-9]|1[0-2]):([0-5]\d)\s?(AM|PM)$/);
                if (match) {
                    hourSelect.value = String(Number(match[1])).padStart(2, '0');
                    const minute = Math.round(Number(match[2]) / 5) * 5;
                    minuteSelect.value = String(minute).padStart(2, '0');
                    periodSelect.value = match[3];
                }
                picker.classList.add('open');
                picker.setAttribute('aria-hidden', 'false');
            }

            function closePicker() {
                picker.classList.remove('open');
                picker.setAttribute('aria-hidden', 'true');
            }

            button.addEventListener('click', (event) => {
                event.stopPropagation();
                openPicker();
            });

            applyButton.addEventListener('click', () => {
                const hour = Number(hourSelect.value);
                input.value = `${hour}:${minuteSelect.value} ${periodSelect.value}`;
                closePicker();
            });

            cancelButton.addEventListener('click', closePicker);

            document.addEventListener('click', (event) => {
                if (!wrapper.contains(event.target)) {
                    closePicker();
                }
            });
        }

        document.querySelectorAll('[data-time-picker]').forEach(initTimePicker);
    </script>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
