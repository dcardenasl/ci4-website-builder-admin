/** Stable preorder, keeping each container adjacent to all of its descendants. */
export function treeOrder(blocks) {
    const children = new Map();
    const known = new Set(blocks.map((block) => block.ref));
    blocks.forEach((block) => {
        const parent = known.has(block.parent_ref) ? block.parent_ref : null;
        if (!children.has(parent)) children.set(parent, []);
        children.get(parent).push(block);
    });
    children.forEach((siblings) => siblings.sort((a, b) => a.sort_order - b.sort_order));
    const ordered = [];
    const seen = new Set();
    const visit = (block, depth) => {
        if (seen.has(block.ref)) return;
        seen.add(block.ref);
        ordered.push({ ...block, depth });
        (children.get(block.ref) || []).forEach((child) => visit(child, depth + 1));
    };
    (children.get(null) || []).forEach((block) => visit(block, 0));
    // Invalid input remains visible; validation belongs to the server.
    blocks.forEach((block) => visit(block, 0));
    return ordered;
}

/** A rail drop reorders siblings; it never silently changes a block's parent. */
export function reorderSibling(blocks, ref, targetRef) {
    const block = blocks.find((item) => item.ref === ref);
    const target = blocks.find((item) => item.ref === targetRef);
    if (!block || !target || block === target || block.parent_ref !== target.parent_ref) return blocks;
    const siblings = treeOrder(blocks).filter((item) => item.parent_ref === block.parent_ref);
    const from = siblings.findIndex((item) => item.ref === ref);
    const to = siblings.findIndex((item) => item.ref === targetRef);
    siblings.splice(to, 0, siblings.splice(from, 1)[0]);
    const positions = new Map(siblings.map((item, index) => [item.ref, index + 1]));
    return treeOrder(blocks.map((item) => positions.has(item.ref) ? { ...item, sort_order: positions.get(item.ref) } : item));
}
