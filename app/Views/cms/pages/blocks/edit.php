<?php
$page      = $page      ?? [];
$block     = $block     ?? [];
$blockType = $blockType ?? [];
$languages = $languages ?? [];

$schema      = is_array($blockType['schema_definition'] ?? [])
    ? ($blockType['schema_definition'] ?? [])
    : json_decode((string) ($blockType['schema_definition'] ?? '{}'), true);
$fields      = $blockType['fields']       ?? $schema['fields']       ?? [];
$configFields = $blockType['config_fields'] ?? $schema['config_fields'] ?? [];
$blockConfig  = is_array($block['block_config'] ?? []) ? ($block['block_config'] ?? []) : [];
$ownerType    = $ownerType    ?? 'page';
$ownerLabel   = $ownerLabel   ?? 'Página';
$ownerBlocksRoute = $ownerBlocksRoute ?? 'admin.cms.pages.blocks';
$ownerEditRoute   = $ownerEditRoute ?? 'admin.cms.pages.blocks.edit';
$ownerUpdateRoute = $ownerUpdateRoute ?? 'admin.cms.pages.blocks.update';
$ownerDeleteRoute = $ownerDeleteRoute ?? 'admin.cms.pages.blocks.delete';
$ownerChildrenRoute = $ownerChildrenRoute ?? 'admin.cms.pages.blocks.children';

$submittedBlockConfig = old('block_config', $blockConfig);
if (! is_array($submittedBlockConfig)) {
    $submittedBlockConfig = $blockConfig;
}

$submittedTranslations = old('translations', $block['translations'] ?? []);
if (! is_array($submittedTranslations)) {
    $submittedTranslations = [];
}

$translationsByLanguage = [];
foreach ($submittedTranslations as $translation) {
    if (! is_array($translation)) {
        continue;
    }

    $languageId = (int) ($translation['language_id'] ?? 0);
    if ($languageId > 0) {
        $translationsByLanguage[$languageId] = $translation;
    }
}

$sortOrderValue = old('sort_order', $block['sort_order'] ?? 0);
$isActiveValue  = (bool) old('is_active', ! empty($block['is_active']));
$blockIdValue   = old('block_id', (string) ($block['block_id'] ?? ''));

$defaultLangId = 0;
foreach ($languages as $l) {
    if (! empty($l['is_default'])) {
        $defaultLangId = (int) $l['id'];
        break;
    }
}
if ($defaultLangId === 0 && ! empty($languages)) {
    $defaultLangId = (int) $languages[0]['id'];
}

$blockKey    = $blockType['block_key'] ?? '';
$previewUrl  = route_to('admin.cms.blocks.preview');
$configJs    = json_encode($blockConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<meta name="block-preview-url" content="<?= esc($previewUrl) ?>">

<div class="mb-4">
    <a href="<?= route_to($ownerBlocksRoute, (string) $page['id']) ?>"
       class="text-sm text-brand-600 hover:text-brand-700">
        &larr; <?= esc(lang('Pages.block_back_to_blocks')) ?> — <?= esc($ownerLabel) ?>
    </a>
</div>

<?php ob_start(); ?>
<div class="max-w-3xl space-y-5">

    <!-- Block type identity card (readonly) -->
    <div class="bg-brand-50 border border-brand-200 rounded-xl p-4 flex items-start gap-4">
        <div class="w-10 h-10 rounded-lg bg-brand-100 flex items-center justify-center text-brand-600 shrink-0">
            <i data-lucide="<?= esc($blockType['icon'] ?? 'layout') ?>" class="w-5 h-5"></i>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-brand-900"><?= esc($blockType['name'] ?? 'Bloque') ?></p>
            <code class="text-xs font-mono text-brand-600 bg-brand-100 px-1.5 py-0.5 rounded"><?= esc($blockKey) ?></code>
            <?php if (! empty($blockType['description'])): ?>
                <p class="text-xs text-brand-700 mt-1"><?= esc($blockType['description']) ?></p>
            <?php endif; ?>
        </div>
        <button type="button"
                onclick="window.dispatchEvent(new CustomEvent('block-preview-open', { detail: { blockKey: <?= esc(json_encode($blockKey), 'attr') ?>, blockConfig: <?= esc($configJs, 'attr') ?>, blockData: {} } }))"
                class="shrink-0 flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 border border-brand-200 hover:border-brand-400 bg-white hover:bg-brand-50 px-3 py-1.5 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.963-7.178Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
            </svg>
            <?= esc(lang('Pages.block_preview_button')) ?>
        </button>
    </div>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="post"
              action="<?= route_to($ownerUpdateRoute, (string) $page['id'], (string) $block['id']) ?>"
              class="space-y-6">
            <?= csrf_field() ?>
            <input type="hidden" name="block_id" value="<?= esc((string) $blockIdValue) ?>">

            <!-- Sort order + active -->
            <div class="grid grid-cols-2 gap-4">
                <?= view('components/form/number', [
                    'name'     => 'sort_order',
                    'label'    => 'Pages.block_sort_order_label',
                    'required' => true,
                    'value'    => $sortOrderValue,
                    'help'     => 'Pages.block_sort_order_help',
                    'errors'   => $errors ?? [],
                ]) ?>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="is_active" value="1"
                               <?= $isActiveValue ? 'checked' : '' ?>
                               class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm font-medium text-gray-700"><?= esc(lang('Pages.block_active_label')) ?></span>
                    </label>
                </div>
            </div>

            <!-- Schema-driven config fields -->
            <?php if (! empty($configFields)): ?>
            <div class="border-t border-gray-100 pt-5">
                <h4 class="text-sm font-semibold text-gray-800 mb-1"><?= esc(lang('Pages.block_config_section')) ?></h4>
                <p class="text-xs text-gray-500 mb-4"><?= esc(lang('Pages.block_config_desc')) ?></p>
                <div class="space-y-4">
                    <?php foreach ($configFields as $cfKey => $cf):
                        $cfType    = $cf['type']     ?? 'string';
                        $cfLabel   = $cf['label']    ?? $cfKey;
                        $cfDefault = $cf['default']  ?? '';
                        $cfVal     = $submittedBlockConfig[$cfKey] ?? $cfDefault;
                        $cfOptions = isset($cf['options']) ? (array) $cf['options'] : [];
                        $cfReq     = ! empty($cf['required']);
                        $cfFieldName = "block_config[{$cfKey}]";
                        ?>
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-gray-700">
                            <?= esc($cfLabel) ?>
                            <?php if ($cfReq): ?><span class="text-red-500 ml-0.5">*</span><?php endif; ?>
                        </label>
                        <?php if ($cfType === 'boolean'): ?>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="<?= esc($cfFieldName, 'attr') ?>" value="1"
                                       <?= ! empty($cfVal) ? 'checked' : '' ?>
                                       class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-sm text-gray-600"><?= esc($cfLabel) ?></span>
                            </label>
                        <?php elseif ($cfType === 'select' && ! empty($cfOptions)): ?>
                            <select name="<?= esc($cfFieldName, 'attr') ?>"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"
                                    <?= $cfReq ? 'required' : '' ?>>
                                <option value="">— Seleccionar —</option>
                                <?php foreach ($cfOptions as $opt): ?>
                                    <option value="<?= esc((string) $opt) ?>" <?= (string) $cfVal === (string) $opt ? 'selected' : '' ?>>
                                        <?= esc((string) $opt) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="<?= $cfType === 'url' ? 'url' : ($cfType === 'integer' ? 'number' : 'text') ?>"
                                   name="<?= esc($cfFieldName, 'attr') ?>"
                                   value="<?= esc((string) $cfVal) ?>"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"
                                   <?= $cfReq ? 'required' : '' ?>>
                        <?php endif; ?>
                        <?= render_field_error($cfFieldName) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Content fields by language -->
            <?php if (! empty($fields)): ?>
            <div class="border-t border-gray-100 pt-5" x-data="langTabs(<?= $defaultLangId ?>)">
                <h4 class="text-sm font-semibold text-gray-800 mb-1"><?= esc(lang('Pages.block_content_section')) ?></h4>
                <p class="text-xs text-gray-500 mb-4"><?= esc(lang('Pages.block_content_desc')) ?></p>

                <!-- Tab bar -->
                <div class="flex border-b border-gray-200 mb-4" role="tablist">
                    <?php foreach ($languages as $lang): ?>
                        <button type="button"
                                role="tab"
                                @click="setTab(<?= (int) $lang['id'] ?>)"
                                :aria-selected="isActive(<?= (int) $lang['id'] ?>)"
                                :class="isActive(<?= (int) $lang['id'] ?>)
                                    ? 'border-brand-600 text-brand-700 bg-brand-50/40'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                            <?= esc(strtoupper((string) ($lang['code'] ?? ''))) ?>
                            <?php if (! empty($lang['is_default'])): ?>
                                <span class="ml-1 text-brand-400">★</span>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Tab panels -->
                <?php foreach ($languages as $idx => $lang):
                    $langId = (int) $lang['id'];
                    $currentRow = [];
                    foreach ($block['translations'] ?? [] as $t) {
                        if (is_array($t) && (int) ($t['language_id'] ?? 0) === $langId) {
                            $currentRow = $t;
                            break;
                        }
                    }
                    $transRow = $translationsByLanguage[$langId] ?? $currentRow;
                    $isDefault = ! empty($lang['is_default']);
                    ?>
                <div x-show="isActive(<?= (int) $lang['id'] ?>)" class="space-y-4">
                    <input type="hidden" name="translations[<?= $idx ?>][language_id]" value="<?= esc((string) $lang['id']) ?>">
                    <input type="hidden" name="translations[<?= $idx ?>][is_published]" value="1">

                    <?php foreach ($fields as $fieldKey => $field):
                        $ft       = $field['type']  ?? 'string';
                        $flabel   = $field['label']  ?? $fieldKey;
                        $freq     = ! empty($field['required']) && $isDefault;
                        $fval     = $transRow['block_data'][$fieldKey] ?? '';
                        $foptions = isset($field['options']) ? (array) $field['options'] : [];
                        $fieldName = "translations[{$idx}][block_data][{$fieldKey}]";
                        ?>
                    <div class="space-y-1">
                        <?php if ($ft !== 'file' && $ft !== 'repeater'): ?>
                        <label class="block text-xs font-semibold text-gray-700">
                            <?= esc($flabel) ?>
                            <?php if ($freq): ?><span class="text-red-500 ml-0.5">*</span><?php endif; ?>
                        </label>
                        <?php endif; ?>
                        <?php if ($ft === 'richtext'): ?>
                            <textarea name="<?= esc($fieldName, 'attr') ?>"
                                      rows="6"
                                      class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm font-mono"
                                      placeholder="HTML permitido…"
                                      <?= $freq ? 'required' : '' ?>><?= esc((string) old($fieldName, $fval)) ?></textarea>
                            <p class="text-[10px] text-gray-400">Soporta HTML</p>
                            <?= render_field_error($fieldName) ?>
                        <?php elseif (in_array($ft, ['text', 'textarea'])): ?>
                            <textarea name="<?= esc($fieldName, 'attr') ?>"
                                      rows="4"
                                      class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"
                                      <?= $freq ? 'required' : '' ?>><?= esc((string) old($fieldName, $fval)) ?></textarea>
                            <?= render_field_error($fieldName) ?>
                        <?php elseif ($ft === 'url'): ?>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
                                    </svg>
                                </span>
                                <input type="url"
                                       name="<?= esc($fieldName, 'attr') ?>"
                                       value="<?= esc((string) old($fieldName, $fval)) ?>"
                                       placeholder="https://"
                                       class="block w-full pl-9 rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"
                                       <?= $freq ? 'required' : '' ?>>
                            </div>
                            <?= render_field_error($fieldName) ?>
                        <?php elseif ($ft === 'integer'): ?>
                            <input type="number"
                                   name="<?= esc($fieldName, 'attr') ?>"
                                   value="<?= esc((string) old($fieldName, $fval)) ?>"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"
                                   <?= $freq ? 'required' : '' ?>>
                            <?= render_field_error($fieldName) ?>
                        <?php elseif ($ft === 'boolean'): ?>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox"
                                       name="<?= esc($fieldName, 'attr') ?>"
                                       value="1"
                                       <?= ! empty(old($fieldName, $fval)) ? 'checked' : '' ?>
                                       class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-sm text-gray-600"><?= esc($flabel) ?></span>
                            </label>
                            <?= render_field_error($fieldName) ?>
                        <?php elseif ($ft === 'select' && ! empty($foptions)): ?>
                            <select name="<?= esc($fieldName, 'attr') ?>"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"
                                    <?= $freq ? 'required' : '' ?>>
                                <option value="">— Seleccionar —</option>
                                <?php foreach ($foptions as $opt): ?>
                                    <option value="<?= esc((string) $opt) ?>" <?= (string) old($fieldName, $fval) === (string) $opt ? 'selected' : '' ?>>
                                        <?= esc((string) $opt) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?= render_field_error($fieldName) ?>
                        <?php elseif ($ft === 'file'): ?>
                            <?php
                            $existingFileId  = old($fieldName . '_file_id', $transRow['block_data'][$fieldKey . '_file_id'] ?? '');
                            $existingFileUrl = old($fieldName . '_url', $transRow['block_data'][$fieldKey . '_url'] ?? '');
                            $existingFileIdJs  = json_encode((string) $existingFileId);
                            $existingFileUrlJs = json_encode((string) $existingFileUrl);
                            $faccept = (string) ($field['accept'] ?? 'image');
                            $facceptJs = json_encode($faccept);
                            ?>
                            <label class="block text-xs font-semibold text-gray-700">
                                <?= esc($flabel) ?>
                                <?php if ($freq): ?><span class="text-red-500 ml-0.5">*</span><?php endif; ?>
                            </label>
                            <div x-data="fileField(<?= $existingFileIdJs ?>, <?= $existingFileUrlJs ?>, <?= $facceptJs ?>)" class="space-y-2">
                                <input type="hidden"
                                       name="<?= esc($fieldName . '_file_id', 'attr') ?>"
                                       x-model="fileId">
                                <input type="hidden"
                                       name="<?= esc($fieldName . '_url', 'attr') ?>"
                                       x-model="fileUrl">
                                <div x-show="previewUrl">
                                    <template x-if="accept === 'video'">
                                        <video :src="previewUrl" class="h-24 w-auto rounded border border-gray-200" controls muted></video>
                                    </template>
                                    <template x-if="accept !== 'video'">
                                        <img :src="previewUrl" class="h-24 w-auto rounded border border-gray-200 object-cover">
                                    </template>
                                </div>
                                <button type="button"
                                        @click="openPicker"
                                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                    </svg>
                                    <span x-text="fileId ? pickerLabels[accept]?.change : pickerLabels[accept]?.select"></span>
                                </button>
                            </div>
                            <?= render_field_error($fieldName) ?>
                            <?= render_field_error($fieldName . '_file_id') ?>
                            <?= render_field_error($fieldName . '_url') ?>
                        <?php elseif ($ft === 'repeater'): ?>
                            <?php
                            $existingItems   = old($fieldName, $fval);
                            $existingItems   = is_array($existingItems) ? $existingItems : [];
                            $itemFields      = $field['item_fields'] ?? [];
                            $existingItemsJs = json_encode(array_values($existingItems), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                            $itemFieldsJs    = json_encode($itemFields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                            $fieldKeyJs      = json_encode($fieldKey);
                            $langIdxJs       = json_encode($idx);
                            ?>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">
                                <?= esc($flabel) ?>
                            </label>
                            <div x-data="repeaterField(<?= $existingItemsJs ?>, <?= $itemFieldsJs ?>, <?= $fieldKeyJs ?>, <?= $langIdxJs ?>)"
                                 class="space-y-3">
                                <template x-for="(item, itemIdx) in items" :key="itemIdx">
                                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs font-semibold text-gray-600" x-text="`Item ${itemIdx + 1}`"></span>
                                            <button type="button" @click="removeItem(itemIdx)"
                                                    class="text-xs text-red-500 hover:text-red-700">Eliminar</button>
                                        </div>
                                        <template x-for="(subField, subKey) in itemFields" :key="subKey">
                                            <div class="space-y-1">
                                                <label class="block text-xs font-medium text-gray-600" x-text="subField.label || subKey"></label>
                                                <template x-if="subField.type === 'file'">
                                                    <div class="space-y-1.5">
                                                        <input type="hidden"
                                                               :name="`translations[${langIdx}][block_data][${fieldKey}][${itemIdx}][${subKey}_file_id]`"
                                                               :value="item[subKey + '_file_id'] || ''">
                                                        <input type="hidden"
                                                               :name="`translations[${langIdx}][block_data][${fieldKey}][${itemIdx}][${subKey}_url]`"
                                                               :value="item[subKey + '_url'] || ''">
                                                        <div x-show="item[subKey + '_url'] || item[subKey + '_preview_url']">
                                                            <template x-if="(subField.accept || 'image') === 'video'">
                                                                <video :src="item[subKey + '_preview_url'] || item[subKey + '_url']"
                                                                       class="h-20 w-auto rounded border border-gray-200" controls muted></video>
                                                            </template>
                                                            <template x-if="(subField.accept || 'image') !== 'video'">
                                                                <img :src="item[subKey + '_preview_url'] || item[subKey + '_url']"
                                                                     class="h-20 w-auto rounded border border-gray-200 object-cover">
                                                            </template>
                                                        </div>
                                                        <button type="button"
                                                                @click="openPickerForItem(itemIdx, subKey, subField.accept || 'image')"
                                                                class="inline-flex items-center gap-1 rounded border border-gray-300 bg-white px-2 py-1 text-xs text-gray-600 hover:bg-gray-50">
                                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                                            </svg>
                                                            <span x-text="item[subKey + '_file_id'] ? 'Cambiar' : 'Seleccionar'"></span>
                                                        </button>
                                                    </div>
                                                </template>
                                                <template x-if="subField.type === 'url'">
                                                    <input type="url"
                                                           :name="`translations[${langIdx}][block_data][${fieldKey}][${itemIdx}][${subKey}]`"
                                                           x-model="item[subKey]"
                                                           placeholder="https://"
                                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm">
                                                </template>
                                                <template x-if="subField.type === 'text' || subField.type === 'textarea'">
                                                    <textarea :name="`translations[${langIdx}][block_data][${fieldKey}][${itemIdx}][${subKey}]`"
                                                              x-model="item[subKey]"
                                                              rows="3"
                                                              class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"></textarea>
                                                </template>
                                                <template x-if="!['file','url','text','textarea'].includes(subField.type)">
                                                    <input type="text"
                                                           :name="`translations[${langIdx}][block_data][${fieldKey}][${itemIdx}][${subKey}]`"
                                                           x-model="item[subKey]"
                                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm">
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <button type="button"
                                        @click="addItem"
                                        class="inline-flex items-center gap-1.5 rounded-md border border-dashed border-brand-300 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 hover:bg-brand-100">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                    </svg>
                                    Agregar item
                                </button>
                            </div>
                        <?php else: ?>
                            <input type="text"
                                   name="<?= esc($fieldName, 'attr') ?>"
                                   value="<?= esc((string) old($fieldName, $fval)) ?>"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"
                                   <?= $freq ? 'required' : '' ?>>
                            <?= render_field_error($fieldName) ?>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('Pages.block_update_button')) ?></button>
                <a href="<?= route_to($ownerBlocksRoute, (string) $page['id']) ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
            </div>
        </form>
    </section>
</div>
<?php $blockEditContent = ob_get_clean(); ?>

<?= view('components/display/form_section', [
    'title' => 'Pages.block_editor_title',
    'description' => 'Pages.block_editor_desc',
    'content' => $blockEditContent,
    'bodyClass' => 'space-y-5',
]) ?>

<!-- File Picker Modal (global for this page) -->
<div x-data="globalFilePicker()"
     @file-picker-open.window="openWith($event.detail.callback, $event.detail.accept)"
     @keydown.escape.window="if(open) { open = false; }"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-3xl rounded-xl bg-white shadow-xl flex flex-col" style="max-height:85vh">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
            <h3 class="text-base font-semibold text-gray-900" x-text="pickerTitle"></h3>
            <button type="button" @click="open = false"
                    class="rounded-md p-1 text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="px-5 py-3 border-b border-gray-100">
            <input type="text" x-model="search" @input.debounce.300ms="load()"
                   placeholder="Buscar archivos..."
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm">
        </div>
        <div class="flex-1 overflow-y-auto p-5">
            <div x-show="loading" class="flex items-center justify-center py-12 text-gray-400 text-sm">Cargando...</div>
            <div x-show="!loading && files.length === 0"
                 class="flex items-center justify-center py-12 text-gray-400 text-sm">No hay archivos disponibles.</div>
            <div x-show="!loading" class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-3">
                <template x-for="file in files" :key="file.id">
                    <button type="button" @click="pick(file)"
                            class="group relative aspect-square overflow-hidden rounded-lg border-2 border-transparent hover:border-brand-500 bg-gray-100 transition-all">
                        <template x-if="file.mime_type && file.mime_type.startsWith('video/')">
                            <video :src="file.url" class="h-full w-full object-cover" muted preload="metadata"></video>
                        </template>
                        <template x-if="!(file.mime_type && file.mime_type.startsWith('video/'))">
                            <img :src="file.thumbnail_url || file.url" :alt="file.original_name || ''"
                                 class="h-full w-full object-cover">
                        </template>
                        <div class="absolute inset-0 bg-brand-600/0 group-hover:bg-brand-600/10 transition-colors"></div>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function langTabs(defaultLangId) {
    return {
        activeTabId: defaultLangId,
        setTab(id) { this.activeTabId = id; },
        isActive(id) { return this.activeTabId === id; }
    };
}

const pickerLabels = {
    image:    { select: 'Seleccionar imagen',    change: 'Cambiar imagen'    },
    video:    { select: 'Seleccionar video',     change: 'Cambiar video'     },
    document: { select: 'Seleccionar documento', change: 'Cambiar documento' },
    any:      { select: 'Seleccionar archivo',   change: 'Cambiar archivo'   },
};

function globalFilePicker() {
    return {
        open: false,
        loading: false,
        files: [],
        search: '',
        accept: 'image',
        callback: null,

        get pickerTitle() {
            const map = { image: 'Seleccionar imagen', video: 'Seleccionar video', document: 'Seleccionar documento', any: 'Seleccionar archivo' };
            return map[this.accept] || 'Seleccionar archivo';
        },

        openWith(cb, accept) {
            this.callback = cb;
            this.accept = accept || 'image';
            this.open = true;
            this.load();
        },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ per_page: 30 });
                if (this.accept && this.accept !== 'any') params.set('type', this.accept);
                if (this.search) params.set('search', this.search);
                const resp = await fetch(`/files/picker-data?${params}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const json = await resp.json();
                this.files = json.data || [];
            } catch (e) {
                this.files = [];
            }
            this.loading = false;
        },

        pick(file) {
            if (this.callback) this.callback(file);
            this.open = false;
            this.callback = null;
        },
    };
}

function fileField(initialId, initialUrl, accept) {
    return {
        fileId: initialId,
        fileUrl: initialUrl,
        previewUrl: initialUrl,
        accept: accept || 'image',
        pickerLabels,
        openPicker() {
            const self = this;
            window.dispatchEvent(new CustomEvent('file-picker-open', {
                detail: {
                    accept: self.accept,
                    callback(file) {
                        self.fileId = file.id;
                        self.fileUrl = file.url;
                        self.previewUrl = file.thumbnail_url || file.url;
                    }
                }
            }));
        }
    };
}

function repeaterField(existingItems, itemFields, fieldKey, langIdx) {
    const initItems = (existingItems || []).map(item => {
        const out = {};
        Object.keys(itemFields || {}).forEach(subKey => {
            if ((itemFields[subKey] || {}).type === 'file') {
                out[subKey + '_file_id']      = item[subKey + '_file_id'] || '';
                out[subKey + '_preview_url']  = '';
                out[subKey + '_url']          = item[subKey + '_url'] || '';
            } else {
                out[subKey] = item[subKey] || '';
            }
        });
        return out;
    });

    return {
        items: initItems,
        itemFields: itemFields || {},
        fieldKey,
        langIdx,

        addItem() {
            const item = {};
            Object.keys(this.itemFields).forEach(subKey => {
                if ((this.itemFields[subKey] || {}).type === 'file') {
                    item[subKey + '_file_id']     = '';
                    item[subKey + '_preview_url'] = '';
                    item[subKey + '_url']         = '';
                } else {
                    item[subKey] = '';
                }
            });
            this.items.push(item);
        },

        removeItem(idx) {
            this.items.splice(idx, 1);
        },

        openPickerForItem(itemIdx, subKey, accept) {
            const self = this;
            window.dispatchEvent(new CustomEvent('file-picker-open', {
                detail: {
                    accept: accept || 'image',
                    callback(file) {
                        self.items[itemIdx][subKey + '_file_id']    = file.id;
                        self.items[itemIdx][subKey + '_url']        = file.url;
                        self.items[itemIdx][subKey + '_preview_url'] = file.thumbnail_url || file.url;
                    }
                }
            }));
        },
    };
}
</script>
