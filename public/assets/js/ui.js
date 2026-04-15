/**
 * UI Manager - Handle UI interactions
 */

class UIManager {
    constructor(app) {
        this.app = app;
    }

    init() {
        this.setupSidebar();
        this.setupDropdowns();
        this.setupTooltips();
        this.setupTabs();
        this.setupAccordions();
    }

    /**
     * Setup sidebar toggle for mobile
     */
    setupSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.querySelector('[data-toggle="sidebar"]');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', (e) => {
                e.preventDefault();
                sidebar.classList.toggle('show');
                document.body.classList.toggle('sidebar-open');

                // Close sidebar when clicking outside
                if (sidebar.classList.contains('show')) {
                    const handleClick = (e) => {
                        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                            sidebar.classList.remove('show');
                            document.body.classList.remove('sidebar-open');
                            document.removeEventListener('click', handleClick);
                        }
                    };
                    setTimeout(() => {
                        document.addEventListener('click', handleClick);
                    }, 100);
                }
            });
        }
    }

    /**
     * Setup dropdown menus
     */
    setupDropdowns() {
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-toggle="dropdown"]');
            if (trigger) {
                e.preventDefault();
                const dropdown = trigger.closest('.dropdown') || trigger.nextElementSibling;
                if (dropdown?.classList.contains('dropdown-menu')) {
                    dropdown.classList.toggle('show');
                }
            }

            // Close dropdown when clicking outside
            const dropdowns = document.querySelectorAll('.dropdown-menu.show');
            dropdowns.forEach(menu => {
                if (!menu.parentElement.contains(e.target)) {
                    menu.classList.remove('show');
                }
            });
        });
    }

    /**
     * Setup tooltips
     */
    setupTooltips() {
        const tooltips = document.querySelectorAll('[data-toggle="tooltip"]');
        tooltips.forEach(el => {
            const tooltipText = el.dataset.title || el.title;
            if (!tooltipText) return;

            el.addEventListener('mouseenter', () => {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip-popup';
                tooltip.textContent = tooltipText;
                document.body.appendChild(tooltip);

                const rect = el.getBoundingClientRect();
                tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
                tooltip.style.left = (rect.left + em.offsetWidth / 2 - tooltip.offsetWidth / 2) + 'px';

                setTimeout(() => tooltip.classList.add('show'), 10);

                el.addEventListener('mouseleave', () => {
                    tooltip.classList.remove('show');
                    setTimeout(() => tooltip.remove(), 300);
                }, { once: true });
            });
        });
    }

    /**
     * Setup tab navigation
     */
    setupTabs() {
        document.addEventListener('click', (e) => {
            const tabTrigger = e.target.closest('[data-toggle="tab"]');
            if (!tabTrigger) return;

            e.preventDefault();
            const targetPane = document.querySelector(tabTrigger.dataset.target);
            if (!targetPane) return;

            // Deactivate all tabs in group
            const tabGroup = tabTrigger.closest('.nav-tabs');
            if (tabGroup) {
                tabGroup.querySelectorAll('.nav-link').forEach(link => {
                    link.classList.remove('active');
                });
            }

            // Deactivate all panes
            const parentContainer = targetPane.closest('.tab-content');
            if (parentContainer) {
                parentContainer.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('active');
                });
            }

            // Activate current tab and pane
            tabTrigger.classList.add('active');
            targetPane.classList.add('active');

            this.app.emit('tab:changed', { trigger: tabTrigger, pane: targetPane });
        });
    }

    /**
     * Setup accordion toggle
     */
    setupAccordions() {
        document.addEventListener('click', (e) => {
            const accordionTrigger = e.target.closest('[data-toggle="accordion"]');
            if (!accordionTrigger) return;

            e.preventDefault();
            const accordionItem = accordionTrigger.closest('.accordion-item');
            const accordionBody = accordionItem?.querySelector('.accordion-body');

            if (!accordionBody) return;

            const isOpen = accordionBody.classList.contains('show');

            // Get parent accordion group
            const accordionGroup = accordionItem.closest('.accordion');

            // Close all other items if closeOthers is enabled
            if (accordionGroup?.dataset.closeOthers !== 'false') {
                accordionGroup?.querySelectorAll('.accordion-body.show').forEach(body => {
                    if (body !== accordionBody) {
                        body.classList.remove('show');
                        body.style.maxHeight = '0';
                    }
                });
            }

            // Toggle current item
            accordionBody.classList.toggle('show');
            if (isOpen) {
                accordionBody.style.maxHeight = '0';
            } else {
                accordionBody.style.maxHeight = accordionBody.scrollHeight + 'px';
            }

            this.app.emit('accordion:toggled', { item: accordionItem, isOpen: !isOpen });
        });
    }

    /**
     * Show loading state on element
     */
    setLoading(element, isLoading = true) {
        if (isLoading) {
            element.classList.add('is-loading');
            element.disabled = true;
        } else {
            element.classList.remove('is-loading');
            element.disabled = false;
        }
    }

    /**
     * Blur background and show overlay
     */
    showOverlay() {
        let overlay = document.querySelector('.ui-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'ui-overlay';
            document.body.appendChild(overlay);
        }
        overlay.classList.add('show');
        return overlay;
    }

    /**
     * Hide overlay
     */
    hideOverlay() {
        const overlay = document.querySelector('.ui-overlay.show');
        if (overlay) {
            overlay.classList.remove('show');
        }
    }

    /**
     * Smooth scroll to element
     */
    scrollToElement(selector, offset = 0) {
        const element = typeof selector === 'string' ? 
            document.querySelector(selector) : 
            selector;

        if (!element) return;

        const elementPosition = element.getBoundingClientRect().top + window.pageYOffset;
        window.scrollTo({
            top: elementPosition - offset,
            behavior: 'smooth'
        });
    }

    /**
     * Format currency display
     */
    formatCurrency(value, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(value);
    }

    /**
     * Format date display
     */
    formatDate(date, format = 'short') {
        const d = new Date(date);
        const options = format === 'short' ? 
            { year: 'numeric', month: 'short', day: 'numeric' } :
            { year: 'numeric', month: 'long', day: 'numeric' };
        
        return d.toLocaleDateString('en-US', options);
    }

    /**
     * Copy text to clipboard
     */
    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            this.app.modules.notifications.showSuccess('Copied to clipboard');
        }).catch(() => {
            this.app.modules.notifications.showError('Failed to copy');
        });
    }

    /**
     * Toggle class on element
     */
    toggleClass(selector, className) {
        const element = typeof selector === 'string' ? 
            document.querySelector(selector) : 
            selector;
        
        if (element) {
            element.classList.toggle(className);
        }
    }

    /**
     * Add/remove class conditionally
     */
    setClass(selector, className, condition) {
        const element = typeof selector === 'string' ? 
            document.querySelector(selector) : 
            selector;
        
        if (element) {
            if (condition) {
                element.classList.add(className);
            } else {
                element.classList.remove(className);
            }
        }
    }
}
