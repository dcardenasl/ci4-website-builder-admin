<?php
/** @var array<string, array<string, mixed>> $settingsMap */
$settingsMap = $settingsMap ?? [];

$siteName    = (string) ($settingsMap['site_name']['setting_value']    ?? '');
$siteTagline = (string) ($settingsMap['site_tagline']['setting_value'] ?? '');
$baseLanguageId = isset($baseLanguageId) && is_numeric($baseLanguageId) ? (int) $baseLanguageId : null;

$siteNameIsTrans    = !empty($settingsMap['site_name']['is_translatable']);
$siteTaglineIsTrans = !empty($settingsMap['site_tagline']['is_translatable']);

$initialLanguageId = 0;
$translationLanguages = [];
foreach ($languages as $language) {
    $languageId = (int) ($language['id'] ?? 0);
    if ($languageId <= 0) {
        continue;
    }
    if ($baseLanguageId !== null && $languageId === $baseLanguageId) {
        continue;
    }
    $translationLanguages[] = $language;
    $initialLanguageId = $languageId;
    break;
}
foreach ($languages as $language) {
    $languageId = (int) ($language['id'] ?? 0);
    if ($languageId <= 0 || ($baseLanguageId !== null && $languageId === $baseLanguageId)) {
        continue;
    }
    if (! in_array($language, $translationLanguages, true)) {
        $translationLanguages[] = $language;
    }
}

$logoMeta    = $settingsMap['site_logo']['setting_meta'] ?? [];
$logoFileId  = (int) ($settingsMap['site_logo']['setting_value'] ?? 0);
$logoUrl     = is_array($logoMeta) ? (string) ($logoMeta['url'] ?? '') : '';
$logoMime    = is_array($logoMeta) ? (string) ($logoMeta['mime_type'] ?? '') : '';

$faviconMeta   = $settingsMap['favicon']['setting_meta'] ?? [];
$faviconFileId = (int) ($settingsMap['favicon']['setting_value'] ?? 0);
$faviconUrl    = is_array($faviconMeta) ? (string) ($faviconMeta['url'] ?? '') : '';
$faviconMime   = is_array($faviconMeta) ? (string) ($faviconMeta['mime_type'] ?? '') : '';
?>

<div class="space-y-6">

    <!-- Page header -->
    <div>
        <h1 class="text-xl font-semibold text-gray-900"><?= lang('SiteIdentity.page_title') ?></h1>
        <p class="mt-1 text-sm text-gray-500"><?= lang('SiteIdentity.section_intro') ?></p>
    </div>

    <!-- Cache note -->
    <div class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
        <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
        </svg>
        <span><?= lang('SiteIdentity.cache_note') ?></span>
    </div>

    <form method="post" action="<?= route_to('admin.cms.site_identity.update') ?>" class="space-y-6"
          x-data="{ activeLangId: <?= $initialLanguageId ?> }">
        <?= csrf_field() ?>

        <?php ob_start(); ?>
        <div class="space-y-6">
            <?php
                $siteNameIsTrans = !empty($settingsMap['site_name']['is_translatable']);
$siteNameTrans = $settingsMap['site_name']['translations'] ?? [];
$siteTaglineIsTrans = !empty($settingsMap['site_tagline']['is_translatable']);
$siteTaglineTrans = $settingsMap['site_tagline']['translations'] ?? [];
$showTranslations = !empty($languages) && ($siteNameIsTrans || $siteTaglineIsTrans);
?>

            <div class="bg-gray-50 border border-gray-100 rounded-xl p-5 space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= lang('SiteIdentity.core_section') ?></h3>
                        <p class="mt-1 text-sm text-gray-500"><?= lang('SiteIdentity.base_section_intro') ?></p>
                    </div>
                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                        <?= lang('SiteIdentity.base_badge') ?>
                    </span>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('SiteIdentity.field_site_name') ?></label>
                        <input type="text" id="site_name" name="site_name" value="<?= esc($siteName) ?>" placeholder="<?= esc(lang('SiteIdentity.placeholder_site_name')) ?>" class="<?= input_class('site_name') ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('SiteIdentity.field_site_tagline') ?></label>
                        <input type="text" id="site_tagline" name="site_tagline" value="<?= esc($siteTagline) ?>" placeholder="<?= esc(lang('SiteIdentity.placeholder_site_tagline')) ?>" class="<?= input_class('site_tagline') ?>">
                    </div>
                </div>
            </div>

            <?php if ($showTranslations): ?>
                <div class="border-b border-gray-200 pb-2">
                    <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                        <?php foreach ($translationLanguages as $lang): ?>
                            <?php $langId = (int) $lang['id']; ?>
                            <button type="button"
                                    @click="activeLangId = <?= $langId ?>"
                                    :class="activeLangId === <?= $langId ?> ? 'border-brand-500 text-brand-600 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                    class="whitespace-nowrap border-b-2 pb-2 px-1 text-sm transition-colors duration-150">
                                <?= esc($lang['native_name'] ?? $lang['name']) ?> (<?= esc(strtoupper($lang['code'])) ?>)
                            </button>
                        <?php endforeach; ?>
                    </nav>
                </div>

                <div class="space-y-6">
                    <?php if ($siteNameIsTrans): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('SiteIdentity.field_site_name_translations') ?></label>
                            <div class="mt-1">
                                <?php foreach ($translationLanguages as $lang): ?>
                                    <?php
                            $langId = (int) $lang['id'];
                                    $langName = esc($lang['native_name'] ?? $lang['name']);
                                    $val = '';
                                    foreach ($siteNameTrans as $t) {
                                        if ((int) ($t['language_id'] ?? 0) === $langId) {
                                            $val = (string) ($t['setting_value'] ?? '');
                                            break;
                                        }
                                    }
                                    ?>
                                    <div x-show="activeLangId === <?= $langId ?>" x-cloak>
                                        <input type="text" name="site_name_translations[<?= $langId ?>]" value="<?= esc($val) ?>" class="<?= input_class("site_name_translations[$langId]") ?> text-sm" placeholder="<?= esc(lang('SiteIdentity.placeholder_site_name')) ?> (<?= strtolower($langName) ?>)">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($siteTaglineIsTrans): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('SiteIdentity.field_site_tagline_translations') ?></label>
                            <div class="mt-1">
                                <?php foreach ($translationLanguages as $lang): ?>
                                    <?php
                                        $langId = (int) $lang['id'];
                                    $langName = esc($lang['native_name'] ?? $lang['name']);
                                    $val = '';
                                    foreach ($siteTaglineTrans as $t) {
                                        if ((int) ($t['language_id'] ?? 0) === $langId) {
                                            $val = (string) ($t['setting_value'] ?? '');
                                            break;
                                        }
                                    }
                                    ?>
                                    <div x-show="activeLangId === <?= $langId ?>" x-cloak>
                                        <input type="text" name="site_tagline_translations[<?= $langId ?>]" value="<?= esc($val) ?>" class="<?= input_class("site_tagline_translations[$langId]") ?> text-sm" placeholder="<?= esc(lang('SiteIdentity.placeholder_site_tagline')) ?> (<?= strtolower($langName) ?>)">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php $coreIdentityContent = ob_get_clean(); ?>

        <?= view('components/display/form_section', [
            'title' => 'SiteIdentity.core_section',
            'description' => 'SiteIdentity.section_intro',
            'content' => $coreIdentityContent,
            'bodyClass' => 'space-y-6'
        ]) ?>

        <?php ob_start(); ?>
        <div class="space-y-4" x-data="{
            fileId: <?= esc((string) $logoFileId, 'attr') ?>,
            fileUrl: <?= esc(json_encode($logoUrl), 'attr') ?>,
            fileMime: <?= esc(json_encode($logoMime), 'attr') ?>,
            open() {
                Alpine.store('filePicker').show({
                    filterType: 'image',
                    accept: 'image/*',
                    multi: false,
                    onSelect: (file) => {
                        this.fileId   = file.id;
                        this.fileUrl  = window.bestFilePreviewUrl ? window.bestFilePreviewUrl(file) : (file.url || '');
                        this.fileMime = file.mime_type || '';
                    },
                });
            },
            remove() { this.fileId = 0; this.fileUrl = ''; this.fileMime = ''; }
        }">
            <input type="hidden" name="site_logo_file_id" :value="fileId">
            <input type="hidden" name="site_logo_url" :value="fileUrl">
            <input type="hidden" name="site_logo_mime_type" :value="fileMime">
            <div class="flex items-center gap-4">
                <div x-show="fileUrl" class="shrink-0">
                    <img :src="fileUrl" alt="" class="h-16 w-auto rounded border border-gray-200 object-contain bg-gray-50">
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="open()" class="<?= action_button_class() ?> text-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                        </svg>
                        <span x-text="fileId ? '<?= esc(lang('SiteIdentity.change_logo')) ?>' : '<?= esc(lang('SiteIdentity.select_logo')) ?>'"></span>
                    </button>
                    <button type="button" x-show="fileId" @click="remove()" class="<?= action_button_class('danger') ?>">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18m-2 0-.75 12.75A2.25 2.25 0 0 1 15 21.75H9a2.25 2.25 0 0 1-2.25-2.25L6 6m3 0V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6m-6 0h6"/>
                        </svg>
                        <?= lang('SiteIdentity.remove_logo') ?>
                    </button>
                </div>
            </div>
        </div>
        <?php $logoContent = ob_get_clean(); ?>

        <?= view('components/display/form_section', [
            'title' => 'SiteIdentity.field_site_logo',
            'description' => 'SiteIdentity.assets_section',
            'content' => $logoContent,
            'bodyClass' => 'space-y-4'
        ]) ?>

        <?php ob_start(); ?>
        <div class="space-y-4" x-data="{
            fileId: <?= esc((string) $faviconFileId, 'attr') ?>,
            fileUrl: <?= esc(json_encode($faviconUrl), 'attr') ?>,
            fileMime: <?= esc(json_encode($faviconMime), 'attr') ?>,
            open() {
                Alpine.store('filePicker').show({
                    filterType: 'image',
                    accept: 'image/*',
                    multi: false,
                    onSelect: (file) => {
                        this.fileId   = file.id;
                        this.fileUrl  = window.bestFilePreviewUrl ? window.bestFilePreviewUrl(file) : (file.url || '');
                        this.fileMime = file.mime_type || '';
                    },
                });
            },
            remove() { this.fileId = 0; this.fileUrl = ''; this.fileMime = ''; }
        }">
            <input type="hidden" name="favicon_file_id" :value="fileId">
            <input type="hidden" name="favicon_url" :value="fileUrl">
            <input type="hidden" name="favicon_mime_type" :value="fileMime">
            <div class="flex items-center gap-4">
                <div x-show="fileUrl" class="shrink-0">
                    <img :src="fileUrl" alt="" class="h-10 w-10 rounded border border-gray-200 object-contain bg-gray-50">
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="open()" class="<?= action_button_class() ?> text-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                        </svg>
                        <span x-text="fileId ? '<?= esc(lang('SiteIdentity.change_favicon')) ?>' : '<?= esc(lang('SiteIdentity.select_favicon')) ?>'"></span>
                    </button>
                    <button type="button" x-show="fileId" @click="remove()" class="<?= action_button_class('danger') ?>">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18m-2 0-.75 12.75A2.25 2.25 0 0 1 15 21.75H9a2.25 2.25 0 0 1-2.25-2.25L6 6m3 0V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6m-6 0h6"/>
                        </svg>
                        <?= lang('SiteIdentity.remove_favicon') ?>
                    </button>
                </div>
            </div>
        </div>
        <?php $faviconContent = ob_get_clean(); ?>

        <?= view('components/display/form_section', [
            'title' => 'SiteIdentity.field_favicon',
            'description' => 'SiteIdentity.assets_section',
            'content' => $faviconContent,
            'bodyClass' => 'space-y-4'
        ]) ?>

        <div class="flex items-center gap-3">
            <button type="submit" class="<?= action_button_class('primary') ?>">
                <?= lang('App.save') ?>
            </button>
        </div>
    </form>
</div>
