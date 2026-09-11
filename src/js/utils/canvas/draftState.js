import { treeOrder } from './treeOrder.js';

/**
 * The canvas holds one draft in memory. Persisted blocks are addressed by their
 * database id and new ones by a temporary id the server maps back on save, so a
 * block keeps the same identity from the moment it is added.
 */

export function refOf(block) {
    if (block.instance_id) {
        return `id_${block.instance_id}`;
    }

    return block.temp_id || '';
}

export function newTempId() {
    const uuid = typeof globalThis.crypto !== 'undefined' && globalThis.crypto.randomUUID
        ? globalThis.crypto.randomUUID()
        : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            const r = (Math.random() * 16) | 0;
            return (c === 'x' ? r : ((r & 0x3) | 0x8)).toString(16);
        });

    return `tmp_${uuid}`;
}

function parentRefOf(block) {
    if (block.parent_instance_id) {
        return `id_${block.parent_instance_id}`;
    }

    return block.parent_temp_id || null;
}

/**
 * Depth of a block within the document tree, for indenting the rail. The rail is
 * a flat list — a nested block that looked like a sibling would make dragging
 * read as a move it is not.
 */
export function depthOf(ref, parents, seen = {}) {
    const parent = parents[ref];
    if (!parent || seen[ref]) {
        return 0;
    }

    return 1 + depthOf(parent, parents, { ...seen, [ref]: true });
}

/** Flattens the server document into the shape the canvas edits. */
export function normalizeDocument(document) {
    const locales = (document.locales || []).map((locale) => locale.code);
    const parents = {};
    (document.blocks || []).forEach((block) => {
        parents[refOf(block)] = parentRefOf(block);
    });

    return treeOrder((document.blocks || []).map((block) => {
        const i18n = {};
        locales.forEach((code) => {
            i18n[code] = JSON.parse(JSON.stringify((block.i18n || {})[code] || {}));
        });

        return {
            ref: refOf(block),
            depth: depthOf(refOf(block), parents),
            instance_id: block.instance_id || null,
            temp_id: block.temp_id || null,
            block_key: block.block_key,
            parent_ref: parentRefOf(block),
            sort_order: block.sort_order || 0,
            config: JSON.parse(JSON.stringify(block.config || {})),
            i18n,
            locked: block.locked === true,
            required: block.required === true,
        };
    }));
}

/** The payload the preview endpoint renders. */
export function toPreviewPayload(blocks, lang, scope) {
    return {
        lang,
        scope: scope || { type: 'document' },
        blocks: blocks.map((block) => {
            const payload = {
                block_key: block.block_key,
                sort_order: block.sort_order,
                config: block.config,
                i18n: block.i18n,
            };

            if (block.instance_id) {
                payload.instance_id = block.instance_id;
            } else {
                payload.temp_id = block.ref;
            }

            if (block.parent_ref && block.parent_ref.startsWith('id_')) {
                payload.parent_instance_id = Number(block.parent_ref.slice(3));
            } else if (block.parent_ref) {
                payload.parent_temp_id = block.parent_ref;
            }

            return payload;
        }),
    };
}

/**
 * Whether a locale has no real value for a field. The editor never shows the
 * fallback inside an input: an empty box is how the editor sees what is missing.
 */
export function isEmptyValue(value) {
    if (value === null || value === undefined) {
        return true;
    }

    return typeof value === 'string' && value.trim() === '';
}
