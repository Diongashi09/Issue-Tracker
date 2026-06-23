import { get, post, ValidationError } from '../lib/http.js';

document.addEventListener('DOMContentLoaded', () => {
    const list    = document.getElementById('comment-list');
    const form    = document.getElementById('comment-form');

    // Guard: only activate on the issue show page
    if (!list || !form) return;

    const indexUrl          = list.dataset.indexUrl;
    const storeUrl          = form.dataset.storeUrl;
    const loadMoreContainer = document.getElementById('comment-load-more-container');
    const loadMoreBtn       = document.getElementById('comment-load-more');
    const submitBtn         = document.getElementById('comment-submit');

    let currentPage  = 1;
    let hasMorePages = false;
    let fetching     = false;

    // -------------------------------------------------------------------------
    // Initial load
    // -------------------------------------------------------------------------
    fetchPage(1, /* replace */ true);

    // -------------------------------------------------------------------------
    // Load older comments (pagination)
    // -------------------------------------------------------------------------
    loadMoreBtn?.addEventListener('click', () => {
        if (fetching || !hasMorePages) return;
        fetchPage(currentPage + 1, /* replace */ false);
    });

    // -------------------------------------------------------------------------
    // Submit new comment
    // -------------------------------------------------------------------------
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();

        submitBtn.disabled = true;
        submitBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Adding…';

        const data = {
            author_name: form.querySelector('[name="author_name"]').value,
            body:        form.querySelector('[name="body"]').value,
        };

        try {
            const result = await post(storeUrl, data);

            // Remove the empty-state placeholder if the list had no comments yet
            list.querySelector('.comment-empty')?.remove();

            // Prepend — newest-first order is preserved
            list.insertAdjacentHTML('afterbegin', result.html);

            // Clear form and return focus so a follow-up comment is easy
            form.reset();
            form.querySelector('[name="author_name"]').focus();

        } catch (err) {
            if (err instanceof ValidationError) {
                paintErrors(err.errors);
            } else {
                alert(`Could not post comment: ${err.message}`);
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Add Comment';
        }
    });

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    async function fetchPage(page, replace) {
        if (fetching) return;
        fetching = true;
        if (loadMoreBtn) loadMoreBtn.disabled = true;

        try {
            const result = await get(`${indexUrl}?page=${page}`);

            if (replace) {
                list.innerHTML = result.html;
            } else {
                // Append older comments below the already-visible ones
                list.insertAdjacentHTML('beforeend', result.html);
            }

            currentPage  = result.pagination.current_page;
            hasMorePages = result.pagination.has_more_pages;

            if (hasMorePages) {
                loadMoreContainer?.classList.remove('d-none');
                if (loadMoreBtn) loadMoreBtn.disabled = false;
            } else {
                loadMoreContainer?.classList.add('d-none');
            }
        } catch (err) {
            if (replace) {
                list.innerHTML =
                    '<div class="list-group-item text-danger small py-3">Failed to load comments.</div>';
            }
            // On a load-more failure the already-visible comments remain intact
        } finally {
            fetching = false;
        }
    }

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
            el.classList.remove('d-block');
        });
    }

    function paintErrors(errors) {
        for (const [field, messages] of Object.entries(errors)) {
            form.querySelector(`[name="${field}"]`)?.classList.add('is-invalid');

            const feedback = document.getElementById(`error-${field}`);
            if (feedback) {
                feedback.textContent = messages[0];
                feedback.classList.add('d-block');
            }
        }
        // Move focus to the first invalid field so keyboard users land in the right place
        form.querySelector('.is-invalid')?.focus();
    }
});
