<?php /* Wizard — Home screen */ ?>

<!-- Draft banner (shown on home screen only) -->
<div x-show="screen === 'home' && draft"
     x-cloak
     class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4 flex justify-between items-center">
    <div>
        <p class="font-medium text-sm"><?= lang('Wizard.draft_banner_title') ?></p>
        <p class="text-xs text-gray-500" x-text="draft ? new Date(draft.savedAt).toLocaleString() : ''"></p>
    </div>
    <div class="flex gap-2">
        <button @click="resumeDraft()" class="btn-primary text-sm"><?= lang('Wizard.draft_continue') ?></button>
        <button @click="discardDraft()" class="btn-secondary text-sm"><?= lang('Wizard.draft_discard') ?></button>
    </div>
</div>

<!-- ── SCREEN: HOME ── -->
<div x-show="screen === 'home'" x-cloak>
    <h1 class="text-2xl font-bold mb-6"><?= lang('Wizard.home_heading') ?></h1>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <button @click="goAddContent()"
                class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-white p-6 text-center hover:border-brand-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-brand-500">
            <span class="text-4xl">📝</span>
            <span class="font-semibold text-gray-800"><?= lang('Wizard.add_content') ?></span>
            <span class="text-xs text-gray-500" x-text="addContentDesc()"></span>
        </button>
        <button @click="goEditPage()"
                class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-white p-6 text-center hover:border-brand-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-brand-500">
            <span class="text-4xl">✏️</span>
            <span class="font-semibold text-gray-800"><?= lang('Wizard.edit_page') ?></span>
            <span class="text-xs text-gray-500"><?= lang('Wizard.edit_page_desc') ?></span>
        </button>
        <button @click="goEditMenu()"
                class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-white p-6 text-center hover:border-brand-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-brand-500">
            <span class="text-4xl">🔗</span>
            <span class="font-semibold text-gray-800"><?= lang('Wizard.edit_menu') ?></span>
            <span class="text-xs text-gray-500"><?= lang('Wizard.edit_menu_desc') ?></span>
        </button>
    </div>
</div>
