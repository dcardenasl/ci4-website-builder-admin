<?php $item = $item ?? []; ?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to('admin.cms.entries') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
    <form method="post" action="<?= route_to('admin.cms.entries.delete', (string) ($item['id'] ?? '')) ?>" onsubmit="return confirm('<?= esc(lang('App.confirm_delete')) ?>');">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Entries.entries_edit')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.entries.update', (string) ($item['id'] ?? '')) ?>" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <?= view('components/form/relation', [
            'name' => 'collection_id',
            'label' => 'Entries.field_collection_id',
            'required' => true,
            'options' => $collections ?? [],
            'placeholder' => 'Entries.field_collection_id_placeholder',
            'help' => 'Entries.field_collection_id_help',
            'value' => $item['collection_id'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/select', [
            'name' => 'status',
            'label' => 'Entries.field_status',
            'required' => true,
            'placeholder' => 'Entries.field_status_placeholder',
            'help' => 'Entries.field_status_help',
            'options' => [
                'draft' => lang('Entries.status_draft'),
                'published' => lang('Entries.status_published'),
                'archived' => lang('Entries.status_archived')
            ],
            'value' => $item['status'] ?? $item['workflow_status'] ?? 'draft',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_featured',
            'label' => 'Entries.field_is_featured',
            'value' => $item['is_featured'] ?? false,
            'on_label' => 'Entries.field_is_featured_on',
            'off_label' => 'Entries.field_is_featured_off',
            'help' => 'Entries.field_is_featured_help',
            'errors' => $errors ?? []
        ]) ?>

        <!-- view_count shown as read-only info, not editable -->
        <?php if (isset($item['view_count'])): ?>
            <p class="text-xs text-gray-500"><?= esc(lang('Entries.field_view_count')) ?>: <strong><?= (int) $item['view_count'] ?></strong></p>
        <?php endif; ?>

        <!-- Publishing & Scheduling -->
        <details class="group border border-gray-200 rounded-lg" <?= (!empty($item['published_at']) || !empty($item['scheduled_at'])) ? 'open' : '' ?>>
            <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg select-none">
                <span><?= esc(lang('Entries.section_publishing')) ?></span>
                <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-100">
                <?= view('components/form/number', [
                    'name' => 'sort_order',
                    'label' => 'Entries.field_sort_order',
                    'required' => false,
                    'value' => $item['sort_order'] ?? 0,
                    'placeholder' => 'Entries.field_sort_order_placeholder',
                    'help' => 'Entries.field_sort_order_help',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/datetime', [
                    'name' => 'published_at',
                    'label' => 'Entries.field_published_at',
                    'required' => false,
                    'value' => $item['published_at'] ?? '',
                    'placeholder' => 'Entries.field_published_at_placeholder',
                    'help' => 'Entries.field_published_at_help',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/datetime', [
                    'name' => 'scheduled_at',
                    'label' => 'Entries.field_scheduled_at',
                    'required' => false,
                    'value' => $item['scheduled_at'] ?? '',
                    'placeholder' => 'Entries.field_scheduled_at_placeholder',
                    'help' => 'Entries.field_scheduled_at_help',
                    'errors' => $errors ?? []
                ]) ?>
            </div>
        </details>

        <!-- SEO & Sitemap -->
        <details class="group border border-gray-200 rounded-lg">
            <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg select-none">
                <span><?= esc(lang('Entries.section_seo_sitemap')) ?></span>
                <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-100">
                <?= view('components/form/boolean', [
                    'name' => 'is_in_sitemap',
                    'label' => 'Entries.field_is_in_sitemap',
                    'value' => $item['is_in_sitemap'] ?? true,
                    'on_label' => 'Entries.field_is_in_sitemap_on',
                    'off_label' => 'Entries.field_is_in_sitemap_off',
                    'help' => 'Entries.field_is_in_sitemap_help',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/text', [
                    'name' => 'sitemap_priority',
                    'label' => 'Entries.field_sitemap_priority',
                    'required' => false,
                    'value' => $item['sitemap_priority'] ?? '',
                    'placeholder' => 'Entries.field_sitemap_priority_placeholder',
                    'help' => 'Entries.field_sitemap_priority_help',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/select', [
                    'name' => 'sitemap_changefreq',
                    'label' => 'Entries.field_sitemap_changefreq',
                    'required' => false,
                    'placeholder' => 'Entries.field_sitemap_changefreq_placeholder',
                    'help' => 'Entries.field_sitemap_changefreq_help',
                    'options' => [
                        'always' => lang('Entries.frequency_always'),
                        'hourly' => lang('Entries.frequency_hourly'),
                        'daily' => lang('Entries.frequency_daily'),
                        'weekly' => lang('Entries.frequency_weekly'),
                        'monthly' => lang('Entries.frequency_monthly'),
                        'yearly' => lang('Entries.frequency_yearly'),
                        'never' => lang('Entries.frequency_never'),
                    ],
                    'value' => $item['sitemap_changefreq'] ?? 'weekly',
                    'errors' => $errors ?? []
                ]) ?>
            </div>
        </details>

        <!-- Translations with language tabs -->
        <?php if (!empty($languages)): ?>
            <?php
                $defaultLangId = 0;
            $defaultLangCode = '';
            $defaultLangIndex = 0;
            foreach ($languages as $i => $l) {
                if (!empty($l['is_default'])) {
                    $defaultLangId = (int) $l['id'];
                    $defaultLangCode = $l['code'] ?? '';
                    $defaultLangIndex = $i;
                    break;
                }
            }
            $translateUrl = route_to('admin.cms.translate');
            $allTargets = [];
            foreach ($languages as $i => $l) {
                if (!empty($l['is_default'])) {
                    continue;
                }
                $allTargets[] = [
                    'langCode'   => strtoupper($l['code'] ?? ''),
                    'fieldPairs' => [
                        ['from' => sprintf('[name="translations[%d][title]"]', $defaultLangIndex),            'to' => sprintf('[name="translations[%d][title]"]', $i)],
                        ['from' => sprintf('[name="translations[%d][excerpt]"]', $defaultLangIndex),          'to' => sprintf('[name="translations[%d][excerpt]"]', $i)],
                        ['from' => sprintf('[name="translations[%d][meta_title]"]', $defaultLangIndex),       'to' => sprintf('[name="translations[%d][meta_title]"]', $i)],
                        ['from' => sprintf('[name="translations[%d][meta_description]"]', $defaultLangIndex), 'to' => sprintf('[name="translations[%d][meta_description]"]', $i)],
                    ],
                ];
            }
            ?>
            <div class="border-t border-gray-100 pt-4">
                <h4 class="text-sm font-semibold text-gray-800 mb-3"><?= esc(lang('Entries.translation_title')) ?></h4>

                <div x-data="langTabs(<?= $defaultLangId ?>, '<?= esc($translateUrl, 'attr') ?>', '<?= esc($defaultLangCode, 'attr') ?>')">
                    <div class="flex items-center justify-between border-b border-gray-200 mb-4">
                        <div class="flex gap-0.5" role="tablist">
                            <?php foreach ($languages as $lang): ?>
                                <button type="button"
                                    role="tab"
                                    @click="setTab(<?= (int) $lang['id'] ?>)"
                                    :aria-selected="isActive(<?= (int) $lang['id'] ?>)"
                                    :class="isActive(<?= (int) $lang['id'] ?>) ? 'border-brand-600 text-brand-700 bg-brand-50/40' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                                    <?= esc(strtoupper($lang['code'])) ?>
                                    <?php if (!empty($lang['is_default'])): ?>
                                        <span class="ml-1 text-brand-400">★</span>
                                    <?php endif; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <?php if (!empty($allTargets)): ?>
                        <button type="button"
                            @click="autoTranslateAll(<?= json_encode($allTargets) ?>)"
                            :disabled="translating || translatingAll"
                            class="mb-px inline-flex items-center gap-1.5 text-xs text-brand-600 hover:text-brand-700 border border-brand-200 rounded px-3 py-1.5 bg-brand-50 hover:bg-brand-100 transition-colors disabled:opacity-50">
                            <span x-show="!translatingAll"><?= ui_icon('languages', 'h-3.5 w-3.5') ?> <?= esc(lang('App.translate_all')) ?></span>
                            <span x-show="translatingAll" x-cloak><?= ui_icon('loader', 'h-3.5 w-3.5 animate-spin') ?> <span x-text="translateAllProgress"></span></span>
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Translate error message -->
                    <p x-show="translateError !== ''" x-text="translateError" x-cloak class="mb-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded px-3 py-2"></p>

                    <?php foreach ($languages as $index => $lang): ?>
                        <?php
                            $transValue = [];
                        if (!empty($item['translations']) && is_array($item['translations'])) {
                            foreach ($item['translations'] as $t) {
                                if (is_array($t) && (int)($t['language_id'] ?? 0) === (int)$lang['id']) {
                                    $transValue = $t;
                                    break;
                                }
                            }
                        }
                        $isDefault = !empty($lang['is_default']);
                        $langCode  = strtoupper($lang['code'] ?? '');
                        $fields = [
                            ['from' => sprintf('[name="translations[%d][title]"]', $defaultLangIndex),            'to' => sprintf('[name="translations[%d][title]"]', $index)],
                            ['from' => sprintf('[name="translations[%d][excerpt]"]', $defaultLangIndex),          'to' => sprintf('[name="translations[%d][excerpt]"]', $index)],
                            ['from' => sprintf('[name="translations[%d][meta_title]"]', $defaultLangIndex),       'to' => sprintf('[name="translations[%d][meta_title]"]', $index)],
                            ['from' => sprintf('[name="translations[%d][meta_description]"]', $defaultLangIndex), 'to' => sprintf('[name="translations[%d][meta_description]"]', $index)],
                        ];
                        ?>
                        <div x-show="isActive(<?= (int) $lang['id'] ?>)" class="space-y-4">
                            <input type="hidden" name="translations[<?= $index ?>][language_id]" value="<?= esc($lang['id']) ?>">

                            <?php if (!$isDefault): ?>
                            <div class="flex justify-end">
                                <button type="button"
                                    @click="autoTranslate('<?= esc($langCode, 'attr') ?>', <?= json_encode($fields) ?>)"
                                    :disabled="translating"
                                    class="inline-flex items-center gap-1.5 text-xs text-brand-600 hover:text-brand-700 border border-brand-200 rounded px-3 py-1.5 bg-brand-50 hover:bg-brand-100 transition-colors disabled:opacity-50">
                                    <span x-show="!translating"><?= ui_icon('languages', 'h-3.5 w-3.5') ?> <?= esc(lang('App.translate_from_default')) ?></span>
                                    <span x-show="translating" x-cloak><?= ui_icon('loader', 'h-3.5 w-3.5 animate-spin') ?> <?= esc(lang('App.translating')) ?></span>
                                </button>
                            </div>
                            <?php endif; ?>

                            <?= view('components/form/text', [
                                'name' => "translations[{$index}][title]",
                                'label' => 'Entries.translation_name_label',
                                'required' => !empty($lang['is_default']),
                                'placeholder' => 'Entries.translation_name_placeholder',
                                'help' => 'Entries.translation_name_help',
                                'value' => old("translations.{$index}.title") ?? $transValue['title'] ?? '',
                                'maxlength' => 255,
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/slug', [
                                'name' => "translations[{$index}][slug]",
                                'label' => 'Entries.translation_slug_label',
                                'required' => !empty($lang['is_default']),
                                'sourceId' => sprintf('[name="translations[%d][title]"]', $index),
                                'checkUrl' => route_to('admin.cms.entries.check_slug') . '?language_id=' . (int)$lang['id'],
                                'currentId' => $item['id'] ?? '',
                                'value' => old("translations.{$index}.slug") ?? $transValue['slug'] ?? '',
                                'help' => 'Entries.translation_slug_help',
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/textarea', [
                                'name' => "translations[{$index}][excerpt]",
                                'label' => 'Entries.translation_excerpt_label',
                                'required' => false,
                                'placeholder' => 'Entries.translation_excerpt_placeholder',
                                'help' => 'Entries.translation_excerpt_help',
                                'value' => old("translations.{$index}.excerpt") ?? $transValue['excerpt'] ?? '',
                                'maxlength' => 500,
                                'errors' => $errors ?? []
                            ]) ?>

                            <details class="group border border-gray-100 rounded-lg bg-gray-50/30" <?= (!empty($transValue['meta_title']) || !empty($transValue['meta_description'])) ? 'open' : '' ?>>
                                <summary class="flex cursor-pointer items-center justify-between px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 rounded-lg select-none">
                                    <span><?= esc(lang('Entries.section_seo_per_lang')) ?></span>
                                    <svg class="h-3.5 w-3.5 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                                </summary>
                                <div class="px-3 pb-3 pt-2 space-y-4 border-t border-gray-100">
                                    <?= view('components/form/text', [
                                        'name' => "translations[{$index}][meta_title]",
                                        'label' => 'Entries.translation_meta_title_label',
                                        'required' => false,
                                        'placeholder' => 'Entries.translation_meta_title_placeholder',
                                        'help' => 'Entries.translation_meta_title_help',
                                        'value' => old("translations.{$index}.meta_title") ?? $transValue['meta_title'] ?? '',
                                        'maxlength' => 255,
                                        'errors' => $errors ?? []
                                    ]) ?>
                                    <?= view('components/form/textarea', [
                                        'name' => "translations[{$index}][meta_description]",
                                        'label' => 'Entries.translation_meta_description_label',
                                        'required' => false,
                                        'placeholder' => 'Entries.translation_meta_description_placeholder',
                                        'help' => 'Entries.translation_meta_description_help',
                                        'value' => old("translations.{$index}.meta_description") ?? $transValue['meta_description'] ?? '',
                                        'maxlength' => 500,
                                        'rows' => 3,
                                        'errors' => $errors ?? []
                                    ]) ?>
                                </div>
                            </details>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.update')) ?></button>
            <a href="<?= route_to('admin.cms.entries') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
