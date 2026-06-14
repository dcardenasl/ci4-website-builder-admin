<?php $item = $item ?? []; ?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to('admin.cms.categories') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
    <form method="post" action="<?= route_to('admin.cms.categories.delete', (string) ($item['id'] ?? '')) ?>" onsubmit="return confirm('<?= esc(lang('App.confirm_delete')) ?>');">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Categories.categories_edit')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.categories.update', (string) ($item['id'] ?? '')) ?>" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <?= view('components/form/relation', [
            'name' => 'collection_id',
            'label' => 'Categories.field_collection_id',
            'required' => true,
            'options' => $collections ?? [],
            'placeholder' => 'Categories.field_collection_id_placeholder',
            'help' => 'Categories.field_collection_id_help',
            'value' => $item['collection_id'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/relation', [
            'name' => 'parent_id',
            'label' => 'Categories.field_parent_id',
            'required' => false,
            'options' => $categories ?? [],
            'placeholder' => 'Categories.field_parent_id_placeholder',
            'help' => 'Categories.field_parent_id_help',
            'value' => $item['parent_id'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/number', [
            'name' => 'sort_order',
            'label' => 'Categories.field_sort_order',
            'required' => false,
            'value' => $item['sort_order'] ?? 0,
            'placeholder' => 'Categories.field_sort_order_placeholder',
            'help' => 'Categories.field_sort_order_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_active',
            'label' => 'Categories.field_is_active',
            'value' => $item['is_active'] ?? false,
            'on_label' => 'Categories.field_is_active_on',
            'off_label' => 'Categories.field_is_active_off',
            'help' => 'Categories.field_is_active_help',
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
                            'name' => "translations[{$index}][name]",
                            'label' => 'Categories.field_name',
                            'required' => !empty($lang['is_default']),
                            'placeholder' => 'Enter category name',
                            'value' => old("translations.{$index}.name") ?? $transValue['name'] ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/text', [
                            'name' => "translations[{$index}][slug]",
                            'label' => 'Categories.field_slug',
                            'required' => !empty($lang['is_default']),
                            'placeholder' => 'Enter category slug',
                            'value' => old("translations.{$index}.slug") ?? $transValue['slug'] ?? '',
                            'errors' => $errors ?? []
                        ]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.update')) ?></button>
            <a href="<?= route_to('admin.cms.categories') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
