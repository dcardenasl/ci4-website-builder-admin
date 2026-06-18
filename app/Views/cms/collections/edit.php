<?php $item = $item ?? []; ?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to('admin.cms.collections') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
    <form method="post" action="<?= route_to('admin.cms.collections.delete', (string) ($item['id'] ?? '')) ?>" onsubmit="return confirm('<?= esc(lang('App.confirm_delete')) ?>');">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Collections.collections_edit')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.collections.update', (string) ($item['id'] ?? '')) ?>" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <?= view('components/form/text', [
            'name' => 'collection_key',
            'label' => 'Collections.field_collection_key',
            'required' => true,
            'value' => $item['collection_key'] ?? '',
            'placeholder' => 'Collections.field_collection_key_placeholder',
            'help' => 'Collections.field_collection_key_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'url_prefix',
            'label' => 'Collections.field_url_prefix',
            'required' => true,
            'value' => $item['url_prefix'] ?? '',
            'placeholder' => 'Collections.field_url_prefix_placeholder',
            'help' => 'Collections.field_url_prefix_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_active',
            'label' => 'Collections.field_is_active',
            'value' => $item['is_active'] ?? true,
            'on_label' => 'Collections.field_is_active_on',
            'off_label' => 'Collections.field_is_active_off',
            'help' => 'Collections.field_is_active_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'requires_approval',
            'label' => 'Collections.field_requires_approval',
            'value' => $item['requires_approval'] ?? false,
            'on_label' => 'Collections.field_requires_approval_on',
            'off_label' => 'Collections.field_requires_approval_off',
            'help' => 'Collections.field_requires_approval_help',
            'errors' => $errors ?? []
        ]) ?>

        <!-- Taxonomy options -->
        <details class="group border border-gray-200 rounded-lg">
            <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg select-none">
                <span><?= esc(lang('Collections.section_taxonomy')) ?></span>
                <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-100">
                <?= view('components/form/boolean', [
                    'name' => 'enables_categories',
                    'label' => 'Collections.field_enables_categories',
                    'value' => $item['enables_categories'] ?? true,
                    'on_label' => 'Collections.field_enables_categories_on',
                    'off_label' => 'Collections.field_enables_categories_off',
                    'help' => 'Collections.field_enables_categories_help',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/boolean', [
                    'name' => 'enables_tags',
                    'label' => 'Collections.field_enables_tags',
                    'value' => $item['enables_tags'] ?? true,
                    'on_label' => 'Collections.field_enables_tags_on',
                    'off_label' => 'Collections.field_enables_tags_off',
                    'help' => 'Collections.field_enables_tags_help',
                    'errors' => $errors ?? []
                ]) ?>
            </div>
        </details>

        <!-- SEO defaults -->
        <details class="group border border-gray-200 rounded-lg">
            <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg select-none">
                <span><?= esc(lang('Collections.section_seo_defaults')) ?></span>
                <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-100">
                <?= view('components/form/number', [
                    'name' => 'sort_order',
                    'label' => 'Collections.field_sort_order',
                    'required' => false,
                    'value' => $item['sort_order'] ?? 0,
                    'placeholder' => 'Collections.field_sort_order_placeholder',
                    'help' => 'Collections.field_sort_order_help',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/decimal', [
                    'name' => 'default_sitemap_priority',
                    'label' => 'Collections.field_default_sitemap_priority',
                    'required' => false,
                    'value' => $item['default_sitemap_priority'] ?? '0.5',
                    'placeholder' => 'Collections.field_default_sitemap_priority_placeholder',
                    'help' => 'Collections.field_default_sitemap_priority_help',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/select', [
                    'name' => 'default_changefreq',
                    'label' => 'Collections.field_default_changefreq',
                    'required' => false,
                    'placeholder' => 'Collections.field_default_changefreq_placeholder',
                    'help' => 'Collections.field_default_changefreq_help',
                    'options' => [
                        'always' => lang('Collections.frequency_always'),
                        'hourly' => lang('Collections.frequency_hourly'),
                        'daily' => lang('Collections.frequency_daily'),
                        'weekly' => lang('Collections.frequency_weekly'),
                        'monthly' => lang('Collections.frequency_monthly'),
                        'yearly' => lang('Collections.frequency_yearly'),
                        'never' => lang('Collections.frequency_never'),
                    ],
                    'value' => $item['default_changefreq'] ?? 'weekly',
                    'errors' => $errors ?? []
                ]) ?>
            </div>
        </details>

        <!-- Translations with language tabs -->
        <?php if (!empty($languages)): ?>
            <?php
                $defaultLangId = 0;
            foreach ($languages as $l) {
                if (!empty($l['is_default'])) {
                    $defaultLangId = (int) $l['id'];
                    break;
                }
            }
            ?>
            <div class="border-t border-gray-100 pt-4">
                <h4 class="text-sm font-semibold text-gray-800 mb-3"><?= esc(lang('Collections.translation_title')) ?></h4>

                <div x-data="langTabs(<?= $defaultLangId ?>)">
                    <div class="flex gap-0.5 border-b border-gray-200 mb-4" role="tablist">
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
                        ?>
                        <div x-show="isActive(<?= (int) $lang['id'] ?>)" class="space-y-4">
                            <input type="hidden" name="translations[<?= $index ?>][language_id]" value="<?= esc($lang['id']) ?>">

                            <?= view('components/form/text', [
                                'name' => "translations[{$index}][name]",
                                'label' => 'Collections.translation_name_label',
                                'required' => !empty($lang['is_default']),
                                'placeholder' => 'Collections.translation_name_placeholder',
                                'help' => 'Collections.translation_name_help',
                                'value' => old("translations.{$index}.name") ?? $transValue['name'] ?? '',
                                'maxlength' => 150,
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/textarea', [
                                'name' => "translations[{$index}][description]",
                                'label' => 'Collections.translation_description_label',
                                'required' => false,
                                'placeholder' => 'Collections.translation_description_placeholder',
                                'help' => 'Collections.translation_description_help',
                                'value' => old("translations.{$index}.description") ?? $transValue['description'] ?? '',
                                'errors' => $errors ?? []
                            ]) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.update')) ?></button>
            <a href="<?= route_to('admin.cms.collections') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
