<?php $item = $item ?? []; ?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to('admin.cms.categories') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
    <form method="post" action="<?= route_to('admin.cms.categories.delete', (string) ($item['id'] ?? '')) ?>" onsubmit="return confirm('<?= esc(confirm_delete_message($item['name'] ?? $item['slug'] ?? null), 'js') ?>');">
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
            'value' => $item['is_active'] ?? true,
            'on_label' => 'Categories.field_is_active_on',
            'off_label' => 'Categories.field_is_active_off',
            'help' => 'Categories.field_is_active_help',
            'errors' => $errors ?? []
        ]) ?>

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
                        ['from' => sprintf('[name="translations[%d][name]"]', $defaultLangIndex),             'to' => sprintf('[name="translations[%d][name]"]', $i)],
                        ['from' => sprintf('[name="translations[%d][meta_title]"]', $defaultLangIndex),       'to' => sprintf('[name="translations[%d][meta_title]"]', $i)],
                        ['from' => sprintf('[name="translations[%d][meta_description]"]', $defaultLangIndex), 'to' => sprintf('[name="translations[%d][meta_description]"]', $i)],
                    ],
                ];
            }
            ?>
            <div class="border-t border-gray-100 pt-4">
                <h4 class="text-sm font-semibold text-gray-800 mb-3"><?= esc(lang('Categories.translations_title')) ?></h4>

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
                            ['from' => sprintf('[name="translations[%d][name]"]', $defaultLangIndex),             'to' => sprintf('[name="translations[%d][name]"]', $index)],
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
                                'name' => "translations[{$index}][name]",
                                'label' => 'Categories.translation_name_label',
                                'required' => !empty($lang['is_default']),
                                'placeholder' => 'Categories.translation_name_placeholder',
                                'help' => 'Categories.translation_name_help',
                                'value' => old("translations.{$index}.name") ?? $transValue['name'] ?? '',
                                'maxlength' => 150,
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/slug', [
                                'name' => "translations[{$index}][slug]",
                                'label' => 'Categories.translation_slug_label',
                                'required' => !empty($lang['is_default']),
                                'sourceId' => sprintf('[name="translations[%d][name]"]', $index),
                                'checkUrl' => route_to('admin.cms.categories.check_slug') . '?language_id=' . (int)$lang['id'],
                                'currentId' => $item['id'] ?? '',
                                'value' => old("translations.{$index}.slug") ?? $transValue['slug'] ?? '',
                                'help' => 'Categories.translation_slug_help',
                                'errors' => $errors ?? []
                            ]) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.update')) ?></button>
            <a href="<?= route_to('admin.cms.categories') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
