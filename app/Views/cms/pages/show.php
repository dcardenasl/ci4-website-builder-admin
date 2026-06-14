<?php $page = $page ?? []; ?>
<div class="mb-4">
    <a href="<?= route_to('admin.cms.pages') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= lang('Pages.pages_title') ?></a>
</div>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($page)): ?>
    <?php $itemId = (string) ($page['id'] ?? ''); ?>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Pages.pages_details') ?></h3>
            <div class="flex items-center gap-2">
                <a href="<?= route_to('admin.cms.pages.edit', $itemId) ?>" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>

                <form method="post" action="<?= route_to('admin.cms.pages.publish', $itemId) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="<?= esc(action_button_class()) ?>">
                        <?= esc(lang('Pages.pages_publish')) ?>
                    </button>
                </form>
                <form method="post" action="<?= route_to('admin.cms.pages.archive', $itemId) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="<?= esc(action_button_class()) ?>">
                        <?= esc(lang('Pages.pages_archive')) ?>
                    </button>
                </form>
                <a href="<?= route_to('admin.cms.pages.reorder') ?>" class="<?= esc(action_button_class('neutral')) ?>">
                    <?= ui_icon('layers', 'h-3.5 w-3.5') ?>
                    <?= esc(lang('Pages.field_sort_order') ?? lang('App.reorder')) ?>
                </a>
                <form method="post" action="<?= route_to('admin.cms.pages.delete', $itemId) ?>" onsubmit="return confirm('<?= esc(lang('App.confirm_delete')) ?>');">
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
                'label' => 'Pages.field_page_type',
                'value' => ! empty($page['page_type']) ? '<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">' . esc($page['page_type']) . '</span>' : '—',
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Pages.field_status',
                'value' => ! empty($page['status']) ? cms_status_badge($page['status']) : '—',
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Pages.field_parent_id',
                'value' => ($pages[(string) ($page['parent_id'] ?? '')] ?? ($page['parent_id'] ?? '—'))
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Pages.field_is_in_sitemap',
                'value' => view('components/table/boolean_cell', ['value' => $page['is_in_sitemap'] ?? false]),
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Pages.field_sitemap_priority',
                'value' => $page['sitemap_priority'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Pages.field_published_at',
                'value' => $page['published_at'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Pages.field_scheduled_at',
                'value' => $page['scheduled_at'] ?? '—'
            ]) ?>
            <div>
                <dt class="text-gray-500"><?= lang('TableColumns.created_at') ?></dt>
                <dd class="mt-1 text-gray-900"><?= esc((string) ($page['created_at'] ?? '-')) ?></dd>
            </div>
        </dl>

        <?php if (! empty($page['translations']) && is_array($page['translations'])): ?>
            <div class="mt-6 border-t border-gray-200 pt-6">
                <h4 class="text-md font-semibold text-gray-800">Translations / Contenido</h4>
                <div class="mt-4 space-y-4">
                    <?php foreach ($page['translations'] as $t): ?>
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
    </section>
<?php endif; ?>
