<div class="mb-4">
    <a href="<?= route_to('admin.cms.entries') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Cms.entries_create')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.entries.store') ?>" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <?= view('components/form/relation', [
            'name' => 'collection_id',
            'label' => 'Cms.field_collection_id',
            'required' => true,
            'options' => $collections ?? [],
            'placeholder' => 'Cms.field_collection_id_placeholder',
            'help' => 'Cms.field_collection_id_help',
            'value' => $item['collection_id'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/select', [
            'name' => 'status',
            'label' => 'Cms.field_status',
            'required' => true,
            'placeholder' => 'Cms.field_status_placeholder',
            'help' => 'Cms.field_status_help',
            'options' => [
                'draft' => 'Draft',
                'published' => 'Published',
                'archived' => 'Archived'
            ],
            'value' => $item['status'] ?? $item['workflow_status'] ?? 'draft',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_featured',
            'label' => 'Cms.field_is_featured',
            'value' => $item['is_featured'] ?? false,
            'on_label' => 'Cms.field_is_featured_on',
            'off_label' => 'Cms.field_is_featured_off',
            'help' => 'Cms.field_is_featured_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/number', [
            'name' => 'view_count',
            'label' => 'Cms.field_view_count',
            'required' => false,
            'value' => $item['view_count'] ?? 0,
            'placeholder' => 'Cms.field_view_count_placeholder',
            'help' => 'Cms.field_view_count_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/number', [
            'name' => 'sort_order',
            'label' => 'Cms.field_sort_order',
            'required' => false,
            'value' => $item['sort_order'] ?? 0,
            'placeholder' => 'Cms.field_sort_order_placeholder',
            'help' => 'Cms.field_sort_order_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_in_sitemap',
            'label' => 'Cms.field_is_in_sitemap',
            'value' => $item['is_in_sitemap'] ?? false,
            'on_label' => 'Cms.field_is_in_sitemap_on',
            'off_label' => 'Cms.field_is_in_sitemap_off',
            'help' => 'Cms.field_is_in_sitemap_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'sitemap_priority',
            'label' => 'Cms.field_sitemap_priority',
            'required' => false,
            'value' => $item['sitemap_priority'] ?? '',
            'placeholder' => 'Cms.field_sitemap_priority_placeholder',
            'help' => 'Cms.field_sitemap_priority_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/select', [
            'name' => 'sitemap_changefreq',
            'label' => 'Cms.field_sitemap_changefreq',
            'required' => false,
            'placeholder' => 'Cms.field_sitemap_changefreq_placeholder',
            'help' => 'Cms.field_sitemap_changefreq_help',
            'options' => [
                'always' => 'Always',
                'hourly' => 'Hourly',
                'daily' => 'Daily',
                'weekly' => 'Weekly',
                'monthly' => 'Monthly',
                'yearly' => 'Yearly',
                'never' => 'Never',
            ],
            'value' => $item['sitemap_changefreq'] ?? 'weekly',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/datetime', [
            'name' => 'published_at',
            'label' => 'Cms.field_published_at',
            'required' => false,
            'value' => $item['published_at'] ?? '',
            'placeholder' => 'Cms.field_published_at_placeholder',
            'help' => 'Cms.field_published_at_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/datetime', [
            'name' => 'scheduled_at',
            'label' => 'Cms.field_scheduled_at',
            'required' => false,
            'value' => $item['scheduled_at'] ?? '',
            'placeholder' => 'Cms.field_scheduled_at_placeholder',
            'help' => 'Cms.field_scheduled_at_help',
            'errors' => $errors ?? []
        ]) ?>

        <?php if (!empty($languages)): ?>
            <div class="space-y-6 border-t border-gray-100 pt-6">
                <h4 class="text-md font-semibold text-gray-800">Translations / Contenido</h4>
                <?php foreach ($languages as $index => $lang): ?>
                    <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50 space-y-4">
                        <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
                            <span class="text-sm font-bold text-brand-700"><?= esc($lang['name']) ?> (<?= esc($lang['code']) ?>)</span>
                            <?php if (!empty($lang['is_default'])): ?>
                                <span class="inline-flex items-center rounded-md bg-brand-50 px-1.5 py-0.5 text-xs font-medium text-brand-700 ring-1 ring-inset ring-brand-700/10">Default</span>
                            <?php endif; ?>
                        </div>
                        
                        <input type="hidden" name="translations[<?= $index ?>][language_id]" value="<?= esc($lang['id']) ?>">

                        <?= view('components/form/text', [
                            'name' => "translations[{$index}][title]",
                            'label' => 'Title',
                            'required' => !empty($lang['is_default']),
                            'placeholder' => 'Enter entry title',
                            'value' => old("translations.{$index}.title") ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/text', [
                            'name' => "translations[{$index}][slug]",
                            'label' => 'Slug',
                            'required' => !empty($lang['is_default']),
                            'placeholder' => 'Enter entry slug',
                            'value' => old("translations.{$index}.slug") ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/textarea', [
                            'name' => "translations[{$index}][excerpt]",
                            'label' => 'Excerpt',
                            'required' => false,
                            'placeholder' => 'Enter short excerpt/summary',
                            'value' => old("translations.{$index}.excerpt") ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/text', [
                            'name' => "translations[{$index}][meta_title]",
                            'label' => 'SEO Meta Title',
                            'required' => false,
                            'placeholder' => 'Enter SEO title',
                            'value' => old("translations.{$index}.meta_title") ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/textarea', [
                            'name' => "translations[{$index}][meta_description]",
                            'label' => 'SEO Meta Description',
                            'required' => false,
                            'placeholder' => 'Enter SEO description',
                            'value' => old("translations.{$index}.meta_description") ?? '',
                            'errors' => $errors ?? []
                        ]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.create')) ?></button>
            <a href="<?= route_to('admin.cms.entries') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
