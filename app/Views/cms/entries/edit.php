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
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Cms.entries_edit')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.entries.update', (string) ($item['id'] ?? '')) ?>" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <?= view('components/form/relation', [
            'name' => 'collection_id',
            'label' => 'Cms.field_collection_id',
            'required' => false,
            'options' => $collections ?? [],
            'placeholder' => 'Cms.field_collection_id_placeholder',
            'help' => 'Cms.field_collection_id_help',
            'value' => $item['collection_id'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/select', [
            'name' => 'status',
            'label' => 'Cms.field_status',
            'required' => false,
            'placeholder' => 'Cms.field_status_placeholder',
            'help' => 'Cms.field_status_help',
            'options' => [
                'draft' => 'Draft',
                'published' => 'Published',
                'archived' => 'Archived'
            ],
            'value' => $item['status'] ?? '',
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
                                <span class="inline-flex items-center rounded-md bg-brand-50 px-1.5 py-0.5 text-xs font-medium text-brand-700 ring-1 ring-inset ring-brand-700/10">Default</span>
                            <?php endif; ?>
                        </div>
                        
                        <input type="hidden" name="translations[<?= $index ?>][language_id]" value="<?= esc($lang['id']) ?>">

                        <?= view('components/form/text', [
                            'name' => "translations[{$index}][title]",
                            'label' => 'Title',
                            'required' => !empty($lang['is_default']),
                            'placeholder' => 'Enter entry title',
                            'value' => old("translations.{$index}.title") ?? $transValue['title'] ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/text', [
                            'name' => "translations[{$index}][slug]",
                            'label' => 'Slug',
                            'required' => !empty($lang['is_default']),
                            'placeholder' => 'Enter entry slug',
                            'value' => old("translations.{$index}.slug") ?? $transValue['slug'] ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/textarea', [
                            'name' => "translations[{$index}][excerpt]",
                            'label' => 'Excerpt',
                            'required' => false,
                            'placeholder' => 'Enter short excerpt/summary',
                            'value' => old("translations.{$index}.excerpt") ?? $transValue['excerpt'] ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/text', [
                            'name' => "translations[{$index}][meta_title]",
                            'label' => 'SEO Meta Title',
                            'required' => false,
                            'placeholder' => 'Enter SEO title',
                            'value' => old("translations.{$index}.meta_title") ?? $transValue['meta_title'] ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/textarea', [
                            'name' => "translations[{$index}][meta_description]",
                            'label' => 'SEO Meta Description',
                            'required' => false,
                            'placeholder' => 'Enter SEO description',
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
