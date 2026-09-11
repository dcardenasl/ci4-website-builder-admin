import { describe, expect, it } from 'vitest';
import { saveFailure } from './saveFailure.js';

describe('canvas save failure', () => {
    it('reports a lost session instead of a generic failure', () => {
        expect(saveFailure(401, { ok: false })).toEqual({ key: 'sessionExpired', detail: '' });
        expect(saveFailure(403, null)).toEqual({ key: 'sessionExpired', detail: '' });
    });

    it('reports a request that never reached the server', () => {
        expect(saveFailure(0, null)).toEqual({ key: 'networkError', detail: '' });
    });

    it('surfaces the messages the endpoint sent', () => {
        expect(saveFailure(413, { ok: false, messages: ['Payload too large.'], fieldErrors: [] }))
            .toEqual({ key: 'saveRejected', detail: 'Payload too large.' });
    });

    it('surfaces field errors, including the list shape', () => {
        const payload = { ok: false, messages: [], fieldErrors: { title: 'Required.', body: ['Too long.', 'Bad HTML.'] } };

        expect(saveFailure(422, payload)).toEqual({ key: 'saveRejected', detail: 'Required. Too long. Bad HTML.' });
    });

    it('ignores empty and non-string detail', () => {
        expect(saveFailure(422, { messages: ['', '   ', 7], fieldErrors: {} })).toEqual({ key: 'saveFailed', detail: '' });
    });

    it('distinguishes a body that was not this endpoint contract', () => {
        expect(saveFailure(500, null)).toEqual({ key: 'serverError', detail: '' });
        expect(saveFailure(500, { ok: false })).toEqual({ key: 'saveFailed', detail: '' });
    });
});
