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
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('BlockTypes.block_types_edit')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.block_types.update', (string) ($item['id'] ?? '')) ?>" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <?php $isSystem = in_array($item['block_key'] ?? '', ['rich_text', 'image', 'cta'], true); ?>

        <?= view('components/form/text', [
            'name' => 'block_key',
            'label' => 'BlockTypes.field_block_key',
            'required' => true,
            'value' => $item['block_key'] ?? '',
            'readonly' => $isSystem,
            'placeholder' => 'BlockTypes.field_block_key_placeholder',
            'help' => 'BlockTypes.field_block_key_help',
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

        <?= view('components/form/text', [
            'name' => 'category',
            'label' => 'BlockTypes.field_category',
            'required' => true,
            'value' => $item['category'] ?? '',
            'placeholder' => 'BlockTypes.field_category_placeholder',
            'help' => 'BlockTypes.field_category_help',
            'errors' => $errors ?? []
        ]) ?>

        <!-- JSON Schema Editor -->
        <?php
            $schemaValue = isset($item['schema_definition'])
                ? (is_array($item['schema_definition']) ? json_encode($item['schema_definition'], JSON_PRETTY_PRINT) : $item['schema_definition'])
                : "{\n  \"fields\": {}\n}";
            $schemaValueJs = json_encode($schemaValue);
        ?>
        <div x-data="jsonEditor(<?= $schemaValueJs ?>)">
            <div class="flex items-center justify-between mb-1">
                <label class="block text-sm font-medium text-gray-700">
                    <?= esc(lang('BlockTypes.field_schema_definition')) ?>
                    <span class="text-red-500" aria-hidden="true">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <button type="button"
                        @click="format()"
                        class="text-xs text-gray-500 hover:text-brand-600 border border-gray-200 rounded px-2 py-1 bg-white transition-colors">
                        <?= esc(lang('BlockTypes.schema_btn_format')) ?>
                    </button>
                    <button type="button"
                        @click="validate()"
                        class="text-xs border rounded px-2 py-1 transition-colors"
                        :class="isValid ? 'text-green-700 border-green-200 bg-green-50' : 'text-red-700 border-red-200 bg-red-50'">
                        <?= esc(lang('BlockTypes.schema_btn_validate')) ?>
                    </button>
                </div>
            </div>
            <textarea
                name="schema_definition"
                id="schema_definition"
                rows="10"
                x-model="value"
                @blur="validate()"
                class="<?= input_class('schema_definition') ?> resize-y font-mono text-sm"
                :class="!isValid ? 'border-red-400 ring-1 ring-red-400' : ''"
                required><?= esc($schemaValue) ?></textarea>
            <p x-show="!isValid" x-text="errorMsg" x-cloak class="mt-1 text-xs text-red-600"></p>
            <p class="mt-1 text-xs text-gray-500"><?= esc(lang('BlockTypes.field_schema_definition_help')) ?></p>
            <?= render_field_error('schema_definition') ?>
        </div>

        <!-- Options (collapsed) -->
        <details class="group border border-gray-200 rounded-lg">
            <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg select-none">
                <span><?= esc(lang('BlockTypes.section_options')) ?></span>
                <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-100">
                <?= view('components/form/textarea', [
                    'name' => 'description',
                    'label' => 'BlockTypes.field_description',
                    'required' => false,
                    'value' => $item['description'] ?? '',
                    'placeholder' => 'BlockTypes.field_description_placeholder',
                    'help' => 'BlockTypes.field_description_help',
                    'rows' => 2,
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
                <?= view('components/form/boolean', [
                    'name' => 'supports_pages',
                    'label' => 'BlockTypes.field_supports_pages',
                    'value' => $item['supports_pages'] ?? true,
                    'on_label' => 'App.yes',
                    'off_label' => 'App.no',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/boolean', [
                    'name' => 'supports_entries',
                    'label' => 'BlockTypes.field_supports_entries',
                    'value' => $item['supports_entries'] ?? true,
                    'on_label' => 'App.yes',
                    'off_label' => 'App.no',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/boolean', [
                    'name' => 'is_container',
                    'label' => 'BlockTypes.field_is_container',
                    'value' => $item['is_container'] ?? false,
                    'on_label' => 'App.yes',
                    'off_label' => 'App.no',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/boolean', [
                    'name' => 'is_active',
                    'label' => 'BlockTypes.field_is_active',
                    'value' => $item['is_active'] ?? true,
                    'on_label' => 'BlockTypes.field_is_active_on',
                    'off_label' => 'BlockTypes.field_is_active_off',
                    'help' => 'BlockTypes.field_is_active_help',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/text', [
                    'name' => 'sort_order',
                    'label' => 'BlockTypes.field_sort_order',
                    'required' => false,
                    'value' => $item['sort_order'] ?? '0',
                    'placeholder' => 'BlockTypes.field_sort_order_placeholder',
                    'errors' => $errors ?? []
                ]) ?>
            </div>
        </details>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.update')) ?></button>
            <a href="<?= route_to('admin.cms.block_types') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
