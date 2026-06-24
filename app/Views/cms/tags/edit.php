<?php
$item = $item ?? [];
$itemId = (string) ($item['id'] ?? '');
?>

<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.cms.tags'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Tags.tags_details',
    'title' => 'Tags.tags_edit',
    'subtitle' => (string) ($item['slug'] ?? $item['name'] ?? ''),
]) ?>

<form method="post" action="<?= route_to('admin.cms.tags.update', $itemId) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2 space-y-6">
        <?php ob_start(); ?>
        <?php if (!empty($languages)): ?>
            <div class="space-y-6">
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
                                <span class="inline-flex items-center rounded-md bg-brand-50 px-1.5 py-0.5 text-xs font-medium text-brand-700 ring-1 ring-inset ring-brand-700/10"><?= esc(lang('Tags.translation_label_default')) ?></span>
                            <?php endif; ?>
                        </div>

                        <input type="hidden" name="translations[<?= $index ?>][language_id]" value="<?= esc($lang['id']) ?>">

                        <?= view('components/form/text', [
                            'name' => "translations[{$index}][name]",
                            'label' => 'Tags.translation_name_label',
                            'required' => !empty($lang['is_default']),
                            'placeholder' => 'Tags.translation_name_placeholder',
                            'help' => 'Tags.translation_name_help',
                            'value' => old("translations.{$index}.name") ?? $transValue['name'] ?? '',
                            'errors' => $errors ?? []
                        ]) ?>

                        <?= view('components/form/text', [
                            'name' => "translations[{$index}][slug]",
                            'label' => 'Tags.translation_slug_label',
                            'required' => !empty($lang['is_default']),
                            'placeholder' => 'Tags.translation_slug_placeholder',
                            'help' => 'Tags.translation_slug_help',
                            'value' => old("translations.{$index}.slug") ?? $transValue['slug'] ?? '',
                            'errors' => $errors ?? []
                        ]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php $translationsContent = ob_get_clean(); ?>

        <?= view('components/display/form_section', [
            'title' => 'Tags.translations_title',
            'description' => 'Tags.tags_details',
            'content' => $translationsContent,
        ]) ?>
    </div>

    <aside class="space-y-6">
        <?php ob_start(); ?>
        <?= view('components/form/boolean', [
            'name' => 'is_active',
            'label' => 'Tags.field_is_active',
            'value' => $item['is_active'] ?? false,
            'on_label' => 'Tags.field_is_active_on',
            'off_label' => 'Tags.field_is_active_off',
            'help' => 'Tags.field_is_active_help',
            'errors' => $errors ?? []
        ]) ?>
        <?php $metaFields = ob_get_clean(); ?>

        <?= view('components/display/form_section', [
            'title' => 'Tags.tags_details',
            'content' => $metaFields,
            'bodyClass' => 'space-y-4',
        ]) ?>

        <?php ob_start(); ?>
        <button type="submit" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.update')) ?></button>
        <a href="<?= route_to('admin.cms.tags') ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.cancel')) ?></a>
        <?php $actionsContent = ob_get_clean(); ?>

        <?php ob_start(); ?>
        <button type="submit" form="delete-tag-form" class="<?= esc(action_button_class('danger')) ?> w-full justify-center">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
        <?php $dangerContent = ob_get_clean(); ?>

        <?= view('components/display/admin_actions_panel', [
            'content' => $actionsContent,
            'dangerContent' => $dangerContent,
        ]) ?>
    </aside>
</form>

<form id="delete-tag-form" method="post" action="<?= route_to('admin.cms.tags.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($item['name'] ?? $item['slug'] ?? null), 'js') ?>', () => $el.submit())">
    <?= csrf_field() ?>
</form>
