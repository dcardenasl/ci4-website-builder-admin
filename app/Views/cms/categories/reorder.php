<div class="mb-4">
    <a href="<?= route_to('admin.cms.categories') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
</div>

<?= view('components/display/reorder', [
    'items' => $items ?? [],
    'saveUrl' => route_to('admin.cms.categories.save_order'),
    'displayKey' => 'collection_id',
    'backUrl' => route_to('admin.cms.categories'),
    'title' => $title ?? lang('App.reorder'),
]) ?>
