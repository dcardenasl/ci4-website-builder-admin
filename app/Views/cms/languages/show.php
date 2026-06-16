<?php $language = $language ?? []; ?>
<div class="mb-4">
    <a href="<?= route_to('admin.cms.languages') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= lang('CmsLanguages.languages_title') ?></a>
</div>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($language)): ?>
    <?php $itemId = (string) ($language['id'] ?? ''); ?>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('CmsLanguages.languages_details') ?></h3>
            <div class="flex items-center gap-2">
                <?php if (has_permission('cms.languages.write') && !($language['is_default'] ?? false)): ?>
                <form method="post" action="<?= route_to('admin.cms.languages.set_default', $itemId) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="<?= esc(action_button_class('primary')) ?>">
                        <?= esc(lang('CmsLanguages.languages_set_default') ?? 'Set Default') ?>
                    </button>
                </form>
                <?php endif; ?>
                <a href="<?= route_to('admin.cms.languages.edit', $itemId) ?>" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>

                <a href="<?= route_to('admin.cms.languages.reorder') ?>" class="<?= esc(action_button_class('neutral')) ?>">
                    <?= ui_icon('layers', 'h-3.5 w-3.5') ?>
                    <?= esc(lang('CmsLanguages.field_sort_order') ?? lang('App.reorder')) ?>
                </a>
                <form method="post" action="<?= route_to('admin.cms.languages.delete', $itemId) ?>" onsubmit="return confirm('<?= esc(lang('App.confirm_delete')) ?>');">
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
                'label' => 'CmsLanguages.field_code',
                'value' => $language['code'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'CmsLanguages.field_name',
                'value' => $language['name'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'CmsLanguages.field_native_name',
                'value' => $language['native_name'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'CmsLanguages.field_fallback_language_id',
                'value' => $language['fallback_language_id'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'CmsLanguages.field_is_default',
                'value' => view('components/table/boolean_cell', ['value' => $language['is_default'] ?? false]),
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'CmsLanguages.field_is_active',
                'value' => view('components/table/boolean_cell', ['value' => $language['is_active'] ?? false]),
                'isHtml' => true
            ]) ?>
            <div>
                <dt class="text-gray-500"><?= lang('TableColumns.created_at') ?></dt>
                <dd class="mt-1 text-gray-900"><?= esc((string) ($language['created_at'] ?? '-')) ?></dd>
            </div>
        </dl>
    </section>
<?php endif; ?>
