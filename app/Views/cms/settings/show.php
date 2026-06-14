<?php $setting = $setting ?? []; ?>
<div class="mb-4">
    <a href="<?= route_to('admin.cms.settings') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= lang('Settings.settings_title') ?></a>
</div>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($setting)): ?>
    <?php $itemId = (string) ($setting['id'] ?? ''); ?>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-3xl">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Settings.settings_details') ?></h3>
            <div class="flex items-center gap-2">
                <a href="<?= route_to('admin.cms.settings.edit', $itemId) ?>" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>

                <form method="post" action="<?= route_to('admin.cms.settings.delete', $itemId) ?>" onsubmit="return confirm('<?= esc(lang('App.confirm_delete')) ?>');">
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
                'label' => 'Settings.field_setting_key',
                'value' => $setting['setting_key'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Settings.field_setting_value',
                'value' => (($setting['setting_type'] ?? '') === 'bool')
                    ? (($setting['setting_value'] ?? '') === '1' || $setting['setting_value'] === 'true' || $setting['setting_value'] === true ? lang('App.yes') : lang('App.no'))
                    : ((($setting['setting_type'] ?? '') === 'json')
                        ? '<pre class="font-mono text-xs bg-gray-50 p-2 rounded border">' . esc(json_encode(json_decode($setting['setting_value'] ?? '{}'), JSON_PRETTY_PRINT)) . '</pre>'
                        : esc($setting['setting_value'] ?? '—')),
                'isHtml' => ($setting['setting_type'] ?? '') === 'json'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Settings.field_setting_type',
                'value' => ! empty($setting['setting_type']) ? '<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">' . esc($setting['setting_type']) . '</span>' : '—',
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Settings.field_setting_group',
                'value' => $setting['setting_group'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Settings.field_is_translatable',
                'value' => view('components/table/boolean_cell', ['value' => $setting['is_translatable'] ?? false]),
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Settings.field_description',
                'value' => $setting['description'] ?? '—'
            ]) ?>
            <div>
                <dt class="text-gray-500"><?= lang('TableColumns.created_at') ?></dt>
                <dd class="mt-1 text-gray-900"><?= esc((string) ($setting['created_at'] ?? '-')) ?></dd>
            </div>
        </dl>

        <?php if (! empty($setting['is_translatable']) && ! empty($setting['translations'])): ?>
            <div class="mt-8">
                <h4 class="text-md font-semibold text-gray-900 mb-3"><?= esc(lang('Settings.translations')) ?></h4>
                <div class="<?= esc(table_wrapper_class()) ?>">
                    <table class="<?= esc(table_class()) ?>">
                        <thead class="<?= esc(table_head_class()) ?>">
                            <tr>
                                <th class="<?= esc(table_th_class()) ?>"><?= esc(lang('Settings.field_language')) ?></th>
                                <th class="<?= esc(table_th_class()) ?>"><?= esc(lang('Settings.field_setting_value')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="<?= esc(table_body_class()) ?>">
                            <?php foreach ($setting['translations'] as $t): ?>
                                <tr class="<?= esc(table_row_class()) ?>">
                                    <td class="<?= esc(table_td_class('primary')) ?>"><?= esc($t['language_name'] ?? ($t['language_code'] ?? $t['language_id'])) ?></td>
                                    <td class="<?= esc(table_td_class('muted')) ?>">
                                        <?php if (($setting['setting_type'] ?? '') === 'json'): ?>
                                            <pre class="font-mono text-xs bg-gray-50 p-2 rounded border"><?= esc(json_encode(json_decode($t['setting_value'] ?? '{}'), JSON_PRETTY_PRINT)) ?></pre>
                                        <?php else: ?>
                                            <?= esc($t['setting_value'] ?? '—') ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
