<div class="mb-4">
    <a href="<?= route_to('admin.cms.languages') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
</div>

<?= view('components/display/reorder', [
    'items' => $items ?? [],
    'saveUrl' => route_to('admin.cms.languages.save_order'),
    'displayKey' => 'code',
    'backUrl' => route_to('admin.cms.languages'),
    'title' => $title ?? lang('App.reorder'),
]) ?>
