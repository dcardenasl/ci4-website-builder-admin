/**
 * Turns two snapshots of the draft into the smallest batch the save endpoint
 * accepts. Sending only what changed keeps an autosave cheap and makes a
 * rejected operation point at the edit that caused it.
 */

import { changedFields } from './reconcileDraft.js';

function identity(block) {
    return block.instance_id ? { instance_id: block.instance_id } : { temp_id: block.ref };
}

function parentFields(block) {
    if (!block.parent_ref) {
        return { parent_instance_id: null };
    }

    return block.parent_ref.startsWith('id_')
        ? { parent_instance_id: Number(block.parent_ref.slice(3)) }
        : { parent_temp_id: block.parent_ref };
}

export function buildOps(baseline, draft) {
    const ops = [];
    const before = new Map(baseline.map((block) => [block.ref, block]));
    const after = new Map(draft.map((block) => [block.ref, block]));

    draft.forEach((block) => {
        const previous = before.get(block.ref);

        if (!previous) {
            ops.push({
                op: 'upsert',
                ...identity(block),
                block_key: block.block_key,
                ...parentFields(block),
                sort_order: block.sort_order,
                config: block.config,
                i18n: block.i18n,
            });

            return;
        }

        const op = { op: 'upsert', ...identity(block) };
        let changed = false;

        const config = changedFields(previous.config, block.config);
        if (Object.keys(config).length) {
            op.config = config;
            changed = true;
        }
        const i18n = {};
        Object.entries(block.i18n).forEach(([locale, fields]) => {
            const changes = changedFields(previous.i18n[locale], fields);
            if (Object.keys(changes).length) i18n[locale] = changes;
        });
        if (Object.keys(i18n).length) {
            op.i18n = i18n;
            changed = true;
        }

        if (changed) {
            ops.push(op);
        }
    });

    baseline.forEach((block) => {
        // The server cascades a deletion through its descendants. Sending those
        // descendants again would make an otherwise valid batch fail.
        if (!after.has(block.ref) && (!block.parent_ref || after.has(block.parent_ref))) {
            ops.push({ op: 'delete', ...identity(block) });
        }
    });

    const orderChanged = draft.length !== baseline.length
        || draft.some((block, index) => baseline[index] === undefined
            || baseline[index].ref !== block.ref
            || baseline[index].parent_ref !== block.parent_ref);

    if (orderChanged && draft.length > 0) {
        const positions = new Map();
        ops.push({
            op: 'reorder',
            items: draft.map((block) => {
                const position = (positions.get(block.parent_ref) || 0) + 1;
                positions.set(block.parent_ref, position);
                return { ...identity(block), ...parentFields(block), sort_order: position };
            }),
        });
    }

    return ops;
}
