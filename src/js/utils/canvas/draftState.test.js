import { describe, expect, it } from 'vitest';
import { depthOf, isEmptyValue, newTempId, normalizeDocument, refOf, toPreviewPayload } from './draftState.js';

describe('refOf', () => {
    it('addresses a persisted block by its database id', () => {
        expect(refOf({ instance_id: 12 })).toBe('id_12');
    });

    it('keeps the temporary id of a block that was never saved', () => {
        expect(refOf({ instance_id: null, temp_id: 'tmp_abc' })).toBe('tmp_abc');
    });
});

describe('newTempId', () => {
    it('produces the tmp_ + uuid shape the server validates', () => {
        expect(newTempId()).toMatch(/^tmp_[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/);
    });

    it('never repeats an id within a session', () => {
        const ids = new Set(Array.from({ length: 200 }, () => newTempId()));
        expect(ids.size).toBe(200);
    });
});

describe('normalizeDocument', () => {
    const document = {
        locales: [{ code: 'es' }, { code: 'en' }],
        blocks: [
            { instance_id: 1, block_key: 'hero', sort_order: 1, config: { accent: '#fff' }, i18n: { es: { title: 'Hola' } }, locked: true, required: true },
            { instance_id: 2, block_key: 'copy', sort_order: 2, parent_instance_id: 1, i18n: {} },
        ],
    };

    it('gives every declared locale a bucket so an edit never creates one by accident', () => {
        const blocks = normalizeDocument(document);

        expect(Object.keys(blocks[0].i18n)).toEqual(['es', 'en']);
        expect(blocks[0].i18n.en).toEqual({});
    });

    it('carries template protection and parent links through', () => {
        const blocks = normalizeDocument(document);

        expect(blocks[0].locked).toBe(true);
        expect(blocks[0].required).toBe(true);
        expect(blocks[1].parent_ref).toBe('id_1');
    });

    it('reports nesting depth so the rail can indent a child', () => {
        const blocks = normalizeDocument(document);

        expect(blocks[0].depth).toBe(0);
        expect(blocks[1].depth).toBe(1);
    });

    it('copies values instead of aliasing the server payload', () => {
        const blocks = normalizeDocument(document);
        blocks[0].config.accent = '#000';

        expect(document.blocks[0].config.accent).toBe('#fff');
    });
});

describe('depthOf', () => {
    const parents = { id_1: null, id_2: 'id_1', id_3: 'id_2' };

    it('places a root block at depth zero', () => {
        expect(depthOf('id_1', parents)).toBe(0);
    });

    it('counts each level of nesting', () => {
        expect(depthOf('id_2', parents)).toBe(1);
        expect(depthOf('id_3', parents)).toBe(2);
    });

    it('stops instead of recursing forever on a parent cycle', () => {
        expect(depthOf('a', { a: 'b', b: 'a' })).toBe(2);
    });

    it('treats an unknown parent as a root', () => {
        expect(depthOf('id_9', parents)).toBe(0);
    });
});

describe('toPreviewPayload', () => {
    it('sends persisted blocks by id and new ones by temporary id', () => {
        const payload = toPreviewPayload(
            [
                { ref: 'id_1', instance_id: 1, block_key: 'hero', sort_order: 1, config: {}, i18n: {}, parent_ref: null },
                { ref: 'tmp_a', instance_id: null, block_key: 'copy', sort_order: 2, config: {}, i18n: {}, parent_ref: 'id_1' },
            ],
            'es',
        );

        expect(payload.blocks[0].instance_id).toBe(1);
        expect(payload.blocks[0].temp_id).toBeUndefined();
        expect(payload.blocks[1].temp_id).toBe('tmp_a');
        expect(payload.blocks[1].parent_instance_id).toBe(1);
        expect(payload.scope).toEqual({ type: 'document' });
    });

    it('passes a block scope through untouched', () => {
        const payload = toPreviewPayload([], 'en', { type: 'block', ref: 'id_9' });

        expect(payload).toMatchObject({ lang: 'en', scope: { type: 'block', ref: 'id_9' } });
    });
});

describe('isEmptyValue', () => {
    it.each([null, undefined, '', '   '])('treats %p as untranslated', (value) => {
        expect(isEmptyValue(value)).toBe(true);
    });

    it.each([0, false, 'x'])('treats %p as a real value', (value) => {
        expect(isEmptyValue(value)).toBe(false);
    });
});
