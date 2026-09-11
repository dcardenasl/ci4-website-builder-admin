import { expect, it, vi } from 'vitest';
import canvasEditor from './index.js';
import { toSwatch } from './props.js';

it('gives the colour input a value it accepts, whatever colour syntax the field holds', () => {
    // The catalog's own overlay default is rgba(); the browser rejected it.
    expect(toSwatch('rgba(15, 23, 42, 0.4)')).toBe('#0f172a');
    expect(toSwatch('rgb(255, 255, 255)')).toBe('#ffffff');
    expect(toSwatch('#ABC')).toBe('#aabbcc');
    expect(toSwatch('#FF8800')).toBe('#ff8800');
    expect(toSwatch('')).toBe('#000000');
    expect(toSwatch('var(--brand)')).toBe('#000000');
});

it('keeps asynchronous field edits attached to their original block and language', () => {
    const editor = canvasEditor();
    editor.applyDocument({ locales: [{ code: 'es', is_default: true }, { code: 'en' }], catalog: [
        { block_key: 'text', fields: { body: { type: 'richtext' } } },
    ], blocks: [1, 2].map((instance_id) => ({ instance_id, block_key: 'text', i18n: { es: {}, en: {} } })) });
    editor.touch = vi.fn();
    editor.refreshPreview = vi.fn();
    editor.selectedRef = 'id_1';
    editor.activeLocale = 'es';
    const field = editor.fieldGroups[0].fields[0];
    editor.selectedRef = 'id_2';
    editor.activeLocale = 'en';
    editor.edit(field, '<p>Original destination</p>');
    expect(editor.blocks[0].i18n.es.body).toBe('<p>Original destination</p>');
    expect(editor.blocks[1].i18n.en).toEqual({});
    editor.blocks.shift();
    editor.edit(field, 'Deleted destination');
    expect(editor.touch).toHaveBeenCalledTimes(1);
});
