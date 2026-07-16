<?php
/**
 * Reusable media reference field.
 *
 * Variables:
 *   $name         - base input name, e.g. block_config[image]
 *   $value        - array with source_kind, file_id, url
 *   $legacyFileId - legacy flat file id fallback
 *   $legacyUrl    - legacy flat url fallback
 *   $label        - visible label
 *   $help         - optional help text
 *   $required     - whether the field is required
 *   $accept       - picker accept hint (default: image)
 *   $fieldKey     - optional block_data key used to copy the value to other languages
 *   $copyEnabled  - whether the copy-to-all-languages action should be shown
 *   $previewClass - CSS classes for the preview element
 */

helper('form');

$name = (string) ($name ?? '');
$label = (string) ($label ?? '');
$help = (string) ($help ?? '');
$required = (bool) ($required ?? false);
$accept = (string) ($accept ?? 'image');
$fieldKey = trim((string) ($fieldKey ?? ''));
$copyEnabled = (bool) ($copyEnabled ?? false);
$previewClass = (string) ($previewClass ?? 'h-36 w-full rounded-xl border border-gray-200 object-cover');
$payload = normalize_media_reference_value(
    is_array($value ?? null) ? $value : [],
    $legacyFileId ?? null,
    $legacyUrl ?? null
);
?>

<div class="space-y-2 rounded-xl border border-gray-200 bg-white p-4"
     x-data="mediaReferenceField(<?= esc(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'attr') ?>, <?= esc(json_encode($accept), 'attr') ?>, <?= esc(json_encode($fieldKey), 'attr') ?>)">
    <div class="flex items-center justify-between gap-3">
        <label class="block text-xs font-semibold text-gray-700">
            <?= esc($label) ?>
            <?php if ($required): ?><span class="ml-0.5 text-red-500">*</span><?php endif; ?>
        </label>
        <select x-model="sourceKind"
                @change="setSourceKind($event.target.value)"
                class="rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-700 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
            <option value="hub_file">Archivo del sistema</option>
            <option value="external_url">URL externa</option>
        </select>
    </div>

    <input type="hidden" name="<?= esc($name . '[source_kind]', 'attr') ?>" x-model="sourceKind">
    <input type="hidden" name="<?= esc($name . '[file_id]', 'attr') ?>" x-model="fileId">
    <input :type="isExternalSource() ? 'url' : 'hidden'"
           name="<?= esc($name . '[url]', 'attr') ?>"
           x-model="url"
           @input="syncExternalUrl()"
           placeholder="https://..."
           inputmode="url"
           spellcheck="false"
           class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">

    <div x-show="previewUrl" x-cloak>
        <template x-if="accept === 'video'">
            <video :src="previewUrl" class="<?= esc($previewClass, 'attr') ?>" controls muted></video>
        </template>
        <template x-if="accept === 'image' || accept === 'any'">
            <img :src="previewUrl" class="<?= esc($previewClass, 'attr') ?>">
        </template>
        <template x-if="accept === 'document' || accept === 'audio'">
            <a :href="previewUrl" target="_blank" rel="noopener" class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600 hover:bg-gray-100">
                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span class="truncate" x-text="previewUrl"></span>
            </a>
        </template>
    </div>

    <div class="space-y-2">
        <div x-show="isFileSource() || isExternalSource()" x-cloak class="flex flex-wrap gap-2">
            <button type="button"
                    @click="openPicker()"
                    class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                </svg>
                <span x-text="fileId ? pickerLabels[accept]?.change : pickerLabels[accept]?.select"></span>
            </button>
            <button type="button"
                    @click="clearReference()"
                    x-show="fileId"
                    class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 shadow-sm hover:bg-red-100 transition-colors">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-10.5 0V6a1.5 1.5 0 0 1 1.5-1.5h6A1.5 1.5 0 0 1 16.5 6v1.5m-9 0 .75 10.5A1.5 1.5 0 0 0 9.75 19.5h4.5a1.5 1.5 0 0 0 1.5-1.5L16.5 7.5m-7.5 3v4.5m3-4.5v4.5"/>
                </svg>
                <span><?= esc(lang('App.remove')) ?></span>
            </button>
            <?php if ($copyEnabled && $fieldKey !== ''): ?>
                <button type="button"
                        @click="copyToAllLanguages()"
                        x-show="fileId || url"
                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 shadow-sm hover:bg-brand-100 transition-colors">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 19H9m4 0h4m-11-8h.01M9 3h6m4 0a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m6 0a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m-6 0h4"/>
                    </svg>
                    <span>Copiar a otros idiomas</span>
                </button>
            <?php endif; ?>
        </div>

        <div x-show="isExternalSource()" x-cloak class="space-y-1">
            <p class="text-[11px] text-gray-500">La URL externa se guardará como referencia canónica.</p>
        </div>
    </div>

    <?php if ($help !== ''): ?>
        <p class="text-xs text-gray-500"><?= esc($help) ?></p>
    <?php endif; ?>
</div>
