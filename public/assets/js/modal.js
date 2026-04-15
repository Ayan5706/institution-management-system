/**
 * Modal Manager - Handle modal dialogs and overlays
 */

class ModalManager {
    constructor(app) {
        this.app = app;
        this.openModals = [];
        this.activeModal = null;
    }

    init() {
        this.setupEventListeners();
    }

    /**
     * Setup modal event listeners
     */
    setupEventListeners() {
        // Modal trigger buttons
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-toggle="modal"]');
            if (trigger) {
                e.preventDefault();
                const targetId = trigger.dataset.target;
                this.open(targetId);
            }
        });

        // Modal close buttons
        document.addEventListener('click', (e) => {
            const closeBtn = e.target.closest('[data-dismiss="modal"]');
            if (closeBtn) {
                e.preventDefault();
                this.closeCurrent();
            }

            // Close on overlay click
            if (e.target.classList.contains('modal-overlay')) {
                this.closeCurrent();
            }
        });

        // Keyboard escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModal) {
                this.closeCurrent();
            }
        });
    }

    /**
     * Open a modal by ID
     */
    open(id) {
        const modal = document.getElementById(id);
        if (!modal) {
            console.error(`Modal not found: ${id}`);
            return;
        }

        // Close current modal if any
        if (this.activeModal) {
            this.close(this.activeModal.id);
        }

        modal.classList.add('modal-active');
        this.activeModal = modal;
        this.openModals.push(modal);

        // Prevent body scroll
        document.body.style.overflow = 'hidden';

        // Emit event
        this.app.emit('modal:opened', { modal });

        console.log('[Modal] Opened:', id);
    }

    /**
     * Close a specific modal
     */
    close(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        modal.classList.remove('modal-active');
        this.openModals = this.openModals.filter(m => m.id !== id);

        if (this.activeModal?.id === id) {
            this.activeModal = null;
        }

        // Restore body scroll if no modals open
        if (this.openModals.length === 0) {
            document.body.style.overflow = '';
        }

        // Emit event
        this.app.emit('modal:closed', { id });

        console.log('[Modal] Closed:', id);
    }

    /**
     * Close current/top modal
     */
    closeCurrent() {
        if (this.activeModal) {
            this.close(this.activeModal.id);
        }
    }

    /**
     * Close all open modals
     */
    closeAll() {
        const ids = [...this.openModals].map(m => m.id);
        ids.forEach(id => this.close(id));
    }

    /**
     * Check if modal is open
     */
    isOpen(id) {
        return this.openModals.some(m => m.id === id);
    }

    /**
     * Toggle modal visibility
     */
    toggle(id) {
        if (this.isOpen(id)) {
            this.close(id);
        } else {
            this.open(id);
        }
    }

    /**
     * Get active modal
     */
    getActive() {
        return this.activeModal;
    }

    /**
     * Show a simple modal with message
     */
    showMessage(title, message, type = 'info', buttons = ['OK']) {
        const modalId = `modal-message-${Date.now()}`;
        const buttonHTML = buttons.map(btn => 
            `<button class="btn btn-${type}" data-dismiss="modal">${btn}</button>`
        ).join('');

        const html = `
            <div id="${modalId}" class="modal modal-message">
                <div class="modal-overlay"></div>
                <div class="modal-content modal-${type}">
                    <h3>${title}</h3>
                    <p>${message}</p>
                    <div class="modal-actions">
                        ${buttonHTML}
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);
        this.open(modalId);

        // Auto-remove after close
        const modal = document.getElementById(modalId);
        modal.addEventListener('transitionend', () => {
            if (!modal.classList.contains('modal-active')) {
                modal.remove();
            }
        });
    }
}
