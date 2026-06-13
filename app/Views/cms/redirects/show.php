<?php $redirect = $redirect ?? []; ?>
<div class="mb-4">
    <a href="<?= route_to('admin.cms.redirects') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= lang('Cms.redirects_title') ?></a>
</div>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($redirect)): ?>
    <?php $itemId = (string) ($redirect['id'] ?? ''); ?>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Cms.redirects_details') ?></h3>
            <div class="flex items-center gap-2">
                <a href="<?= route_to('admin.cms.redirects.edit', $itemId) ?>" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>

                <form method="post" action="<?= route_to('admin.cms.redirects.delete', $itemId) ?>" onsubmit="return confirm('<?= esc(lang('App.confirm_delete')) ?>');">
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
                'label' => 'Cms.field_from_path',
                'value' => $redirect['from_path'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Cms.field_to_path',
                'value' => $redirect['to_path'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Cms.field_status_code',
                'value' => ! empty($redirect['status_code']) ? '<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">' . esc($redirect['status_code']) . '</span>' : '—',
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Cms.field_is_active',
                'value' => view('components/table/boolean_cell', ['value' => $redirect['is_active'] ?? false]),
                'isHtml' => true
            ]) ?>
            <div>
                <dt class="text-gray-500"><?= lang('TableColumns.created_at') ?></dt>
                <dd class="mt-1 text-gray-900"><?= esc((string) ($redirect['created_at'] ?? '-')) ?></dd>
            </div>
        </dl>
    </section>
<?php endif; ?>
