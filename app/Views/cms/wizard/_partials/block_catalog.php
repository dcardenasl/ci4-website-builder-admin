<?php /* Wizard — Block type catalog picker (NEW) */ ?>

<!-- ── SCREEN: BLOCK CATALOG ── -->
<div x-show="screen === 'block-catalog'" x-cloak>

    <!-- Header with context -->
    <div class="flex items-center gap-2 mb-5">
        <button @click="screen = 'page-blocks'" class="text-sm text-gray-500 hover:text-gray-700"><?= lang('Wizard.btn_cancel') ?></button>
        <span class="text-gray-300">/</span>
        <div>
            <h2 class="text-xl font-bold"><?= lang('Wizard.catalog_heading') ?></h2>
            <p class="text-xs text-gray-400 mt-0.5" x-show="catalogContext">
                <?= lang('Wizard.catalog_adding_child_to') ?>
                <span x-text="blockLabel(catalogContext, 0)" class="font-medium"></span>
            </p>
        </div>
    </div>

    <!-- Empty state -->
    <div x-show="availableBlockTypes().length === 0"
         class="text-center py-12 text-gray-400 text-sm">
        <div class="text-4xl mb-3">🚫</div>
        <p><?= lang('Wizard.catalog_no_types') ?></p>
    </div>

    <!-- Block type grid -->
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
        <template x-for="bt in availableBlockTypes()" :key="bt.key">
            <button @click="selectBlockType(bt)"
                    class="flex flex-col items-start gap-1 rounded-xl border-2 border-gray-200 bg-white p-4 text-left hover:border-brand-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="text-2xl" x-text="blockIcon(bt.key)"></span>
                <span class="font-semibold text-sm text-gray-800 mt-1" x-text="bt.name || humanizeKey(bt.key)"></span>
                <span class="text-xs text-gray-400 line-clamp-2" x-text="bt.description || ''"></span>
                <span x-show="bt.is_container"
                      class="text-xs bg-blue-50 text-blue-600 border border-blue-200 rounded px-1.5 py-0.5 mt-1">
                    <?= lang('Wizard.catalog_container_badge') ?>
                </span>
            </button>
        </template>
    </div>

</div>
