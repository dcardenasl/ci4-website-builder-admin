<?php declare(strict_types=1); ?>
<nav class="flex flex-none gap-1 border-b border-gray-200 bg-white px-3 py-2 lg:hidden" :aria-label="t('editorPanels')">
    <template x-for="panel in ['blocks', 'preview', 'properties']" :key="panel">
        <button type="button" class="flex-1 rounded-md px-2 py-2 text-xs font-semibold"
                :class="mobilePanel === panel ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50'"
                :aria-pressed="mobilePanel === panel" :aria-controls="'canvas-panel-' + panel"
                @click="mobilePanel = panel" x-text="t(panel)"></button>
    </template>
</nav>
