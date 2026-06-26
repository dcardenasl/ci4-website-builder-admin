<?php /* Wizard — B1: Page selection */ ?>

<!-- ── SCREEN: PAGE SELECT (B1) ── -->
<div x-show="screen === 'page-select'" x-cloak>
    <h2 class="text-xl font-bold mb-4"><?= lang('Wizard.page_select_heading') ?></h2>
    <div x-show="(config?.pages ?? []).length === 0"
         class="text-gray-400 text-sm py-8 text-center">
        <?= lang('Wizard.no_pages') ?>
    </div>
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
        <template x-for="page in (config?.pages ?? [])" :key="page.id">
            <button @click="selectPage(page)"
                    class="flex flex-col items-center justify-center gap-1 rounded-xl border-2 border-gray-200 bg-white p-5 text-center hover:border-brand-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="text-3xl">📄</span>
                <span class="font-semibold text-sm text-gray-800" x-text="page.title || page.slug || strings.page_fallback"></span>
                <span class="text-xs text-gray-400" x-text="page.slug ? '/' + page.slug : ''"></span>
            </button>
        </template>
    </div>
    <button @click="screen = 'home'" class="mt-4 text-sm text-gray-500 hover:text-gray-700"><?= lang('Wizard.btn_back') ?></button>
</div>
