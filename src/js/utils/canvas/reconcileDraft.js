import { treeOrder } from './treeOrder.js';

/** Compare JSON values independently of object key order. */
export function sameValue(a, b) {
    if (a === b) return true;
    if (a == null || b == null || typeof a !== 'object' || typeof b !== 'object') return false;
    if (Array.isArray(a) !== Array.isArray(b)) return false;
    const keys = Object.keys(a);
    return keys.length === Object.keys(b).length && keys.every((key) => Object.hasOwn(b, key) && sameValue(a[key], b[key]));
}

export function changedFields(before = {}, after = {}) {
    return Object.fromEntries(Object.entries(after).filter(([key, value]) => !sameValue(before[key], value)));
}

export function adoptBlockIds(blocks, idMap) {
    blocks.forEach((block) => {
        if (idMap[block.ref]) {
            block.instance_id = idMap[block.ref];
            block.temp_id = null;
            block.ref = `id_${block.instance_id}`;
        }
        if (idMap[block.parent_ref]) block.parent_ref = `id_${idMap[block.parent_ref]}`;
    });
}

/** Accept canonical values only where no later local edit superseded the sent snapshot. */
export function reconcileDraft(sent, current, canonical) {
    const before = new Map(sent.map((block) => [block.ref, block]));
    const saved = new Map(canonical.map((block) => [block.ref, block]));
    return treeOrder(current.map((block) => {
        const previous = before.get(block.ref);
        const confirmed = saved.get(block.ref);
        if (!previous || !confirmed) return block;
        const i18n = { ...confirmed.i18n };
        Object.entries(block.i18n).forEach(([locale, fields]) => {
            i18n[locale] = { ...(i18n[locale] || {}), ...changedFields(previous.i18n[locale], fields) };
        });
        return {
            ...confirmed,
            ...changedFields(previous, block),
            config: { ...confirmed.config, ...changedFields(previous.config, block.config) },
            i18n,
        };
    }));
}
