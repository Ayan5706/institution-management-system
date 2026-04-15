# JavaScript Assets Library

Complete JavaScript module system for the IMS (Institution Management System) application.

## Directory Structure

```
js/
├── app.js              # Main application entry point
├── modal.js            # Modal dialog manager
├── form.js             # Form validation and submission
├── ui.js               # UI interactions (sidebar, dropdowns, tabs)
├── notifications.js    # Toast/alert notifications
├── api.js              # API client and fetch wrapper
├── table.js            # Table sorting, filtering, pagination
└── utils.js            # Utility functions
```

## Core Modules

### app.js - Main Application
Central application initializer and module orchestrator.

**Key Features:**
- Module registration and initialization
- Global event listeners setup
- CSRF token management
- Keyboard shortcut handlers
- Custom event system (emit/on/off)

**Usage:**
```javascript
// Access global IMS instance
window.IMS.init();             // Initialize app
window.IMS.getCSRFToken();     // Get CSRF token
window.IMS.emit('event:name'); // Emit custom event
window.IMS.on('event:name', callback);  // Listen to events
```

**Available Modules:**
- `window.IMS.modules.modal` - Modal manager
- `window.IMS.modules.form` - Form validation
- `window.IMS.modules.ui` - UI manager
- `window.IMS.modules.notifications` - Notification manager
- `window.IMS.modules.api` - API client

---

### modal.js - Modal Manager
Handle modal dialogs, overlays, and popups.

**Methods:**
- `open(id)` - Open modal by ID
- `close(id)` - Close specific modal
- `closeCurrent()` - Close active modal
- `closeAll()` - Close all open modals
- `isOpen(id)` - Check if modal is open
- `toggle(id)` - Toggle modal visibility
- `showMessage(title, message, type, buttons)` - Show simple message modal

**HTML Triggers:**
```html
<!-- Open modal -->
<button data-toggle="modal" data-target="#myModal">Open</button>

<!-- Close modal -->
<button data-dismiss="modal">Close</button>

<!-- Modal Markup -->
<div id="myModal" class="modal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <h3>Modal Title</h3>
        <p>Content here</p>
    </div>
</div>
```

**Events:**
- `modal:opened` - When modal opens
- `modal:closed` - When modal closes

---

### form.js - Form Validator
Comprehensive form validation and submission handling.

**Validation Rules:**
- `required` - Field is required
- `email` - Valid email format
- `min:length` - Minimum character length
- `max:length` - Maximum character length
- `numeric` - Numeric values only
- `match:fieldName` - Match another field value
- `phone` - Valid phone number
- `url` - Valid URL format

**HTML Attributes:**
```html
<form class="auto-submit" data-reset-on-success>
    <input type="text" name="email" data-validate="required|email">
    <input type="password" name="password" data-validate="required|min:8">
    <input type="password" name="confirm" data-validate="required|match:password">
    <button type="submit">Submit</button>
</form>
```

**Methods:**
- `validate(form)` - Validate form and return boolean
- `displayErrors(form)` - Show validation errors
- `clearErrors(form)` - Clear all errors
- `handleSubmit(form)` - Submit form via AJAX
- `serializeToJSON(form)` - Convert form to JSON
- `reset(form)` - Reset form to defaults
- `isDirtyForm(form)` - Check if form has changes

**Events:**
- `form:submit` - Form submitted
- `form:success` - Form submission successful
- `form:validation-complete` - Validation finished
- `form:error` - Form submission failed

---

### ui.js - UI Manager
Handle interactive UI elements and components.

**Features:**
- Sidebar toggle on mobile
- Dropdown menus
- Tooltips
- Tab navigation
- Accordion item toggle

**Methods:**
- `setLoading(element, isLoading)` - Show loading state
- `showOverlay()` - Show background overlay
- `hideOverlay()` - Hide overlay
- `scrollToElement(selector, offset)` - Smooth scroll
- `formatCurrency(value, currency)` - Format currency
- `formatDate(date, format)` - Format date
- `copyToClipboard(text)` - Copy to clipboard
- `toggleClass(selector, className)` - Toggle CSS class
- `setClass(selector, className, condition)` - Conditionally add/remove class

**HTML Triggers:**
```html
<!-- Sidebar toggle -->
<button data-toggle="sidebar">Menu</button>

<!-- Dropdown -->
<div class="dropdown">
    <button data-toggle="dropdown">Dropdown</button>
    <div class="dropdown-menu">
        <a href="#">Option 1</a>
        <a href="#">Option 2</a>
    </div>
</div>

<!-- Tooltip -->
<span data-toggle="tooltip" data-title="Help text">Hover me</span>

<!-- Tabs -->
<div class="nav-tabs">
    <a data-toggle="tab" data-target="#pane1" class="nav-link active">Tab 1</a>
    <a data-toggle="tab" data-target="#pane2" class="nav-link">Tab 2</a>
</div>
<div class="tab-content">
    <div id="pane1" class="tab-pane active">Content 1</div>
    <div id="pane2" class="tab-pane">Content 2</div>
</div>

<!-- Accordion -->
<div class="accordion">
    <div class="accordion-item">
        <button data-toggle="accordion">Accordion Title</button>
        <div class="accordion-body">Content here</div>
    </div>
</div>
```

**Events:**
- `tab:changed` - Tab switched
- `accordion:toggled` - Accordion item toggled

---

### notifications.js - Notification Manager
Show toast/alert notifications to user.

**Methods:**
- `show(message, type, duration)` - Show notification
- `showSuccess(message)` - Show success notification
- `showError(message)` - Show error notification
- `showWarning(message)` - Show warning notification
- `showInfo(message)` - Show info notification
- `close(id)` - Close specific notification
- `closeAll()` - Close all notifications
- `showConfirm(title, message, onConfirm, onCancel)` - Show confirmation dialog

**Usage:**
```javascript
// Show notifications
window.IMS.modules.notifications.showSuccess('Action completed!');
window.IMS.modules.notifications.showError('Something went wrong');
window.IMS.modules.notifications.showWarning('Please verify');

// Show confirmation
window.IMS.modules.notifications.showConfirm(
    'Delete Item',
    'Are you sure?',
    () => { /* confirm action */ },
    () => { /* cancel action */ }
);
```

---

### api.js - API Client
HTTP client for AJAX requests with CSRF token support.

**Methods:**
- `request(url, options)` - Make generic request
- `get(url, options)` - GET request
- `post(url, data, options)` - POST request
- `put(url, data, options)` - PUT request
- `delete(url, options)` - DELETE request
- `upload(url, file, fieldName, additionalData)` - File upload
- `retry(url, options, maxRetries)` - Retry with exponential backoff
- `createAbortController()` - Create request abort controller

**Usage:**
```javascript
// GET request
const response = await window.IMS.modules.api.get('/api/users');
const data = await response.json();

// POST request
const response = await window.IMS.modules.api.post('/api/users', {
    name: 'John Doe',
    email: 'john@example.com'
});

// File upload
const file = document.querySelector('input[type="file"]').files[0];
const response = await window.IMS.modules.api.upload('/api/upload', file);

// Retry on failure
const response = await window.IMS.modules.api.retry('/api/users', {}, 3);
```

---

### table.js - Table Manager
Manage table data: sorting, filtering, pagination.

**Methods:**
- `sort(column)` - Sort by column
- `loadData(data)` - Load data into table
- `addRow(rowData)` - Add new row
- `removeRow(index)` - Remove row
- `updateRow(index, rowData)` - Update row
- `search(query)` - Search table
- `render()` - Re-render table

**HTML Attributes:**
```html
<table data-table-manager="true">
    <thead>
        <tr>
            <th data-column="name" data-sortable="true">Name</th>
            <th data-column="email" data-sortable="true">Email</th>
            <th data-column="status">
                <select data-filter="status">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<!-- Pagination -->
<div class="pagination">
    <button data-pagination="prev">Previous</button>
    <span data-role="pagination-info"></span>
    <button data-pagination="next">Next</button>
</div>
```

---

### utils.js - Utility Functions
Common helper functions for development.

**Available Utilities:**
- `debounce(func, wait)` - Debounce function execution
- `throttle(func, limit)` - Throttle function execution
- `deepClone(obj)` - Deep clone object
- `merge(target, source)` - Merge objects
- `isEmpty(str)` - Check if string is empty
- `capitalize(str)` - Capitalize first letter
- `camelToKebab(str)` - Convert camelCase to kebab-case
- `snakeToCamel(str)` - Convert snake_case to camelCase
- `unique(arr)` - Get unique array values
- `groupBy(arr, key)` - Group array by key
- `sortBy(arr, key, order)` - Sort array of objects
- `flatten(arr)` - Flatten nested array
- `parseQuery(queryString)` - Parse URL query string
- `buildQuery(params)` - Build URL query string
- `wait(ms)` - Wait/sleep with Promise
- `retry(func, maxRetries, delay)` - Retry with exponential backoff
- `isMobile()` - Check if device is mobile
- `isTablet()` - Check if device is tablet
- `getDeviceInfo()` - Get device information

**Usage:**
```javascript
// Array operations
Utils.unique([1, 2, 2, 3]);  // [1, 2, 3]
Utils.groupBy(users, 'role');  // { admin: [...], user: [...] }
Utils.sortBy(users, 'name', 'asc');

// String operations
Utils.capitalize('hello');  // Hello
Utils.camelToKebab('myVariable');  // my-variable
Utils.snakeToCamel('my_variable');  // myVariable

// Async operations
Utils.debounce(search, 300);  // Wait 300ms after last call
Utils.throttle(scroll, 1000);  // Call at most once per second
await Utils.wait(1000);  // Wait 1 second
await Utils.retry(fetchData, 3);  // Retry 3 times

// Device detection
Utils.isMobile();  // true/false
Utils.getDeviceInfo();  // { browser: '...', mobile: false, ... }
```

## Integration

### Load all JavaScript files in HTML:

```html
<body>
    <!-- ... content ... -->
    
    <!-- Scripts -->
    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/modal.js"></script>
    <script src="/assets/js/form.js"></script>
    <script src="/assets/js/ui.js"></script>
    <script src="/assets/js/notifications.js"></script>
    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/table.js"></script>
    <script src="/assets/js/utils.js"></script>
</body>
```

## Custom Events

Listen to application events:

```javascript
// Listen to any event
window.IMS.on('modal:opened', (e) => {
    console.log('Modal opened:', e.detail);
});

window.IMS.on('form:success', (e) => {
    console.log('Form submitted successfully:', e.detail.data);
});

window.IMS.on('app:initialized', () => {
    console.log('Application is ready');
});
```

## Best Practices

1. **Use CSRF Protection**: Token automatically included in all requests
2. **Validate Forms**: Use data-validate attributes for client-side validation
3. **Handle Errors**: Always check response status before using data
4. **Optimize Performance**: Use debounce/throttle for input events
5. **Lazy Load**: Load JavaScript dynamically when needed
6. **Accessibility**: Ensure keyboard navigation works for all components
7. **Mobile First**: Test on mobile devices, use responsive utilities

## Performance Tips

- Minimize initialization on page load
- Use event delegation for dynamic elements
- Debounce/throttle expensive operations
- Cache DOM queries in variables
- Use DocumentFragment for bulk DOM updates
- Lazy-load modules that aren't immediately needed

---

**Last Updated**: April 12, 2026
**Version**: 1.0.0
