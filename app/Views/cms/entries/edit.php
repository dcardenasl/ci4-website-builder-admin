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

        <?= view('components/form/number', [
            'name' => 'view_count',
            'label' => 'Entries.field_view_count',
            'required' => false,
            'value' => $item['view_count'] ?? 0,
            'placeholder' => 'Entries.field_view_count_placeholder',
            'help' => 'Entries.field_view_count_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/number', [
            'name' => 'sort_order',
            'label' => 'Entries.field_sort_order',
            'required' => false,
            'value' => $item['sort_order'] ?? 0,
            'placeholder' => 'Entries.field_sort_order_placeholder',
            'help' => 'Entries.field_sort_order_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_in_sitemap',
            'label' => 'Entries.field_is_in_sitemap',
            'value' => $item['is_in_sitemap'] ?? false,
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

        <?php if (!empty($languages)): ?>
            <div class="space-y-6 border-t border-gray-100 pt-6">
                <h4 class="text-md font-semibold text-gray-800"><?= esc(lang('Entries.translation_title')) ?></h4>
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
                    <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50 space-y-4">
                        <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
                            <span class="text-sm font-bold text-brand-700"><?= esc($lang['name']) ?> (<?= esc($lang['code']) ?>)</span>
                            <?php if (!empty($lang['is_default'])): ?>
                                <span class="inline-flex items-center rounded-md bg-brand-50 px-1.5 py-0.5 text-xs font-medium text-brand-700 ring-1 ring-inset ring-brand-700/10"><?= esc(lang('Entries.translation_label_default')) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <input type="hidden" name="translations[<?= $index ?>][language_id]" value="<?= esc($lang['id']) ?>">

                        <?= view('components/form/text', [
                            'name' => "translations[{$index}][title]",
                            'label' => 'Entries.translation_name_label',
                            'required' => !empty($lang['is_default']),
                            'placeholder' => 'Entries.translation_name_placeholder',
                            'help' => 'Entries.translation_name_help',
                            'value' => old("translations.{$index}.title") ?? $transValue['title'] ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/text', [
                            'name' => "translations[{$index}][slug]",
                            'label' => 'Entries.translation_slug_label',
                            'required' => !empty($lang['is_default']),
                            'placeholder' => 'Entries.translation_slug_placeholder',
                            'help' => 'Entries.translation_slug_help',
                            'value' => old("translations.{$index}.slug") ?? $transValue['slug'] ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/textarea', [
                            'name' => "translations[{$index}][excerpt]",
                            'label' => 'Entries.translation_excerpt_label',
                            'required' => false,
                            'placeholder' => 'Entries.translation_excerpt_placeholder',
                            'help' => 'Entries.translation_excerpt_help',
                            'value' => old("translations.{$index}.excerpt") ?? $transValue['excerpt'] ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/text', [
                            'name' => "translations[{$index}][meta_title]",
                            'label' => 'Entries.translation_meta_title_label',
                            'required' => false,
                            'placeholder' => 'Entries.translation_meta_title_placeholder',
                            'help' => 'Entries.translation_meta_title_help',
                            'value' => old("translations.{$index}.meta_title") ?? $transValue['meta_title'] ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/textarea', [
                            'name' => "translations[{$index}][meta_description]",
                            'label' => 'Entries.translation_meta_description_label',
                            'required' => false,
                            'placeholder' => 'Entries.translation_meta_description_placeholder',
                            'help' => 'Entries.translation_meta_description_help',
                            'value' => old("translations.{$index}.meta_description") ?? $transValue['meta_description'] ?? '',
                            'errors' => $errors ?? []
                        ]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.update')) ?></button>
            <a href="<?= route_to('admin.cms.entries') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
