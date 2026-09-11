import { describe, expect, it } from 'vitest';
import { buildOps } from './patchBuilder.js';

const block = (over = {}) => ({
    ref: 'id_1', instance_id: 1, temp_id: null, block_key: 'hero',
    parent_ref: null, sort_order: 1, config: { accent: '#fff' }, i18n: { es: { title: 'Hola' } },
    ...over,
});

describe('buildOps', () => {
    it('sends nothing when nothing changed', () => {
        const baseline = [block()];

        expect(buildOps(baseline, [block()])).toEqual([]);
    });

    it('sends only the part of a block that changed', () => {
        const ops = buildOps([block()], [block({ i18n: { es: { title: 'Adiós' } } })]);

        expect(ops).toHaveLength(1);
        expect(ops[0]).toMatchObject({ op: 'upsert', instance_id: 1, i18n: { es: { title: 'Adiós' } } });
        expect(ops[0].config).toBeUndefined();
    });

    it('creates a new block with its type and content', () => {
        const created = block({ ref: 'tmp_a', instance_id: null, temp_id: 'tmp_a', block_key: 'copy', sort_order: 2 });
        const ops = buildOps([block()], [block(), created]);

        const upsert = ops.find((op) => op.temp_id === 'tmp_a');
        expect(upsert).toMatchObject({ op: 'upsert', block_key: 'copy', parent_instance_id: null });
    });

    it('deletes a block that is no longer in the draft', () => {
        const ops = buildOps([block(), block({ ref: 'id_2', instance_id: 2 })], [block()]);

        expect(ops).toContainEqual({ op: 'delete', instance_id: 2 });
    });

    it('renumbers positions when the order changes', () => {
        const second = block({ ref: 'id_2', instance_id: 2, sort_order: 2 });
        const ops = buildOps([block(), second], [second, block()]);

        const reorder = ops.find((op) => op.op === 'reorder');
        expect(reorder.items).toEqual([
            { instance_id: 2, parent_instance_id: null, sort_order: 1 },
            { instance_id: 1, parent_instance_id: null, sort_order: 2 },
        ]);
    });

    it('reorders when a block is moved into a container', () => {
        const child = block({ ref: 'id_2', instance_id: 2, sort_order: 2 });
        const moved = { ...child, parent_ref: 'id_1' };
        const reorder = buildOps([block(), child], [block(), moved]).find((op) => op.op === 'reorder');

        expect(reorder.items[1]).toMatchObject({ instance_id: 2, parent_instance_id: 1 });
    });

    it('does not emit a reorder for an empty draft', () => {
        const ops = buildOps([block()], []);

        expect(ops).toEqual([{ op: 'delete', instance_id: 1 }]);
    });
});
