import { post, del, ValidationError } from '../lib/http.js';

const SPINNER_HTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

document.addEventListener('DOMContentLoaded', () => {
    const section = document.getElementById('members-section');

    if (!section) return;

    // Assign — form submit (delegated: form is replaced on every response)
    section.addEventListener('submit', async (e) => {
        if (!e.target.matches('#member-assign-form')) return;
        e.preventDefault();

        const form      = e.target;
        const select    = form.querySelector('[name="user_id"]');
        const submitBtn = form.querySelector('[type="submit"]');
        const userId    = select?.value;

        if (!userId) return;

        clearFormError(form, 'error-user_id', select);
        submitBtn.disabled = true;
        const originalLabel = submitBtn.innerHTML;
        submitBtn.innerHTML = SPINNER_HTML;

        try {
            const result = await post(form.dataset.storeUrl, { user_id: parseInt(userId, 10) });
            section.innerHTML = result.html;
        } catch (err) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalLabel;

            if (err instanceof ValidationError) {
                const message = err.errors.user_id?.[0] ?? 'Invalid selection.';
                paintFormError(form, 'error-user_id', select, message);
            } else {
                alert(`Could not assign member: ${err.message}`);
            }
        }
    });

    // Unassign — "×" click (delegated for same reason)
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
            alert(`Could not remove member: ${err.message}`);
        }
    });
});

function paintFormError(form, feedbackId, field, message) {
    field?.classList.add('is-invalid');
    const feedback = form.querySelector(`#${feedbackId}`);
    if (feedback) {
        feedback.textContent = message;
        feedback.classList.remove('d-none');
    }
}

function clearFormError(form, feedbackId, field) {
    field?.classList.remove('is-invalid');
    const feedback = form.querySelector(`#${feedbackId}`);
    if (feedback) {
        feedback.textContent = '';
        feedback.classList.add('d-none');
    }
}
