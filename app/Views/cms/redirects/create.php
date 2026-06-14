<div class="mb-4">
    <a href="<?= route_to('admin.cms.redirects') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Redirects.redirects_create')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.redirects.store') ?>" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <?= view('components/form/text', [
            'name' => 'old_path',
            'label' => 'Redirects.field_old_path',
            'required' => true,
            'value' => $item['old_path'] ?? '',
            'placeholder' => 'Redirects.field_old_path_placeholder',
            'help' => 'Redirects.field_old_path_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'new_url',
            'label' => 'Redirects.field_new_url',
            'required' => true,
            'value' => $item['new_url'] ?? '',
            'placeholder' => 'Redirects.field_new_url_placeholder',
            'help' => 'Redirects.field_new_url_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/select', [
            'name' => 'redirect_type',
            'label' => 'Redirects.field_redirect_type',
            'required' => false,
            'placeholder' => 'Redirects.field_redirect_type_placeholder',
            'help' => 'Redirects.field_redirect_type_help',
            'options' => [
                '301' => '301',
                '302' => '302'
            ],
            'value' => $item['redirect_type'] ?? '301',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'note',
            'label' => 'Redirects.field_note',
            'required' => false,
            'value' => $item['note'] ?? '',
            'placeholder' => 'Redirects.field_note_placeholder',
            'help' => 'Redirects.field_note_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_active',
            'label' => 'Redirects.field_is_active',
            'value' => $item['is_active'] ?? false,
            'on_label' => 'Redirects.field_is_active_on',
            'off_label' => 'Redirects.field_is_active_off',
            'help' => 'Redirects.field_is_active_help',
            'errors' => $errors ?? []
        ]) ?>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.create')) ?></button>
            <a href="<?= route_to('admin.cms.redirects') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
