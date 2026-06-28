<?php
/**
 * Site Identity — Metadata-driven view.
 *
 * Renders every setting in the 'identity' group using its input_type.
 * To add a new identity field, create a cms_setting with group='identity'
 * and the correct input_type — no code change required here.
 *
 * Layout:
 *   - Left (2/3): all text/bool/select/etc. settings stacked in one card
 *   - Right (1/3): image/file pickers (brand assets)
 *
 * @var array<string, array<string, mixed>> $settingsMap   keyed by setting_key
 * @var array<int, array<string, mixed>>    $languages
 * @var int|null                            $baseLanguageId
 */
$settingsMap    = $settingsMap ?? [];
$languages      = $languages ?? [];
$baseLanguageId = isset($baseLanguageId) && is_numeric($baseLanguageId) ? (int) $baseLanguageId : null;

$translationLanguages = [];
$initialLangId        = 0;
foreach ($languages as $language) {
    $langId = (int) ($language['id'] ?? 0);
    if ($langId <= 0 || $langId === $baseLanguageId) {
        continue;
    }
    if ($initialLangId === 0) {
        $initialLangId = $langId;
    }
    $translationLanguages[] = $language;
}

$sortedSettings = array_values($settingsMap);
usort($sortedSettings, fn ($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

$assetSettings   = array_values(array_filter($sortedSettings, fn ($s) => in_array($s['input_type'] ?? 'text', ['image', 'file'], true)));
$contentSettings = array_values(array_filter($sortedSettings, fn ($s) => !in_array($s['input_type'] ?? 'text', ['image', 'file'], true)));

/** @param array<string, mixed> $setting */
function identity_resolve_label(array $setting): string
{
    foreach ($setting['translations'] ?? [] as $t) {
        if (!empty($t['label'])) {
            return (string) $t['label'];
        }
    }
    return (string) ($setting['description'] ?? $setting['setting_key'] ?? '');
}

/** @param array<string, mixed> $setting */
function identity_resolve_placeholder(array $setting): string
{
    foreach ($setting['translations'] ?? [] as $t) {
        if (!empty($t['placeholder'])) {
            return (string) $t['placeholder'];
        }
    }
    return '';
}

/** @param array<string, mixed> $setting */
function identity_resolve_help(array $setting): string
{
    foreach ($setting['translations'] ?? [] as $t) {
        if (!empty($t['help_text'])) {
            return (string) $t['help_text'];
        }
    }
    return '';
}

/** @param array<string, mixed> $setting */
function identity_get_translation(array $setting, int $langId, string $field = 'setting_value'): string
{
    foreach ($setting['translations'] ?? [] as $t) {
        if ((int) ($t['language_id'] ?? 0) === $langId && isset($t[$field])) {
            return (string) $t[$field];
        }
    }
    return '';
}
?>

<div class="space-y-5">

    <div>
        <h1 class="text-xl font-semibold text-gray-900"><?= lang('SiteIdentity.page_title') ?></h1>
        <p class="mt-1 text-sm text-gray-500"><?= lang('SiteIdentity.section_intro') ?></p>
    </div>

    <?php if (empty($sortedSettings)): ?>

        <div class="rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-500">
            <?= lang('SiteIdentity.no_settings') ?>
        </div>

    <?php else: ?>

    <div class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
        <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
        </svg>
        <span><?= lang('SiteIdentity.cache_note') ?></span>
    </div>

    <form method="post" action="<?= route_to('admin.cms.site_identity.update') ?>"
          x-data="{ activeLangId: <?= $initialLangId ?> }" class="space-y-6">
        <?= csrf_field() ?>

        <?php
        $hasContent = !empty($contentSettings);
        $hasAssets  = !empty($assetSettings);
        $colClass   = ($hasContent && $hasAssets) ? 'grid grid-cols-1 gap-6 lg:grid-cols-3' : '';
        ?>
        <div class="<?= esc($colClass) ?>">

            <?php if ($hasContent): ?>
            <div class="<?= $hasAssets ? 'lg:col-span-2' : '' ?> space-y-6">

                <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= lang('SiteIdentity.core_section') ?></h3>
                    </div>
                    <div class="divide-y divide-gray-100">

                    <?php foreach ($contentSettings as $idx => $setting):
                        $key         = (string) ($setting['setting_key'] ?? '');
                        $inputType   = (string) ($setting['input_type'] ?? 'text');
                        $currentVal  = (string) ($setting['setting_value'] ?? '');
                        $isTrans     = !empty($setting['is_translatable']);
                        $isReadonly  = !empty($setting['is_readonly']);
                        $label       = identity_resolve_label($setting);
                        $placeholder = identity_resolve_placeholder($setting);
                        $helpText    = identity_resolve_help($setting);
                        ?>
                    <div class="px-5 py-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            <?= esc($label) ?>
                            <?php if (!empty($setting['is_required'])): ?>
                                <span class="text-red-400 ml-0.5" aria-hidden="true">*</span>
                            <?php endif; ?>
                        </label>

                        <?php if ($inputType === 'boolean'): ?>
                            <?= view('components/form/boolean', [
                                    'name'      => "{$key}_value",
                                    'label'     => '',
                                    'value'     => filter_var($currentVal, FILTER_VALIDATE_BOOLEAN),
                                    'on_label'  => 'App.yes',
                                    'off_label' => 'App.no',
                                    'readonly'  => $isReadonly,
                                    'errors'    => $errors ?? [],
                                ]) ?>

                        <?php elseif ($inputType === 'textarea' || $inputType === 'richtext'): ?>
                            <textarea name="<?= esc($key) ?>_value"
                                      rows="4"
                                      placeholder="<?= esc($placeholder) ?>"
                                      class="form-input text-sm"
                                      <?= $isReadonly ? 'readonly' : '' ?>><?= esc($currentVal) ?></textarea>

                        <?php elseif ($inputType === 'code'): ?>
                            <textarea name="<?= esc($key) ?>_value"
                                      rows="5"
                                      placeholder="<?= esc($placeholder) ?>"
                                      class="form-input font-mono text-sm bg-gray-950 text-green-400 border-gray-700"
                                      <?= $isReadonly ? 'readonly' : '' ?>><?= esc($currentVal) ?></textarea>

                        <?php elseif ($inputType === 'select'):
                            $rawOptions = $setting['options_json'] ?? null;
                            $options    = [];
                            if (is_array($rawOptions)) {
                                foreach ($rawOptions as $opt) {
                                    $options[(string) ($opt['value'] ?? '')] = (string) ($opt['label'] ?? '');
                                }
                            }
                            ?>
                            <select name="<?= esc($key) ?>_value" class="form-input text-sm" <?= $isReadonly ? 'disabled' : '' ?>>
                                <?php foreach ($options as $optVal => $optLabel): ?>
                                    <option value="<?= esc($optVal) ?>" <?= ($currentVal === $optVal) ? 'selected' : '' ?>>
                                        <?= esc($optLabel) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        <?php else:
                            $htmlType = match ($inputType) {
                                'url'    => 'url',
                                'email'  => 'email',
                                'phone'  => 'tel',
                                'color'  => 'color',
                                'number' => 'number',
                                'slug'   => 'text',
                                default  => 'text',
                            };
                            ?>
                            <input type="<?= esc($htmlType) ?>"
                                   name="<?= esc($key) ?>_value"
                                   value="<?= esc($currentVal) ?>"
                                   placeholder="<?= esc($placeholder) ?>"
                                   class="form-input text-sm"
                                   <?= $isReadonly ? 'readonly' : '' ?>>
                        <?php endif; ?>

                        <?php if ($helpText !== ''): ?>
                            <p class="mt-1 text-xs text-gray-400"><?= esc($helpText) ?></p>
                        <?php endif; ?>

                        <?php if ($isTrans && !empty($translationLanguages)): ?>
                            <div class="mt-3 rounded-lg border border-gray-100 bg-gray-50 px-3 pt-2 pb-3">
                                <div class="mb-2 flex gap-3 border-b border-gray-200">
                                    <?php foreach ($translationLanguages as $tLang): ?>
                                        <?php $tId = (int) $tLang['id']; ?>
                                        <button type="button"
                                                @click="activeLangId = <?= $tId ?>"
                                                :class="activeLangId === <?= $tId ?> ? 'border-brand-500 text-brand-600 font-semibold' : 'border-transparent text-gray-400 hover:text-gray-600'"
                                                class="whitespace-nowrap border-b-2 pb-1.5 px-0.5 text-xs transition-colors">
                                            <?= esc(strtoupper((string) ($tLang['code'] ?? ''))) ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                                <?php foreach ($translationLanguages as $tLang):
                                    $tId  = (int) $tLang['id'];
                                    $tVal = identity_get_translation($setting, $tId, 'setting_value');
                                    ?>
                                    <div x-show="activeLangId === <?= $tId ?>" x-cloak>
                                        <input type="text"
                                               name="<?= esc($key) ?>_translations[<?= $tId ?>]"
                                               value="<?= esc($tVal) ?>"
                                               class="form-input text-sm"
                                               placeholder="<?= esc($label) ?> (<?= esc(strtolower((string) ($tLang['native_name'] ?? $tLang['name'] ?? ''))) ?>)"
                                               <?= $isReadonly ? 'readonly' : '' ?>>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    </div>
                </section>

            </div>
            <?php endif; ?>

            <?php if ($hasAssets): ?>
            <aside class="space-y-4">
                <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= lang('SiteIdentity.assets_section') ?></h3>
                    </div>
                    <div class="divide-y divide-gray-100 px-5">

                    <?php foreach ($assetSettings as $setting):
                        $key        = (string) ($setting['setting_key'] ?? '');
                        $inputType  = (string) ($setting['input_type'] ?? 'image');
                        $isReadonly = !empty($setting['is_readonly']);
                        $label      = identity_resolve_label($setting);
                        $helpText   = identity_resolve_help($setting);
                        $fileId     = (string) ($setting['setting_value'] ?? '');
                        $fpAccept   = ($inputType === 'image') ? 'image/*' : '';
                        $fpFilter   = ($inputType === 'image') ? 'image' : '';
                        ?>
                    <div class="py-4">
                        <p class="mb-2 text-sm font-medium text-gray-700"><?= esc($label) ?></p>

                        <div x-data="filePickerField({
                                name: '<?= esc("{$key}_file_id", 'js') ?>',
                                value: '<?= esc($fileId, 'js') ?>',
                                accept: '<?= esc($fpAccept, 'js') ?>',
                                filterType: '<?= esc($fpFilter, 'js') ?>'
                            })"
                             x-init="init()"
                             <?= $isReadonly ? 'data-readonly="true"' : '' ?>>

                            <input type="hidden" :name="fieldName" :value="fileId">
                            <input type="hidden" name="<?= esc($key) ?>_url" :value="fileInfo.url">
                            <input type="hidden" name="<?= esc($key) ?>_mime_type" :value="fileInfo.mime_type">

                            <!-- File selected -->
                            <div x-show="fileId !== ''" x-cloak
                                 class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center overflow-hidden rounded-md border border-gray-100 bg-white">
                                    <template x-if="fileInfo.is_image && fileInfo.url !== ''">
                                        <img :src="fileInfo.url" :alt="fileInfo.original_name" class="h-full w-full object-cover">
                                    </template>
                                    <template x-if="!fileInfo.is_image || fileInfo.url === ''">
                                        <?= ui_icon('file', 'h-7 w-7 text-gray-400') ?>
                                    </template>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900" x-text="fileInfo.original_name || '—'"></p>
                                    <p class="text-xs text-gray-400" x-text="fileInfo.human_size"></p>
                                </div>
                                <?php if (!$isReadonly): ?>
                                <div class="flex flex-shrink-0 flex-col gap-1">
                                    <button type="button" @click="openPicker()" class="<?= action_button_class() ?> text-xs">
                                        <?= esc(lang('Files.picker_change')) ?>
                                    </button>
                                    <button type="button" @click="clearFile()" class="<?= action_button_class('danger') ?> text-xs">
                                        <?= esc(lang('App.remove')) ?>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- No file selected -->
                            <button type="button"
                                    x-show="fileId === ''"
                                    @click="<?= $isReadonly ? '' : 'openPicker()' ?>"
                                    <?= $isReadonly ? 'disabled' : '' ?>
                                    class="flex w-full cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-5 text-center transition-colors <?= $isReadonly ? 'opacity-60 cursor-not-allowed' : 'hover:border-brand-400 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500' ?>">
                                <div class="flex flex-col items-center gap-2">
                                    <?= ui_icon('upload', 'h-7 w-7 text-gray-400') ?>
                                    <p class="text-sm text-gray-400"><?= esc(lang('SiteIdentity.select_file')) ?></p>
                                </div>
                            </button>

                            <!-- Loading -->
                            <div x-show="loading" x-cloak class="mt-1 flex items-center gap-1 text-xs text-gray-400">
                                <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <?= esc(lang('App.loading')) ?>
                            </div>
                        </div>

                        <?php if ($helpText !== ''): ?>
                            <p class="mt-1.5 text-xs text-gray-400"><?= esc($helpText) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    </div>
                </section>
            </aside>
            <?php endif; ?>

        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= action_button_class('primary') ?>">
                <?= lang('App.save') ?>
            </button>
        </div>

    </form>
    <?php endif; ?>

</div>
