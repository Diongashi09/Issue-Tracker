import { post, del } from '../lib/http.js';

document.addEventListener('DOMContentLoaded', () => {
    const section = document.getElementById('members-section');

    if (!section) return;

    // Assign — form submit (delegated: the form is replaced on every response)
    section.addEventListener('submit', async (e) => {
        if (!e.target.matches('#member-assign-form')) return;
        e.preventDefault();

        const form      = e.target;
        const userId    = form.querySelector('[name="user_id"]').value;
        const submitBtn = form.querySelector('[type="submit"]');

        if (!userId) return;

        submitBtn.disabled = true;

        try {
            const result = await post(form.dataset.storeUrl, { user_id: parseInt(userId, 10) });
            section.innerHTML = result.html;
        } catch (err) {
            submitBtn.disabled = false;
            alert(`Could not assign member: ${err.message}`);
        }
    });

    // Unassign — "×" click (delegated for the same reason)
    section.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-destroy-url]');
        if (!btn) return;

        btn.disabled = true;

        try {
            const result = await del(btn.dataset.destroyUrl);
            section.innerHTML = result.html;
        } catch (err) {
            btn.disabled = false;
            alert(`Could not remove member: ${err.message}`);
        }
    });
});
