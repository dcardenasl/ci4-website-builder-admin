<div class="mb-4">
    <a href="<?= route_to('admin.cms.redirects') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Cms.redirects_create')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.redirects.store') ?>" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <?= view('components/form/text', [
            'name' => 'from_path',
            'label' => 'Cms.field_from_path',
            'required' => true,
            'value' => $item['from_path'] ?? '',
            'placeholder' => 'Cms.field_from_path_placeholder',
            'help' => 'Cms.field_from_path_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'to_path',
            'label' => 'Cms.field_to_path',
            'required' => true,
            'value' => $item['to_path'] ?? '',
            'placeholder' => 'Cms.field_to_path_placeholder',
            'help' => 'Cms.field_to_path_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/select', [
            'name' => 'status_code',
            'label' => 'Cms.field_status_code',
            'required' => false,
            'placeholder' => 'Cms.field_status_code_placeholder',
            'help' => 'Cms.field_status_code_help',
            'options' => [
                '301' => '301',
                '302' => '302'
            ],
            'value' => $item['status_code'] ?? '',
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

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.create')) ?></button>
            <a href="<?= route_to('admin.cms.redirects') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
