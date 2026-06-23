export function paintFormError(form, feedbackId, field, message) {
    field?.classList.add('is-invalid');
    const feedback = form.querySelector(`#${feedbackId}`);
    if (feedback) {
        feedback.textContent = message;
        feedback.classList.remove('d-none');
    }
}

export function clearFormError(form, feedbackId, field) {
    field?.classList.remove('is-invalid');
    const feedback = form.querySelector(`#${feedbackId}`);
    if (feedback) {
        feedback.textContent = '';
        feedback.classList.add('d-none');
    }
}
