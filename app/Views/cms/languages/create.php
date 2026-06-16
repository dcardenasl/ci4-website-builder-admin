<div class="mb-4">
    <a href="<?= route_to('admin.cms.languages') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('CmsLanguages.languages_create')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.languages.store') ?>" class="mt-4 space-y-4">
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
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.create')) ?></button>
            <a href="<?= route_to('admin.cms.languages') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
