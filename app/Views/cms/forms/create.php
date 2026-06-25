<?php
/** @var list<array<string, mixed>> $languages */
?>

<div class="mb-5 flex items-center gap-3">
    <a href="<?= route_to('admin.cms.forms') ?>" class="text-gray-400 hover:text-gray-600">
        <i data-lucide="arrow-left" class="h-5 w-5"></i>
    </a>
    <h1 class="text-xl font-semibold text-gray-900"><?= lang('Forms.create_title') ?></h1>
</div>

<form method="post" action="<?= route_to('admin.cms.forms.store') ?>" class="space-y-6">
    <?= csrf_field() ?>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-base font-semibold text-gray-800"><?= lang('Forms.section_general') ?></h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_key') ?> <span class="text-red-500">*</span></label>
                <input type="text" name="form_key" required
                       placeholder="contact"
                       pattern="[a-zA-Z0-9_-]+"
                       class="form-input w-full"
                       value="<?= esc(old('form_key', '')) ?>">
                <p class="mt-1 text-xs text-gray-400"><?= lang('Forms.field_key_hint') ?></p>
            </div>
            <div class="flex items-end gap-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300">
                    <?= lang('Forms.field_active') ?>
                </label>
                <div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="has_captcha" value="0">
                        <input type="checkbox" name="has_captcha" value="1" class="rounded border-gray-300">
                        <?= lang('Forms.field_captcha') ?>
                    </label>
                    <p class="mt-1 text-xs text-gray-400"><?= lang('Forms.field_captcha_hint') ?></p>
                </div>
            </div>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_notify_email') ?></label>
                <input type="email" name="notify_email" class="form-input w-full" placeholder="admin@example.com" value="<?= esc(old('notify_email', '')) ?>">
                <p class="mt-1 text-xs text-gray-400"><?= lang('Forms.field_notify_email_hint') ?></p>
            </div>
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="autoreply_enabled" value="0">
                    <input type="checkbox" name="autoreply_enabled" value="1" class="rounded border-gray-300">
                    <?= lang('Forms.field_autoreply') ?>
                </label>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600"><?= lang('Forms.field_autoreply_email_field') ?></label>
                    <input type="text" name="autoreply_email_field" class="form-input w-full text-sm" placeholder="email" value="<?= esc(old('autoreply_email_field', '')) ?>">
                    <p class="mt-1 text-xs text-gray-400"><?= lang('Forms.field_autoreply_email_field_hint') ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($languages)): ?>
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm" x-data="{ activeTab: '<?= esc($languages[0]['code'] ?? 'es') ?>' }">
            <h2 class="mb-4 text-base font-semibold text-gray-800"><?= lang('Forms.section_translations') ?></h2>
            <div class="mb-4 flex gap-1 border-b border-gray-200">
                <?php foreach ($languages as $lang): ?>
                    <button type="button"
                            @click="activeTab = '<?= esc($lang['code']) ?>'"
                            :class="activeTab === '<?= esc($lang['code']) ?>' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="border-b-2 px-4 py-2 text-sm font-medium transition-colors">
                        <?= esc(strtoupper($lang['code'])) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <?php foreach ($languages as $idx => $language): ?>
                <div x-show="activeTab === '<?= esc($language['code']) ?>'" class="space-y-4">
                    <input type="hidden" name="translations[<?= (int) $language['id'] ?>][language_id]" value="<?= (int) $language['id'] ?>">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_name') ?> <span class="text-red-500">*</span></label>
                            <input type="text" name="translations[<?= (int) $language['id'] ?>][name]" class="form-input w-full" required>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_submit_label') ?></label>
                            <input type="text" name="translations[<?= (int) $language['id'] ?>][submit_label]" class="form-input w-full" value="Enviar">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_description') ?></label>
                        <textarea name="translations[<?= (int) $language['id'] ?>][description]" rows="2" class="form-input block w-full resize-none"></textarea>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_success_message') ?></label>
                            <textarea name="translations[<?= (int) $language['id'] ?>][success_message]" rows="2" class="form-input block w-full resize-none"></textarea>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_error_message') ?></label>
                            <textarea name="translations[<?= (int) $language['id'] ?>][error_message]" rows="2" class="form-input block w-full resize-none"></textarea>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="flex items-center justify-end gap-3">
        <a href="<?= route_to('admin.cms.forms') ?>" class="btn btn-secondary"><?= lang('Forms.btn_cancel') ?></a>
        <button type="submit" class="btn btn-primary"><?= lang('Forms.btn_save') ?></button>
    </div>
</form>
