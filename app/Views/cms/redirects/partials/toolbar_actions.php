
    <?= view('components/table/export_button', [
        'exportUrl' => route_to('admin.cms.redirects.export_csv'),
        'label' => 'Cms.redirects_export_csv',
    ]) ?>
    <?= view('components/form/export_import', [
        'importUrl' => route_to('admin.cms.redirects.import_csv'),
        'importLabel' => 'Cms.redirects_import_csv',
        'previewView' => 'components/form/import_preview',
    ]) ?>
<a href="<?= route_to('admin.cms.redirects.create') ?>" class="<?= esc(action_button_class('primary')) ?>">
    <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
    <?= lang('Redirects.new') ?>
</a>
