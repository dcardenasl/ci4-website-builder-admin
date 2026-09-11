import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import canvasEditor from './index.js';
import { buildOps } from '../../utils/canvas/patchBuilder.js';

function editor() {
    const component = canvasEditor();
    component.applyDocument({ version: 'v1', locales: [{ code: 'es' }], blocks: [
        { instance_id: 1, block_key: 'container', sort_order: 1 },
        { instance_id: 2, block_key: 'text', sort_order: 1, parent_instance_id: 1 },
        { instance_id: 3, block_key: 'text', sort_order: 2 },
    ] });
    component.refreshPreview = vi.fn();
    return component;
}

beforeEach(() => { vi.useFakeTimers(); vi.stubGlobal('fetch', vi.fn()); });
afterEach(() => { vi.useRealTimers(); vi.unstubAllGlobals(); });

describe('canvas subtree removal', () => {
    it('retains the whole subtree for undo and never sends a delete before the deadline', async () => {
        const component = editor();
        component.removeBlock('id_1');
        expect(component.blocks.map((block) => block.ref)).toEqual(['id_3']);
        expect(buildOps(component.baseline, component.blocks).filter((op) => op.op === 'delete'))
            .toEqual([{ op: 'delete', instance_id: 1 }]);
        await vi.advanceTimersByTimeAsync(4999);
        expect(fetch).not.toHaveBeenCalled();
        component.undoRemove();
        await vi.advanceTimersByTimeAsync(1000);
        expect(component.blocks.map((block) => block.ref)).toEqual(['id_1', 'id_2', 'id_3']);
        expect(fetch).not.toHaveBeenCalled();
        expect(component.status).toBe('saved');
    });

    it('saves only after undo expires and does not offer a stale restore', async () => {
        const component = editor();
        fetch.mockResolvedValue({ ok: true, status: 200, json: async () => ({ ok: true, data: { document: {
            version: 'v2', locales: [{ code: 'es' }], blocks: [{ instance_id: 3, block_key: 'text', sort_order: 1 }],
        } } }) });
        component.removeBlock('id_1');
        await vi.advanceTimersByTimeAsync(5000);
        expect(fetch).toHaveBeenCalledTimes(1);
        component.undoRemove();
        expect(component.blocks.map((block) => block.ref)).toEqual(['id_3']);
        expect(component.undo.removed).toEqual([]);
    });

    it('refuses to remove an ancestor of a protected block', () => {
        const component = editor();
        component.blocks[1].locked = true;
        component.removeBlock('id_1');
        expect(component.blocks).toHaveLength(3);
        expect(component.status).toBe('saved');
    });

    it('adopts IDs in an undo snapshot when an earlier create finishes', () => {
        const component = editor();
        component.blocks = component.blocks.slice(0, 2).map((block, index) => ({
            ...block, instance_id: null, ref: `tmp_${index}`, temp_id: `tmp_${index}`, parent_ref: index ? 'tmp_0' : null,
        }));
        component.removeBlock('tmp_0');
        component.adoptIds({ tmp_0: 11, tmp_1: 12 });
        component.undoRemove();
        expect(component.blocks.map((block) => block.ref)).toEqual(['id_11', 'id_12']);
        expect(component.blocks[1].parent_ref).toBe('id_11');
    });

    it('keeps the latest undo window open after successive removals', async () => {
        const component = editor();
        component.removeBlock('id_1');
        await vi.advanceTimersByTimeAsync(4000);
        component.removeBlock('id_3');
        await vi.advanceTimersByTimeAsync(2000);
        expect(fetch).not.toHaveBeenCalled();
        component.undoRemove();
        expect(component.blocks.map((block) => block.ref)).toEqual(['id_3']);
    });
});

it('adds only allowed children to the requested container', () => {
    const component = editor();
    component.catalog = [
        { block_key: 'container', is_container: true, allowed_children: ['text'] },
        { block_key: 'text' },
        { block_key: 'unrelated' },
    ];
    component.openCatalog('id_1');
    expect(component.availableTypes.map((type) => type.block_key)).toEqual(['text']);
    component.addBlock('unrelated');
    expect(component.blocks).toHaveLength(3);
    component.addBlock('text');
    expect(component.blocks.at(-1).parent_ref).toBe('id_1');
    expect(component.treeRows.at(-2).depth).toBe(1);
});
