<?php $item = $item ?? []; ?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to('admin.cms.redirects') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
    <form method="post" action="<?= route_to('admin.cms.redirects.delete', (string) ($item['id'] ?? '')) ?>" onsubmit="return confirm('<?= esc(lang('App.confirm_delete')) ?>');">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Redirects.edit')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.redirects.update', (string) ($item['id'] ?? '')) ?>" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <?= view('components/form/text', [
            'name' => 'old_path',
            'label' => 'Cms.field_old_path',
            'required' => true,
            'value' => $item['old_path'] ?? '',
            'placeholder' => 'Cms.field_old_path_placeholder',
            'help' => 'Cms.field_old_path_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'new_url',
            'label' => 'Cms.field_new_url',
            'required' => true,
            'value' => $item['new_url'] ?? '',
            'placeholder' => 'Cms.field_new_url_placeholder',
            'help' => 'Cms.field_new_url_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/select', [
            'name' => 'redirect_type',
            'label' => 'Cms.field_redirect_type',
            'required' => false,
            'placeholder' => 'Cms.field_redirect_type_placeholder',
            'help' => 'Cms.field_redirect_type_help',
            'options' => [
                '301' => '301',
                '302' => '302'
            ],
            'value' => $item['redirect_type'] ?? '301',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'note',
            'label' => 'Cms.field_note',
            'required' => false,
            'value' => $item['note'] ?? '',
            'placeholder' => 'Cms.field_note_placeholder',
            'help' => 'Cms.field_note_help',
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
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.update')) ?></button>
            <a href="<?= route_to('admin.cms.redirects') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
