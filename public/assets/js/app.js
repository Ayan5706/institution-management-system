/**
 * IMS Main Application JavaScript
 * Core functionality and initialization
 */

class IMS {
    constructor() {
        this.initialized = false;
        this.config = {
            debug: true,
            apiEndpoint: '/api',
            csrfTokenSelector: '[name="_token"]',
            animationDuration: 300
        };
        this.modules = {};
    }

    /**
     * Initialize the application
     */
    init() {
        if (this.initialized) return;

        console.log('[IMS] Initializing application...');

        // Initialize core modules
        this.registerModule('modal', ModalManager);
        this.registerModule('form', FormValidator);
        this.registerModule('ui', UIManager);
        this.registerModule('notifications', NotificationManager);
        this.registerModule('api', APIClient);

        // Initialize modules
        this.modules.modal.init();
        this.modules.ui.init();
        this.modules.notifications.init();

        // Setup global event listeners
        this.setupGlobalListeners();

        // Load CSRF token
        this.loadCSRFToken();

        this.initialized = true;
        console.log('[IMS] Application initialized successfully');

        // Emit custom event
        this.emit('app:initialized');
    }

    /**
     * Register a module
     */
    registerModule(name, moduleClass) {
        this.modules[name] = new moduleClass(this);
        console.log(`[IMS] Module registered: ${name}`);
    }

    /**
     * Setup global event listeners
     */
    setupGlobalListeners() {
        // Prevent default form submissions
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.classList.contains('auto-submit')) {
                e.preventDefault();
                this.modules.form.handleSubmit(form);
            }
        });

        // Handle confirm dialogs
        document.addEventListener('click', (e) => {
            if (e.target.dataset.confirm) {
                if (!confirm(e.target.dataset.confirm)) {
                    e.preventDefault();
                }
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            this.handleKeyboardShortcuts(e);
        });
    }

    /**
     * Handle keyboard shortcuts
     */
    handleKeyboardShortcuts(e) {
        // ESC: Close modals
        if (e.key === 'Escape') {
            this.modules.modal.closeAll();
        }

        // Ctrl/Cmd + K: Focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('[data-role="search"]');
            if (searchInput) searchInput.focus();
        }

        // Ctrl/Cmd + S: Save (prevent default, trigger custom)
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const activeForm = document.querySelector('form:focus-within');
            if (activeForm) {
                this.modules.form.handleSubmit(activeForm);
            }
        }
    }

    /**
     * Load CSRF token from page
     */
    loadCSRFToken() {
        const token = document.querySelector(this.config.csrfTokenSelector);
        if (token) {
            this.csrfToken = token.value;
        }
    }

    /**
     * Get CSRF token
     */
    getCSRFToken() {
        return this.csrfToken || '';
    }

    /**
     * Emit custom events
     */
    emit(eventName, detail = {}) {
        const event = new CustomEvent(eventName, { detail });
        document.dispatchEvent(event);
    }

    /**
     * Listen to custom events
     */
    on(eventName, callback) {
        document.addEventListener(eventName, callback);
    }

    /**
     * Remove event listener
     */
    off(eventName, callback) {
        document.removeEventListener(eventName, callback);
    }

    /**
     * Debug logging
     */
    log(...args) {
        if (this.config.debug) {
            console.log('[IMS]', ...args);
        }
    }

    /**
     * Get configuration value
     */
    getConfig(key) {
        return this.config[key];
    }

    /**
     * Set configuration value
     */
    setConfig(key, value) {
        this.config[key] = value;
    }
}

// Initialize application on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.IMS = new IMS();
        window.IMS.init();
    });
} else {
    window.IMS = new IMS();
    window.IMS.init();
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = IMS;
}
