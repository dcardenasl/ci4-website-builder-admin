<?php $item = $item ?? []; ?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to('admin.cms.languages') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
    <form method="post" action="<?= route_to('admin.cms.languages.delete', (string) ($item['id'] ?? '')) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($item['name'] ?? $item['code'] ?? null), 'js') ?>', () => $el.submit())">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
</div>

<?php ob_start(); ?>
<form method="post" action="<?= route_to('admin.cms.languages.update', (string) ($item['id'] ?? '')) ?>" class="space-y-6">
        <?= csrf_field() ?>

        <?= view('components/form/text', [
            'name' => 'code',
            'label' => 'CmsLanguages.field_code',
            'required' => true,
            'value' => $item['code'] ?? '',
            'placeholder' => 'CmsLanguages.field_code_placeholder',
            'help' => 'CmsLanguages.field_code_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'name',
            'label' => 'CmsLanguages.field_name',
            'required' => true,
            'value' => $item['name'] ?? '',
            'placeholder' => 'CmsLanguages.field_name_placeholder',
            'help' => 'CmsLanguages.field_name_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'native_name',
            'label' => 'CmsLanguages.field_native_name',
            'required' => false,
            'value' => $item['native_name'] ?? '',
            'placeholder' => 'CmsLanguages.field_native_name_placeholder',
            'help' => 'CmsLanguages.field_native_name_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_default',
            'label' => 'CmsLanguages.field_is_default',
            'value' => $item['is_default'] ?? false,
            'on_label' => 'CmsLanguages.field_is_default_on',
            'off_label' => 'CmsLanguages.field_is_default_off',
            'help' => 'CmsLanguages.field_is_default_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_active',
            'label' => 'CmsLanguages.field_is_active',
            'value' => $item['is_active'] ?? false,
            'on_label' => 'CmsLanguages.field_is_active_on',
            'off_label' => 'CmsLanguages.field_is_active_off',
            'help' => 'CmsLanguages.field_is_active_help',
            'errors' => $errors ?? []
        ]) ?>

        <?php
            $fallbackOptions = [];
foreach ($languages ?? [] as $langItem) {
    if (isset($langItem['id']) && isset($langItem['name'])) {
        $fallbackOptions[$langItem['id']] = $langItem['name'] . ' (' . ($langItem['code'] ?? '') . ')';
    }
}
?>

        <?= view('components/form/select', [
    'name' => 'fallback_language_id',
    'label' => 'CmsLanguages.field_fallback_language_id',
    'options' => $fallbackOptions,
    'required' => false,
    'value' => $item['fallback_language_id'] ?? '',
    'placeholder' => 'CmsLanguages.field_fallback_language_placeholder',
    'help' => 'CmsLanguages.field_fallback_language_help',
    'errors' => $errors ?? []
]) ?>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.update')) ?></button>
            <a href="<?= route_to('admin.cms.languages') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
<?php $sectionContent = ob_get_clean(); ?>
<?= view('components/display/form_section', [
    'title' => 'CmsLanguages.languages_edit',
    'description' => 'CmsLanguages.languages_details',
    'content' => $sectionContent,
]) ?>
