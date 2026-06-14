<?php $item = $item ?? []; ?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to('admin.cms.menus') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
    <form method="post" action="<?= route_to('admin.cms.menus.delete', (string) ($item['id'] ?? '')) ?>" onsubmit="return confirm('<?= esc(lang('App.confirm_delete')) ?>');">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Menus.menus_edit')) ?></h3>

    <form method="post" action="<?= route_to('admin.cms.menus.update', (string) ($item['id'] ?? '')) ?>" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <?= view('components/form/text', [
            'name' => 'menu_key',
            'label' => 'Menus.field_menu_key',
            'required' => true,
            'value' => $item['menu_key'] ?? '',
            'placeholder' => 'Menus.field_menu_key_placeholder',
            'help' => 'Menus.field_menu_key_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'location',
            'label' => 'Menus.field_location',
            'required' => true,
            'value' => $item['location'] ?? '',
            'placeholder' => 'Menus.field_location_placeholder',
            'help' => 'Menus.field_location_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_active',
            'label' => 'Menus.field_is_active',
            'value' => $item['is_active'] ?? false,
            'on_label' => 'Menus.field_is_active_on',
            'off_label' => 'Menus.field_is_active_off',
            'help' => 'Menus.field_is_active_help',
            'errors' => $errors ?? []
        ]) ?>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.update')) ?></button>
            <a href="<?= route_to('admin.cms.menus') ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
        </div>
    </form>
</section>
