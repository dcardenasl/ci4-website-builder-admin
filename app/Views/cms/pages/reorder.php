<div class="mb-4">
    <a href="<?= route_to('admin.cms.pages') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
</div>

<?= view('components/display/reorder', [
    'items' => $items ?? [],
    'saveUrl' => route_to('admin.cms.pages.save_order'),
    'displayKey' => 'page_type',
    'backUrl' => route_to('admin.cms.pages'),
    'title' => $title ?? lang('App.reorder'),
]) ?>
