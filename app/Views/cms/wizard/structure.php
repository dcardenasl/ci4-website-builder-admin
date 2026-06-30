<div class="max-w-5xl mx-auto space-y-8">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500"><?= lang('Wizard.structure_sidebar_label') ?></p>
        <h1 class="mt-2 text-3xl font-bold text-gray-900"><?= lang('Wizard.structure_heading') ?></h1>
        <p class="mt-3 max-w-2xl text-sm text-gray-600"><?= lang('Wizard.structure_intro') ?></p>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <a href="<?= route_to('admin.cms.pages.create') ?>" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-brand-400 hover:shadow-md">
            <div class="text-3xl">📄</div>
            <div class="mt-3 text-lg font-semibold text-gray-900"><?= lang('Wizard.create_page') ?></div>
            <div class="mt-1 text-sm text-gray-600"><?= lang('Wizard.create_page_desc') ?></div>
        </a>
        <a href="<?= route_to('admin.cms.collections.create') ?>" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-brand-400 hover:shadow-md">
            <div class="text-3xl">🗂️</div>
            <div class="mt-3 text-lg font-semibold text-gray-900"><?= lang('Wizard.create_collection') ?></div>
            <div class="mt-1 text-sm text-gray-600"><?= lang('Wizard.create_collection_desc') ?></div>
        </a>
        <a href="<?= route_to('admin.cms.menus.create') ?>" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-brand-400 hover:shadow-md">
            <div class="text-3xl">🔗</div>
            <div class="mt-3 text-lg font-semibold text-gray-900"><?= lang('Wizard.create_menu') ?></div>
            <div class="mt-1 text-sm text-gray-600"><?= lang('Wizard.create_menu_desc') ?></div>
        </a>
        <a href="<?= route_to('admin.cms.redirects.create') ?>" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-brand-400 hover:shadow-md">
            <div class="text-3xl">↪️</div>
            <div class="mt-3 text-lg font-semibold text-gray-900"><?= lang('Wizard.create_redirect') ?></div>
            <div class="mt-1 text-sm text-gray-600"><?= lang('Wizard.create_redirect_desc') ?></div>
        </a>
    </div>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
        <p class="text-sm font-semibold text-amber-900"><?= lang('Wizard.wizard_structure_hint') ?></p>
        <p class="mt-2 text-sm text-amber-800"><?= lang('Wizard.collection_wizard_intro') ?></p>
        <p class="mt-1 text-sm text-amber-800"><?= lang('Wizard.collection_wizard_minimum') ?></p>
    </div>

    <div class="flex flex-wrap gap-3">
        <a href="<?= route_to('admin.cms.collections') ?>" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
            <?= lang('Wizard.go_structure_panel') ?>
        </a>
        <a href="<?= route_to('admin.cms.wizard') ?>" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:border-gray-400">
            <?= lang('Wizard.btn_back_panel') ?>
        </a>
    </div>
</div>
