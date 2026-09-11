import { describe, expect, it } from 'vitest';
import { buildFieldGroups, controlFor } from './schemaToFields.js';

const labels = { content: 'Contenido', design: 'Diseño' };

describe('controlFor', () => {
    it.each([
        ['text', 'text'], ['string', 'text'], ['url', 'text'],
        ['textarea', 'textarea'], ['richtext', 'richtext'], ['rich-text', 'richtext'], ['html', 'richtext'],
        ['number', 'number'], ['boolean', 'boolean'],
        ['select', 'select'], ['color', 'color'], ['media_reference', 'media'],
    ])('maps %s to the %s control', (type, control) => {
        expect(controlFor(type)).toBe(control);
    });

    it.each(['repeater', 'collection', 'mystery', undefined])(
        'refuses to guess a control for %p',
        (type) => {
            expect(controlFor(type)).toBe('unsupported');
        },
    );
});

describe('buildFieldGroups', () => {
    const type = {
        fields: { title: { type: 'text', label: 'Título' }, body: { type: 'richtext' } },
        config_fields: { accent: { type: 'color', label: 'Acento' } },
    };
    const block = {
        i18n: { es: { title: 'Hola', body: '<p>Cuerpo</p>' }, en: { title: '' } },
        config: { accent: '#8a9a6b' },
    };

    it('separates translatable content from locale-independent design', () => {
        const [content, design] = buildFieldGroups(block, type, 'es', 'es', labels);

        expect(content.fields.map((f) => f.key)).toEqual(['title', 'body']);
        expect(content.fields[0].scope).toBe('i18n');
        expect(design.fields[0]).toMatchObject({ key: 'accent', scope: 'config', control: 'color', value: '#8a9a6b' });
    });

    it('falls back to the field key when the schema declares no label', () => {
        const [content] = buildFieldGroups(block, type, 'es', 'es', labels);

        expect(content.fields[1].label).toBe('body');
    });

    it('shows the real value of the active locale, never the fallback', () => {
        const [content] = buildFieldGroups(block, type, 'en', 'es', labels);

        expect(content.fields[0].value).toBe('');
        expect(content.fields[1].value).toBe('');
    });

    it('offers to copy the source copy only where the active locale is empty', () => {
        const [content] = buildFieldGroups(block, type, 'en', 'es', labels);

        expect(content.fields[0].canCopyFallback).toBe(true);
        expect(content.fields[0].fallbackValue).toBe('Hola');
        expect(content.fields[1].canCopyFallback).toBe(true);
    });

    it('never offers to copy while editing the default locale', () => {
        const [content] = buildFieldGroups(block, type, 'es', 'es', labels);

        expect(content.fields.every((field) => field.canCopyFallback === false)).toBe(true);
    });

    it('identifies rich text independently from plain multiline fields', () => {
        const [content] = buildFieldGroups(block, type, 'es', 'es', labels);

        expect(content.fields[0].isRichText).toBe(false);
        expect(content.fields[1].isRichText).toBe(true);
    });

    it('carries the media accept hint the picker filters by', () => {
        const withMedia = {
            fields: { photo: { type: 'media_reference', accept: 'image' }, doc: { type: 'media_reference' } },
            config_fields: {},
        };
        const [content] = buildFieldGroups({ i18n: { es: {} }, config: {} }, withMedia, 'es', 'es', labels);

        expect(content.fields[0]).toMatchObject({ control: 'media', accept: 'image' });
        expect(content.fields[1].accept).toBe('image');
    });

    it('returns nothing when no block is selected', () => {
        expect(buildFieldGroups(null, type, 'es', 'es', labels)).toEqual([]);
    });
});
