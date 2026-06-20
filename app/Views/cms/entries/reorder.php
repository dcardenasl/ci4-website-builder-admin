<?= view('components/display/reorder', [
    'items'        => $items ?? [],
    'saveUrl'      => route_to('admin.cms.entries.save_order'),
    'displayKey'   => 'title',
    'subtitleKeys' => ['collection_key', 'slug'],
    'backUrl'      => route_to('admin.cms.entries'),
    'title'        => $title ?? lang('App.reorder'),
]);
