/**
 * Typed error for Laravel 422 Unprocessable Entity responses.
 * Carries the errors bag so AJAX modules can paint inline field feedback
 * without any per-module error-shape handling.
 *
 * @property {Record<string, string[]>} errors  Laravel validation errors bag
 */
export class ValidationError extends Error {
    constructor(errors) {
        super('The given data was invalid.');
        this.name = 'ValidationError';
        this.errors = errors;
    }
}

/** Reads the CSRF token Laravel embeds in <meta name="csrf-token">. */
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/**
 * Base fetch wrapper used by every AJAX call in this app.
 *
 * - Attaches X-CSRF-TOKEN, Accept: application/json, X-Requested-With headers
 * - Parses the JSON response body
 * - Throws ValidationError on 422 (typed so callers can paint inline errors)
 * - Throws Error on any other non-OK status using Laravel's message field
 * - AbortError is NOT caught here — it propagates to the caller who intentionally
 *   cancelled the request (e.g., search/filter debounce via AbortController)
 *
 * @param {string} url
 * @param {RequestInit} [options]
 * @returns {Promise<any>}
 */
export async function http(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...options.headers,
        },
    });

    const isJson = response.headers.get('Content-Type')?.includes('application/json');
    const body = isJson ? await response.json() : null;

    if (response.status === 422) {
        throw new ValidationError(body?.errors ?? {});
    }

    if (!response.ok) {
        throw new Error(body?.message ?? `HTTP ${response.status}`);
    }

    return body;
}

/**
 * GET — used for comment pagination and search/filter.
 * Pass { signal: controller.signal } in options for AbortController support.
 *
 * @param {string} url
 * @param {RequestInit} [options]
 */
export function get(url, options = {}) {
    return http(url, { ...options, method: 'GET' });
}

/**
 * POST with a JSON body — used for creating comments and attaching tags/members.
 *
 * @param {string} url
 * @param {Record<string, any>} [data]
 * @param {RequestInit} [options]
 */
export function post(url, data = {}, options = {}) {
    return http(url, {
        ...options,
        method: 'POST',
        headers: { 'Content-Type': 'application/json', ...options.headers },
        body: JSON.stringify(data),
    });
}

/**
 * DELETE — used for detaching tags and unassigning members.
 * Named 'del' because 'delete' is a reserved word in JavaScript.
 *
 * @param {string} url
 * @param {RequestInit} [options]
 */
export function del(url, options = {}) {
    return http(url, { ...options, method: 'DELETE' });
}
