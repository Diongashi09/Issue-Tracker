import { post, del } from '../lib/http.js';

document.addEventListener('DOMContentLoaded', () => {
    const section = document.getElementById('tags-section');

    // Guard: only activate on the issue show page
    if (!section) return;

    // -------------------------------------------------------------------------
    // Attach tag — "Add" form submit (delegated because the form is re-rendered
    // on every response and the original DOM node is replaced each time)
    // -------------------------------------------------------------------------
    section.addEventListener('submit', async (e) => {
        if (!e.target.matches('#tag-attach-form')) return;
        e.preventDefault();

        const form      = e.target;
        const tagId     = form.querySelector('[name="tag_id"]').value;
        const submitBtn = form.querySelector('[type="submit"]');

        if (!tagId) return;

        submitBtn.disabled = true;

        try {
            const result = await post(form.dataset.storeUrl, { tag_id: parseInt(tagId, 10) });
            section.innerHTML = result.html;
        } catch (err) {
            // Re-enable on error so the user can retry; on success the whole
            // section is replaced so the button no longer exists anyway.
            submitBtn.disabled = false;
            // Phase 6 replaces this with a toast
            alert(`Could not attach tag: ${err.message}`);
        }
    });

    // -------------------------------------------------------------------------
    // Detach tag — "×" button click (delegated for same reason as above)
    // -------------------------------------------------------------------------
    section.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-destroy-url]');
        if (!btn) return;

        btn.disabled = true;

        try {
            const result = await del(btn.dataset.destroyUrl);
            section.innerHTML = result.html;
        } catch (err) {
            btn.disabled = false;
            alert(`Could not remove tag: ${err.message}`);
        }
    });
});
