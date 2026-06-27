<?php
/**
 * Reusable translated image field.
 *
 * Variables:
 *   $label                     — translation key for the field label
 *   $help                      — translation key for the help text
 *   $fileIdName                — hidden input name for file ID
 *   $fileUrlName               — hidden input name for file URL
 *   $fileIdInputId             — hidden input id for file ID
 *   $fileUrlInputId            — hidden input id for file URL
 *   $fileIdValue               — current file ID
 *   $fileUrlValue              — current file URL
 *   $copyTargetFileIdSelectors — array of selectors for target file ID inputs
 *   $copyTargetFileUrlSelectors — array of selectors for target file URL inputs
 *   $accept                    — picker accept hint (default: image)
 *   $previewClass              — preview image classes (default: h-32 w-full rounded-lg border border-gray-200 object-cover)
 *   $copyLabel                 — translation key for copy button label
 */

helper('form');

$fileIdName = (string) ($fileIdName ?? '');
$fileUrlName = (string) ($fileUrlName ?? '');
$fileIdInputId = (string) ($fileIdInputId ?? '');
$fileUrlInputId = (string) ($fileUrlInputId ?? '');
$fileIdValue = (string) ($fileIdValue ?? '');
$fileUrlValue = (string) ($fileUrlValue ?? '');
$accept = (string) ($accept ?? 'image');
$help = (string) ($help ?? '');
$copyLabel = (string) ($copyLabel ?? 'Entries.translation_copy_to_other_languages');
$previewClass = (string) ($previewClass ?? 'h-32 w-full rounded-lg border border-gray-200 object-cover');
$copyTargetFileIdSelectors = is_array($copyTargetFileIdSelectors ?? null) ? $copyTargetFileIdSelectors : [];
$copyTargetFileUrlSelectors = is_array($copyTargetFileUrlSelectors ?? null) ? $copyTargetFileUrlSelectors : [];

if ($fileUrlValue === '' && $fileIdValue !== '') {
    $fileUrlValue = site_url('files/' . (int) $fileIdValue . '/view');
}
?>
<div class="space-y-2 rounded-lg border border-gray-200 bg-white p-4"
     x-data="translatableFileField(<?= esc(json_encode($fileIdValue), 'attr') ?>, <?= esc(json_encode($fileUrlValue), 'attr') ?>, <?= esc(json_encode($accept), 'attr') ?>)">
    <input type="hidden"
           id="<?= esc($fileIdInputId, 'attr') ?>"
           name="<?= esc($fileIdName, 'attr') ?>"
           x-model="fileId">
    <input type="hidden"
           id="<?= esc($fileUrlInputId, 'attr') ?>"
           name="<?= esc($fileUrlName, 'attr') ?>"
           x-model="fileUrl">

    <div x-show="previewUrl">
        <img :src="previewUrl" class="<?= esc($previewClass, 'attr') ?>">
    </div>

    <div class="flex flex-wrap gap-2">
        <button type="button"
                @click="openPicker()"
                class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
            </svg>
            <span x-text="fileId ? pickerLabels[accept]?.change : pickerLabels[accept]?.select"></span>
        </button>
        <?php if ($copyTargetFileIdSelectors !== []): ?>
            <button type="button"
                    @click="window.copyLangTabsFileFieldToTargets('<?= esc($fileIdInputId, 'js') ?>', '<?= esc($fileUrlInputId, 'js') ?>', <?= esc(json_encode($copyTargetFileIdSelectors), 'attr') ?>, <?= esc(json_encode($copyTargetFileUrlSelectors), 'attr') ?>)"
                    x-show="fileId"
                    class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 shadow-sm hover:bg-blue-100 transition-colors">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19H9m4 0h4m-11-8h.01M9 3h6m4 0a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m6 0a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m-6 0h4"/>
                </svg>
                <span><?= esc(lang($copyLabel)) ?></span>
            </button>
        <?php endif; ?>
    </div>
    <?php if ($help !== ''): ?>
        <p class="mt-1 text-xs text-gray-500"><?= esc(lang($help)) ?></p>
    <?php endif; ?>
</div>
