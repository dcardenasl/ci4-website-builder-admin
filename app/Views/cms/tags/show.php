<?php $tag = $tag ?? []; ?>
<div class="mb-4">
    <a href="<?= route_to('admin.cms.tags') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= lang('Tags.tags_title') ?></a>
</div>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($tag)): ?>
    <?php $itemId = (string) ($tag['id'] ?? ''); ?>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Tags.tags_details') ?></h3>
            <div class="flex items-center gap-2">
                <a href="<?= route_to('admin.cms.entries') . '?tag_id=' . urlencode($itemId) ?>" class="<?= esc(action_button_class('neutral')) ?>">
                    <?= ui_icon('link', 'h-3.5 w-3.5') ?>
                    View Entries
                </a>
                <a href="<?= route_to('admin.cms.tags.edit', $itemId) ?>" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>

                <form method="post" action="<?= route_to('admin.cms.tags.delete', $itemId) ?>" onsubmit="return confirm('<?= esc(lang('App.confirm_delete')) ?>');">
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
                'label' => 'Tags.field_is_active',
                'value' => view('components/table/boolean_cell', ['value' => $tag['is_active'] ?? false]),
                'isHtml' => true
            ]) ?>
            <div>
                <dt class="text-gray-500"><?= lang('TableColumns.created_at') ?></dt>
                <dd class="mt-1 text-gray-900"><?= esc((string) ($tag['created_at'] ?? '-')) ?></dd>
            </div>
        </dl>

        <?php if (! empty($tag['translations']) && is_array($tag['translations'])): ?>
            <div class="mt-6 border-t border-gray-200 pt-6">
                <h4 class="text-md font-semibold text-gray-800">Translations / Contenido</h4>
                <div class="mt-4 space-y-4">
                    <?php foreach ($tag['translations'] as $t): ?>
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50">
                            <div class="font-bold text-sm text-brand-700 pb-2 border-b border-gray-200 flex justify-between">
                                <span>Language ID: <?= esc($t['language_id']) ?></span>
                                <span class="text-gray-500 font-mono">/<?= esc($t['slug']) ?></span>
                            </div>
                            <dl class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-xs">
                                <div>
                                    <dt class="text-gray-500 font-semibold">Name</dt>
                                    <dd class="text-gray-900 mt-0.5 font-medium"><?= esc($t['name'] ?? '—') ?></dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-semibold">Slug</dt>
                                    <dd class="text-gray-900 mt-0.5"><?= esc($t['slug'] ?? '—') ?></dd>
                                </div>
                            </dl>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
