<?php
$page             = $page             ?? [];
$blockTypes       = $blockTypes       ?? [];
$languages        = $languages        ?? [];
$parentInstanceId = $parentInstanceId ?? null;
$parentBlockType  = $parentBlockType  ?? null;
$ownerType        = $ownerType        ?? 'page';
$ownerLabel       = $ownerLabel       ?? 'Página';
$ownerBlocksRoute = $ownerBlocksRoute ?? 'admin.cms.pages.blocks';
$ownerStoreRoute  = $ownerStoreRoute  ?? 'admin.cms.pages.blocks.store';
$ownerChildLabel  = $ownerChildLabel  ?? 'Diapositiva';

// When creating a child block, filter dynamically using parent block's allowed_children schema definition
if ($parentInstanceId !== null) {
    $allowedChildren = [];
    if ($parentBlockType !== null) {
        $parentSchema = is_array($parentBlockType['schema_definition'] ?? [])
            ? ($parentBlockType['schema_definition'] ?? [])
            : json_decode((string)($parentBlockType['schema_definition'] ?? '{}'), true);
        $allowedChildren = $parentSchema['allowed_children'] ?? [];
    }

    if (!empty($allowedChildren)) {
        $blockTypes = array_values(array_filter($blockTypes, static fn (array $bt) => in_array($bt['block_key'], $allowedChildren, true)));
    } else {
        // Fallback for backward compatibility
        $blockTypes = array_values(array_filter($blockTypes, static fn (array $bt) => $bt['block_key'] === 'slide_banner'));
    }
}

$blockTypesJs  = json_encode(array_values($blockTypes), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$languagesJs   = json_encode(array_values($languages), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$previewUrl    = route_to('admin.cms.blocks.preview');
$parentIdJs    = json_encode($parentInstanceId);
?>
<meta name="block-preview-url" content="<?= esc($previewUrl) ?>">

<div class="mb-4">
    <?php if ($parentInstanceId !== null): ?>
        <a href="javascript:history.back()" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; <?= esc(lang('Pages.block_back_to_blocks')) ?> — <?= esc($ownerChildLabel) ?>
        </a>
    <?php else: ?>
        <a href="<?= route_to($ownerBlocksRoute, (string)$page['id']) ?>" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; <?= esc(lang('Pages.block_back_to_blocks')) ?> — <?= esc($ownerLabel) ?>
        </a>
    <?php endif; ?>
</div>

<div x-data="blockInstanceBuilder(<?= esc($blockTypesJs, 'attr') ?>, <?= esc($languagesJs, 'attr') ?>)" class="space-y-6 max-w-4xl">
    <?php ob_start(); ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        <template x-for="bt in blockTypes" :key="bt.id">
            <button type="button"
                @click="selectBlockType(bt)"
                :class="selectedBlockType?.id === bt.id
                    ? 'border-brand-600 bg-brand-50 ring-2 ring-brand-400'
                    : 'border-gray-200 bg-white hover:border-brand-400 hover:bg-brand-50/30'"
                class="relative flex flex-col items-center gap-2 p-4 border-2 rounded-xl text-center cursor-pointer transition-all">

                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500"
                     :class="selectedBlockType?.id === bt.id ? 'bg-brand-100 text-brand-600' : ''">
                    <i :data-lucide="bt.icon || 'layout-template'" class="w-5 h-5"></i>
                </div>
                <span class="text-xs font-semibold text-gray-800 leading-tight" x-text="bt.name"></span>
                <code class="text-[10px] text-gray-400 font-mono" x-text="bt.block_key"></code>

                <span x-show="selectedBlockType?.id === bt.id"
                      class="absolute top-2 right-2 w-4 h-4 bg-brand-600 rounded-full flex items-center justify-center">
                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M13.485 1.431a1.473 1.473 0 0 1 2.104 0 1.473 1.473 0 0 1 0 2.104L6.555 12.64 1.127 7.212a1.473 1.473 0 0 1 0-2.104 1.474 1.474 0 0 1 2.104 0l3.324 3.324 6.93-6.94z"/>
                    </svg>
                </span>
            </button>
        </template>
    </div>

    <div x-show="selectedBlockType" x-cloak class="mt-4 p-3 bg-brand-50 border border-brand-200 rounded-lg text-sm text-brand-800 flex items-start gap-2">
        <svg class="w-4 h-4 mt-0.5 shrink-0 text-brand-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
        </svg>
        <span x-text="selectedBlockType?.description || ''"></span>
    </div>
    <?php $step1Content = ob_get_clean(); ?>
    <?= view('components/display/form_section', [
        'title' => 'Pages.block_step1_title',
        'description' => 'Pages.block_step1_desc',
        'content' => $step1Content,
        'bodyClass' => 'space-y-4',
    ]) ?>

    <div x-show="selectedBlockType" x-cloak>
        <?php ob_start(); ?>
        <div class="flex items-center justify-end mb-5">
            <button type="button"
                @click="openPreview()"
                class="flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 border border-brand-200 hover:border-brand-400 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.963-7.178Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                </svg>
                <?= esc(lang('Pages.block_preview_button')) ?>
            </button>
        </div>

        <form method="post" action="<?= route_to($ownerStoreRoute, (string)$page['id']) ?>" class="space-y-6">
            <?= csrf_field() ?>
            <input type="hidden" name="block_id" :value="selectedBlockType?.id">
            <?php if ($parentInstanceId !== null): ?>
                <input type="hidden" name="parent_instance_id" value="<?= esc((string) $parentInstanceId) ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pb-4 border-b border-gray-100">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= esc(lang('Pages.block_sort_order_label')) ?> <span class="text-red-500">*</span></label>
                    <input type="number" name="sort_order" value="0" min="0"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm">
                    <p class="text-xs text-gray-400 mt-1"><?= esc(lang('Pages.block_sort_order_help')) ?></p>
                </div>
                <div class="flex items-center pt-6">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                           class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <label for="is_active" class="ml-2 block text-sm font-medium text-gray-700"><?= esc(lang('Pages.block_active_label')) ?></label>
                </div>
            </div>

            <div x-show="configFields && Object.keys(configFields).length > 0" x-cloak>
                <h4 class="text-sm font-semibold text-gray-800 mb-3">Configuración del Diseño</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                    <template x-for="(field, key) in configFields" :key="key">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                <span x-text="field.label || key"></span>
                            </label>

                            <template x-if="field.type === 'select'">
                                <select :name="`block_config[${key}]`"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm">
                                    <template x-for="opt in (field.options || [])" :key="opt">
                                        <option :value="opt" :selected="opt === (field.default || '')" x-text="opt"></option>
                                    </template>
                                </select>
                            </template>

                            <template x-if="field.type === 'boolean'">
                                <input type="checkbox" :name="`block_config[${key}]`" value="1"
                                       :checked="field.default == '1' || field.default === true"
                                       class="h-4 w-4 rounded border-gray-300 text-brand-600">
                            </template>

                            <template x-if="field.type !== 'select' && field.type !== 'boolean'">
                                <input type="text" :name="`block_config[${key}]`"
                                       :value="field.default || ''"
                                       :placeholder="field.default || ''"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm">
                            </template>

                            <p x-show="field.description" class="text-[11px] text-gray-400 mt-0.5" x-text="field.description"></p>
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="contentFields && Object.keys(contentFields).length > 0" x-cloak>
                <h4 class="text-sm font-semibold text-gray-800 mb-3">Contenido por Idioma</h4>

                <div class="flex border-b border-gray-200 mb-4" role="tablist">
                    <template x-for="(lang, index) in languages" :key="lang.id">
                        <button type="button"
                                role="tab"
                                @click="activeLangId = lang.id"
                                :aria-selected="activeLangId == lang.id"
                                :class="activeLangId == lang.id ? 'border-brand-600 text-brand-700 bg-brand-50/40' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                            <span x-text="lang.code.toUpperCase()"></span>
                            <span x-show="lang.is_default == 1" class="ml-1 text-brand-400">★</span>
                        </button>
                    </template>
                </div>

                <template x-for="(lang, langIndex) in languages" :key="lang.id">
                    <div x-show="activeLangId == lang.id" class="space-y-4">
                        <input type="hidden" :name="`translations[${langIndex}][language_id]`" :value="lang.id">
                        <input type="hidden" :name="`translations[${langIndex}][is_published]`" value="1">

                        <template x-for="(field, fieldKey) in contentFields" :key="fieldKey">
                            <div class="space-y-1">
                                <label class="block text-xs font-semibold text-gray-700">
                                    <span x-text="field.label || fieldKey"></span>
                                    <span x-show="field.required && lang.is_default == 1" class="text-red-500 ml-0.5">*</span>
                                </label>

                                <template x-if="field.type === 'richtext'">
                                    <textarea :name="`translations[${langIndex}][block_data][${fieldKey}]`"
                                              rows="6"
                                              :required="field.required && lang.is_default == 1"
                                              class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm font-mono"></textarea>
                                </template>
                                <template x-if="field.type === 'text' || field.type === 'textarea'">
                                    <textarea :name="`translations[${langIndex}][block_data][${fieldKey}]`"
                                              rows="4"
                                              :required="field.required && lang.is_default == 1"
                                              class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"></textarea>
                                </template>
                                <template x-if="field.type === 'url'">
                                    <input type="url" :name="`translations[${langIndex}][block_data][${fieldKey}]`"
                                           :required="field.required && lang.is_default == 1"
                                           placeholder="https://"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm">
                                </template>
                                <template x-if="field.type === 'integer' || field.type === 'int'">
                                    <input type="number" :name="`translations[${langIndex}][block_data][${fieldKey}]`"
                                           :required="field.required && lang.is_default == 1"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm">
                                </template>
                                <template x-if="field.type === 'select'">
                                    <select :name="`translations[${langIndex}][block_data][${fieldKey}]`"
                                            :required="field.required && lang.is_default == 1"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm">
                                        <template x-for="opt in (field.options || [])" :key="opt">
                                            <option :value="opt" :selected="opt === (field.default || '')" x-text="opt"></option>
                                        </template>
                                    </select>
                                </template>
                                <template x-if="field.type === 'file'">
                                    <div class="space-y-2">
                                        <input type="hidden"
                                               :name="`translations[${langIndex}][block_data][${fieldKey}_file_id]`"
                                               :value="getPickedFileId(lang.id, fieldKey)">
                                        <input type="hidden"
                                               :name="`translations[${langIndex}][block_data][${fieldKey}_url]`"
                                               :value="getPickedFileUrl(lang.id, fieldKey)">
                                        <div x-show="getPickedFileUrl(lang.id, fieldKey)"
                                             class="relative inline-block">
                                            <template x-if="(field.accept || 'image') === 'video'">
                                                <video :src="getPickedFileUrl(lang.id, fieldKey)"
                                                       class="h-24 w-auto rounded border border-gray-200" controls muted></video>
                                            </template>
                                            <template x-if="(field.accept || 'image') !== 'video'">
                                                <img :src="getPickedFileUrl(lang.id, fieldKey)"
                                                     class="h-24 w-auto rounded border border-gray-200 object-cover">
                                            </template>
                                        </div>
                                        <button type="button"
                                                @click="openFilePicker((file) => pickFile(lang.id, fieldKey, null, null, file), field.accept || 'image')"
                                                class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                            </svg>
                                            <span x-text="getPickedFileId(lang.id, fieldKey) ? (pickerChangeLabels[field.accept || 'image'] || 'Cambiar archivo') : (pickerSelectLabels[field.accept || 'image'] || 'Seleccionar archivo')"></span>
                                        </button>
                                    </div>
                                </template>
                                <template x-if="field.type === 'repeater'">
                                    <div class="space-y-3">
                                        <template x-for="(item, itemIdx) in repeaterList(lang.id, fieldKey)" :key="itemIdx">
                                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-3">
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="text-xs font-semibold text-gray-600" x-text="`Item ${itemIdx + 1}`"></span>
                                                    <button type="button"
                                                            @click="removeItem(lang.id, fieldKey, itemIdx)"
                                                            class="text-xs text-red-500 hover:text-red-700">
                                                        Eliminar
                                                    </button>
                                                </div>
                                                <template x-for="(subField, subKey) in (field.item_fields || {})" :key="subKey">
                                                    <div class="space-y-1">
                                                        <label class="block text-xs font-medium text-gray-600" x-text="subField.label || subKey"></label>
                                                        <template x-if="subField.type === 'file'">
                                                            <div class="space-y-1.5">
                                                                <input type="hidden"
                                                                       :name="`translations[${langIndex}][block_data][${fieldKey}][${itemIdx}][${subKey}_file_id]`"
                                                                       :value="item[subKey + '_file_id'] || ''">
                                                                <input type="hidden"
                                                                       :name="`translations[${langIndex}][block_data][${fieldKey}][${itemIdx}][${subKey}_url]`"
                                                                       :value="item[subKey + '_url'] || ''">
                                                                <div x-show="item[subKey + '_preview_url']">
                                                                    <template x-if="(subField.accept || 'image') === 'video'">
                                                                        <video :src="item[subKey + '_preview_url']"
                                                                               class="h-20 w-auto rounded border border-gray-200" controls muted></video>
                                                                    </template>
                                                                    <template x-if="(subField.accept || 'image') !== 'video'">
                                                                        <img :src="item[subKey + '_preview_url']"
                                                                             class="h-20 w-auto rounded border border-gray-200 object-cover">
                                                                    </template>
                                                                </div>
                                                                <button type="button"
                                                                        @click="openFilePicker((file) => pickFile(lang.id, fieldKey, itemIdx, subKey, file), subField.accept || 'image')"
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
                                                                   :name="`translations[${langIndex}][block_data][${fieldKey}][${itemIdx}][${subKey}]`"
                                                                   x-model="item[subKey]"
                                                                   placeholder="https://"
                                                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm">
                                                        </template>
                                                        <template x-if="subField.type === 'text' || subField.type === 'textarea'">
                                                            <textarea :name="`translations[${langIndex}][block_data][${fieldKey}][${itemIdx}][${subKey}]`"
                                                                      x-model="item[subKey]"
                                                                      rows="3"
                                                                      class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"></textarea>
                                                        </template>
                                                        <template x-if="!['file','url','text','textarea'].includes(subField.type)">
                                                            <input type="text"
                                                                   :name="`translations[${langIndex}][block_data][${fieldKey}][${itemIdx}][${subKey}]`"
                                                                   x-model="item[subKey]"
                                                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm">
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                        <button type="button"
                                                @click="addItem(lang.id, fieldKey, field.item_fields || {})"
                                                class="inline-flex items-center gap-1.5 rounded-md border border-dashed border-brand-300 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 hover:bg-brand-100">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                            </svg>
                                            Agregar item
                                        </button>
                                    </div>
                                </template>
                                <template x-if="!['richtext','text','textarea','url','integer','int','select','file','repeater'].includes(field.type)">
                                    <input type="text" :name="`translations[${langIndex}][block_data][${fieldKey}]`"
                                           :required="field.required && lang.is_default == 1"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm">
                                </template>

                                <p x-show="field.type === 'richtext'" class="text-[11px] text-gray-400">Acepta HTML enriquecido.</p>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div x-show="selectedBlockType && Object.keys(contentFields).length === 0" x-cloak
                 class="p-4 bg-gray-50 border border-dashed border-gray-300 rounded-lg text-sm text-gray-500 text-center">
                <?= esc(lang('Pages.block_structural_note')) ?>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('Pages.block_add_button')) ?></button>
                <a href="<?= route_to($ownerBlocksRoute, (string)$page['id']) ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
            </div>
        </form>
        <?php $step2Content = ob_get_clean(); ?>
        <?= view('components/display/form_section', [
            'title' => 'Pages.block_step2_title',
            'description' => 'Pages.block_step2_desc',
            'content' => $step2Content,
            'bodyClass' => 'space-y-6',
        ]) ?>
    </div>

    <!-- File Picker Modal -->
    <div x-show="filePickerOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
         @keydown.escape.window="filePickerOpen = false">
        <div class="w-full max-w-3xl rounded-xl bg-white shadow-xl flex flex-col" style="max-height:85vh">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <h3 class="text-base font-semibold text-gray-900" x-text="pickerTitle"></h3>
                <button type="button" @click="filePickerOpen = false"
                        class="rounded-md p-1 text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-5 py-3 border-b border-gray-100">
                <input type="text" x-model="filePickerSearch" @input.debounce.300ms="loadPickerFiles()"
                       placeholder="Buscar archivos..."
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm">
            </div>
            <div class="flex-1 overflow-y-auto p-5">
                <div x-show="filePickerLoading" class="flex items-center justify-center py-12 text-gray-400 text-sm">Cargando...</div>
                <div x-show="!filePickerLoading && filePickerFiles.length === 0"
                     class="flex items-center justify-center py-12 text-gray-400 text-sm">
                    No hay archivos disponibles.
                </div>
                <div x-show="!filePickerLoading" class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-3">
                    <template x-for="file in filePickerFiles" :key="file.id">
                        <button type="button"
                                @click="selectPickerFile(file)"
                                class="group relative aspect-square overflow-hidden rounded-lg border-2 border-transparent hover:border-brand-500 bg-gray-100 transition-all">
                            <template x-if="file.mime_type && file.mime_type.startsWith('video/')">
                                <video :src="file.url" class="h-full w-full object-cover" muted preload="metadata"></video>
                            </template>
                            <template x-if="!(file.mime_type && file.mime_type.startsWith('video/'))">
                                <img :src="file.thumbnail_url || file.url"
                                     :alt="file.original_name || ''"
                                     class="h-full w-full object-cover">
                            </template>
                            <div class="absolute inset-0 bg-brand-600/0 group-hover:bg-brand-600/10 transition-colors"></div>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
const pickerSelectLabels = { image: 'Seleccionar imagen', video: 'Seleccionar video', document: 'Seleccionar documento', any: 'Seleccionar archivo' };
const pickerChangeLabels = { image: 'Cambiar imagen',    video: 'Cambiar video',     document: 'Cambiar documento',     any: 'Cambiar archivo'   };

function blockInstanceBuilder(blockTypes, languages) {
    return {
        blockTypes,
        languages,
        selectedBlockType: null,
        activeLangId: null,
        contentFields: {},
        configFields: {},

        // Repeater state: keyed by `${langId}_${fieldKey}`
        repeaterItems: {},

        // File picker state
        filePickerOpen: false,
        filePickerCallback: null,
        filePickerFiles: [],
        filePickerLoading: false,
        filePickerSearch: '',
        filePickerAccept: 'image',
        pickerSelectLabels,
        pickerChangeLabels,
        // Picked file metadata keyed by `${langId}_${fieldKey}` (top-level file fields)
        pickedFilesMap: {},

        get pickerTitle() {
            const map = { image: 'Seleccionar imagen', video: 'Seleccionar video', document: 'Seleccionar documento', any: 'Seleccionar archivo' };
            return map[this.filePickerAccept] || 'Seleccionar archivo';
        },

        init() {
            const def = this.languages.find(l => l.is_default == 1);
            this.activeLangId = def ? def.id : (this.languages[0]?.id || null);
        },

        selectBlockType(bt) {
            this.selectedBlockType = bt;
            const schema = bt.schema_definition || {};
            this.contentFields = schema.fields       || {};
            this.configFields  = schema.config_fields || {};
            this.repeaterItems = {};
            this.pickedFilesMap = {};
            if (typeof lucide !== 'undefined') { setTimeout(() => lucide.createIcons(), 50); }
        },

        // ── Repeater helpers ─────────────────────────────────────────────────
        repeaterList(langId, fieldKey) {
            const k = `${langId}_${fieldKey}`;
            if (!this.repeaterItems[k]) this.repeaterItems[k] = [];
            return this.repeaterItems[k];
        },

        addItem(langId, fieldKey, itemFields) {
            const k = `${langId}_${fieldKey}`;
            if (!this.repeaterItems[k]) this.repeaterItems[k] = [];
            const item = {};
            Object.keys(itemFields || {}).forEach(subKey => {
                if ((itemFields[subKey] || {}).type === 'file') {
                    item[subKey + '_file_id']    = '';
                    item[subKey + '_url']        = '';
                    item[subKey + '_preview_url'] = '';
                } else {
                    item[subKey] = '';
                }
            });
            this.repeaterItems[k].push(item);
        },

        removeItem(langId, fieldKey, idx) {
            const k = `${langId}_${fieldKey}`;
            if (this.repeaterItems[k]) this.repeaterItems[k].splice(idx, 1);
        },

        // ── File picker helpers ───────────────────────────────────────────────
        getPickedFileId(langId, fieldKey) {
            return (this.pickedFilesMap[`${langId}_${fieldKey}`] || {}).id || '';
        },

        getPickedFileUrl(langId, fieldKey) {
            return (this.pickedFilesMap[`${langId}_${fieldKey}`] || {}).url || '';
        },

        openFilePicker(callback, accept) {
            this.filePickerCallback = callback;
            this.filePickerAccept = accept || 'image';
            this.filePickerOpen = true;
            this.loadPickerFiles();
        },

        async loadPickerFiles() {
            this.filePickerLoading = true;
            try {
                const params = new URLSearchParams({ per_page: 30 });
                if (this.filePickerAccept && this.filePickerAccept !== 'any') params.set('type', this.filePickerAccept);
                if (this.filePickerSearch) params.set('search', this.filePickerSearch);
                const resp = await fetch(`/files/picker-data?${params}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const json = await resp.json();
                this.filePickerFiles = json.data || [];
            } catch (e) {
                this.filePickerFiles = [];
            }
            this.filePickerLoading = false;
        },

        selectPickerFile(file) {
            if (this.filePickerCallback) this.filePickerCallback(file);
            this.filePickerOpen = false;
            this.filePickerCallback = null;
        },

        // pickFile is called by the openFilePicker callback
        // For top-level file fields: langId, fieldKey, itemIdx=null, subKey=null
        // For repeater sub-fields: all four are set
        pickFile(langId, fieldKey, itemIdx, subKey, file) {
            if (itemIdx === null) {
                this.pickedFilesMap[`${langId}_${fieldKey}`] = { id: file.id, url: file.thumbnail_url || file.url };
            } else {
                const k = `${langId}_${fieldKey}`;
                if (this.repeaterItems[k] && this.repeaterItems[k][itemIdx]) {
                    this.repeaterItems[k][itemIdx][subKey + '_file_id']    = file.id;
                    this.repeaterItems[k][itemIdx][subKey + '_url']        = file.url;
                    this.repeaterItems[k][itemIdx][subKey + '_preview_url'] = file.thumbnail_url || file.url;
                }
            }
        },

        openPreview() {
            if (!this.selectedBlockType) return;
            const config = {};
            Object.entries(this.configFields || {}).forEach(([key, field]) => {
                config[key] = field.default || '';
            });
            window.dispatchEvent(new CustomEvent('block-preview-open', {
                detail: { blockKey: this.selectedBlockType.block_key, blockConfig: config, blockData: {} },
            }));
        },
    };
}
</script>
