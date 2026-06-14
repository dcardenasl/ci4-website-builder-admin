<div class="mb-4">
    <a href="<?= route_to('admin.cms.settings') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Settings.settings_create')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.settings.store') ?>" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <?= view('components/form/text', [
            'name' => 'setting_key',
            'label' => 'Settings.field_setting_key',
            'required' => true,
            'value' => $item['setting_key'] ?? '',
            'placeholder' => 'Settings.field_setting_key_placeholder',
            'help' => 'Settings.field_setting_key_help',
            'errors' => $errors ?? []
        ]) ?>

    <div x-data="{ settingType: '<?= esc($item['setting_type'] ?? 'string') ?>' }">
        <?= view('components/form/select', [
            'name' => 'setting_type',
            'label' => 'Settings.field_setting_type',
            'required' => true,
            'placeholder' => 'Settings.field_setting_type_placeholder',
            'help' => 'Settings.field_setting_type_help',
            'options' => [
                'string' => lang('BlockTypes.type_string'),
                'int' => lang('BlockTypes.type_int'),
                'bool' => lang('BlockTypes.type_bool'),
                'json' => lang('BlockTypes.type_json'),
                'file_id' => lang('BlockTypes.type_file_id')
            ],
            'value' => $item['setting_type'] ?? 'string',
            'errors' => $errors ?? [],
            'attributes' => ['x-model' => 'settingType']
        ]) ?>

        <!-- String Input -->
        <div x-show="settingType === 'string' || settingType === 'file_id'">
            <?= view('components/form/text', [
                'name' => 'setting_value_string',
                'label' => 'Settings.field_setting_value',
                'required' => false,
                'value' => ($item['setting_type'] ?? '') === 'string' || ($item['setting_type'] ?? '') === 'file_id' ? ($item['setting_value'] ?? '') : '',
                'placeholder' => 'Settings.field_setting_value_placeholder',
                'errors' => $errors ?? [],
                'attributes' => [':name' => "settingType === 'string' || settingType === 'file_id' ? 'setting_value' : ''"]
            ]) ?>
        </div>

        <!-- Integer Input -->
        <div x-show="settingType === 'int'" x-cloak>
            <?= view('components/form/number', [
                'name' => 'setting_value_int',
                'label' => 'Settings.field_setting_value',
                'required' => false,
                'value' => ($item['setting_type'] ?? '') === 'int' ? ($item['setting_value'] ?? '') : '',
                'placeholder' => 'Settings.field_setting_value_placeholder',
                'errors' => $errors ?? [],
                'attributes' => [':name' => "settingType === 'int' ? 'setting_value' : ''"]
            ]) ?>
        </div>

        <!-- Boolean Input -->
        <div x-show="settingType === 'bool'" x-cloak>
            <?= view('components/form/boolean', [
                'name' => 'setting_value_bool',
                'label' => 'Settings.field_setting_value',
                'value' => ($item['setting_type'] ?? '') === 'bool' ? filter_var($item['setting_value'] ?? false, FILTER_VALIDATE_BOOLEAN) : false,
                'on_label' => 'App.yes' ?? 'Yes',
                'off_label' => 'App.no' ?? 'No',
                'errors' => $errors ?? [],
                'attributes' => [':name' => "settingType === 'bool' ? 'setting_value' : ''"]
            ]) ?>
        </div>

        <!-- JSON Textarea -->
        <div x-show="settingType === 'json'" x-cloak>
            <?= view('components/form/textarea', [
                'name' => 'setting_value_json',
                'label' => 'Settings.field_setting_value',
                'required' => false,
                'value' => ($item['setting_type'] ?? '') === 'json' ? ($item['setting_value'] ?? '') : '',
                'placeholder' => 'Settings.field_setting_value_placeholder',
                'errors' => $errors ?? [],
                'rows' => 5,
                'class' => 'font-mono text-sm',
                'attributes' => [':name' => "settingType === 'json' ? 'setting_value' : ''"]
            ]) ?>
        </div>
    </div>

        <?= view('components/form/text', [
            'name' => 'setting_group',
            'label' => 'Settings.field_setting_group',
            'required' => false,
            'value' => $item['setting_group'] ?? '',
            'placeholder' => 'Settings.field_setting_group_placeholder',
            'help' => 'Settings.field_setting_group_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_translatable',
            'label' => 'Settings.field_is_translatable',
            'value' => $item['is_translatable'] ?? false,
            'on_label' => 'Settings.field_is_translatable_on',
            'off_label' => 'Settings.field_is_translatable_off',
            'help' => 'Settings.field_is_translatable_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'description',
            'label' => 'Settings.field_description',
            'required' => false,
            'value' => $item['description'] ?? '',
            'placeholder' => 'Settings.field_description_placeholder',
            'help' => 'Settings.field_description_help',
            'errors' => $errors ?? []
        ]) ?>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.create')) ?></button>
            <a href="<?= route_to('admin.cms.settings') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
