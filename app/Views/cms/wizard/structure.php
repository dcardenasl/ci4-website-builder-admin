<?php
/**
 * @var string $title
 * @var string $csrfName
 * @var string $csrfToken
 */
$csrfName  ??= csrf_token();
$csrfToken ??= csrf_hash();
?>
<div class="max-w-6xl mx-auto space-y-6" x-data="structureWizard()" x-init="init()" @slug-availability-changed.window="onSlugAvailabilityChanged($event)">
    <div x-show="screen === 'loading'" x-cloak class="text-center py-16 text-gray-400">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand-600 mx-auto mb-4"></div>
        <p><?= esc(lang('Wizard.loading')) ?></p>
    </div>

    <div x-show="screen === 'error'" x-cloak class="rounded-xl border border-red-200 bg-red-50 p-6 shadow-sm">
        <p class="text-sm font-semibold text-red-700" x-text="errorMsg"></p>
        <div class="mt-4 flex gap-3">
            <button type="button" @click="init()" class="btn-secondary"><?= esc(lang('Wizard.btn_retry')) ?></button>
            <a href="<?= route_to('admin.cms.wizard') ?>" class="btn-primary"><?= esc(lang('Wizard.btn_back_panel')) ?></a>
        </div>
    </div>

    <div x-show="screen === 'home'" x-cloak class="space-y-8">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500"><?= esc(lang('Wizard.structure_sidebar_label')) ?></p>
            <h1 class="mt-2 text-3xl font-bold text-gray-900"><?= esc(lang('Wizard.structure_heading')) ?></h1>
            <p class="mt-3 max-w-2xl text-sm text-gray-600"><?= esc(lang('Wizard.structure_intro')) ?></p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <button type="button" @click="start('collection')" class="min-h-[140px] rounded-2xl border-2 border-gray-200 bg-white p-6 text-left shadow-sm transition hover:border-brand-400 hover:shadow-md">
                <div class="text-4xl">🗂️</div>
                <div class="mt-3 text-lg font-semibold text-gray-900"><?= esc(lang('Wizard.create_collection')) ?></div>
                <div class="mt-1 text-sm text-gray-600"><?= esc(lang('Wizard.create_collection_desc')) ?></div>
            </button>
            <button type="button" @click="start('page')" class="min-h-[140px] rounded-2xl border-2 border-gray-200 bg-white p-6 text-left shadow-sm transition hover:border-brand-400 hover:shadow-md">
                <div class="text-4xl">📄</div>
                <div class="mt-3 text-lg font-semibold text-gray-900"><?= esc(lang('Wizard.create_page')) ?></div>
                <div class="mt-1 text-sm text-gray-600"><?= esc(lang('Wizard.create_page_desc')) ?></div>
            </button>
            <button type="button" @click="start('menu')" class="min-h-[140px] rounded-2xl border-2 border-gray-200 bg-white p-6 text-left shadow-sm transition hover:border-brand-400 hover:shadow-md">
                <div class="text-4xl">🔗</div>
                <div class="mt-3 text-lg font-semibold text-gray-900"><?= esc(lang('Wizard.create_menu')) ?></div>
                <div class="mt-1 text-sm text-gray-600"><?= esc(lang('Wizard.create_menu_desc')) ?></div>
            </button>
        </div>

        <div x-show="screen === 'collection' && collectionStep === 1" x-cloak class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.collection_wizard_intro')) ?></h2>
                    <p class="mt-1 text-sm text-gray-600"><?= esc(lang('Wizard.collection_wizard_minimum')) ?></p>
                </div>
                <a href="<?= route_to('admin.cms.collections') ?>" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:border-gray-400">
                    <?= esc(lang('Wizard.go_structure_panel')) ?>
                </a>
            </div>
        </div>
    </div>

    <div x-show="screen === 'collection'" x-cloak class="space-y-6">
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <button type="button" @click="screen = 'home'" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 font-semibold text-gray-700"><?= esc(lang('App.back')) ?></button>
            <span x-text="stepLabel()"></span>
        </div>
        <div class="h-2 overflow-hidden rounded-full bg-gray-200">
            <div class="h-2 rounded-full bg-brand-600 transition-all" :style="`width: ${Math.round((collectionStep / 2) * 100)}%`"></div>
        </div>
        <div class="grid gap-6 lg:grid-cols-12">
            <aside class="lg:col-span-3" x-show="collectionStep === 1" x-cloak>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.wizard_structure_step1')) ?></p>
                    <div class="mt-4 space-y-2">
                        <template x-for="option in config?.collection_types ?? []" :key="option.key">
                            <button type="button" @click="selectCollectionType(option.key)" :class="form.collection_type === option.key ? 'border-brand-500 bg-brand-50 text-brand-800' : 'border-gray-200 bg-white text-gray-700'" class="w-full rounded-xl border p-3 text-left transition">
                                <div class="font-semibold" x-text="option.label"></div>
                            </button>
                        </template>
                    </div>
                </div>
            </aside>
            <main class="lg:col-span-9">
                <form @submit.prevent="submitCollection()" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.wizard_structure_step1')) ?></p>
                        <h2 class="mt-1 text-2xl font-bold text-gray-900"><?= esc(lang('Wizard.create_collection')) ?></h2>
                        <p class="mt-1 text-sm text-gray-600"><?= esc(lang('Wizard.wizard_structure_collection_review_intro')) ?></p>
                    </div>
                    <template x-if="collectionStep === 1">
                        <div class="space-y-4">
                            <div x-show="collectionErrors.step1" x-cloak class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700" x-text="collectionErrors.step1"></div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <?= view('components/forms/text_input', ['name' => 'collection_name', 'label' => lang('Wizard.wizard_structure_field_name'), 'type' => 'text', 'class' => 'block md:col-span-2', 'attrs' => 'x-model="form.name"']) ?>
                                <?= view('components/form/slug', [
                                    'name' => 'collection_slug_base',
                                    'label' => 'Wizard.wizard_structure_field_slug_base',
                                    'sourceId' => '#collection_name',
                                    'checkUrl' => route_to('admin.cms.collections.check_slug'),
                                    'required' => true,
                                    'help' => 'Wizard.wizard_structure_slug_help',
                                    'invalidMessage' => lang('Wizard.wizard_structure_slug_invalid'),
                                    'attrs' => 'x-model="form.slug_base" @input="form.collection_key = form.slug_base; validateCollectionSlug()" @blur="validateCollectionSlug(true)"',
                                ]) ?>
                                <p x-show="collectionErrors.slug_base" x-cloak class="md:col-span-2 -mt-2 text-sm text-red-600" x-text="collectionErrors.slug_base"></p>
                            </div>
                        </div>
                    </template>
                    <div x-show="collectionStep === 2" class="space-y-4 rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                        <div>
                            <p class="font-semibold text-gray-900 mb-1"><?= esc(lang('Wizard.wizard_structure_final_summary')) ?></p>
                            <p><strong><?= esc(lang('Wizard.wizard_structure_summary_name')) ?>:</strong> <span x-text="form.name || '—'"></span></p>
                            <p><strong><?= esc(lang('Wizard.wizard_structure_summary_internal_slug')) ?>:</strong> <span x-text="form.collection_key || '—'"></span></p>
                            <p><strong><?= esc(lang('Wizard.wizard_structure_summary_type')) ?>:</strong> <span x-text="collectionTypeLabel()"></span></p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.wizard_structure_preset_summary_title')) ?></p>
                                    <h3 class="mt-1 text-base font-semibold text-gray-900"><?= esc(lang('Wizard.wizard_structure_preset_blocks_title')) ?></h3>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" @click="usePreset = true" :class="usePreset ? 'border-brand-500 bg-brand-50 text-brand-800' : 'border-gray-200 bg-white text-gray-700'" class="rounded-lg border px-3 py-1.5 text-sm font-semibold transition">
                                        <?= esc(lang('Wizard.wizard_structure_preset_apply')) ?>
                                    </button>
                                    <button type="button" @click="usePreset = false" :class="!usePreset ? 'border-brand-500 bg-brand-50 text-brand-800' : 'border-gray-200 bg-white text-gray-700'" class="rounded-lg border px-3 py-1.5 text-sm font-semibold transition">
                                        <?= esc(lang('Wizard.wizard_structure_preset_skip')) ?>
                                    </button>
                                </div>
                            </div>
                            <template x-if="collectionPresetBlocks().length > 0">
                                <div class="mt-4 space-y-3">
                                    <template x-for="block in collectionPresetBlocks()" :key="block.sort_order + '-' + block.block_key">
                                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <div>
                                                    <p class="font-semibold text-gray-900" x-text="block.label || block.block_key"></p>
                                                    <p class="text-xs text-gray-500" x-text="block.block_key"></p>
                                                </div>
                                                <div class="flex flex-wrap gap-2 text-[11px] font-semibold uppercase tracking-[0.14em]">
                                                    <span :class="block.required ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'" class="rounded-full px-2 py-1" x-text="block.required ? '<?= esc(lang('Wizard.wizard_structure_preset_block_required'), 'js') ?>' : '<?= esc(lang('Wizard.wizard_structure_preset_block_optional'), 'js') ?>'"></span>
                                                    <span :class="block.locked ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600'" class="rounded-full px-2 py-1" x-text="block.locked ? '<?= esc(lang('Wizard.wizard_structure_preset_block_locked'), 'js') ?>' : '<?= esc(lang('Wizard.wizard_structure_preset_block_editable'), 'js') ?>'"></span>
                                                </div>
                                            </div>
                                            <p class="mt-2 text-sm text-gray-600" x-text="block.help_text || '—'"></p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <p class="mt-4 text-xs leading-5 text-gray-500">
                                <?= esc(lang('Wizard.wizard_structure_preset_legend')) ?>
                            </p>
                            <div x-show="collectionPresetBlocks().length === 0" class="mt-4 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-3 text-sm text-gray-500">
                                <?= esc(lang('Wizard.wizard_structure_preset_empty_hint')) ?>
                            </div>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-600">
                            <p><strong>Estado:</strong> <span x-text="usePreset ? 'Se aplicará el preset recomendado' : 'Se creará sin preset'"></span></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" @click="prevCollectionStep()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700" :disabled="collectionStep === 1"><?= esc(lang('Wizard.wizard_structure_prev')) ?></button>
                        <button type="button" @click="nextCollectionStep()" x-show="collectionStep < 2" class="btn-secondary text-sm" :disabled="!canAdvanceCollectionStep()"><?= esc(lang('Wizard.wizard_structure_next')) ?></button>
                        <button type="submit" x-show="collectionStep === 2" class="btn-primary text-sm" :disabled="saving || !canSubmitCollection()">
                            <span x-show="!saving"><?= esc(lang('Wizard.wizard_structure_create')) ?></span>
                            <span x-show="saving"><?= esc(lang('Wizard.wizard_structure_creating')) ?></span>
                        </button>
                        <button type="button" @click="screen = 'home'" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700"><?= esc(lang('Wizard.btn_back_panel')) ?></button>
                    </div>
                </form>
            </main>
        </div>
        <div x-show="message" x-cloak class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 shadow-sm" x-text="message"></div>
        <div x-show="errorMsg" x-cloak class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 shadow-sm" x-text="errorMsg"></div>
        <div x-show="createdCollectionId" x-cloak class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.wizard_structure_collection_ready')) ?></p>
            <p class="mt-1 text-sm text-gray-600"><?= esc(lang('Wizard.wizard_structure_post_create_help')) ?></p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a :href="collectionDetailUrl()" class="btn-primary text-sm"><?= esc(lang('Wizard.wizard_structure_go_detail')) ?></a>
                <button type="button" @click="resetCollectionFlow()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700"><?= esc(lang('Wizard.wizard_structure_create_another')) ?></button>
                <a href="<?= route_to('admin.cms.wizard') ?>" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700"><?= esc(lang('Wizard.btn_back_panel')) ?></a>
            </div>
        </div>
    </div>

    <div x-show="screen === 'page'" x-cloak class="space-y-6">
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <button type="button" @click="screen = 'home'" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 font-semibold text-gray-700"><?= esc(lang('App.back')) ?></button>
        </div>
        <form @submit.prevent="submitPage()" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.create_page')) ?></p>
                <h2 class="mt-1 text-2xl font-bold text-gray-900"><?= esc(lang('Wizard.create_page')) ?></h2>
                <p class="mt-1 text-sm text-gray-600"><?= esc(lang('Wizard.wizard_structure_page_review_intro')) ?></p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_page_type')) ?></span>
                    <select x-model="page.page_type" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <template x-for="option in config?.page_types ?? []" :key="option.key">
                            <option :value="option.key" x-text="option.label"></option>
                        </template>
                    </select>
                </label>
                <?= view('components/forms/text_input', ['name' => 'page_title', 'label' => lang('Wizard.wizard_structure_page_title'), 'type' => 'text', 'class' => 'block', 'attrs' => 'x-model="page.title"']) ?>
                <?= view('components/forms/text_input', ['name' => 'page_slug', 'label' => lang('Wizard.wizard_structure_page_slug'), 'type' => 'text', 'class' => 'block', 'attrs' => 'x-model="page.slug"']) ?>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                <p class="font-semibold text-gray-900 mb-3"><?= esc(lang('Wizard.wizard_structure_final_summary')) ?></p>
                <p><strong><?= esc(lang('Wizard.wizard_structure_page_type')) ?>:</strong> <span x-text="pageTypeLabel()"></span></p>
                <p><strong><?= esc(lang('Wizard.wizard_structure_page_title')) ?>:</strong> <span x-text="page.title || '—'"></span></p>
                <p><strong><?= esc(lang('Wizard.wizard_structure_page_slug')) ?>:</strong> <span x-text="page.slug || '—'"></span></p>
            </div>
            <div class="flex flex-wrap gap-3"><button type="submit" class="btn-primary text-sm"><?= esc(lang('Wizard.create_page')) ?></button><button type="button" @click="screen='home'" class="btn-secondary text-sm"><?= esc(lang('Wizard.btn_back_panel')) ?></button></div>
        </form>
        <div x-show="message" x-cloak class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 shadow-sm" x-text="message"></div>
        <div x-show="errorMsg" x-cloak class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 shadow-sm" x-text="errorMsg"></div>
    </div>

    <div x-show="screen === 'menu'" x-cloak class="space-y-6">
        <div class="flex items-center gap-3 text-sm text-gray-500"><button type="button" @click="screen='home'" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 font-semibold text-gray-700"><?= esc(lang('App.back')) ?></button></div>
        <form @submit.prevent="submitMenu()" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-6">
            <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.create_menu')) ?></p><h2 class="mt-1 text-2xl font-bold text-gray-900"><?= esc(lang('Wizard.create_menu')) ?></h2></div>
            <div class="grid gap-4 md:grid-cols-2">
                <?= view('components/forms/text_input', ['name' => 'menu_key', 'label' => lang('Wizard.wizard_structure_menu_key'), 'type' => 'text', 'class' => 'block', 'attrs' => 'x-model="menu.menu_key"']) ?>
                <?= view('components/forms/text_input', ['name' => 'menu_location', 'label' => lang('Wizard.wizard_structure_menu_location'), 'type' => 'text', 'class' => 'block', 'attrs' => 'x-model="menu.location"']) ?>
                <label class="flex items-center gap-3 rounded-xl border border-gray-200 p-4"><input type="checkbox" x-model="menu.is_active" class="rounded border-gray-300"><span class="text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.wizard_structure_menu_active')) ?></span></label>
                <?= view('components/forms/text_input', ['name' => 'menu_name', 'label' => lang('Wizard.wizard_structure_menu_name'), 'type' => 'text', 'class' => 'block', 'attrs' => 'x-model="menu.name"']) ?>
            </div>
            <div class="flex flex-wrap gap-3"><button type="submit" class="btn-primary text-sm"><?= esc(lang('Wizard.create_menu')) ?></button><button type="button" @click="screen='home'" class="btn-secondary text-sm"><?= esc(lang('Wizard.btn_back_panel')) ?></button></div>
        </form>
        <div x-show="message" x-cloak class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 shadow-sm" x-text="message"></div>
        <div x-show="errorMsg" x-cloak class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 shadow-sm" x-text="errorMsg"></div>
    </div>
</div>

<script <?= csp_script_nonce() ?>>
(function () {
    'use strict';
    const CSRF_NAME = <?= json_encode($csrfName) ?>;
    const CSRF_TOKEN = <?= json_encode($csrfToken) ?>;
    function headers() { return { 'X-CSRF-TOKEN': CSRF_TOKEN, [CSRF_NAME]: CSRF_TOKEN, 'Content-Type': 'application/json' }; }
    async function req(url, body) { return fetch(url, { method: 'POST', credentials: 'same-origin', headers: headers(), body: JSON.stringify(body) }); }
    function slugify(v) { return (v || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-').replace(/-+/g, '-').substring(0, 50); }
    window.structureWizard = function () {
        return {
            screen: 'loading', config: null, errorMsg: '', message: '', saving: false, createdCollectionId: '',
            collectionErrors: { step1: '', slug_base: '' },
            collectionSlugAvailability: '',
            form: { name: '', slug_base: '', collection_key: '', sort_order: 0, collection_type: 'blog' },
            collectionStep: 1,
            translation: { language_id: 0, description: '' },
            usePreset: true,
            collectionPreset: null,
            page: { page_type: 'generic', parent_id: null, translations: [], title: '', slug: ''},
            menu: { menu_key: '', location: 'main', is_active: true, name: '' },
            async init() {
                try {
                    const res = await fetch('<?= route_to('admin.cms.wizard.structure.config') ?>', { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const json = await res.json();
                    this.config = json.data || {};
                    this.translation.language_id = this.resolveDefaultLanguageId();
                    this.page.translations = [{ language_id: this.translation.language_id, slug: '', title: '', excerpt: '', meta_title: '', meta_description: '' }];
                    this.form.collection_type = (this.config.collection_types || [])[0]?.key || 'other';
                    this.collectionPreset = this.resolveCollectionPreset(this.form.collection_type);
                    this.collectionSlugAvailability = '';
                    this.syncCollectionSlugLanguage();
                    this.screen = 'home';
                } catch (e) { this.errorMsg = <?= json_encode(lang('Wizard.wizard_structure_error_load')) ?>; this.screen = 'error'; }
            },
            resolveDefaultLanguageId() {
                const configDefault = Number(this.config?.default_language_id || 0);
                if (configDefault > 0) {
                    return configDefault;
                }
                const languages = this.config?.languages || [];
                const defaultLanguage = languages.find((language) => language?.is_default);
                return Number(defaultLanguage?.id || languages[0]?.id || 0);
            },
            syncCollectionSlugLanguage() {
                const slugInput = this.collectionSlugInput();
                if (!(slugInput && slugInput.tagName === 'INPUT')) {
                    return;
                }
                const languageId = Number(this.config?.default_language_id || this.translation.language_id || 0);
                slugInput.dataset.slugLanguageId = String(languageId > 0 ? languageId : '');
                slugInput.dispatchEvent(new Event('input', { bubbles: true }));
            },
            resolveCollectionPreset(type) {
                const presets = this.config?.collection_presets || {};
                return presets?.[type] || presets?.other || null;
            },
            collectionPresetBlocks() {
                return Array.isArray(this.collectionPreset?.block_template?.blocks) ? this.collectionPreset.block_template.blocks : [];
            },
            start(kind) { this.message=''; this.errorMsg=''; this.createdCollectionId=''; this.collectionErrors = { step1: '', slug_base: '' }; this.collectionSlugAvailability = ''; this.screen = kind; if (kind === 'collection') { this.collectionStep = 1; this.form.collection_type = (this.config.collection_types || [])[0]?.key || 'other'; this.collectionPreset = this.resolveCollectionPreset(this.form.collection_type); this.usePreset = true; this.form.slug_base = ''; this.form.collection_key = ''; this.form.name = ''; } if (kind === 'page') { this.page.title=''; this.page.slug=''; } if (kind==='menu') { this.menu.menu_key=''; this.menu.name=''; } },
            resetCollectionFlow() { this.createdCollectionId=''; this.message=''; this.errorMsg=''; this.collectionErrors = { step1: '', slug_base: '' }; this.collectionSlugAvailability = ''; this.collectionStep = 1; this.form.collection_type = (this.config.collection_types || [])[0]?.key || 'other'; this.collectionPreset = this.resolveCollectionPreset(this.form.collection_type); this.usePreset = true; this.form.slug_base=''; this.form.collection_key=''; this.form.name=''; this.screen = 'collection'; },
            collectionDetailUrl() { return this.createdCollectionId ? '<?= route_to('admin.cms.collections') ?>/' + this.createdCollectionId : '<?= route_to('admin.cms.collections') ?>'; },
            selectCollectionType(type) {
                this.form.collection_type = type || 'other';
                this.collectionPreset = this.resolveCollectionPreset(this.form.collection_type);
                this.usePreset = true;
            },
            collectionSlugInput() { return this.$el.querySelector('#collection_slug_base'); },
            onSlugAvailabilityChanged(event) {
                const detail = event?.detail || {};
                if (detail.id !== 'collection_slug_base') {
                    return;
                }
                this.collectionSlugAvailability = String(detail.status || '');
            },
            collectionSlugStatus() { return String(this.collectionSlugAvailability || this.collectionSlugInput()?.dataset?.slugAvailability || ''); },
            isCollectionSlugValid() {
                const slugInput = this.collectionSlugInput();
                const status = this.collectionSlugStatus();
                return Boolean(slugInput && slugInput.tagName === 'INPUT' && status === 'available');
            },
            validateCollectionSlug(announce = false) {
                const slugInput = this.collectionSlugInput();
                if (!(slugInput && slugInput.tagName === 'INPUT')) {
                    return true;
                }

                const status = this.collectionSlugStatus();
                if (status === 'checking') {
                    if (announce) {
                        this.collectionErrors.slug_base = <?= json_encode(lang('Wizard.wizard_structure_slug_checking')) ?>;
                    }
                    return false;
                }

                if (status === 'available') {
                    this.collectionErrors.slug_base = '';
                    return true;
                }

                if (slugInput.value.trim() === '') {
                    this.collectionErrors.slug_base = announce ? <?= json_encode(lang('Wizard.wizard_structure_step1_error')) ?> : '';
                    return false;
                }

                this.collectionErrors.slug_base = slugInput.validationMessage || <?= json_encode(lang('Wizard.wizard_structure_slug_unavailable')) ?>;
                return false;
            },
            validateCollectionStep1(announce = false) {
                this.collectionErrors.step1 = this.form.name ? '' : <?= json_encode(lang('Wizard.wizard_structure_step1_error')) ?>;
                const slugOk = this.validateCollectionSlug(announce);
                return Boolean(this.form.name) && slugOk;
            },
            canAdvanceCollectionStep() { return this.collectionStep === 1 ? Boolean(this.form.name) && this.collectionSlugStatus() === 'available' : true; },
            nextCollectionStep() {
                if (!this.validateCollectionStep1(true)) {
                    return;
                }
                this.collectionErrors = { step1: '', slug_base: '' };
                if (this.collectionStep < 2) { this.collectionStep += 1; }
            },
            prevCollectionStep() { if (this.collectionStep > 1) this.collectionStep -= 1; },
            canSubmitCollection() { return Boolean(this.form.name && this.form.collection_key && this.form.collection_type); },
            collectionTypeLabel() { return (this.config?.collection_types || []).find((option) => option.key === this.form.collection_type)?.label || this.form.collection_type || '—'; },
            stepLabel() { return <?= json_encode(sprintf(lang('Wizard.step_of'), '%s', '2')) ?>.replace('%s', this.collectionStep); },
            async submitCollection() {
                this.saving = true; this.message=''; this.errorMsg='';
                try {
                    const payload = {
                        collection_type: this.form.collection_type,
                        collection_key: this.form.collection_key || this.form.slug_base,
                        sort_order: this.form.sort_order ?? 0,
                        use_preset: this.usePreset ? 1 : 0,
                        translations: [{
                            language_id: this.translation.language_id,
                            slug: this.form.slug_base || this.form.collection_key,
                            name: this.form.name,
                            description: '',
                        }],
                    };
                    const res = await req('<?= route_to('admin.cms.wizard.structure.create_collection') ?>', payload); const json = await res.json();
                    if (!json.ok) {
                        const fieldErrors = json.fieldErrors && typeof json.fieldErrors === 'object' ? Object.values(json.fieldErrors).filter(Boolean) : [];
                        const detail = typeof json.detail === 'string' ? json.detail : '';
                        const errors = json.errors && typeof json.errors === 'object' ? Object.values(json.errors).filter(Boolean) : [];
                        const generalError = json.errors && typeof json.errors === 'object' && typeof json.errors.general === 'string' ? json.errors.general : '';
                        const message = fieldErrors.length > 0
                            ? String(fieldErrors[0])
                            : (json.message || detail || (errors.length > 0 ? String(errors[0]) : '') || generalError || <?= json_encode(lang('Wizard.wizard_structure_error_collection')) ?>);
                        if (message) {
                            this.collectionErrors.step1 = '';
                            this.collectionErrors.slug_base = message;
                            this.collectionStep = 1;
                            const slugInput = this.collectionSlugInput();
                            if (slugInput && slugInput.tagName === 'INPUT') {
                                slugInput.setCustomValidity(message);
                                slugInput.focus();
                            }
                            return;
                        }
                        throw new Error(<?= json_encode(lang('Wizard.wizard_structure_error_collection')) ?>);
                    }
                    const id = json.data?.id || '';
                    this.message = <?= json_encode(lang('Wizard.wizard_structure_collection_ready')) ?>;
                    this.createdCollectionId = id ? String(id) : '';
                } catch (e) {
                    this.errorMsg = e.message || <?= json_encode(lang('Wizard.wizard_structure_error_collection')) ?>;
                } finally { this.saving = false; }
            },
            pageTypeLabel() { return (this.config?.page_types || []).find((option) => option.key === this.page.page_type)?.label || this.page.page_type || '—'; },
            async submitPage() {
                this.message=''; this.errorMsg='';
                try {
                    const payload = { page_type: this.page.page_type, parent_id: null, translations: [{ language_id: this.translation.language_id, slug: slugify(this.page.slug || this.page.title || <?= json_encode(lang('Wizard.wizard_structure_page_default_title')) ?>), title: this.page.title || <?= json_encode(lang('Wizard.wizard_structure_page_default_title')) ?>, excerpt: '', meta_title: '', meta_description: '' }] };
                    const res = await req('<?= route_to('admin.cms.wizard.structure.create_page') ?>', payload); const json = await res.json(); if (!json.ok) throw new Error(json.message || <?= json_encode(lang('Wizard.wizard_structure_error_page')) ?>);
                    const id = json.data?.id || ''; this.message=<?= json_encode(lang('Wizard.wizard_structure_page_created')) ?>; if (id) setTimeout(() => window.location.href = '<?= route_to('admin.cms.pages') ?>/' + id, 700);
                } catch (e) { this.errorMsg = e.message || <?= json_encode(lang('Wizard.wizard_structure_error_page')) ?>; }
            },
            async submitMenu() {
                this.message=''; this.errorMsg='';
                try {
                    const payload = { menu_key: slugify(this.menu.menu_key || this.menu.name || <?= json_encode(lang('Wizard.wizard_structure_menu_default_key')) ?>), location: this.menu.location || 'main', is_active: this.menu.is_active ? 1 : 0, translations: [{ language_id: this.translation.language_id, name: this.menu.name || <?= json_encode(lang('Wizard.wizard_structure_menu_default_name')) ?> }] };
                    const res = await req('<?= route_to('admin.cms.wizard.structure.create_menu') ?>', payload); const json = await res.json(); if (!json.ok) throw new Error(json.message || <?= json_encode(lang('Wizard.wizard_structure_error_menu')) ?>);
                    const id = json.data?.id || ''; this.message=<?= json_encode(lang('Wizard.wizard_structure_menu_created')) ?>; if (id) setTimeout(() => window.location.href = '<?= route_to('admin.cms.menus') ?>/' + id, 700);
                } catch (e) { this.errorMsg = e.message || <?= json_encode(lang('Wizard.wizard_structure_error_menu')) ?>; }
            },
        };
    };
})();
</script>
