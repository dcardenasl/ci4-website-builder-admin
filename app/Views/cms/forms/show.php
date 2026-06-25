<?php $form = $form ?? []; ?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($form)): ?>
    <?php
    $itemId = (string) ($form['id'] ?? '');
    $formTitle = (string) (($form['translations'][0]['name'] ?? null) ?: $form['form_key'] ?: $itemId);

    // Build language code map: id → uppercase code
    $langCodeMap = [];
    foreach ($languages ?? [] as $l) {
        if (is_array($l) && isset($l['id'], $l['code'])) {
            $langCodeMap[(int) $l['id']] = strtoupper((string) $l['code']);
        }
    }
    ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.cms.forms'),
        'backLabel' => 'Forms.title',
        'eyebrow' => 'Forms.show_title',
        'title' => $formTitle,
        'badge' => view('components/table/boolean_cell', ['value' => $form['is_active'] ?? false]),
    ]) ?>

    <?php ob_start(); ?>
    <div class="space-y-6">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Forms.section_general') ?></h3>
            <dl class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <?= view('components/display/field_row', [
                    'label' => 'Forms.field_key',
                    'value' => '<code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-700">' . esc($form['form_key'] ?? '') . '</code>',
                    'isHtml' => true,
                ]) ?>
                <?= view('components/display/field_row', [
                    'label' => 'Forms.field_notify_email',
                    'value' => esc($form['notify_email'] ?? '—'),
                ]) ?>
                <?= view('components/display/field_row', [
                    'label' => 'Forms.field_autoreply_email_field',
                    'value' => esc($form['autoreply_email_field'] ?? '—'),
                ]) ?>
            </dl>
        </section>

        <?php if (! empty($form['translations']) && is_array($form['translations'])): ?>
            <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <h3 class="text-lg font-semibold text-gray-900"><?= lang('Forms.translation_title') ?></h3>
                <div class="mt-4 space-y-4">
                    <?php foreach ($form['translations'] as $t):
                        $tLangId = (int) ($t['language_id'] ?? 0);
                        $tLangCode = $langCodeMap[$tLangId] ?? (string) $tLangId;
                        ?>
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50">
                            <div class="font-bold text-sm text-brand-700 pb-2 border-b border-gray-200 flex justify-between">
                                <span><?= esc(lang('Forms.field_name')) ?> (<?= esc($tLangCode) ?>)</span>
                            </div>
                            <dl class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-xs">
                                <div>
                                    <dt class="text-gray-500 font-semibold"><?= esc(lang('Forms.field_name')) ?></dt>
                                    <dd class="text-gray-900 mt-0.5 font-medium"><?= esc($t['name'] ?? '—') ?></dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-semibold"><?= esc(lang('Forms.field_submit_label')) ?></dt>
                                    <dd class="text-gray-900 mt-0.5"><?= esc($t['submit_label'] ?? '—') ?></dd>
                                </div>
                                <div class="md:col-span-2">
                                    <dt class="text-gray-500 font-semibold"><?= esc(lang('Forms.field_description')) ?></dt>
                                    <dd class="text-gray-900 mt-0.5"><?= esc($t['description'] ?? '—') ?></dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-semibold"><?= esc(lang('Forms.field_success_message')) ?></dt>
                                    <dd class="text-gray-900 mt-0.5"><?= esc($t['success_message'] ?? '—') ?></dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-semibold"><?= esc(lang('Forms.field_error_message')) ?></dt>
                                    <dd class="text-gray-900 mt-0.5"><?= esc($t['error_message'] ?? '—') ?></dd>
                                </div>
                            </dl>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Forms.fields_title') ?></h3>
            <?php if (! empty($form['fields']) && is_array($form['fields'])): ?>
                <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-left text-xs text-gray-500">
                        <thead class="bg-gray-50 text-[11px] font-semibold uppercase tracking-wider text-gray-700 border-b border-gray-200">
                            <tr>
                                <th class="p-3"><?= esc(lang('Forms.field_field_key')) ?></th>
                                <th class="p-3"><?= esc(lang('Forms.field_field_type')) ?></th>
                                <th class="p-3 text-center"><?= esc(lang('Forms.field_required')) ?></th>
                                <th class="p-3">Detalles de Traducciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <?php foreach ($form['fields'] as $f): ?>
                                <tr>
                                    <td class="p-3 font-mono font-medium text-gray-900"><?= esc($f['field_key'] ?? '') ?></td>
                                    <td class="p-3 text-gray-600"><?= esc(lang('Forms.field_type_' . ($f['field_type'] ?? 'text')) ?? ($f['field_type'] ?? '')) ?></td>
                                    <td class="p-3 text-center">
                                        <div class="inline-flex justify-center w-full">
                                            <?= view('components/table/boolean_cell', ['value' => $f['is_required'] ?? false]) ?>
                                        </div>
                                    </td>
                                    <td class="p-3 space-y-1">
                                        <?php foreach ($f['translations'] ?? [] as $ft):
                                            $ftLangId = (int) ($ft['language_id'] ?? 0);
                                            $ftLangCode = $langCodeMap[$ftLangId] ?? (string) $ftLangId;
                                            ?>
                                            <div class="text-[11px] text-gray-600">
                                                <span class="font-bold text-brand-700"><?= esc($ftLangCode) ?>:</span>
                                                <span><?= esc($ft['label'] ?? '—') ?></span>
                                                <?php if (! empty($ft['placeholder'])): ?>
                                                    <span class="text-gray-400 font-normal">(Placeholder: <?= esc($ft['placeholder']) ?>)</span>
                                                <?php endif; ?>
                                                <?php if (! empty($ft['help_text'])): ?>
                                                    <span class="text-gray-400 italic">(Ayuda: <?= esc($ft['help_text']) ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="mt-3 text-sm text-gray-500"><?= lang('Forms.fields_empty') ?></p>
            <?php endif; ?>
        </section>
    </div>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?= view('components/display/admin_meta_panel', [
        'title' => 'Forms.show_title',
        'items' => [
            ['label' => 'Forms.field_active', 'value' => view('components/table/boolean_cell', ['value' => $form['is_active'] ?? false]), 'isHtml' => true],
            ['label' => 'Forms.field_captcha', 'value' => view('components/table/boolean_cell', ['value' => $form['has_captcha'] ?? false]), 'isHtml' => true],
            ['label' => 'Forms.field_autoreply', 'value' => view('components/table/boolean_cell', ['value' => $form['autoreply_enabled'] ?? false]), 'isHtml' => true],
            ['label' => 'TableColumns.created_at', 'value' => (string) ($form['created_at'] ?? '-')],
        ],
    ]) ?>

    <?php ob_start(); ?>
    <a href="<?= route_to('admin.cms.forms.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center">
        <?= ui_icon('pencil', 'h-3.5 w-3.5') ?>
        <?= lang('App.edit') ?>
    </a>
    <a href="<?= route_to('admin.cms.form_submissions') ?>?form_key=<?= urlencode((string) ($form['form_key'] ?? '')) ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center">
        <?= ui_icon('mail', 'h-3.5 w-3.5') ?>
        <span><?= esc(lang('Forms.view_submissions')) ?></span>
    </a>
    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <form method="post" action="<?= route_to('admin.cms.forms.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($form['form_key'] ?? null), 'js') ?>', () => $el.submit())">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?> w-full justify-center">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
    <?php $dangerContent = ob_get_clean(); ?>

    <?= view('components/display/admin_actions_panel', [
        'content' => $actionsContent,
        'dangerContent' => $dangerContent,
    ]) ?>
    <?php $asideContent = ob_get_clean(); ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => $asideContent,
    ]) ?>
<?php endif; ?>
