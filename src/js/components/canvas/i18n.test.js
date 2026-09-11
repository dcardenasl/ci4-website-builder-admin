import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import i18n, { isTranslatableField } from './i18n.js';

const strings = {
    copyFrom: 'Copy from %s',
    translateAllHelp: 'Translate text from %s into the other active languages.',
    translationDefault: 'Default',
    translationMissing: '%s missing',
    translationComplete: 'Complete',
    translationNoText: 'No text fields',
    translationOverwriteConfirm: 'Replace?',
    translationNothingToDo: 'Nothing to do',
    translationDone: 'Done',
    translationFailed: 'Failed',
};

function editor() {
    const component = i18n();
    component.t = (key) => strings[key] || key;
    component.locales = [
        { code: 'es', name: 'Español', is_default: true },
        { code: 'en', name: 'English', is_default: false },
    ];
    component.catalog = [{
        block_key: 'hero',
        fields: {
            title: { type: 'text' },
            body: { type: 'richtext' },
            target: { type: 'url' },
        },
    }];
    component.typeOf = (key) => component.catalog.find((type) => type.block_key === key);
    component.blocks = [{
        block_key: 'hero',
        i18n: {
            es: { title: 'Hola', body: '<p>Contenido</p>', target: '/reservar' },
            en: { title: '', body: '<p>Existing</p>', target: '' },
        },
    }];
    component.endpoints = { translate: '/admin/cms/translate' };
    component.touch = vi.fn();
    component.refreshPreview = vi.fn();
    return component;
}

beforeEach(() => {
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
        ok: true,
        status: 200,
        json: async () => ({ translated: 'Translated' }),
    }));
    window.confirm = vi.fn().mockReturnValue(true);
});

afterEach(() => {
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
});

describe('canvas translation workflow', () => {
    it('limits automatic translation to text-like schema fields', () => {
        expect(isTranslatableField({ type: 'text' })).toBe(true);
        expect(isTranslatableField({ type: 'richtext' })).toBe(true);
        expect(isTranslatableField({ type: 'url' })).toBe(false);
        expect(isTranslatableField({ type: 'media_reference' })).toBe(false);
    });

    it('reports missing text and skips fields that are already translated', () => {
        const component = editor();

        expect(component.translationStatsFor('en')).toEqual({ total: 2, translated: 1, missing: 1 });
        expect(component.translationJobs()).toHaveLength(1);
        expect(component.translationJobs()[0]).toMatchObject({ locale: 'en', key: 'title', text: 'Hola' });
    });

    it('allows the editor to deselect every secondary language', () => {
        const component = editor();
        component.translationSelectionInitialized = true;
        component.translationSelectedLocales = [];

        expect(component.translationJobs()).toEqual([]);
    });

    it('translates every missing field, updates the draft, and schedules one save', async () => {
        const component = editor();

        await component.translateAll();

        expect(fetch).toHaveBeenCalledTimes(1);
        expect(fetch.mock.calls[0][0].searchParams.get('source_lang')).toBe('ES');
        expect(fetch.mock.calls[0][0].searchParams.get('target_lang')).toBe('EN');
        expect(component.blocks[0].i18n.en.title).toBe('Translated');
        expect(component.touch).toHaveBeenCalledTimes(1);
        expect(component.refreshPreview).toHaveBeenCalledTimes(1);
        expect(component.translationSuccess).toBe('Done');
    });

    it('can explicitly retranslate existing values after confirmation', async () => {
        const component = editor();
        component.translationOverwrite = true;

        await component.translateAll();

        expect(window.confirm).toHaveBeenCalledWith('Replace?');
        expect(fetch).toHaveBeenCalledTimes(2);
        expect(component.blocks[0].i18n.en).toMatchObject({ title: 'Translated', body: 'Translated' });
    });
});
