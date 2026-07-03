<?php $collection = $collection ?? []; ?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($collection)): ?>
    <?php $itemId = (string) ($collection['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.cms.collections'),
        'backLabel' => 'Collections.collections_title',
        'eyebrow' => 'Collections.collections_details',
        'title' => (string) ($collection['collection_key'] ?? '—'),
        'badge' => view('components/table/boolean_cell', ['value' => $collection['is_active'] ?? false]),
    ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900"><?= lang('Collections.collections_details') ?></h3>
        <dl class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <?= view('components/display/field_row', [
                'label' => 'Collections.field_collection_key',
                'value' => $collection['collection_key'] ?? '—'
            ]) ?>
        </dl>

        <?php if (!empty($collection['translations']) && is_array($collection['translations'])): ?>
            <div class="mt-6 border-t border-gray-100 pt-6">
                <h4 class="text-md font-semibold text-gray-800 mb-3"><?= esc(lang('Collections.translation_title')) ?></h4>
                <div class="space-y-4">
                    <?php foreach ($collection['translations'] as $t): ?>
                        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50/50">
                            <div class="text-xs font-semibold text-brand-700 mb-1"><?= esc(lang('Collections.translation_language_label')) ?>: <?= esc((string)($t['language_id'] ?? '')) ?></div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 block"><?= esc(lang('Collections.translation_name_label')) ?></span>
                                    <span class="text-gray-900 font-medium"><?= esc($t['name'] ?? '—') ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block"><?= esc(lang('Collections.translation_description_label')) ?></span>
                                    <span class="text-gray-900"><?= esc($t['description'] ?? '—') ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?= view('components/display/admin_meta_panel', [
        'title' => 'Collections.collections_details',
        'items' => [
            ['label' => 'Collections.field_is_active', 'value' => view('components/table/boolean_cell', ['value' => $collection['is_active'] ?? false]), 'isHtml' => true],
            ['label' => 'Collections.field_requires_approval', 'value' => view('components/table/boolean_cell', ['value' => $collection['requires_approval'] ?? false]), 'isHtml' => true],
            ['label' => 'Collections.field_enables_categories', 'value' => view('components/table/boolean_cell', ['value' => $collection['enables_categories'] ?? false]), 'isHtml' => true],
            ['label' => 'Collections.field_enables_tags', 'value' => view('components/table/boolean_cell', ['value' => $collection['enables_tags'] ?? false]), 'isHtml' => true],
            ['label' => 'TableColumns.created_at', 'value' => (string) ($collection['created_at'] ?? '-')],
        ],
    ]) ?>

    <?php ob_start(); ?>
    <?php if (has_permission('cms.collections.write')): ?>
        <a href="<?= route_to('admin.cms.collections.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center">
            <?= ui_icon('pencil', 'h-3.5 w-3.5') ?>
            <?= lang('App.edit') ?>
        </a>
    <?php endif; ?>
    <a href="<?= site_url('admin/cms/entries?collection_id=' . $itemId) ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center">
        <?= ui_icon('cms-entry', 'h-4 w-4') ?>
        <span><?= esc(lang('Collections.collections_entries')) ?></span>
    </a>
    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (has_permission('cms.collections.write')): ?>
        <form method="post" action="<?= route_to('admin.cms.collections.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($collection['name'] ?? $collection['collection_key'] ?? null), 'js') ?>', () => $el.submit())">
            <?= csrf_field() ?>
            <button type="submit" class="<?= esc(action_button_class('danger')) ?> w-full justify-center">
                <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                <?= esc(lang('App.delete')) ?>
            </button>
        </form>
    <?php endif; ?>
    <?php $dangerContent = ob_get_clean(); ?>

    <?= view('components/display/admin_actions_panel', [
        'content' => $actionsContent,
        'dangerContent' => $dangerContent,
    ]) ?>
    <?php $asideContent = ob_get_clean(); ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => $asideContent,
    ]) ?>
<?php endif; ?>
