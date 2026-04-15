/**
 * Form Validator and Manager
 */

class FormValidator {
    constructor(app) {
        this.app = app;
        this.rules = {};
        this.errors = {};
        this.isDirty = false;
    }

    /**
     * Validate a form
     */
    validate(formElement) {
        this.errors = {};
        const formData = new FormData(formElement);
        const fields = formElement.querySelectorAll('[name]');

        let isValid = true;

        fields.forEach(field => {
            const fieldName = field.name;
            const value = formData.get(fieldName);
            const customRules = field.dataset.validate;

            if (customRules) {
                const fieldErrors = this.validateField(fieldName, value, customRules);
                if (fieldErrors.length > 0) {
                    this.errors[fieldName] = fieldErrors;
                    isValid = false;
                }
            }
        });

        return isValid;
    }

    /**
     * Validate a single field
     */
    validateField(fieldName, value, rules) {
        const errors = [];
        const ruleList = rules.split('|');

        ruleList.forEach(rule => {
            const [ruleName, ...params] = rule.split(':');

            switch (ruleName.trim()) {
                case 'required':
                    if (!value || value.trim() === '') {
                        errors.push(`${fieldName} is required`);
                    }
                    break;

                case 'email':
                    if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                        errors.push(`${fieldName} must be a valid email`);
                    }
                    break;

                case 'min':
                    const minLength = parseInt(params[0]);
                    if (value && value.length < minLength) {
                        errors.push(`${fieldName} must be at least ${minLength} characters`);
                    }
                    break;

                case 'max':
                    const maxLength = parseInt(params[0]);
                    if (value && value.length > maxLength) {
                        errors.push(`${fieldName} must not exceed ${maxLength} characters`);
                    }
                    break;

                case 'numeric':
                    if (value && isNaN(value)) {
                        errors.push(`${fieldName} must be numeric`);
                    }
                    break;

                case 'match':
                    const matchField = params[0];
                    const matchValue = document.querySelector(`[name="${matchField}"]`)?.value;
                    if (value !== matchValue) {
                        errors.push(`${fieldName} does not match ${matchField}`);
                    }
                    break;

                case 'phone':
                    if (value && !/^\+?[\d\s\-()]{10,}$/.test(value)) {
                        errors.push(`${fieldName} must be a valid phone number`);
                    }
                    break;

                case 'url':
                    try {
                        new URL(value);
                    } catch {
                        errors.push(`${fieldName} must be a valid URL`);
                    }
                    break;
            }
        });

        return errors;
    }

    /**
     * Display validation errors on form
     */
    displayErrors(formElement) {
        // Clear previous errors
        formElement.querySelectorAll('.form-error').forEach(el => el.remove());
        formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // Display new errors
        Object.keys(this.errors).forEach(fieldName => {
            const field = formElement.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.classList.add('is-invalid');

                const errorDiv = document.createElement('div');
                errorDiv.className = 'form-error';
                errorDiv.textContent = this.errors[fieldName][0];
                field.parentNode.insertBefore(errorDiv, field.nextSibling);
            }
        });

        this.app.emit('form:validation-complete', { errors: this.errors, isValid: Object.keys(this.errors).length === 0 });
    }

    /**
     * Clear form errors
     */
    clearErrors(formElement) {
        formElement.querySelectorAll('.form-error').forEach(el => el.remove());
        formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        this.errors = {};
    }

    /**
     * Handle form submission
     */
    async handleSubmit(formElement) {
        this.clearErrors(formElement);

        // Validate form
        if (!this.validate(formElement)) {
            this.displayErrors(formElement);
            return false;
        }

        // Get form data
        const formData = new FormData(formElement);

        // Add CSRF token
        formData.append('_token', this.app.getCSRFToken());

        // Determine method and action
        const method = (formElement.method || 'POST').toUpperCase();
        const action = formElement.action;

        try {
            this.app.emit('form:submit', { form: formElement });

            const response = await this.app.modules.api.request(action, {
                method,
                body: formData,
                headers: {
                    'X-CSRF-Token': this.app.getCSRFToken()
                }
            });

            if (response.ok) {
                const data = await response.json();

                // Show success message
                if (data.message) {
                    this.app.modules.notifications.showSuccess(data.message);
                }

                // Emit success event
                this.app.emit('form:success', { data });

                // Reset form if configured
                if (formElement.dataset.resetOnSuccess) {
                    formElement.reset();
                }

                // Redirect if configured
                if (data.redirect) {
                    window.location.href = data.redirect;
                }

                return true;
            } else {
                const data = await response.json();
                if (data.errors) {
                    this.errors = data.errors;
                    this.displayErrors(formElement);
                } else {
                    this.app.modules.notifications.showError(data.message || 'An error occurred');
                }
                return false;
            }
        } catch (error) {
            console.error('[Form] Submission error:', error);
            this.app.modules.notifications.showError('An error occurred. Please try again.');
            return false;
        }
    }

    /**
     * Serialize form to JSON
     */
    serializeToJSON(formElement) {
        const formData = new FormData(formElement);
        const object = {};

        formData.forEach((value, key) => {
            if (object.hasOwnProperty(key)) {
                if (Array.isArray(object[key])) {
                    object[key].push(value);
                } else {
                    object[key] = [object[key], value];
                }
            } else {
                object[key] = value;
            }
        });

        return object;
    }

    /**
     * Reset form fields
     */
    reset(formElement) {
        formElement.reset();
        this.clearErrors(formElement);
    }

    /**
     * Check if form is dirty
     */
    isDirtyForm(formElement) {
        return Array.from(formElement.elements).some(element => {
            if (element.type === 'checkbox' || element.type === 'radio') {
                return element.checked !== element.defaultChecked;
            } else {
                return element.value !== element.defaultValue;
            }
        });
    }

    /**
     * Set up dirty form warning
     */
    setupDirtyFormWarning(formElement) {
        window.addEventListener('beforeunload', (e) => {
            if (this.isDirtyForm(formElement)) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });

        formElement.addEventListener('submit', () => {
            window.onbeforeunload = null;
        });
    }
}
