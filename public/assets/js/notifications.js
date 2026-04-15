/**
 * Notification Manager - Toast/Alert messages
 */

class NotificationManager {
    constructor(app) {
        this.app = app;
        this.container = null;
        this.notifications = [];
        this.timeout = 5000; // 5 seconds
    }

    init() {
        this.createContainer();
    }

    /**
     * Create notification container
     */
    createContainer() {
        this.container = document.querySelector('.notification-container');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'notification-container';
            document.body.appendChild(this.container);
        }
    }

    /**
     * Show notification
     */
    show(message, type = 'info', duration = this.timeout) {
        const notificationId = `notification-${Date.now()}`;
        const notification = document.createElement('div');
        notification.id = notificationId;
        notification.className = `notification notification-${type}`;

        // Build notification HTML
        let HTML = `<div class="notification-content">`;
        HTML += `<div class="notification-icon">`;
        
        switch(type) {
            case 'success':
                HTML += '✓';
                break;
            case 'error':
                HTML += '✕';
                break;
            case 'warning':
                HTML += '!';
                break;
            case 'info':
            default:
                HTML += 'ℹ';
        }
        
        HTML += `</div>`;
        HTML += `<div class="notification-message">${message}</div>`;
        HTML += `<button class="notification-close" data-dismiss="notification">×</button>`;
        HTML += `</div>`;
        HTML += `<div class="notification-progress"></div>`;

        notification.innerHTML = HTML;
        this.container.appendChild(notification);

        // Trigger animation
        setTimeout(() => notification.classList.add('show'), 10);

        // Close button
        notification.querySelector('.notification-close').addEventListener('click', () => {
            this.close(notificationId);
        });

        // Auto-close
        if (duration > 0) {
            const timeoutId = setTimeout(() => {
                this.close(notificationId);
            }, duration);

            notification.dataset.timeoutId = timeoutId;
        }

        // Clear previous timeout if hovering
        notification.addEventListener('mouseenter', () => {
            if (notification.dataset.timeoutId) {
                clearTimeout(parseInt(notification.dataset.timeoutId));
            }
        });

        notification.addEventListener('mouseleave', () => {
            if (notification.dataset.timeoutId) {
                const timeoutId = setTimeout(() => {
                    this.close(notificationId);
                }, duration);
                notification.dataset.timeoutId = timeoutId;
            }
        });

        this.notifications.push(notificationId);
        return notificationId;
    }

    /**
     * Show success notification
     */
    showSuccess(message, duration = this.timeout) {
        return this.show(message, 'success', duration);
    }

    /**
     * Show error notification
     */
    showError(message, duration = this.timeout) {
        return this.show(message, 'error', duration);
    }

    /**
     * Show warning notification
     */
    showWarning(message, duration = this.timeout) {
        return this.show(message, 'warning', duration);
    }

    /**
     * Show info notification
     */
    showInfo(message, duration = this.timeout) {
        return this.show(message, 'info', duration);
    }

    /**
     * Close notification
     */
    close(notificationId) {
        const notification = document.getElementById(notificationId);
        if (!notification) return;

        notification.classList.remove('show');

        setTimeout(() => {
            notification.remove();
            this.notifications = this.notifications.filter(id => id !== notificationId);
        }, 300);
    }

    /**
     * Close all notifications
     */
    closeAll() {
        this.notifications.forEach(notificationId => {
            this.close(notificationId);
        });
    }

    /**
     * Show confirmation dialog
     */
    showConfirm(title, message, onConfirm, onCancel) {
        const confirmId = `confirm-${Date.now()}`;
        
        const HTML = `
            <div id="${confirmId}" class="modal modal-confirm">
                <div class="modal-overlay"></div>
                <div class="modal-content">
                    <h3>${title}</h3>
                    <p>${message}</p>
                    <div class="modal-actions">
                        <button class="btn btn-secondary" data-action="cancel">Cancel</button>
                        <button class="btn btn-primary" data-action="confirm">Confirm</button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', HTML);
        const modal = document.getElementById(confirmId);

        modal.querySelector('[data-action="confirm"]').addEventListener('click', () => {
            if (onConfirm) onConfirm();
            modal.remove();
        });

        modal.querySelector('[data-action="cancel"]').addEventListener('click', () => {
            if (onCancel) onCancel();
            modal.remove();
        });

        modal.QuerySelector('.modal-overlay').addEventListener('click', () => {
            if (onCancel) onCancel();
            modal.remove();
        });

        modal.classList.add('modal-active');
    }

    /**
     * Clear all notifications
     */
    clear() {
        this.closeAll();
    }
}
