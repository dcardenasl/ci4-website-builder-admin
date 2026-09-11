<?php declare(strict_types=1); ?>
<aside id="canvas-panel-blocks" class="w-full flex-none flex-col border-r border-gray-200 bg-white lg:w-72"
       :class="mobilePanel === 'blocks' ? 'flex' : 'hidden lg:flex'">
    <div class="flex items-center justify-between px-4 pb-1 pt-4">
        <h2 class="text-xs font-bold uppercase tracking-wide text-gray-500" x-text="t('blocks')"></h2>
        <span class="rounded-full bg-gray-100 px-2 text-xs tabular-nums text-gray-500" x-text="blocks.length"></span>
    </div>

    <?php /* Reordering advice is noise until there are two blocks to reorder. */ ?>
    <p x-show="blocks.length > 1" class="px-4 pb-2 text-xs text-gray-500" x-text="t('reorderHelp')"></p>

    <?php /* An empty page opened on a blank rail with no hint of what to do. */ ?>
    <div x-show="!blocks.length" class="mx-4 mb-2 rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center">
        <p class="text-sm font-semibold text-gray-700" x-text="t('noBlocksYet')"></p>
        <p class="mt-1 text-xs leading-5 text-gray-500" x-text="t('noBlocksHint')"></p>
    </div>

    <ul class="min-h-0 flex-1 overflow-y-auto px-2 py-1" role="list" :aria-label="t('blocks')" x-ref="blockList">
        <template x-for="block in treeRows" :key="block.ref">
            <li :data-block-ref="block.ref" :data-parent-ref="block.parent_ref || ''"
                @keydown.alt.arrow-up.prevent="moveSibling(block.ref, -1)" @keydown.alt.arrow-down.prevent="moveSibling(block.ref, 1)">
                <div class="group mb-1 flex items-center gap-2 rounded-lg border border-transparent px-2 py-2"
                     :style="`margin-left:${(block.depth || 0) * 14}px`"
                     :class="block.ref === selectedRef ? 'border-brand-200 bg-brand-50' : 'hover:bg-gray-50'">
                    <span class="cursor-grab text-gray-300 hover:text-gray-500" data-drag-handle aria-hidden="true">&#8942;&#8942;</span>
                    <button type="button" class="min-w-0 flex-1 text-left" :aria-pressed="block.ref === selectedRef"
                            :aria-label="blockName(block) + ', ' + t('level') + ' ' + (block.depth + 1)" @click="select(block.ref)">
                        <span class="flex items-center gap-1 truncate text-sm font-semibold text-gray-900">
                            <span x-text="blockName(block)"></span>
                            <span x-show="block.untranslated" class="h-1.5 w-1.5 rounded-full bg-amber-500" :title="t('untranslated')"></span>
                        </span>
                        <span class="block truncate text-xs text-gray-500" x-text="block.block_key"></span>
                    </button>
                    <button type="button" x-show="typeOf(block.block_key)?.is_container" class="rounded p-1 text-gray-500 hover:bg-gray-100"
                            :aria-label="t('addChild')" @click.stop="openCatalog(block.ref)">+</button>
                    <span x-show="block.locked || block.required" class="text-[10px] font-bold uppercase text-gray-400"
                          x-text="block.locked ? t('locked') : t('required')"></span>
                    <button type="button" x-show="!block.locked && !block.required"
                            class="rounded p-1 text-gray-400 opacity-0 hover:bg-red-50 hover:text-red-600 group-hover:opacity-100 focus:opacity-100"
                            :aria-label="t('removeBlock')" @click.stop="removeBlock(block.ref)">&times;</button>
                </div>
            </li>
        </template>
    </ul>

    <div class="relative flex-none p-3">
        <button type="button" class="w-full rounded-lg border border-dashed border-gray-300 py-2 text-sm text-gray-600 hover:border-brand-500 hover:text-brand-700"
                x-text="'+ ' + t('addBlock')" @click.stop="openCatalog()" :aria-expanded="catalogOpen"></button>

        <div x-show="catalogOpen" @click.outside="catalogOpen = false"
             class="absolute bottom-full left-3 right-3 z-20 mb-2 max-h-72 overflow-y-auto rounded-lg border border-gray-200 bg-white p-1 shadow-lg">
            <template x-for="type in availableTypes" :key="type.block_key">
                <button type="button" class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-left text-sm font-medium hover:bg-gray-50"
                        x-text="type.name" @click="addBlock(type.block_key)"></button>
            </template>
        </div>
    </div>
</aside>
