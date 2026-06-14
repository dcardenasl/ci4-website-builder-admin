<?php $item = $item ?? []; ?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to('admin.cms.block_types') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
    <form method="post" action="<?= route_to('admin.cms.block_types.delete', (string) ($item['id'] ?? '')) ?>" onsubmit="return confirm('<?= esc(lang('App.confirm_delete')) ?>');">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('BlockTypes.edit')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.block_types.update', (string) ($item['id'] ?? '')) ?>" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <?php
        // System blocks (rich_text, image, cta) should have their key read-only
        $isSystem = in_array($item['block_key'] ?? '', ['rich_text', 'image', 'cta'], true);
?>

        <?= view('components/form/text', [
    'name' => 'block_key',
    'label' => 'BlockTypes.field_block_key' ?? 'Block Key',
    'required' => true,
    'value' => $item['block_key'] ?? '',
    'readonly' => $isSystem,
    'placeholder' => 'BlockTypes.field_block_key_placeholder' ?? 'e.g. rich_text',
    'help' => 'BlockTypes.field_block_key_help' ?? 'Unique identifier for the block type.',
    'errors' => $errors ?? []
]) ?>

        <?= view('components/form/text', [
    'name' => 'name',
    'label' => 'BlockTypes.field_name',
    'required' => true,
    'value' => $item['name'] ?? '',
    'placeholder' => 'BlockTypes.field_name_placeholder',
    'help' => 'BlockTypes.field_name_help',
    'errors' => $errors ?? []
]) ?>

        <?= view('components/form/textarea', [
    'name' => 'description',
    'label' => 'BlockTypes.field_description',
    'required' => false,
    'value' => $item['description'] ?? '',
    'placeholder' => 'BlockTypes.field_description_placeholder',
    'help' => 'BlockTypes.field_description_help',
    'errors' => $errors ?? []
]) ?>

        <?= view('components/form/text', [
    'name' => 'category',
    'label' => 'BlockTypes.field_category' ?? 'Category',
    'required' => true,
    'value' => $item['category'] ?? '',
    'placeholder' => 'BlockTypes.field_category_placeholder' ?? 'e.g. content, media, marketing',
    'help' => 'BlockTypes.field_category_help' ?? 'Block grouping category.',
    'errors' => $errors ?? []
]) ?>

        <?= view('components/form/text', [
    'name' => 'icon',
    'label' => 'BlockTypes.field_icon',
    'required' => false,
    'value' => $item['icon'] ?? '',
    'placeholder' => 'BlockTypes.field_icon_placeholder',
    'help' => 'BlockTypes.field_icon_help',
    'errors' => $errors ?? []
]) ?>

        <?= view('components/form/textarea', [
    'name' => 'schema_definition',
    'label' => 'BlockTypes.field_schema_definition' ?? 'Schema Definition (JSON)',
    'required' => true,
    'value' => isset($item['schema_definition']) ? (is_array($item['schema_definition']) ? json_encode($item['schema_definition'], JSON_PRETTY_PRINT) : $item['schema_definition']) : '{\n  \"fields\": {}\n}',
    'placeholder' => '{\n  \"fields\": {}\n}',
    'help' => 'BlockTypes.field_schema_definition_help' ?? 'Define structural and localized fields configuration in JSON format.',
    'errors' => $errors ?? []
]) ?>

        <?= view('components/form/boolean', [
    'name' => 'supports_pages',
    'label' => 'BlockTypes.field_supports_pages' ?? 'Supports Pages',
    'value' => $item['supports_pages'] ?? true,
    'on_label' => 'App.yes',
    'off_label' => 'App.no',
    'errors' => $errors ?? []
]) ?>

        <?= view('components/form/boolean', [
    'name' => 'supports_entries',
    'label' => 'BlockTypes.field_supports_entries' ?? 'Supports Entries',
    'value' => $item['supports_entries'] ?? true,
    'on_label' => 'App.yes',
    'off_label' => 'App.no',
    'errors' => $errors ?? []
]) ?>

        <?= view('components/form/boolean', [
    'name' => 'is_container',
    'label' => 'BlockTypes.field_is_container' ?? 'Is Container',
    'value' => $item['is_container'] ?? false,
    'on_label' => 'App.yes',
    'off_label' => 'App.no',
    'errors' => $errors ?? []
]) ?>

        <?= view('components/form/boolean', [
    'name' => 'is_active',
    'label' => 'BlockTypes.field_is_active',
    'value' => $item['is_active'] ?? false,
    'on_label' => 'BlockTypes.field_is_active_on',
    'off_label' => 'BlockTypes.field_is_active_off',
    'help' => 'BlockTypes.field_is_active_help',
    'errors' => $errors ?? []
]) ?>

        <?= view('components/form/text', [
    'name' => 'sort_order',
    'label' => 'BlockTypes.field_sort_order' ?? 'Sort Order',
    'required' => true,
    'value' => $item['sort_order'] ?? '0',
    'errors' => $errors ?? []
]) ?>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.update')) ?></button>
            <a href="<?= route_to('admin.cms.block_types') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
