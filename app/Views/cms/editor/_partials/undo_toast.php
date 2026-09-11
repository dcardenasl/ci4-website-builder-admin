<?php declare(strict_types=1); ?>
<div x-show="undo.visible" x-transition
     class="fixed bottom-6 left-1/2 z-40 flex -translate-x-1/2 items-center gap-4 rounded-lg bg-gray-900 px-4 py-2 text-sm text-white shadow-lg"
     role="status" aria-live="polite">
    <span x-text="undo.message"></span>
    <button type="button" data-canvas-undo class="rounded font-bold text-brand-300 hover:underline focus:outline-none focus:ring-2 focus:ring-brand-300 focus:ring-offset-2 focus:ring-offset-gray-900" x-text="t('undo')" @click="undoRemove()"></button>
</div>
