<div class="mb-4">
    <a href="<?= route_to('admin.cms.languages') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Cms.languages_create')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.languages.store') ?>" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <?= view('components/form/text', [
            'name' => 'code',
            'label' => 'Cms.field_code',
            'required' => true,
            'value' => $item['code'] ?? '',
            'placeholder' => 'Cms.field_code_placeholder',
            'help' => 'Cms.field_code_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'name',
            'label' => 'Cms.field_name',
            'required' => true,
            'value' => $item['name'] ?? '',
            'placeholder' => 'Cms.field_name_placeholder',
            'help' => 'Cms.field_name_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'native_name',
            'label' => 'Cms.field_native_name',
            'required' => false,
            'value' => $item['native_name'] ?? '',
            'placeholder' => 'Cms.field_native_name_placeholder',
            'help' => 'Cms.field_native_name_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_default',
            'label' => 'Cms.field_is_default',
            'value' => $item['is_default'] ?? false,
            'on_label' => 'Cms.field_is_default_on',
            'off_label' => 'Cms.field_is_default_off',
            'help' => 'Cms.field_is_default_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_active',
            'label' => 'Cms.field_is_active',
            'value' => $item['is_active'] ?? false,
            'on_label' => 'Cms.field_is_active_on',
            'off_label' => 'Cms.field_is_active_off',
            'help' => 'Cms.field_is_active_help',
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
        'label' => 'Cms.field_fallback_language_id',
        'options' => $fallbackOptions,
        'required' => false,
        'value' => $item['fallback_language_id'] ?? '',
        'placeholder' => 'Cms.field_fallback_language_placeholder',
        'help' => 'Cms.field_fallback_language_help',
        'errors' => $errors ?? []
    ]) ?>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.create')) ?></button>
            <a href="<?= route_to('admin.cms.languages') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
