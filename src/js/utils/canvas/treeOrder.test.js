import { describe, expect, it } from 'vitest';
import { reorderSibling, treeOrder } from './treeOrder.js';

const blocks = [
    { ref: 'parent', parent_ref: null, sort_order: 1 },
    { ref: 'other', parent_ref: null, sort_order: 2 },
    { ref: 'child2', parent_ref: 'parent', sort_order: 2 },
    { ref: 'grandchild', parent_ref: 'child2', sort_order: 1 },
    { ref: 'child1', parent_ref: 'parent', sort_order: 1 },
];

describe('canvas tree order', () => {
    it('displays server rows in preorder with the correct nesting depth', () => {
        expect(treeOrder(blocks).map(({ ref, depth }) => [ref, depth])).toEqual([
            ['parent', 0], ['child1', 1], ['child2', 1], ['grandchild', 2], ['other', 0],
        ]);
    });
    it('moves a container and all its descendants as one subtree', () => {
        const moved = reorderSibling(blocks, 'parent', 'other');
        expect(moved.map(({ ref }) => ref)).toEqual(['other', 'parent', 'child1', 'child2', 'grandchild']);
        expect(moved.find(({ ref }) => ref === 'grandchild').parent_ref).toBe('child2');
        expect(blocks[0].sort_order).toBe(1);
    });
    it('renumbers only siblings when a nested block is moved', () => {
        const moved = reorderSibling(blocks, 'child2', 'child1');
        expect(moved.map(({ ref }) => ref)).toEqual(['parent', 'child2', 'grandchild', 'child1', 'other']);
        expect(moved.find(({ ref }) => ref === 'other').sort_order).toBe(2);
    });
    it('rejects drops across parent boundaries', () => {
        expect(reorderSibling(blocks, 'child1', 'other')).toBe(blocks);
        expect(reorderSibling(blocks, 'parent', 'child1')).toBe(blocks);
    });
    it('does not hide or loop forever on malformed input', () => {
        expect(treeOrder([{ ref: 'a', parent_ref: 'b' }, { ref: 'b', parent_ref: 'a' }])).toHaveLength(2);
    });
});
