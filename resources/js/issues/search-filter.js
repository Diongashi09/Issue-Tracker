import { get } from '../lib/http.js';

const DEBOUNCE_MS = 300;

document.addEventListener('DOMContentLoaded', () => {
    const form          = document.getElementById('filter-form');
    const listContainer = document.getElementById('issue-list-container');
    const pagination    = document.getElementById('issue-pagination');

    if (!form || !listContainer) return;

    let debounceTimer   = null;
    let abortController = null;

    // -------------------------------------------------------------------------
    // Build the fetch URL from the form's current values.
    // Empty values are omitted so the query string stays clean.
    // -------------------------------------------------------------------------
    function buildUrl(base) {
        const params = new URLSearchParams();
        new FormData(form).forEach((value, key) => {
            if (value !== '') params.set(key, value);
        });
        const qs = params.toString();
        return qs ? `${base}?${qs}` : base;
    }

    // -------------------------------------------------------------------------
    // Fire the AJAX request; cancel any in-flight one first.
    // -------------------------------------------------------------------------
    async function fetchIssues(url) {
        if (abortController) abortController.abort();
        abortController = new AbortController();

        // Dim the list and block interaction while the request is in flight
        listContainer.classList.add('opacity-50', 'pe-none');

        try {
            const result = await get(url, { signal: abortController.signal });

            listContainer.innerHTML = result.html;
            listContainer.classList.remove('opacity-50', 'pe-none');

            if (pagination) {
                pagination.innerHTML = result.pagination.html;
                pagination.classList.toggle('d-none', !result.pagination.has_pages);
            }

            // Keep the URL bar in sync so the filtered view is bookmarkable.
            history.pushState(null, '', url);

        } catch (err) {
            if (err.name === 'AbortError') return; // intentional cancel
            listContainer.classList.remove('opacity-50', 'pe-none');
            console.error('Issue filter request failed:', err.message);
        } finally {
            abortController = null;
        }
    }

    // Immediate fetch (selects / clear)
    function fetchNow() {
        clearTimeout(debounceTimer);
        fetchIssues(buildUrl(form.dataset.indexUrl));
    }

    // Debounced fetch (free-text search box)
    function fetchDebounced() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchIssues(buildUrl(form.dataset.indexUrl)), DEBOUNCE_MS);
    }

    // Wire select dropdowns — fire immediately on change
    form.querySelectorAll('select.filter-control').forEach(el => {
        el.addEventListener('change', fetchNow);
    });

    // Wire search box — debounced input
    const searchInput = form.querySelector('input[name="q"]');
    if (searchInput) searchInput.addEventListener('input', fetchDebounced);

    // Suppress native form submit (Enter key in search box, etc.)
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        fetchNow();
    });

    // Intercept the "× Clear all filters" link so it stays AJAX instead of a full reload.
    // The link is only in the DOM when at least one filter is active.
    document.addEventListener('click', (e) => {
        if (e.target.closest('#filter-clear')) {
            e.preventDefault();
            form.reset();
            fetchNow();
        }
    });

    // -------------------------------------------------------------------------
    // Intercept pagination link clicks so they stay AJAX instead of full reloads.
    // The paginator uses withQueryString(), so each link already carries the
    // active filters — we just fetch the href directly.
    // -------------------------------------------------------------------------
    if (pagination) {
        pagination.addEventListener('click', (e) => {
            const link = e.target.closest('a[href]');
            if (!link) return;
            e.preventDefault();
            fetchIssues(link.href);
        });
    }
});
