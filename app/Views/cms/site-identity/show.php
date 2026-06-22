<?php
/** @var array<string, array<string, mixed>> $settingsMap */
$settingsMap = $settingsMap ?? [];

$siteName    = (string) ($settingsMap['site_name']['setting_value']    ?? '');
$siteTagline = (string) ($settingsMap['site_tagline']['setting_value'] ?? '');

$logoMeta    = $settingsMap['site_logo']['setting_meta'] ?? [];
$logoFileId  = (int) ($settingsMap['site_logo']['setting_value'] ?? 0);
$logoUrl     = is_array($logoMeta) ? (string) ($logoMeta['url'] ?? '') : '';
$logoMime    = is_array($logoMeta) ? (string) ($logoMeta['mime_type'] ?? '') : '';

$faviconMeta   = $settingsMap['favicon']['setting_meta'] ?? [];
$faviconFileId = (int) ($settingsMap['favicon']['setting_value'] ?? 0);
$faviconUrl    = is_array($faviconMeta) ? (string) ($faviconMeta['url'] ?? '') : '';
$faviconMime   = is_array($faviconMeta) ? (string) ($faviconMeta['mime_type'] ?? '') : '';
?>

<div class="max-w-2xl space-y-6">

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

    <form method="post" action="<?= route_to('admin.cms.site_identity.update') ?>" class="space-y-6">
        <?= csrf_field() ?>

        <!-- Text fields -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <div>
                <label for="site_name" class="block text-sm font-medium text-gray-700 mb-1">
                    <?= lang('SiteIdentity.field_site_name') ?>
                </label>
                <input type="text" id="site_name" name="site_name"
                       value="<?= esc($siteName) ?>"
                       placeholder="<?= esc(lang('SiteIdentity.placeholder_site_name')) ?>"
                       class="form-input w-full">
            </div>
            <div>
                <label for="site_tagline" class="block text-sm font-medium text-gray-700 mb-1">
                    <?= lang('SiteIdentity.field_site_tagline') ?>
                </label>
                <input type="text" id="site_tagline" name="site_tagline"
                       value="<?= esc($siteTagline) ?>"
                       placeholder="<?= esc(lang('SiteIdentity.placeholder_site_tagline')) ?>"
                       class="form-input w-full">
            </div>
        </div>

        <!-- Site Logo -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
             x-data="{
                fileId: <?= esc((string) $logoFileId, 'attr') ?>,
                fileUrl: <?= esc(json_encode($logoUrl), 'attr') ?>,
                fileMime: <?= esc(json_encode($logoMime), 'attr') ?>,
                open() {
                    openFilePicker((file) => {
                        this.fileId   = file.id;
                        this.fileUrl  = file.url || file.thumbnail_url || '';
                        this.fileMime = file.mime_type || '';
                    }, 'image');
                },
                remove() { this.fileId = 0; this.fileUrl = ''; this.fileMime = ''; }
             }">
            <h3 class="text-sm font-semibold text-gray-900 mb-3"><?= lang('SiteIdentity.field_site_logo') ?></h3>
            <input type="hidden" name="site_logo_file_id"  :value="fileId">
            <input type="hidden" name="site_logo_url"       :value="fileUrl">
            <input type="hidden" name="site_logo_mime_type" :value="fileMime">

            <div class="flex items-center gap-4">
                <div x-show="fileUrl" class="shrink-0">
                    <img :src="fileUrl" alt="" class="h-16 w-auto rounded border border-gray-200 object-contain bg-gray-50">
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="open()"
                            class="<?= action_button_class() ?> text-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                        </svg>
                        <span x-text="fileId ? '<?= esc(lang('SiteIdentity.change_logo')) ?>' : '<?= esc(lang('SiteIdentity.select_logo')) ?>'"></span>
                    </button>
                    <button type="button" x-show="fileId" @click="remove()"
                            class="text-sm text-red-600 hover:text-red-700">
                        <?= lang('SiteIdentity.remove_logo') ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Favicon -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
             x-data="{
                fileId: <?= esc((string) $faviconFileId, 'attr') ?>,
                fileUrl: <?= esc(json_encode($faviconUrl), 'attr') ?>,
                fileMime: <?= esc(json_encode($faviconMime), 'attr') ?>,
                open() {
                    openFilePicker((file) => {
                        this.fileId   = file.id;
                        this.fileUrl  = file.url || file.thumbnail_url || '';
                        this.fileMime = file.mime_type || '';
                    }, 'image');
                },
                remove() { this.fileId = 0; this.fileUrl = ''; this.fileMime = ''; }
             }">
            <h3 class="text-sm font-semibold text-gray-900 mb-3"><?= lang('SiteIdentity.field_favicon') ?></h3>
            <input type="hidden" name="favicon_file_id"  :value="fileId">
            <input type="hidden" name="favicon_url"       :value="fileUrl">
            <input type="hidden" name="favicon_mime_type" :value="fileMime">

            <div class="flex items-center gap-4">
                <div x-show="fileUrl" class="shrink-0">
                    <img :src="fileUrl" alt="" class="h-10 w-10 rounded border border-gray-200 object-contain bg-gray-50">
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="open()"
                            class="<?= action_button_class() ?> text-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                        </svg>
                        <span x-text="fileId ? '<?= esc(lang('SiteIdentity.change_favicon')) ?>' : '<?= esc(lang('SiteIdentity.select_favicon')) ?>'"></span>
                    </button>
                    <button type="button" x-show="fileId" @click="remove()"
                            class="text-sm text-red-600 hover:text-red-700">
                        <?= lang('SiteIdentity.remove_favicon') ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="<?= action_button_class('primary') ?>">
                <?= lang('App.save') ?>
            </button>
        </div>
    </form>
</div>
