<?php $collection = $collection ?? []; ?>
<div class="mb-4">
    <a href="<?= route_to('admin.cms.collections') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= lang('Collections.collections_title') ?></a>
</div>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($collection)): ?>
    <?php $itemId = (string) ($collection['id'] ?? ''); ?>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Collections.collections_details') ?></h3>
            <div class="flex items-center gap-2">
                <a href="<?= route_to('admin.cms.collections.edit', $itemId) ?>" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>

                <form method="post" action="<?= route_to('admin.cms.collections.delete', $itemId) ?>" onsubmit="return confirm('<?= esc(lang('App.confirm_delete')) ?>');">
                    <?= csrf_field() ?>
                    <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
                        <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                        <?= esc(lang('App.delete')) ?>
                    </button>
                </form>
            </div>
        </div>

        <dl class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <?= view('components/display/field_row', [
                'label' => 'Collections.field_collection_key',
                'value' => $collection['collection_key'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Collections.field_is_active',
                'value' => view('components/table/boolean_cell', ['value' => $collection['is_active'] ?? false]),
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Collections.field_requires_approval',
                'value' => view('components/table/boolean_cell', ['value' => $collection['requires_approval'] ?? false]),
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Collections.field_enables_categories',
                'value' => view('components/table/boolean_cell', ['value' => $collection['enables_categories'] ?? false]),
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Collections.field_enables_tags',
                'value' => view('components/table/boolean_cell', ['value' => $collection['enables_tags'] ?? false]),
                'isHtml' => true
            ]) ?>
            <div>
                <dt class="text-gray-500"><?= lang('TableColumns.created_at') ?></dt>
                <dd class="mt-1 text-gray-900"><?= esc((string) ($collection['created_at'] ?? '-')) ?></dd>
            </div>
        </dl>

        <?php if (!empty($collection['translations']) && is_array($collection['translations'])): ?>
            <div class="mt-6 border-t border-gray-100 pt-6">
                <h4 class="text-md font-semibold text-gray-800 mb-3">Translations / Traducciones</h4>
                <div class="space-y-4">
                    <?php foreach ($collection['translations'] as $t): ?>
                        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50/50">
                            <div class="text-xs font-semibold text-brand-700 mb-1">Language ID: <?= esc((string)($t['language_id'] ?? '')) ?></div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 block">Name</span>
                                    <span class="text-gray-900 font-medium"><?= esc($t['name'] ?? '—') ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">Description</span>
                                    <span class="text-gray-900"><?= esc($t['description'] ?? '—') ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-6 border-t border-gray-100 pt-6">
            <a href="<?= site_url('admin/cms/entries?collection_id=' . $itemId) ?>" class="<?= esc(action_button_class('primary')) ?>">
                <?= ui_icon('cms-entry', 'h-4 w-4') ?>
                <span>Ver Entries</span>
            </a>
        </div>
    </section>
<?php endif; ?>
