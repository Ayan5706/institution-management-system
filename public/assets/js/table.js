/**
 * Table Manager - Sorting, filtering, pagination
 */

class TableManager {
    constructor(tableElement) {
        this.table = tableElement;
        this.data = [];
        this.currentPage = 1;
        this.pageSize = 10;
        this.sortColumn = null;
        this.sortDirection = 'asc';
        this.filters = {};
        this.init();
    }

    init() {
        this.setupSorting();
        this.setupFiltering();
        this.setupPagination();
    }

    /**
     * Setup column sorting
     */
    setupSorting() {
        const headers = this.table.querySelectorAll('th[data-sortable="true"]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                const column = header.dataset.column;
                this.sort(column);
            });
        });
    }

    /**
     * Sort table by column
     */
    sort(column) {
        if (this.sortColumn === column) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = column;
            this.sortDirection = 'asc';
        }

        // Update header indicators
        this.table.querySelectorAll('th').forEach(header => {
            header.classList.remove('sort-asc', 'sort-desc');
            if (header.dataset.column === column) {
                header.classList.add(`sort-${this.sortDirection}`);
            }
        });

        this.render();
    }

    /**
     * Setup column filtering
     */
    setupFiltering() {
        const filters = this.table.querySelectorAll('[data-filter]');
        filters.forEach(filter => {
            filter.addEventListener('change', (e) => {
                const column = filter.dataset.filter;
                this.filters[column] = e.target.value;
                this.currentPage = 1; // Reset to first page
                this.render();
            });
        });
    }

    /**
     * Setup pagination
     */
    setupPagination() {
        const prevBtn = document.querySelector('[data-pagination="prev"]');
        const nextBtn = document.querySelector('[data-pagination="next"]');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (this.currentPage > 1) {
                    this.currentPage--;
                    this.render();
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                const maxPages = Math.ceil(this.getFilteredData().length / this.pageSize);
                if (this.currentPage < maxPages) {
                    this.currentPage++;
                    this.render();
                }
            });
        }
    }

    /**
     * Get filtered data
     */
    getFilteredData() {
        return this.data.filter(row => {
            for (const [column, value] of Object.entries(this.filters)) {
                if (value && row[column] !== value) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Sort data
     */
    getSortedData(data) {
        if (!this.sortColumn) return data;

        return [...data].sort((a, b) => {
            const aVal = a[this.sortColumn];
            const bVal = b[this.sortColumn];

            let comparison = 0;
            if (aVal > bVal) comparison = 1;
            if (aVal < bVal) comparison = -1;

            return this.sortDirection === 'asc' ? comparison : -comparison;
        });
    }

    /**
     * Get paginated data
     */
    getPaginatedData(data) {
        const start = (this.currentPage - 1) * this.pageSize;
        const end = start + this.pageSize;
        return data.slice(start, end);
    }

    /**
     * Render table
     */
    render() {
        const filtered = this.getFilteredData();
        const sorted = this.getSortedData(filtered);
        const paginated = this.getPaginatedData(sorted);

        const tbody = this.table.querySelector('tbody');
        tbody.innerHTML = '';

        paginated.forEach(row => {
            const tr = document.createElement('tr');
            const columns = Object.values(row);
            columns.forEach(cell => {
                const td = document.createElement('td');
                td.textContent = cell;
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });

        this.updatePaginationInfo();
    }

    /**
     * Update pagination info
     */
    updatePaginationInfo() {
        const filtered = this.getFilteredData();
        const maxPages = Math.ceil(filtered.length / this.pageSize);
        const pageInfo = document.querySelector('[data-role="pagination-info"]');

        if (pageInfo) {
            const start = (this.currentPage - 1) * this.pageSize + 1;
            const end = Math.min(this.currentPage * this.pageSize, filtered.length);
            pageInfo.textContent = `Showing ${start} to ${end} of ${filtered.length}`;
        }

        // Update button states
        const prevBtn = document.querySelector('[data-pagination="prev"]');
        const nextBtn = document.querySelector('[data-pagination="next"]');

        if (prevBtn) {
            prevBtn.disabled = this.currentPage === 1;
        }

        if (nextBtn) {
            nextBtn.disabled = this.currentPage >= maxPages;
        }
    }

    /**
     * Load data into table
     */
    loadData(data) {
        this.data = data;
        this.currentPage = 1;
        this.render();
    }

    /**
     * Add row to table
     */
    addRow(rowData) {
        this.data.push(rowData);
        this.render();
    }

    /**
     * Remove row from table
     */
    removeRow(index) {
        this.data.splice(index, 1);
        this.render();
    }

    /**
     * Update row in table
     */
    updateRow(index, rowData) {
        this.data[index] = { ...this.data[index], ...rowData };
        this.render();
    }

    /**
     * Search table
     */
    search(query) {
        const searchTerm = query.toLowerCase();
        return this.data.filter(row => {
            return Object.values(row).some(value =>
                String(value).toLowerCase().includes(searchTerm)
            );
        });
    }
}

// Auto-initialize tables
document.addEventListener('DOMContentLoaded', () => {
    const tables = document.querySelectorAll('[data-table-manager="true"]');
    tables.forEach(table => {
        window.tableManager = new TableManager(table);
    });
});
