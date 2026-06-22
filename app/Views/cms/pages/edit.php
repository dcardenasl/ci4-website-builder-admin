<?php $item = $item ?? []; ?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to('admin.cms.pages') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
    <form method="post" action="<?= route_to('admin.cms.pages.delete', (string) ($item['id'] ?? '')) ?>" onsubmit="return confirm('<?= esc(lang('App.confirm_delete')) ?>');">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Pages.pages_edit')) ?></h3>

    <?php
    $hasTranslationIssues = false;
    $issueDetails = [];
    if (!empty($languages)) {
        foreach ($languages as $lang) {
            $transValue = [];
            if (!empty($item['translations']) && is_array($item['translations'])) {
                foreach ($item['translations'] as $t) {
                    if (is_array($t) && (int)($t['language_id'] ?? 0) === (int)$lang['id']) {
                        $transValue = $t;
                        break;
                    }
                }
            }
            if (empty($transValue)) {
                $hasTranslationIssues = true;
                $issueDetails[] = strtoupper($lang['code']) . ' (Missing)';
            } elseif (empty($transValue['title']) || empty($transValue['slug'])) {
                $hasTranslationIssues = true;
                $issueDetails[] = strtoupper($lang['code']) . ' (Incomplete)';
            }
        }
    }
    ?>

    <?php if ($hasTranslationIssues): ?>
        <div class="mt-4 rounded-lg bg-yellow-50 border border-yellow-200 p-4 text-sm text-yellow-800 flex items-center gap-2">
            <svg class="h-5 w-5 text-yellow-600 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a1 1 0 112 0v5a1 1 0 11-2 0V5zm1 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
            <div>
                <span class="font-bold">Attention:</span> Translation issues detected for: <span class="font-semibold"><?= implode(', ', $issueDetails) ?></span>.
            </div>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= route_to('admin.cms.pages.update', (string) ($item['id'] ?? '')) ?>" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <?= view('components/form/select', [
            'name' => 'page_type',
            'label' => 'Pages.field_page_type',
            'required' => true,
            'placeholder' => 'Pages.field_page_type_placeholder',
            'help' => 'Pages.field_page_type_help',
            'options' => [
                'home' => lang('Pages.page_type_home'),
                'generic' => lang('Pages.page_type_generic'),
                'contact' => lang('Pages.page_type_contact'),
                'privacy' => lang('Pages.page_type_privacy'),
                'terms' => lang('Pages.page_type_terms'),
                '404' => lang('Pages.page_type_404'),
                '500' => lang('Pages.page_type_500'),
                'maintenance' => lang('Pages.page_type_maintenance')
            ],
            'value' => $item['page_type'] ?? 'generic',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/select', [
            'name' => 'status',
            'label' => 'Pages.field_status',
            'required' => true,
            'placeholder' => 'Pages.field_status_placeholder',
            'help' => 'Pages.field_status_help',
            'options' => [
                'draft' => lang('Pages.status_draft'),
                'published' => lang('Pages.status_published'),
                'archived' => lang('Pages.status_archived')
            ],
            'value' => $item['status'] ?? 'draft',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/relation', [
            'name' => 'parent_id',
            'label' => 'Pages.field_parent_id',
            'required' => false,
            'options' => $pages ?? [],
            'placeholder' => 'Pages.field_parent_id_placeholder',
            'help' => 'Pages.field_parent_id_help',
            'value' => $item['parent_id'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/number', [
            'name' => 'sort_order',
            'label' => 'Pages.field_sort_order',
            'required' => false,
            'value' => $item['sort_order'] ?? 0,
            'placeholder' => 'Pages.field_sort_order_placeholder',
            'help' => 'Pages.field_sort_order_help',
            'errors' => $errors ?? []
        ]) ?>

        <!-- Publishing & Scheduling -->
        <details class="group border border-gray-200 rounded-lg" <?= (!empty($item['published_at']) || !empty($item['scheduled_at'])) ? 'open' : '' ?>>
            <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg select-none">
                <span><?= esc(lang('Pages.section_publishing')) ?></span>
                <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-100">
                <?= view('components/form/datetime', [
                    'name' => 'published_at',
                    'label' => 'Pages.field_published_at',
                    'required' => false,
                    'value' => $item['published_at'] ?? '',
                    'placeholder' => 'Pages.field_published_at_placeholder',
                    'help' => 'Pages.field_published_at_help',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/datetime', [
                    'name' => 'scheduled_at',
                    'label' => 'Pages.field_scheduled_at',
                    'required' => false,
                    'value' => $item['scheduled_at'] ?? '',
                    'placeholder' => 'Pages.field_scheduled_at_placeholder',
                    'help' => 'Pages.field_scheduled_at_help',
                    'errors' => $errors ?? []
                ]) ?>
            </div>
        </details>

        <!-- SEO & Sitemap -->
        <details class="group border border-gray-200 rounded-lg">
            <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg select-none">
                <span><?= esc(lang('Pages.section_seo_sitemap')) ?></span>
                <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-100">
                <?= view('components/form/boolean', [
                    'name' => 'is_in_sitemap',
                    'label' => 'Pages.field_is_in_sitemap',
                    'value' => $item['is_in_sitemap'] ?? true,
                    'on_label' => 'Pages.field_is_in_sitemap_on',
                    'off_label' => 'Pages.field_is_in_sitemap_off',
                    'help' => 'Pages.field_is_in_sitemap_help',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/text', [
                    'name' => 'sitemap_priority',
                    'label' => 'Pages.field_sitemap_priority',
                    'required' => false,
                    'value' => $item['sitemap_priority'] ?? '',
                    'placeholder' => 'Pages.field_sitemap_priority_placeholder',
                    'help' => 'Pages.field_sitemap_priority_help',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/select', [
                    'name' => 'sitemap_changefreq',
                    'label' => 'Pages.field_sitemap_changefreq',
                    'required' => false,
                    'placeholder' => 'Pages.field_sitemap_changefreq_placeholder',
                    'help' => 'Pages.field_sitemap_changefreq_help',
                    'options' => [
                        'always' => lang('Pages.sitemap_changefreq_always'),
                        'hourly' => lang('Pages.sitemap_changefreq_hourly'),
                        'daily' => lang('Pages.sitemap_changefreq_daily'),
                        'weekly' => lang('Pages.sitemap_changefreq_weekly'),
                        'monthly' => lang('Pages.sitemap_changefreq_monthly'),
                        'yearly' => lang('Pages.sitemap_changefreq_yearly'),
                        'never' => lang('Pages.sitemap_changefreq_never'),
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
                <h4 class="text-sm font-semibold text-gray-800 mb-3"><?= esc(lang('Pages.translations_title')) ?></h4>

                <div x-data="langTabs(<?= $defaultLangId ?>, '<?= esc($translateUrl, 'attr') ?>', '<?= esc($defaultLangCode, 'attr') ?>')">
                    <!-- Tab bar + translate-all button -->
                    <div class="flex items-center justify-between border-b border-gray-200 mb-4">
                        <div class="flex gap-0.5" role="tablist">
                            <?php foreach ($languages as $lang): ?>
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
                                    $t_status = 'missing';
                                    if (!empty($transValue)) {
                                        $t_status = (!empty($transValue['title']) && !empty($transValue['slug'])) ? 'complete' : 'incomplete';
                                    }
                                ?>
                                <button type="button"
                                    role="tab"
                                    @click="setTab(<?= (int) $lang['id'] ?>)"
                                    :aria-selected="isActive(<?= (int) $lang['id'] ?>)"
                                    :class="isActive(<?= (int) $lang['id'] ?>) ? 'border-brand-600 text-brand-700 bg-brand-50/40' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors inline-flex items-center gap-1.5">
                                    <span><?= esc(strtoupper($lang['code'])) ?></span>
                                    <?php if ($t_status === 'missing'): ?>
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500" title="Missing translation"></span>
                                    <?php elseif ($t_status === 'incomplete'): ?>
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-500" title="Incomplete translation"></span>
                                    <?php else: ?>
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500" title="Complete translation"></span>
                                    <?php endif; ?>
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

                    <!-- Tab panels -->
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
                                'label' => 'Pages.translation_title_label',
                                'required' => !empty($lang['is_default']),
                                'placeholder' => 'Pages.translation_title_placeholder',
                                'help' => 'Pages.translation_title_help',
                                'value' => old("translations.{$index}.title") ?? $transValue['title'] ?? '',
                                'maxlength' => 255,
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/slug', [
                                'name' => "translations[{$index}][slug]",
                                'label' => 'Pages.translation_slug_label',
                                'required' => !empty($lang['is_default']),
                                'sourceId' => sprintf('[name="translations[%d][title]"]', $index),
                                'checkUrl' => route_to('admin.cms.pages.check_slug') . '?language_id=' . (int)$lang['id'],
                                'currentId' => $item['id'] ?? '',
                                'value' => old("translations.{$index}.slug") ?? $transValue['slug'] ?? '',
                                'help' => 'Pages.translation_slug_help',
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/textarea', [
                                'name' => "translations[{$index}][excerpt]",
                                'label' => 'Pages.translation_excerpt_label',
                                'required' => false,
                                'placeholder' => 'Pages.translation_excerpt_placeholder',
                                'help' => 'Pages.translation_excerpt_help',
                                'value' => old("translations.{$index}.excerpt") ?? $transValue['excerpt'] ?? '',
                                'maxlength' => 500,
                                'errors' => $errors ?? []
                            ]) ?>

                            <!-- SEO per language (collapsed, open if has values) -->
                            <details class="group border border-gray-100 rounded-lg bg-gray-50/30" <?= (!empty($transValue['meta_title']) || !empty($transValue['meta_description'])) ? 'open' : '' ?>>
                                <summary class="flex cursor-pointer items-center justify-between px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 rounded-lg select-none">
                                    <span><?= esc(lang('Pages.section_seo_per_lang')) ?></span>
                                    <svg class="h-3.5 w-3.5 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                                </summary>
                                <div class="px-3 pb-3 pt-2 space-y-4 border-t border-gray-100">
                                    <?= view('components/form/text', [
                                        'name' => "translations[{$index}][meta_title]",
                                        'label' => 'Pages.translation_meta_title_label',
                                        'required' => false,
                                        'placeholder' => 'Pages.translation_meta_title_placeholder',
                                        'help' => 'Pages.translation_meta_title_help',
                                        'value' => old("translations.{$index}.meta_title") ?? $transValue['meta_title'] ?? '',
                                        'maxlength' => 255,
                                        'errors' => $errors ?? []
                                    ]) ?>
                                    <?= view('components/form/textarea', [
                                        'name' => "translations[{$index}][meta_description]",
                                        'label' => 'Pages.translation_meta_description_label',
                                        'required' => false,
                                        'placeholder' => 'Pages.translation_meta_description_placeholder',
                                        'help' => 'Pages.translation_meta_description_help',
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
            <a href="<?= route_to('admin.cms.pages') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
