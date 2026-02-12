/**
 * AJAX Search Script
 * Handles dynamic form submission for filters and pagination.
 */
document.addEventListener('DOMContentLoaded', function () {
    initAjaxSearch();
});

function initAjaxSearch() {
    const filterForms = document.querySelectorAll('.js-filter-form');

    filterForms.forEach(form => {
        // Prevent double binding
        if (form.dataset.ajaxBound) return;
        form.dataset.ajaxBound = 'true';

        // Helper to perform the search
        const performSearch = () => {
            const url = new URL(form.action || window.location.href);
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);

            // Fetch with AJAX header
            fetch(`${url.pathname}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.text())
                .then(html => {
                    const targetSelector = form.dataset.contentSelector || '#list-content';
                    const target = document.querySelector(targetSelector);
                    if (target) {
                        target.innerHTML = html;
                        // Re-init any plugins or scripts if needed
                        // update URL without reload
                        window.history.pushState(null, '', `?${params.toString()}`);
                    } else {
                        console.error('Target element not found:', targetSelector);
                    }
                })
                .catch(error => console.error('Error:', error));
        };

        // Debounce function
        const debounce = (func, wait) => {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        };

        // Bind input events
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('input', debounce(performSearch, 300));
            input.addEventListener('change', performSearch);
        });

        // Handle submit to prevent reload
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            performSearch();
        });
    });

    // Handle pagination clicks if they are inside the list content
    document.addEventListener('click', function (e) {
        if (e.target.closest('.pagination a')) {
            const link = e.target.closest('a');
            const listContent = link.closest('#list-content');

            if (listContent) {
                e.preventDefault();
                const url = link.href;

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.text())
                    .then(html => {
                        listContent.innerHTML = html;
                        window.history.pushState(null, '', url);
                    })
                    .catch(error => console.error('Error:', error));
            }
        }
    });
}
