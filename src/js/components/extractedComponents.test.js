import { afterEach, describe, expect, it, vi } from 'vitest';
import { blockInstanceBuilder } from './blockInstanceBuilder.js';
import { bootConfirmAction } from './confirmAction.js';
import { collectionBlockTemplateBuilder } from './collectionBlockTemplateBuilder.js';
import { menuItemForm } from './menuItemForm.js';

afterEach(() => {
    delete globalThis.document;
    delete globalThis.window;
    vi.restoreAllMocks();
});

describe('extracted Alpine components', () => {
    it('initializes the block instance builder from the default language', () => {
        globalThis.window = {};
        const builder = blockInstanceBuilder(
            [{ id: 4, block_key: 'hero', schema_definition: { fields: {}, config_fields: {} } }],
            [{ id: 1, code: 'es', is_default: 1 }],
        );

        builder.init();

        expect(builder.activeLangId).toBe(1);
        expect(builder.selectedBlockType).toBeNull();
    });

    it('serializes a collection template using the extracted builder', () => {
        const builder = collectionBlockTemplateBuilder([], null, [], null, {}, {
            blockFallback: 'Bloque',
            presetConfirm: 'Confirmar',
        });
        builder.$refs = {};

        builder.init();

        expect(JSON.parse(builder.json)).toEqual({ version: '1.0', blocks: [] });
        expect(builder.valid).toBe(false);
    });

    it('autofills menu item translations using the supplied language rows', () => {
        const inputs = new Map([
            ['input[name=\'translations[0][custom_url]\']', { value: '' }],
            ['input[name=\'translations[0][label]\']', { value: '' }],
        ]);
        globalThis.document = { querySelector: selector => inputs.get(selector) };
        const form = menuItemForm('custom_url', [{ id: 10, code: 'es' }]);
        form.categoryOptions = [{
            id: 20,
            key: 'news',
            translations: { 10: { slug: 'noticias' } },
            categories: [{ id: 30, translations: { 10: { slug: 'actualidad', name: 'Actualidad' } } }],
        }];
        form.selectedColId = '20';

        form.onCollectionChange();
        form.selectedCatId = '30';
        form.onCategoryChange();

        expect(inputs.get("input[name='translations[0][custom_url]']").value).toBe('/noticias?category=actualidad');
        expect(inputs.get("input[name='translations[0][label]']").value).toBe('Actualidad');
    });

    it('cancels data-confirm-message actions when confirmation is rejected', () => {
        const listeners = [];
        globalThis.document = { addEventListener: (_type, listener) => listeners.push(listener) };
        globalThis.window = { confirm: vi.fn().mockReturnValue(false) };
        bootConfirmAction();
        const button = { dataset: { confirmMessage: 'Confirmar' } };
        const event = {
            target: { closest: () => button },
            preventDefault: vi.fn(),
            stopImmediatePropagation: vi.fn(),
        };
        listeners[0](event);

        expect(event.preventDefault).toHaveBeenCalledOnce();
        expect(event.stopImmediatePropagation).toHaveBeenCalledOnce();
        expect(window.confirm).toHaveBeenCalledWith('Confirmar');
    });
});
