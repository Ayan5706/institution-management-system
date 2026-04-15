/**
 * API Client - Handle AJAX requests
 */

class APIClient {
    constructor(app) {
        this.app = app;
        this.baseURL = '/api';
        this.timeout = 30000; // 30 seconds
    }

    /**
     * Make API request
     */
    async request(url, options = {}) {
        const finalURL = url.startsWith('http') ? url : this.baseURL + url;
        
        const defaultOptions = {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': this.app.getCSRFToken()
            },
            timeout: this.timeout
        };

        // Merge options
        const finalOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...options.headers
            }
        };

        try {
            const response = await fetch(finalURL, finalOptions);
            
            // Log request
            this.app.log(`[API] ${finalOptions.method} ${finalURL} - ${response.status}`);

            return response;
        } catch (error) {
            console.error('[API] Request failed:', error);
            throw error;
        }
    }

    /**
     * GET request
     */
    async get(url, options = {}) {
        const finalOptions = { ...options, method: 'GET' };
        return this.request(url, finalOptions);
    }

    /**
     * POST request
     */
    async post(url, data = {}, options = {}) {
        const finalOptions = {
            ...options,
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        };
        return this.request(url, finalOptions);
    }

    /**
     * PUT request
     */
    async put(url, data = {}, options = {}) {
        const finalOptions = {
            ...options,
            method: 'PUT',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        };
        return this.request(url, finalOptions);
    }

    /**
     * DELETE request
     */
    async delete(url, options = {}) {
        const finalOptions = { ...options, method: 'DELETE' };
        return this.request(url, finalOptions);
    }

    /**
     * Upload file
     */
    async upload(url, file, fieldName = 'file', additionalData = {}) {
        const formData = new FormData();
        formData.append(fieldName, file);

        Object.keys(additionalData).forEach(key => {
            formData.append(key, additionalData[key]);
        });

        const options = {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-Token': this.app.getCSRFToken()
            }
        };

        return this.request(url, options);
    }

    /**
     * JSON response handler
     */
    async handleJSON(response) {
        try {
            return await response.json();
        } catch (error) {
            console.error('[API] Failed to parse JSON:', error);
            return null;
        }
    }

    /**
     * Retry request with exponential backoff
     */
    async retry(url, options = {}, maxRetries = 3) {
        let lastError;

        for (let i = 0; i < maxRetries; i++) {
            try {
                const response = await this.request(url, options);
                if (response.ok) {
                    return response;
                }
            } catch (error) {
                lastError = error;
                if (i < maxRetries - 1) {
                    const delay = Math.pow(2, i) * 1000; // Exponential backoff
                    await new Promise(resolve => setTimeout(resolve, delay));
                }
            }
        }

        throw lastError;
    }

    /**
     * Abort request (with AbortController)
     */
    createAbortController() {
        return new AbortController();
    }
}
