import { reorderSibling, treeOrder } from '../../utils/canvas/treeOrder.js';

export default function tree() {
    let sorter = null;
    return {
        get treeRows() { return treeOrder(this.blocks); },

        moveSibling(ref, direction) {
            const block = this.blocks.find((item) => item.ref === ref);
            if (!block) return;
            const siblings = this.treeRows.filter((item) => item.parent_ref === block.parent_ref);
            const target = siblings[siblings.findIndex((item) => item.ref === ref) + direction];
            if (target) this.moveBlock(ref, target.ref);
        },

        moveBlock(ref, targetRef) {
            const reordered = reorderSibling(this.blocks, ref, targetRef);
            if (reordered === this.blocks) return;
            this.blocks = reordered;
            this.touch();
            this.refreshPreview();
        },

        startSorting() {
            const list = this.$refs.blockList;
            if (!list || typeof window.Sortable !== 'function') return;
            sorter?.destroy();
            sorter = window.Sortable.create(list, {
                handle: '[data-drag-handle]',
                draggable: '[data-block-ref]',
                animation: 150,
                onMove: (event) => event.dragged.dataset.parentRef === event.related.dataset.parentRef,
                onEnd: (event) => {
                    const rows = this.treeRows;
                    const ref = event.item.dataset.blockRef;
                    const targetRef = rows[event.newDraggableIndex]?.ref;
                    // Restore the DOM before Alpine applies its keyed list update.
                    const children = Array.from(event.from.children).filter((child) => child.matches('[data-block-ref]') && child !== event.item);
                    event.from.insertBefore(event.item, children[event.oldDraggableIndex] || null);
                    this.moveBlock(ref, targetRef);
                },
            });
        },

        stopSorting() { sorter?.destroy(); sorter = null; },
    };
}
