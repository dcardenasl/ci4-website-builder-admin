<?php $entry = $entry ?? []; ?>
<div class="mb-4">
    <a href="<?= route_to('admin.cms.entries') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= lang('Entries.title') ?></a>
</div>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($entry)): ?>
    <?php $itemId = (string) ($entry['id'] ?? ''); ?>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Entries.details') ?></h3>
            <div class="flex items-center gap-2">
                <a href="<?= route_to('admin.cms.entries.edit', $itemId) ?>" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>

                <form method="post" action="<?= route_to('admin.cms.entries.publish', $itemId) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="<?= esc(action_button_class()) ?>">
                        <?= esc(lang('Entries.publish')) ?>
                    </button>
                </form>
                <form method="post" action="<?= route_to('admin.cms.entries.archive', $itemId) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="<?= esc(action_button_class()) ?>">
                        <?= esc(lang('Entries.archive')) ?>
                    </button>
                </form>
                <a href="<?= route_to('admin.cms.entries.reorder') ?>" class="<?= esc(action_button_class('neutral')) ?>">
                    <?= ui_icon('layers', 'h-3.5 w-3.5') ?>
                    <?= esc(lang('Entries.field_sort_order') ?? lang('App.reorder')) ?>
                </a>
                <form method="post" action="<?= route_to('admin.cms.entries.delete', $itemId) ?>" onsubmit="return confirm('<?= esc(lang('App.confirm_delete')) ?>');">
                    <?= csrf_field() ?>
                    <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
                        <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                        <?= esc(lang('App.delete')) ?>
                    </button>
                </form>
            </div>
        </div>

        <dl class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <?= view('components/display/field_row', [
                'label' => 'Entries.field_collection_id',
                'value' => ($collections[(string) ($entry['collection_id'] ?? '')] ?? ($entry['collection_id'] ?? '—'))
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Entries.field_status',
                'value' => ! empty($entry['status']) ? cms_status_badge($entry['status']) : '—',
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Entries.field_published_at',
                'value' => $entry['published_at'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Entries.field_scheduled_at',
                'value' => $entry['scheduled_at'] ?? '—'
            ]) ?>
            <div>
                <dt class="text-gray-500"><?= lang('TableColumns.created_at') ?></dt>
                <dd class="mt-1 text-gray-900"><?= esc((string) ($entry['created_at'] ?? '-')) ?></dd>
            </div>
            
            <?php if (! empty($entry['categories']) && is_array($entry['categories'])): ?>
                <div>
                    <dt class="text-gray-500">Categories</dt>
                    <dd class="mt-1 flex flex-wrap gap-1">
                        <?php foreach ($entry['categories'] as $c): ?>
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                <?= esc($c['name'] ?? $c['slug'] ?? $c['id']) ?>
                            </span>
                        <?php endforeach; ?>
                    </dd>
                </div>
            <?php endif; ?>

            <?php if (! empty($entry['tags']) && is_array($entry['tags'])): ?>
                <div>
                    <dt class="text-gray-500">Tags</dt>
                    <dd class="mt-1 flex flex-wrap gap-1">
                        <?php foreach ($entry['tags'] as $tg): ?>
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                                #<?= esc($tg['name'] ?? $tg['slug'] ?? $tg['id']) ?>
                            </span>
                        <?php endforeach; ?>
                    </dd>
                </div>
            <?php endif; ?>
        </dl>

        <?php if (! empty($entry['translations']) && is_array($entry['translations'])): ?>
            <div class="mt-6 border-t border-gray-200 pt-6">
                <h4 class="text-md font-semibold text-gray-800">Translations / Contenido</h4>
                <div class="mt-4 space-y-4">
                    <?php foreach ($entry['translations'] as $t): ?>
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50">
                            <div class="font-bold text-sm text-brand-700 pb-2 border-b border-gray-200 flex justify-between">
                                <span>Language ID: <?= esc($t['language_id']) ?></span>
                                <span class="text-gray-500 font-mono">/<?= esc($t['slug']) ?></span>
                            </div>
                            <dl class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-xs">
                                <div>
                                    <dt class="text-gray-500 font-semibold">Title</dt>
                                    <dd class="text-gray-900 mt-0.5 font-medium"><?= esc($t['title'] ?? '—') ?></dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-semibold">Excerpt</dt>
                                    <dd class="text-gray-900 mt-0.5"><?= esc($t['excerpt'] ?? '—') ?></dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-semibold">Meta Title</dt>
                                    <dd class="text-gray-900 mt-0.5"><?= esc($t['meta_title'] ?? '—') ?></dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-semibold">Meta Description</dt>
                                    <dd class="text-gray-900 mt-0.5"><?= esc($t['meta_description'] ?? '—') ?></dd>
                                </div>
                            </dl>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (! empty($entry['blocks']) && is_array($entry['blocks'])): ?>
            <div class="mt-6 border-t border-gray-200 pt-6">
                <h4 class="text-md font-semibold text-gray-800">Block Instances</h4>
                <ul class="mt-2 divide-y divide-gray-100 border border-gray-200 rounded-lg">
                    <?php foreach ($entry['blocks'] as $b): ?>
                        <li class="flex items-center justify-between p-3 text-xs">
                            <span class="font-medium text-gray-900"><?= esc($b['block_type'] ?? 'Block') ?></span>
                            <span class="text-gray-500 font-mono">ID: <?= esc($b['id'] ?? '—') ?> (Sort: <?= esc($b['sort_order'] ?? '0') ?>)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
