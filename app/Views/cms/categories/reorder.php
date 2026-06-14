<?= view('components/display/reorder', [
    'items' => $items ?? [],
    'saveUrl' => route_to('admin.cms.categories.save_order'),
    'displayKey' => 'collection_id',
    'backUrl' => route_to('admin.cms.categories'),
    'title' => $title ?? lang('App.reorder'),
]);
