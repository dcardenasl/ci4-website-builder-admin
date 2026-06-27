<?php
/** @var array<string, mixed> $form */
/** @var list<array<string, mixed>> $languages */

$fieldTypes = [
    'text'     => lang('Forms.field_type_text'),
    'email'    => lang('Forms.field_type_email'),
    'phone'    => lang('Forms.field_type_phone'),
    'textarea' => lang('Forms.field_type_textarea'),
];

// Index translations by language_id for the form
$transById = [];
foreach ($form['translations'] ?? [] as $t) {
    $transById[(int) ($t['language_id'] ?? 0)] = $t;
}

$apiFieldsUrl   = route_to('admin.cms.forms.fields.store', $form['id']);
$apiReorderUrl  = route_to('admin.cms.forms.fields.reorder', $form['id']);
$csrfToken      = csrf_hash();
$csrfName       = csrf_token();
$jsonFlags      = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR;
$initialFields  = json_encode(array_values($form['fields'] ?? []), $jsonFlags);
$languageItems  = json_encode(array_values($languages), $jsonFlags);
?>

<div class="mb-5 flex items-center gap-3">
    <a href="<?= route_to('admin.cms.forms') ?>" class="text-gray-400 hover:text-gray-600">
        <i data-lucide="arrow-left" class="h-5 w-5"></i>
    </a>
    <h1 class="text-xl font-semibold text-gray-900"><?= lang('Forms.edit_title') ?> — <code class="text-sm text-gray-500"><?= esc($form['form_key']) ?></code></h1>
</div>

<form method="post" action="<?= route_to('admin.cms.forms.update', $form['id']) ?>" class="space-y-6">
    <?= csrf_field() ?>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-base font-semibold text-gray-800"><?= lang('Forms.section_general') ?></h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_key') ?></label>
                <input type="text" class="form-input w-full bg-gray-50 text-gray-500" value="<?= esc($form['form_key']) ?>" disabled>
                <p class="mt-1 text-xs text-gray-400"><?= lang('Forms.field_key_readonly') ?></p>
            </div>
            <div class="flex items-end gap-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" <?= $form['is_active'] ? 'checked' : '' ?> class="rounded border-gray-300">
                    <?= lang('Forms.field_active') ?>
                </label>
                <div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="has_captcha" value="0">
                        <input type="checkbox" name="has_captcha" value="1" <?= $form['has_captcha'] ? 'checked' : '' ?> class="rounded border-gray-300">
                        <?= lang('Forms.field_captcha') ?>
                    </label>
                    <p class="mt-1 text-xs text-gray-400"><?= lang('Forms.field_captcha_hint') ?></p>
                </div>
            </div>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_notify_email') ?></label>
                <input type="email" name="notify_email" class="form-input w-full" placeholder="admin@example.com" value="<?= esc($form['notify_email'] ?? '') ?>">
                <p class="mt-1 text-xs text-gray-400"><?= lang('Forms.field_notify_email_hint') ?></p>
            </div>
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="autoreply_enabled" value="0">
                    <input type="checkbox" name="autoreply_enabled" value="1" <?= $form['autoreply_enabled'] ? 'checked' : '' ?> class="rounded border-gray-300">
                    <?= lang('Forms.field_autoreply') ?>
                </label>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600"><?= lang('Forms.field_autoreply_email_field') ?></label>
                    <input type="text" name="autoreply_email_field" class="form-input w-full text-sm"
                           placeholder="email" value="<?= esc($form['autoreply_email_field'] ?? '') ?>">
                    <p class="mt-1 text-xs text-gray-400"><?= lang('Forms.field_autoreply_email_field_hint') ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($languages)): ?>
        <?php
            $defaultLangId = 0;
        $defaultLangCode = '';
        foreach ($languages as $i => $l) {
            if (!empty($l['is_default'])) {
                $defaultLangId = (int) $l['id'];
                $defaultLangCode = $l['code'] ?? '';
                break;
            }
        }
        $translateUrl = route_to('admin.cms.translate');
        ?>
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold text-gray-800"><?= lang('Forms.section_translations') ?></h2>

            <div x-data="langTabs(<?= $defaultLangId ?>, '<?= esc($translateUrl, 'attr') ?>', '<?= esc($defaultLangCode, 'attr') ?>')">
                <div class="mb-4 flex items-center justify-between border-b border-gray-200">
                    <div class="flex gap-1">
                        <?php foreach ($languages as $lang): ?>
                            <button type="button"
                                    role="tab"
                                    @click="setTab(<?= (int) $lang['id'] ?>)"
                                    :aria-selected="isActive(<?= (int) $lang['id'] ?>)"
                                    :class="isActive(<?= (int) $lang['id'] ?>) ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                    class="border-b-2 px-4 py-2 text-sm font-medium transition-colors">
                                <?= esc(strtoupper($lang['code'])) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($translateTargets)): ?>
                    <button type="button"
                        @click="autoTranslateAll(<?= esc(json_encode($translateTargets, JSON_THROW_ON_ERROR), 'attr') ?>)"
                        :disabled="translating || translatingAll"
                        class="inline-flex items-center gap-1.5 text-xs text-brand-600 hover:text-brand-700 border border-brand-200 rounded px-3 py-1.5 bg-brand-50 hover:bg-brand-100 transition-colors disabled:opacity-50">
                        <span x-show="!translatingAll"><?= ui_icon('languages', 'h-3.5 w-3.5') ?> <?= esc(lang('App.translate_all')) ?></span>
                        <span x-show="translatingAll" x-cloak><?= ui_icon('loader', 'h-3.5 w-3.5 animate-spin') ?> <span x-text="translateAllProgress"></span></span>
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Translate error message -->
                <p x-show="translateError !== ''" x-text="translateError" x-cloak class="mb-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded px-3 py-2"></p>

                <?php foreach ($languages as $language): ?>
                    <?php $t = $transById[(int) $language['id']] ?? []; ?>
                    <div x-show="isActive(<?= (int) $language['id'] ?>)" class="space-y-4">
                        <input type="hidden" name="translations[<?= (int) $language['id'] ?>][language_id]" value="<?= (int) $language['id'] ?>">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_name') ?> <span class="text-red-500">*</span></label>
                                <input type="text" name="translations[<?= (int) $language['id'] ?>][name]" class="form-input w-full"
                                       value="<?= esc($t['name'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_submit_label') ?></label>
                                <input type="text" name="translations[<?= (int) $language['id'] ?>][submit_label]" class="form-input w-full"
                                       value="<?= esc($t['submit_label'] ?? 'Enviar') ?>">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_description') ?></label>
                            <textarea name="translations[<?= (int) $language['id'] ?>][description]" rows="2" class="form-input block w-full resize-none"><?= esc($t['description'] ?? '') ?></textarea>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_success_message') ?></label>
                                <textarea name="translations[<?= (int) $language['id'] ?>][success_message]" rows="2" class="form-input block w-full resize-none"><?= esc($t['success_message'] ?? '') ?></textarea>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_error_message') ?></label>
                                <textarea name="translations[<?= (int) $language['id'] ?>][error_message]" rows="2" class="form-input block w-full resize-none"><?= esc($t['error_message'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="flex items-center justify-end gap-3">
        <a href="<?= route_to('admin.cms.forms') ?>" class="btn btn-secondary"><?= lang('Forms.btn_cancel') ?></a>
        <button type="submit" class="btn btn-primary"><?= lang('Forms.btn_save') ?></button>
    </div>
</form>

<?php /* ── Field Builder ─────────────────────────────────────────────────── */ ?>
<script type="application/json" id="cms-form-initial-fields"><?= $initialFields ?></script>
<script type="application/json" id="cms-form-languages"><?= $languageItems ?></script>
<div class="mt-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
	     x-data="formFieldBuilder({
	         formId: <?= (int) $form['id'] ?>,
	         initialFieldsElementId: 'cms-form-initial-fields',
	         languagesElementId: 'cms-form-languages',
	         storeUrl: <?= esc(json_encode($apiFieldsUrl, JSON_THROW_ON_ERROR), 'attr') ?>,
	         reorderUrl: <?= esc(json_encode($apiReorderUrl, JSON_THROW_ON_ERROR), 'attr') ?>,
	         updateUrlTemplate: <?= esc(json_encode($apiFieldsUrl, JSON_THROW_ON_ERROR), 'attr') ?>,
	         deleteUrlTemplate: <?= esc(json_encode($apiFieldsUrl, JSON_THROW_ON_ERROR), 'attr') ?>,
	         csrfName: <?= esc(json_encode($csrfName, JSON_THROW_ON_ERROR), 'attr') ?>,
	         csrfToken: <?= esc(json_encode($csrfToken, JSON_THROW_ON_ERROR), 'attr') ?>,
	         fieldKeyRequiredMessage: <?= esc(json_encode(lang('Forms.field_key_required'), JSON_THROW_ON_ERROR), 'attr') ?>,
	         saveFieldFailedMessage: <?= esc(json_encode(lang('Forms.save_field_failed'), JSON_THROW_ON_ERROR), 'attr') ?>,
	         confirmDeleteFieldMessage: <?= esc(json_encode(lang('Forms.confirm_delete_field'), JSON_THROW_ON_ERROR), 'attr') ?>,
	         deleteFailedMessage: <?= esc(json_encode(lang('Forms.delete_failed'), JSON_THROW_ON_ERROR), 'attr') ?>,
	     })"
     x-init="init()">

    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-800"><?= lang('Forms.section_fields') ?></h2>
        <button type="button" @click="openCreate()" class="btn btn-secondary inline-flex items-center gap-2 text-sm">
            <i data-lucide="plus" class="h-4 w-4"></i>
            <?= lang('Forms.btn_add_field') ?>
        </button>
    </div>

    <p x-show="fields.length === 0" class="py-8 text-center text-sm text-gray-400"><?= lang('Forms.fields_empty') ?></p>

    <ul x-sortable @sortable:end="onReorder($event)" class="space-y-2">
        <template x-for="field in fields" :key="field.id">
            <li :data-id="field.id"
                class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 cursor-grab">
                <div class="flex items-center gap-3">
                    <i data-lucide="grip-vertical" class="h-4 w-4 text-gray-400"></i>
                    <div>
                        <span class="font-medium text-gray-800 text-sm" x-text="field.field_key"></span>
                        <span class="ml-2 text-xs text-gray-400" x-text="field.field_type"></span>
                        <span x-show="field.is_required" class="ml-2 inline-flex rounded-full bg-red-50 px-1.5 py-0.5 text-xs text-red-600 ring-1 ring-red-200"><?= lang('Forms.required') ?></span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="openEdit(field)" class="text-xs text-brand-600 hover:text-brand-800"><?= lang('Forms.btn_edit') ?></button>
                    <button type="button" @click="deleteField(field)" class="text-xs text-red-600 hover:text-red-800"><?= lang('Forms.btn_delete') ?></button>
                </div>
            </li>
        </template>
    </ul>

    <?php /* ── Field Modal ─────────────────────────────────────────────────── */ ?>
    <div x-show="showModal" x-cloak @keydown.escape.window="closeModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 sm:p-6 lg:p-8">
        <form @submit.prevent="saveField()" class="flex max-h-[calc(100vh-4rem)] w-full max-w-4xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black/5">
            <div class="flex items-center justify-between gap-4 border-b border-gray-200 px-5 py-4 sm:px-6">
                <div>
                    <h3 class="text-base font-semibold text-gray-900" x-text="editingField ? '<?= lang('Forms.modal_edit_field') ?>' : '<?= lang('Forms.modal_create_field') ?>'"></h3>
                    <p class="mt-1 text-xs text-gray-500"><?= lang('Forms.section_fields') ?></p>
                </div>
                <button type="button" @click="closeModal()" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700" aria-label="<?= esc(lang('Forms.btn_cancel')) ?>">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-5 sm:px-6">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)]">
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-700"><?= lang('Forms.field_field_key') ?> <span class="text-red-500">*</span></label>
                            <input type="text" x-model="fieldForm.field_key" :disabled="!!editingField"
                                   class="form-input w-full text-sm" placeholder="email">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-700"><?= lang('Forms.field_field_type') ?></label>
                            <select x-model="fieldForm.field_type" class="form-input w-full text-sm">
                                <?php foreach ($fieldTypes as $val => $label): ?>
                                    <option value="<?= esc($val) ?>"><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="space-y-3">
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" x-model="fieldForm.is_required" class="rounded border-gray-300">
                                    <?= lang('Forms.field_required') ?>
                                </label>
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" x-model="fieldForm.is_active" class="rounded border-gray-300">
                                    <?= lang('Forms.field_active') ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="min-w-0">
                        <div class="mb-4 flex flex-wrap gap-1 border-b border-gray-200">
                            <?php foreach ($languages as $lang): ?>
                                <button type="button"
                                        @click="activeFieldLang = '<?= esc($lang['code']) ?>'"
                                        :class="activeFieldLang === '<?= esc($lang['code']) ?>' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                        class="border-b-2 px-3 py-2 text-xs font-semibold transition">
                                    <?= esc(strtoupper($lang['code'])) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <?php foreach ($languages as $lang): ?>
                            <div x-show="activeFieldLang === '<?= esc($lang['code']) ?>'" class="space-y-4">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-700"><?= lang('Forms.field_label') ?></label>
                                    <input type="text" x-model="fieldForm.translations[<?= (int) $lang['id'] ?>].label" class="form-input w-full text-sm">
                                </div>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-gray-700"><?= lang('Forms.field_placeholder') ?></label>
                                        <input type="text" x-model="fieldForm.translations[<?= (int) $lang['id'] ?>].placeholder" class="form-input w-full text-sm">
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-gray-700"><?= lang('Forms.field_help_text') ?></label>
                                        <input type="text" x-model="fieldForm.translations[<?= (int) $lang['id'] ?>].help_text" class="form-input w-full text-sm">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <p x-show="fieldError" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" x-text="fieldError"></p>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-5 py-4 sm:px-6">
                <button type="button" @click="closeModal()" class="btn btn-secondary text-sm"><?= lang('Forms.btn_cancel') ?></button>
                <button type="submit" class="btn btn-primary text-sm"><?= lang('Forms.btn_save') ?></button>
            </div>
        </form>
    </div>
</div>
