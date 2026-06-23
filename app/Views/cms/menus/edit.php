<?php $item = $item ?? []; ?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to('admin.cms.menus') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
    <form method="post" action="<?= route_to('admin.cms.menus.delete', (string) ($item['id'] ?? '')) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($item['name'] ?? $item['menu_key'] ?? null), 'js') ?>', () => $el.submit())">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
</div>

<?php ob_start(); ?>
<form method="post" action="<?= route_to('admin.cms.menus.update', (string) ($item['id'] ?? '')) ?>" class="space-y-6">
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
<?php $sectionContent = ob_get_clean(); ?>
<?= view('components/display/form_section', [
    'title' => 'Menus.menus_edit',
    'description' => 'Menus.menus_details',
    'content' => $sectionContent,
]) ?>
