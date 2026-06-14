<div class="mb-4">
    <a href="<?= route_to('admin.cms.collections') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Collections.collections_create')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.collections.store') ?>" class="mt-4 space-y-4">
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

        <?= view('components/form/number', [
            'name' => 'sort_order',
            'label' => 'Collections.field_sort_order',
            'required' => false,
            'value' => $item['sort_order'] ?? 0,
            'placeholder' => 'Collections.field_sort_order_placeholder',
            'help' => 'Collections.field_sort_order_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_active',
            'label' => 'Collections.field_is_active',
            'value' => $item['is_active'] ?? false,
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

        <?= view('components/form/boolean', [
            'name' => 'enables_categories',
            'label' => 'Collections.field_enables_categories',
            'value' => $item['enables_categories'] ?? false,
            'on_label' => 'Collections.field_enables_categories_on',
            'off_label' => 'Collections.field_enables_categories_off',
            'help' => 'Collections.field_enables_categories_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'enables_tags',
            'label' => 'Collections.field_enables_tags',
            'value' => $item['enables_tags'] ?? false,
            'on_label' => 'Collections.field_enables_tags_on',
            'off_label' => 'Collections.field_enables_tags_off',
            'help' => 'Collections.field_enables_tags_help',
            'errors' => $errors ?? []
        ]) ?>

        <?php if (!empty($languages)): ?>
            <div class="space-y-6 border-t border-gray-100 pt-6">
                <h4 class="text-md font-semibold text-gray-800"><?= esc(lang('Collections.translation_title')) ?></h4>
                <?php foreach ($languages as $index => $lang): ?>
                    <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50 space-y-4">
                        <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
                            <span class="text-sm font-bold text-brand-700"><?= esc($lang['name']) ?> (<?= esc($lang['code']) ?>)</span>
                            <?php if (!empty($lang['is_default'])): ?>
                                <span class="inline-flex items-center rounded-md bg-brand-50 px-1.5 py-0.5 text-xs font-medium text-brand-700 ring-1 ring-inset ring-brand-700/10"><?= esc(lang('Collections.translation_label_default')) ?></span>
                            <?php endif; ?>
                        </div>

                        <input type="hidden" name="translations[<?= $index ?>][language_id]" value="<?= esc($lang['id']) ?>">

                        <?= view('components/form/text', [
                            'name' => "translations[{$index}][name]",
                            'label' => 'Collections.translation_name_label',
                            'required' => !empty($lang['is_default']),
                            'placeholder' => 'Collections.translation_name_placeholder',
                            'value' => old("translations.{$index}.name") ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/textarea', [
                            'name' => "translations[{$index}][description]",
                            'label' => 'Collections.translation_description_label',
                            'required' => false,
                            'placeholder' => 'Collections.translation_description_placeholder',
                            'value' => old("translations.{$index}.description") ?? '',
                            'errors' => $errors ?? []
                        ]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.create')) ?></button>
            <a href="<?= route_to('admin.cms.collections') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
