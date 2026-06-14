<div class="mb-4">
    <a href="<?= route_to('admin.cms.pages') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Pages.pages_create')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.pages.store') ?>" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <?= view('components/form/select', [
            'name' => 'page_type',
            'label' => 'Pages.field_page_type',
            'required' => true,
            'placeholder' => 'Pages.field_page_type_placeholder',
            'help' => 'Pages.field_page_type_help',
            'options' => [
                'home' => 'Home',
                'generic' => 'Generic',
                'contact' => 'Contact',
                'privacy' => 'Privacy',
                'terms' => 'Terms',
                '404' => '404',
                '500' => '500',
                'maintenance' => 'Maintenance'
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
                'draft' => 'Draft',
                'published' => 'Published',
                'archived' => 'Archived'
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

        <?= view('components/form/boolean', [
            'name' => 'is_in_sitemap',
            'label' => 'Pages.field_is_in_sitemap',
            'value' => $item['is_in_sitemap'] ?? false,
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
                            'placeholder' => 'Enter page title',
                            'value' => old("translations.{$index}.title") ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/text', [
                            'name' => "translations[{$index}][slug]",
                            'label' => 'Slug',
                            'required' => !empty($lang['is_default']),
                            'placeholder' => 'Enter page slug',
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
            <a href="<?= route_to('admin.cms.pages') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
