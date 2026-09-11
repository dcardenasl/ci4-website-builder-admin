/**
 * Focus for the block rail.
 *
 * Removing a block destroys the control that had focus, and the browser then
 * drops focus on <body>: a keyboard user loses their place in the list. The
 * fix has to happen *before* the list changes, while the row that will survive
 * is still on screen — focusing after the fact means racing Alpine's repaint,
 * which lands or does not depending on the machine.
 */
export function focusRow(ref) {
    if (!ref || typeof document === 'undefined') {
        return false;
    }

    const target = document.querySelector(`[data-block-ref="${ref}"] button`);
    if (!(target instanceof HTMLElement)) {
        return false;
    }

    target.focus();

    return document.activeElement === target;
}
