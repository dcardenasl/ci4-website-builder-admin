import { newTempId } from '../../utils/canvas/draftState.js';
import { focusRow } from '../../utils/canvas/focusRow.js';

/** Adding, removing, selecting — and the five seconds in which a removal is reversible. */
export default function blocks() {
    return {
        selectedRef: null,
        mobilePanel: 'blocks',
        catalogOpen: false,
        catalogParent: null,

        get availableTypes() {
            const parent = this.blocks.find((block) => block.ref === this.catalogParent);
            if (this.catalogParent && !parent) return [];
            return parent ? this.catalog.filter((type) => (this.typeOf(parent.block_key)?.allowed_children || []).includes(type.block_key)) : this.catalog;
        },

        openCatalog(parentRef = null) {
            this.catalogParent = parentRef;
            this.catalogOpen = true;
        },
        undo: { visible: false, message: '', removed: [], timer: null },

        get selected() {
            return this.blocks.find((block) => block.ref === this.selectedRef) || null;
        },

        select(ref) {
            this.selectedRef = ref;
            this.mobilePanel = 'properties';
            this.sendToFrame('canvas:select', { ref });
            this.sendToFrame('canvas:scrollTo', { ref });
        },

        addBlock(blockKey) {
            const type = this.availableTypes.find((item) => item.block_key === blockKey);
            if (!type) {
                return;
            }

            const i18n = {};
            this.locales.forEach((locale) => {
                i18n[locale.code] = {};
            });

            const block = {
                ref: newTempId(),
                instance_id: null,
                temp_id: null,
                block_key: blockKey,
                parent_ref: this.catalogParent,
                sort_order: Math.max(0, ...this.blocks.filter((item) => item.parent_ref === this.catalogParent).map((item) => item.sort_order)) + 1,
                config: {},
                i18n,
                locked: false,
                required: false,
            };
            block.temp_id = block.ref;

            this.blocks.push(block);
            this.catalogOpen = false;
            this.selectedRef = block.ref;
            this.mobilePanel = 'properties';
            this.touch();
            this.refreshPreview();
        },

        /**
         * Removed at once with an undo notice rather than behind a blocking
         * dialog: the action is already easy to reverse.
         */
        removeBlock(ref) {
            const index = this.blocks.findIndex((block) => block.ref === ref);
            if (index === -1) {
                return;
            }

            const block = this.blocks[index];
            const refs = new Set([ref]);
            // Include every descendant even when the server list is not in tree order.
            let size;
            do {
                size = refs.size;
                this.blocks.forEach((item) => {
                    if (refs.has(item.parent_ref)) refs.add(item.ref);
                });
            } while (refs.size !== size);
            const removed = this.blocks.flatMap((item, position) => refs.has(item.ref) ? [{ block: item, index: position }] : []);
            if (removed.some(({ block: item }) => item.locked || item.required)) return;

            const survivors = this.blocks.filter((item) => !refs.has(item.ref));
            const nextSelected = refs.has(this.selectedRef)
                ? (survivors[Math.min(index, survivors.length - 1)] || null)
                : this.blocks.find((item) => item.ref === this.selectedRef) || null;

            // Deleting the row destroys the control holding focus, which leaves
            // it on <body> — a keyboard user loses their place in the list. The
            // surviving row is already rendered and keeps its node across the
            // update, so handing focus over before the mutation is the one
            // moment that needs no guessing about when Alpine repaints.
            focusRow(nextSelected ? nextSelected.ref : null);

            this.blocks = survivors;
            if (refs.has(this.selectedRef)) {
                this.selectedRef = nextSelected ? nextSelected.ref : null;
            }

            this.showUndo(block, removed);
            this.touch();
            this.refreshPreview();
        },

        showUndo(block, removed) {
            clearTimeout(this.undo.timer);
            this.undo = {
                visible: true,
                message: `${this.t('blockRemoved')}: ${this.blockName(block)}`,
                removed,
                timer: setTimeout(() => {
                    this.undo.visible = false;
                    this.undo.removed = [];
                    void this.save();
                }, 5000),
            };
        },

        undoRemove() {
            if (!this.undo.visible) return;
            clearTimeout(this.undo.timer);
            this.undo.removed.forEach(({ block, index }) => this.blocks.splice(index, 0, block));
            this.selectedRef = this.undo.removed[0]?.block.ref || this.selectedRef;
            this.undo.visible = false;
            this.undo.removed = [];
            this.touch();
            this.refreshPreview();
        },
    };
}
