import { beforeEach, describe, expect, it } from 'vitest';
import { focusRow } from './focusRow.js';

describe('canvas rail focus', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <ul>
                <li data-block-ref="id_1"><button aria-label="First">First</button></li>
                <li data-block-ref="id_2"><button aria-label="Second">Second</button></li>
            </ul>
        `;
    });

    it('focuses the row it is given', () => {
        expect(focusRow('id_2')).toBe(true);
        expect(document.activeElement.getAttribute('aria-label')).toBe('Second');
    });

    it('does nothing without a row to move to', () => {
        expect(focusRow(null)).toBe(false);
        expect(focusRow('')).toBe(false);
    });

    it('does nothing when the row is not rendered', () => {
        expect(focusRow('id_missing')).toBe(false);
    });
});
