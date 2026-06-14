<?php $blockType = $blockType ?? []; ?>
<div class="mb-4">
    <a href="<?= route_to('admin.cms.block_types') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= lang('BlockTypes.title') ?></a>
</div>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($blockType)): ?>
    <?php $itemId = (string) ($blockType['id'] ?? ''); ?>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('BlockTypes.details') ?></h3>
            <div class="flex items-center gap-2">
                <a href="<?= route_to('admin.cms.block_types.edit', $itemId) ?>" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>

                <form method="post" action="<?= route_to('admin.cms.block_types.delete', $itemId) ?>" onsubmit="return confirm('<?= esc(lang('App.confirm_delete')) ?>');">
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
                'label' => 'Cms.field_block_key' ?? 'Block Key',
                'value' => $blockType['block_key'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Cms.field_name',
                'value' => $blockType['name'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Cms.field_description',
                'value' => $blockType['description'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Cms.field_category' ?? 'Category',
                'value' => $blockType['category'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Cms.field_icon',
                'value' => $blockType['icon'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Cms.field_is_active',
                'value' => view('components/table/boolean_cell', ['value' => $blockType['is_active'] ?? false]),
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Cms.field_supports_pages' ?? 'Supports Pages',
                'value' => view('components/table/boolean_cell', ['value' => $blockType['supports_pages'] ?? false]),
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Cms.field_supports_entries' ?? 'Supports Entries',
                'value' => view('components/table/boolean_cell', ['value' => $blockType['supports_entries'] ?? false]),
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Cms.field_is_container' ?? 'Is Container',
                'value' => view('components/table/boolean_cell', ['value' => $blockType['is_container'] ?? false]),
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Cms.field_sort_order' ?? 'Sort Order',
                'value' => $blockType['sort_order'] ?? '—'
            ]) ?>
            <div class="col-span-1 md:col-span-2">
                <dt class="text-gray-500 font-semibold mb-1"><?= lang('BlockTypes.field_schema_definition') ?? 'Schema Definition' ?></dt>
                <dd class="mt-1 bg-gray-55 text-gray-900 font-mono text-xs p-3 rounded-lg overflow-x-auto border border-gray-200">
                    <pre><?= esc(is_array($blockType['schema_definition']) ? json_encode($blockType['schema_definition'], JSON_PRETTY_PRINT) : (json_encode(json_decode($blockType['schema_definition'] ?? '{}'), JSON_PRETTY_PRINT) ?: '{}')) ?></pre>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500"><?= lang('TableColumns.created_at') ?></dt>
                <dd class="mt-1 text-gray-900"><?= esc((string) ($blockType['created_at'] ?? '-')) ?></dd>
            </div>
        </dl>
    </section>
<?php endif; ?>
