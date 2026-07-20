import { beforeEach, describe, expect, it, vi } from 'vitest';
import { langTabs } from './langTabs.js';

class FakeInput {
    constructor(value = '') {
        this.value = value;
        this.events = [];
    }

    dispatchEvent(event) {
        this.events.push(event.type);
    }
}

globalThis.HTMLInputElement = FakeInput;
globalThis.HTMLTextAreaElement = FakeInput;
globalThis.confirm = () => false;
globalThis.window = globalThis;

describe('langTabs copyDefaultToAll', () => {
    beforeEach(() => {
        vi.restoreAllMocks();
    });

    it('delegates to the shared translationCopy utility (full coverage lives in translationCopy.test.js)', () => {
        const source = new FakeInput('Base');
        const target = new FakeInput('Existing');
        globalThis.document = {
            querySelectorAll: vi.fn((selector) => (selector === '#source' ? [source] : [target])),
        };
        vi.spyOn(globalThis, 'confirm').mockReturnValue(true);

        const result = langTabs().copyDefaultToAll([{ source: '#source', targets: ['#target'] }], 'Confirmar');

        expect(result).toBe(true);
        expect(target.value).toBe('Base');
    });
});
