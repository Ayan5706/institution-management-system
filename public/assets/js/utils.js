/**
 * Utility Functions
 */

const Utils = {
    /**
     * Debounce function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    /**
     * Throttle function
     */
    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    /**
     * Deep clone object
     */
    deepClone(obj) {
        return JSON.parse(JSON.stringify(obj));
    },

    /**
     * Merge objects
     */
    merge(target, source) {
        const output = Object.assign({}, target);
        if (this.isObject(target) && this.isObject(source)) {
            Object.keys(source).forEach(key => {
                if (this.isObject(source[key])) {
                    if (!(key in target))
                        Object.assign(output, { [key]: source[key] });
                    else
                        output[key] = this.merge(target[key], source[key]);
                } else {
                    Object.assign(output, { [key]: source[key] });
                }
            });
        }
        return output;
    },

    /**
     * Check if value is an object
     */
    isObject(item) {
        return item && typeof item === 'object' && !Array.isArray(item);
    },

    /**
     * Check if string is empty
     */
    isEmpty(str) {
        return !str || /^\s*$/.test(str);
    },

    /**
     * Capitalize string
     */
    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    },

    /**
     * Convert camelCase to kebab-case
     */
    camelToKebab(str) {
        return str.replace(/([a-z0-9]|(?=[A-Z]))([A-Z])/g, '$1-$2').toLowerCase();
    },

    /**
     * Convert snake_case to camelCase
     */
    snakeToCamel(str) {
        return str.replace(/_([a-z])/g, (g) => g[1].toUpperCase());
    },

    /**
     * Get unique values from array
     */
    unique(arr) {
        return [...new Set(arr)];
    },

    /**
     * Get unique objects from array
     */
    uniqueBy(arr, key) {
        return [...new Map(arr.map(item => [item[key], item])).values()];
    },

    /**
     * Group array by key
     */
    groupBy(arr, key) {
        return arr.reduce((result, item) => {
            const group = item[key];
            if (!result[group]) {
                result[group] = [];
            }
            result[group].push(item);
            return result;
        }, {});
    },

    /**
     * Sort array of objects
     */
    sortBy(arr, key, order = 'asc') {
        return [...arr].sort((a, b) => {
            if (a[key] > b[key]) return order === 'asc' ? 1 : -1;
            if (a[key] < b[key]) return order === 'asc' ? -1 : 1;
            return 0;
        });
    },

    /**
     * Flatten array
     */
    flatten(arr) {
        return arr.reduce((flat, toFlatten) => {
            return flat.concat(Array.isArray(toFlatten) ? this.flatten(toFlatten) : toFlatten);
        }, []);
    },

    /**
     * Wait for ms milliseconds
     */
    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    },

    /**
     * Retry function
     */
    async retry(func, maxRetries = 3, delay = 1000) {
        let lastError;
        for (let i = 0; i < maxRetries; i++) {
            try {
                return await func();
            } catch (error) {
                lastError = error;
                if (i < maxRetries - 1) {
                    await this.wait(delay * Math.pow(2, i));
                }
            }
        }
        throw lastError;
    },

    /**
     * Parse query string
     */
    parseQuery(queryString) {
        const query = {};
        const pairs = (queryString[0] === '?' ? queryString.substr(1) : queryString).split('&');
        for (let i = 0; i < pairs.length; i++) {
            const pair = pairs[i].split('=');
            query[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1] || '');
        }
        return query;
    },

    /**
     * Build query string
     */
    buildQuery(params) {
        return Object.keys(params)
            .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(params[key]))
            .join('&');
    },

    /**
     * Get DOM data attributes as object
     */
    getDataAttributes(element) {
        const data = {};
        Array.from(element.attributes).forEach(attr => {
            if (attr.name.startsWith('data-')) {
                const key = this.snakeToCamel(attr.name.replace('data-', ''));
                data[key] = attr.value;
            }
        });
        return data;
    },

    /**
     * Detect browser
     */
    detectBrowser() {
        const ua = navigator.userAgent;
        if (ua.indexOf('Firefox') > -1) return 'Firefox';
        if (ua.indexOf('Chrome') > -1) return 'Chrome';
        if (ua.indexOf('Safari') > -1) return 'Safari';
        if (ua.indexOf('Edge') > -1) return 'Edge';
        return 'Unknown';
    },

    /**
     * Check if device is mobile
     */
    isMobile() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    },

    /**
     * Check if device is tablet
     */
    isTablet() {
        return /iPad|Android(?!.*Mobile)|Kindle/i.test(navigator.userAgent);
    },

    /**
     * Get device info
     */
    getDeviceInfo() {
        return {
            browser: this.detectBrowser(),
            mobile: this.isMobile(),
            tablet: this.isTablet(),
            online: navigator.onLine,
            language: navigator.language
        };
    }
};

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Utils;
}
