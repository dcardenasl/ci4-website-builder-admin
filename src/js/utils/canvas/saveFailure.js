/**
 * A failed save has to say what to do about it. An expired session, a payload
 * the server refused and a transport failure are three different problems, and
 * only one of them is solved by pressing Retry — collapsing them into a single
 * "could not save" leaves the editor guessing with unsaved work in hand.
 *
 * Returns the key of the message to show, plus the server's own detail when it
 * sent one, so the caller stays responsible for translation.
 */

function detailsOf(payload) {
    if (payload === null || typeof payload !== 'object') {
        return [];
    }

    const messages = Array.isArray(payload.messages) ? payload.messages : [];
    const fieldErrors = payload.fieldErrors && typeof payload.fieldErrors === 'object'
        ? Object.values(payload.fieldErrors)
        : [];

    return [...messages, ...fieldErrors]
        .flat()
        .filter((entry) => typeof entry === 'string' && entry.trim() !== '')
        .map((entry) => entry.trim());
}

/**
 * @param  {number}      status  HTTP status of the response, or 0 when the request never arrived.
 * @param  {object|null} payload Decoded JSON envelope, or null when the body was not JSON.
 * @return {{key: string, detail: string}}
 */
export function saveFailure(status, payload) {
    if (status === 0) {
        return { key: 'networkError', detail: '' };
    }

    if (status === 401 || status === 403) {
        return { key: 'sessionExpired', detail: '' };
    }

    const detail = detailsOf(payload);
    if (detail.length > 0) {
        // The generic label invites a retry, which is wrong advice when the
        // server has just explained what it refused.
        return { key: 'saveRejected', detail: detail.join(' ') };
    }

    // No envelope at all: the server answered with something that is not this
    // endpoint's contract, so there is nothing specific to report.
    return { key: payload === null ? 'serverError' : 'saveFailed', detail: '' };
}
