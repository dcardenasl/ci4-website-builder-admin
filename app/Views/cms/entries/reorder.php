<?= view('components/display/reorder', [
    'items' => $items ?? [],
    'saveUrl' => route_to('admin.cms.entries.save_order'),
    'displayKey' => 'collection_id',
    'backUrl' => route_to('admin.cms.entries'),
    'title' => $title ?? lang('App.reorder'),
]);
