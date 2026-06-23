import { post, del, ValidationError } from '../lib/http.js';
import { paintFormError, clearFormError } from '../lib/form-errors.js';

const SPINNER_HTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

document.addEventListener('DOMContentLoaded', () => {
    const section = document.getElementById('tags-section');

    if (!section) return;

    // Attach tag — form submit (delegated: form is replaced on every response)
    section.addEventListener('submit', async (e) => {
        if (!e.target.matches('#tag-attach-form')) return;
        e.preventDefault();

        const form      = e.target;
        const select    = form.querySelector('[name="tag_id"]');
        const submitBtn = form.querySelector('[type="submit"]');
        const tagId     = select?.value;

        if (!tagId) return;

        clearFormError(form, 'error-tag_id', select);
        submitBtn.disabled = true;
        const originalLabel = submitBtn.innerHTML;
        submitBtn.innerHTML = SPINNER_HTML;

        try {
            const result = await post(form.dataset.storeUrl, { tag_id: parseInt(tagId, 10) });
            section.innerHTML = result.html;
        } catch (err) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalLabel;

            if (err instanceof ValidationError) {
                const message = err.errors.tag_id?.[0] ?? 'Invalid selection.';
                paintFormError(form, 'error-tag_id', select, message);
            } else {
                alert(`Could not attach tag: ${err.message}`);
            }
        }
    });

    // Detach tag — "×" button click (delegated for same reason)
    section.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-destroy-url]');
        if (!btn) return;

        btn.disabled = true;
        const originalLabel = btn.innerHTML;
        btn.innerHTML = SPINNER_HTML;

        try {
            const result = await del(btn.dataset.destroyUrl);
            section.innerHTML = result.html;
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = originalLabel;
            alert(`Could not remove tag: ${err.message}`);
        }
    });
});
