<?php
/**
 * @var string $title
 * @var string $csrfName
 * @var string $csrfToken
 */
$csrfName  ??= csrf_token();
$csrfToken ??= csrf_hash();
?>
<div class="max-w-6xl mx-auto" x-data="structureWizard()" x-init="init()">
    <div x-show="screen === 'loading'" x-cloak class="text-center py-16 text-gray-400">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand-600 mx-auto mb-4"></div>
        <p><?= esc(lang('Wizard.loading')) ?></p>
    </div>

    <div x-show="screen === 'error'" x-cloak class="rounded-2xl border border-red-200 bg-red-50 p-6">
        <p class="text-sm font-semibold text-red-700" x-text="errorMsg"></p>
        <div class="mt-4 flex gap-3">
            <button type="button" @click="init()" class="btn-secondary"><?= esc(lang('Wizard.btn_retry')) ?></button>
            <a href="<?= route_to('admin.cms.wizard') ?>" class="btn-primary"><?= esc(lang('Wizard.btn_back_panel')) ?></a>
        </div>
    </div>

    <div x-show="screen === 'home'" x-cloak class="space-y-8">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500"><?= esc(lang('Wizard.structure_sidebar_label')) ?></p>
            <h1 class="mt-2 text-3xl font-bold text-gray-900"><?= esc(lang('Wizard.structure_heading')) ?></h1>
            <p class="mt-3 max-w-2xl text-sm text-gray-600"><?= esc(lang('Wizard.structure_intro')) ?></p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <button type="button" @click="start('collection')" class="rounded-2xl border-2 border-gray-200 bg-white p-6 text-left shadow-sm transition hover:border-brand-400 hover:shadow-md">
                <div class="text-4xl">🗂️</div>
                <div class="mt-3 text-lg font-semibold text-gray-900"><?= esc(lang('Wizard.create_collection')) ?></div>
                <div class="mt-1 text-sm text-gray-600"><?= esc(lang('Wizard.create_collection_desc')) ?></div>
            </button>
            <button type="button" @click="start('page')" class="rounded-2xl border-2 border-gray-200 bg-white p-6 text-left shadow-sm transition hover:border-brand-400 hover:shadow-md">
                <div class="text-4xl">📄</div>
                <div class="mt-3 text-lg font-semibold text-gray-900"><?= esc(lang('Wizard.create_page')) ?></div>
                <div class="mt-1 text-sm text-gray-600"><?= esc(lang('Wizard.create_page_desc')) ?></div>
            </button>
            <button type="button" @click="start('menu')" class="rounded-2xl border-2 border-gray-200 bg-white p-6 text-left shadow-sm transition hover:border-brand-400 hover:shadow-md">
                <div class="text-4xl">🔗</div>
                <div class="mt-3 text-lg font-semibold text-gray-900"><?= esc(lang('Wizard.create_menu')) ?></div>
                <div class="mt-1 text-sm text-gray-600"><?= esc(lang('Wizard.create_menu_desc')) ?></div>
            </button>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6">
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
        <div class="grid gap-6 lg:grid-cols-12">
            <aside class="lg:col-span-3">
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.wizard_structure_step1')) ?></p>
                    <div class="mt-4 space-y-2">
                        <template x-for="option in config?.intent_options ?? []" :key="option.key">
                            <button type="button" @click="selectIntent(option)" :class="selectedIntent?.key === option.key ? 'border-brand-500 bg-brand-50 text-brand-800' : 'border-gray-200 bg-white text-gray-700'" class="w-full rounded-xl border p-3 text-left transition">
                                <div class="font-semibold" x-text="option.label"></div>
                            </button>
                        </template>
                    </div>
                </div>
            </aside>
            <main class="lg:col-span-9">
                <form @submit.prevent="submitCollection()" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.wizard_structure_step2')) ?></p>
                        <h2 class="mt-1 text-2xl font-bold text-gray-900"><?= esc(lang('Wizard.create_collection')) ?></h2>
                        <p class="mt-1 text-sm text-gray-600"><?= esc(lang('Wizard.wizard_structure_collection_review_intro')) ?></p>
                    </div>
                    <template x-if="collectionStep === 1">
                        <div class="space-y-4">
                            <div x-show="collectionErrors.step1" x-cloak class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700" x-text="collectionErrors.step1"></div>
                            <div class="grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_field_name')) ?></span><input type="text" x-model="form.name" @input="syncCollection()" class="w-full rounded-lg border-gray-300"></label>
                            <label class="block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_field_collection_key')) ?></span><input type="text" x-model="form.collection_key" @input="syncCollection(true)" class="w-full rounded-lg border-gray-300"></label>
                            <label class="block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_field_url_prefix')) ?></span><input type="text" x-model="form.url_prefix" class="w-full rounded-lg border-gray-300"></label>
                            <label class="block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_field_order')) ?></span><input type="number" x-model.number="form.sort_order" class="w-full rounded-lg border-gray-300" min="0"></label>
                            </div>
                        </div>
                    </template>
                    <template x-if="collectionStep === 2">
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.wizard_structure_step3')) ?></p>
                            <div class="mt-3 grid gap-4 md:grid-cols-2">
                                <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4"><input type="checkbox" x-model="form.requires_approval" class="rounded border-gray-300"><span><span class="block text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.wizard_structure_flag_requires_approval')) ?></span><span class="block text-xs text-gray-500"><?= esc(lang('Wizard.wizard_structure_flag_requires_approval_help')) ?></span></span></label>
                                <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4"><input type="checkbox" x-model="form.enables_categories" class="rounded border-gray-300"><span><span class="block text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.wizard_structure_flag_categories')) ?></span><span class="block text-xs text-gray-500"><?= esc(lang('Wizard.wizard_structure_flag_categories_help')) ?></span></span></label>
                                <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4"><input type="checkbox" x-model="form.enables_tags" class="rounded border-gray-300"><span><span class="block text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.wizard_structure_flag_tags')) ?></span><span class="block text-xs text-gray-500"><?= esc(lang('Wizard.wizard_structure_flag_tags_help')) ?></span></span></label>
                                <div class="rounded-xl border border-gray-200 bg-white p-4">
                                    <label class="block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_seo_default')) ?></span></label>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <label class="block"><span class="mb-1 block text-xs font-medium text-gray-600"><?= esc(lang('Wizard.wizard_structure_page_sitemap_priority')) ?></span><input type="number" step="0.1" min="0" max="1" x-model="form.default_sitemap_priority" class="w-full rounded-lg border-gray-300"></label>
                                        <label class="block"><span class="mb-1 block text-xs font-medium text-gray-600"><?= esc(lang('Wizard.wizard_structure_page_sitemap_changefreq')) ?></span><select x-model="form.default_changefreq" class="w-full rounded-lg border-gray-300"><option value="weekly"><?= esc(lang('Wizard.wizard_structure_frequency_weekly')) ?></option><option value="daily"><?= esc(lang('Wizard.wizard_structure_frequency_daily')) ?></option><option value="monthly"><?= esc(lang('Wizard.wizard_structure_frequency_monthly')) ?></option><option value="yearly"><?= esc(lang('Wizard.wizard_structure_frequency_yearly')) ?></option><option value="always"><?= esc(lang('Wizard.wizard_structure_frequency_always')) ?></option><option value="hourly"><?= esc(lang('Wizard.wizard_structure_frequency_hourly')) ?></option><option value="never"><?= esc(lang('Wizard.wizard_structure_frequency_never')) ?></option></select></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template x-if="collectionStep === 3">
                        <div class="space-y-4">
                            <div x-show="collectionErrors.step3" x-cloak class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700" x-text="collectionErrors.step3"></div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.wizard_structure_step4')) ?></p>
                            <div class="mt-3 grid gap-4 md:grid-cols-2">
                                <label class="block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_field_language_name')) ?></span><input type="text" x-model="translation.name" class="w-full rounded-lg border-gray-300"></label>
                                <label class="block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_field_language_slug')) ?></span><input type="text" x-model="translation.slug" class="w-full rounded-lg border-gray-300"></label>
                                <label class="md:col-span-2 block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_field_language_description')) ?></span><textarea x-model="translation.description" rows="3" class="w-full rounded-lg border-gray-300"></textarea></label>
                            </div>
                            </div>
                        </div>
                    </template>
                    <template x-if="collectionStep === 4">
                        <div class="rounded-2xl border border-gray-200 bg-white p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.wizard_structure_step5')) ?></p>
                            <div class="mt-3 flex items-center justify-between gap-3">
                                <div><p class="text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.wizard_structure_optional_config')) ?></p><p class="text-xs text-gray-500"><?= esc(lang('Wizard.wizard_structure_optional_config_hint')) ?></p></div>
                                <select x-model="form.wizard_config_mode" class="rounded-lg border-gray-300"><option value="empty"><?= esc(lang('Wizard.wizard_structure_empty_mode')) ?></option><option value="base"><?= esc(lang('Wizard.wizard_structure_base_mode')) ?></option></select>
                            </div>
                            <template x-if="form.wizard_config_mode === 'base'">
                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
                                    <p class="font-semibold text-gray-900 mb-2"><?= esc(lang('Wizard.wizard_structure_preview_title')) ?></p>
                                    <pre class="overflow-auto rounded-lg bg-white p-3 text-xs border border-gray-200" x-text="wizardConfigPreview()"></pre>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="collectionStep === 5">
                        <div class="space-y-4">
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                                <p class="font-semibold text-gray-900 mb-3"><?= esc(lang('Wizard.wizard_structure_final_summary')) ?></p>
                                <p><strong><?= esc(lang('Wizard.wizard_structure_summary_name')) ?>:</strong> <span x-text="form.name || '—'"></span></p>
                                <p><strong><?= esc(lang('Wizard.wizard_structure_field_collection_key')) ?>:</strong> <span x-text="form.collection_key || '—'"></span></p>
                                <p><strong><?= esc(lang('Wizard.wizard_structure_field_url_prefix')) ?>:</strong> <span x-text="form.url_prefix || '—'"></span></p>
                                <p><strong><?= esc(lang('Wizard.wizard_structure_summary_slug')) ?>:</strong> <span x-text="translation.slug || '—'"></span></p>
                                <p><strong><?= esc(lang('Wizard.wizard_structure_summary_language')) ?>:</strong> <span x-text="defaultLanguageLabel()"></span></p>
                                <p><strong><?= esc(lang('Wizard.wizard_structure_summary_assistant')) ?>:</strong> <span x-text="form.wizard_config_mode === 'base' ? <?= json_encode(lang('Wizard.wizard_structure_base_mode')) ?> : <?= json_encode(lang('Wizard.wizard_structure_empty_mode')) ?>"></span></p>
                            </div>
                            <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-xl p-3"><?= esc(lang('Wizard.wizard_structure_conflict_warning')) ?></p>
                        </div>
                    </template>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" @click="prevCollectionStep()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700" :disabled="collectionStep === 1"><?= esc(lang('Wizard.wizard_structure_prev')) ?></button>
                        <button type="button" @click="nextCollectionStep()" x-show="collectionStep < 5" class="rounded-lg border border-brand-300 bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-700" :disabled="!canAdvanceCollectionStep()"><?= esc(lang('Wizard.wizard_structure_next')) ?></button>
                        <button type="submit" x-show="collectionStep === 5" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-60" :disabled="saving || !canSubmitCollection()"><span x-show="!saving"><?= esc(lang('Wizard.wizard_structure_create')) ?></span><span x-show="saving"><?= esc(lang('Wizard.wizard_structure_creating')) ?></span></button>
                        <button type="button" @click="screen = 'home'" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700"><?= esc(lang('App.cancel')) ?></button>
                    </div>
                </form>
            </main>
        </div>
        <div x-show="message" x-cloak class="rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800" x-text="message"></div>
        <div x-show="errorMsg" x-cloak class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" x-text="errorMsg"></div>
        <div x-show="createdCollectionId" x-cloak class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.wizard_structure_collection_ready')) ?></p>
            <p class="mt-1 text-sm text-gray-600"><?= esc(lang('Wizard.wizard_structure_post_create_help')) ?></p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a :href="collectionDetailUrl()" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700"><?= esc(lang('Wizard.wizard_structure_go_detail')) ?></a>
                <button type="button" @click="resetCollectionFlow()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700"><?= esc(lang('Wizard.wizard_structure_create_another')) ?></button>
                <a href="<?= route_to('admin.cms.wizard') ?>" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700"><?= esc(lang('Wizard.btn_back_panel')) ?></a>
            </div>
        </div>
    </div>

    <div x-show="screen === 'page'" x-cloak class="space-y-6">
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <button type="button" @click="screen = 'home'" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 font-semibold text-gray-700"><?= esc(lang('App.back')) ?></button>
        </div>
        <form @submit.prevent="submitPage()" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.create_page')) ?></p><h2 class="mt-1 text-2xl font-bold text-gray-900"><?= esc(lang('Wizard.create_page')) ?></h2></div>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_page_type')) ?></span><select x-model="page.page_type" class="w-full rounded-lg border-gray-300"><option value="generic"><?= esc(lang('Wizard.wizard_structure_page_type_generic')) ?></option><option value="home"><?= esc(lang('Wizard.wizard_structure_page_type_home')) ?></option><option value="contact"><?= esc(lang('Wizard.wizard_structure_page_type_contact')) ?></option><option value="privacy"><?= esc(lang('Wizard.wizard_structure_page_type_privacy')) ?></option></select></label>
                <label class="block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_page_status')) ?></span><select x-model="page.status" class="w-full rounded-lg border-gray-300"><option value="draft"><?= esc(lang('Wizard.confirm_status_draft')) ?></option><option value="published"><?= esc(lang('Wizard.confirm_status_published')) ?></option></select></label>
                <label class="block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_page_title')) ?></span><input type="text" x-model="page.title" class="w-full rounded-lg border-gray-300"></label>
                <label class="block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_page_slug')) ?></span><input type="text" x-model="page.slug" class="w-full rounded-lg border-gray-300"></label>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="flex items-center gap-3 rounded-xl border border-gray-200 p-4"><input type="checkbox" x-model="page.is_in_sitemap" class="rounded border-gray-300"><span class="text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.wizard_structure_page_sitemap')) ?></span></label>
                <label class="block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_field_order')) ?></span><input type="number" x-model.number="page.sort_order" class="w-full rounded-lg border-gray-300"></label>
            </div>
            <div class="flex flex-wrap gap-3"><button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white"><?= esc(lang('Wizard.create_page')) ?></button><button type="button" @click="screen='home'" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700"><?= esc(lang('App.cancel')) ?></button></div>
        </form>
        <div x-show="message" x-cloak class="rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800" x-text="message"></div>
        <div x-show="errorMsg" x-cloak class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" x-text="errorMsg"></div>
    </div>

    <div x-show="screen === 'menu'" x-cloak class="space-y-6">
        <div class="flex items-center gap-3 text-sm text-gray-500"><button type="button" @click="screen='home'" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 font-semibold text-gray-700"><?= esc(lang('App.back')) ?></button></div>
        <form @submit.prevent="submitMenu()" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.create_menu')) ?></p><h2 class="mt-1 text-2xl font-bold text-gray-900"><?= esc(lang('Wizard.create_menu')) ?></h2></div>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_menu_key')) ?></span><input type="text" x-model="menu.menu_key" class="w-full rounded-lg border-gray-300"></label>
                <label class="block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_menu_location')) ?></span><input type="text" x-model="menu.location" class="w-full rounded-lg border-gray-300"></label>
                <label class="flex items-center gap-3 rounded-xl border border-gray-200 p-4"><input type="checkbox" x-model="menu.is_active" class="rounded border-gray-300"><span class="text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.wizard_structure_menu_active')) ?></span></label>
                <label class="block"><span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_menu_name')) ?></span><input type="text" x-model="menu.name" class="w-full rounded-lg border-gray-300"></label>
            </div>
            <div class="flex flex-wrap gap-3"><button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white"><?= esc(lang('Wizard.create_menu')) ?></button><button type="button" @click="screen='home'" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700"><?= esc(lang('App.cancel')) ?></button></div>
        </form>
        <div x-show="message" x-cloak class="rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800" x-text="message"></div>
        <div x-show="errorMsg" x-cloak class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" x-text="errorMsg"></div>
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
            screen: 'loading', config: null, errorMsg: '', message: '', saving: false, selectedIntent: null, createdCollectionId: '',
            collectionErrors: { step1: '', step3: '' },
            form: { name: '', collection_key: '', url_prefix: '', sort_order: 0, is_active: true, requires_approval: false, enables_categories: true, enables_tags: true, default_sitemap_priority: 0.5, default_changefreq: 'weekly', wizard_config_mode: 'empty' },
            collectionStep: 1,
            translation: { language_id: 0, name: '', slug: '', description: '' },
            page: { page_type: 'generic', status: 'draft', parent_id: null, sort_order: 0, is_in_sitemap: true, sitemap_priority: 0.5, sitemap_changefreq: 'weekly', translations: [] , title: '', slug: ''},
            menu: { menu_key: '', location: 'main', is_active: true, name: '' },
            async init() {
                try {
                    const res = await fetch('<?= route_to('admin.cms.wizard.structure.config') ?>', { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const json = await res.json();
                    this.config = json.data || {};
                    this.translation.language_id = this.config.default_language_id || 0;
                    this.page.translations = [{ language_id: this.translation.language_id, slug: '', title: '', excerpt: '', meta_title: '', meta_description: '' }];
                    this.screen = 'home';
                } catch (e) { this.errorMsg = <?= json_encode(lang('Wizard.wizard_structure_error_load')) ?>; this.screen = 'error'; }
            },
            start(kind) { this.message=''; this.errorMsg=''; this.createdCollectionId=''; this.collectionErrors = { step1: '', step3: '' }; this.screen = kind; if (kind === 'collection') { this.collectionStep = 1; if (!this.selectedIntent) this.selectIntent((this.config.intent_options || [])[0] || null); } if (kind === 'page') { this.page.title=''; this.page.slug=''; } if (kind==='menu') { this.menu.menu_key=''; this.menu.name=''; } },
            resetCollectionFlow() { this.createdCollectionId=''; this.message=''; this.errorMsg=''; this.collectionErrors = { step1: '', step3: '' }; this.collectionStep = 1; this.selectedIntent = (this.config.intent_options || [])[0] || null; this.selectIntent(this.selectedIntent); this.screen = 'collection'; },
            collectionDetailUrl() { return this.createdCollectionId ? '<?= route_to('admin.cms.collections') ?>/' + this.createdCollectionId : '<?= route_to('admin.cms.collections') ?>'; },
            selectIntent(option) { this.selectedIntent = option; const s = option?.suggestions || {}; const name = option?.label || 'Colección'; this.form.name = this.form.name || name; this.form.collection_key = this.form.collection_key || slugify(name); this.form.url_prefix = this.form.url_prefix || this.form.collection_key; this.form.requires_approval = !!s.requires_approval; this.form.enables_categories = s.enables_categories !== undefined ? !!s.enables_categories : true; this.form.enables_tags = s.enables_tags !== undefined ? !!s.enables_tags : true; this.form.default_sitemap_priority = s.default_sitemap_priority ?? 0.5; this.form.default_changefreq = s.default_changefreq ?? 'weekly'; this.translation.name = this.form.name; this.translation.slug = slugify(this.form.name); },
            syncCollection(forceKey = false) { this.form.collection_key = slugify(this.form.collection_key); this.form.url_prefix = slugify(this.form.url_prefix); if (!this.form.url_prefix || forceKey) this.form.url_prefix = this.form.collection_key; if (!this.translation.slug || forceKey) this.translation.slug = slugify(this.form.name || this.form.collection_key); },
            canAdvanceCollectionStep() {
                if (this.collectionStep === 1) return Boolean(this.form.name && this.form.collection_key && this.form.url_prefix);
                if (this.collectionStep === 3) return Boolean(this.translation.language_id && this.translation.name && this.translation.slug);
                return true;
            },
            nextCollectionStep() {
                if (!this.canAdvanceCollectionStep()) {
                    if (this.collectionStep === 1) this.collectionErrors.step1 = <?= json_encode(lang('Wizard.wizard_structure_step1_error')) ?>;
                    if (this.collectionStep === 3) this.collectionErrors.step3 = <?= json_encode(lang('Wizard.wizard_structure_step3_error')) ?>;
                    return;
                }
                this.collectionErrors = { step1: '', step3: '' };
                if (this.collectionStep === 1) { this.syncCollection(); }
                if (this.collectionStep === 2) { this.form.requires_approval = !!this.form.requires_approval; }
                if (this.collectionStep < 5) { this.collectionStep += 1; }
            },
            prevCollectionStep() { if (this.collectionStep > 1) this.collectionStep -= 1; },
            canSubmitCollection() {
                return Boolean(this.form.name && this.form.collection_key && this.form.url_prefix && this.translation.language_id && this.translation.name && this.translation.slug);
            },
            defaultLanguageLabel() { const lang = (this.config.languages || []).find((i) => Number(i.id) === Number(this.translation.language_id)); return lang ? `${lang.code} - ${lang.name}` : '—'; },
            wizardConfigPreview() {
                return JSON.stringify({ icon: '🗂️', label: this.form.name || <?= json_encode(lang('Wizard.create_collection')) ?>, description: this.translation.description || '', steps: [{ step_title: <?= json_encode(lang('Wizard.default_step1_title')) ?>, fields: [{ key: 'title', label: <?= json_encode(lang('Wizard.default_field_title')) ?>, type: 'text', required: true }] }, { step_title: <?= json_encode(lang('Wizard.default_step3_title')) ?>, fields: [{ key: 'excerpt', label: <?= json_encode(lang('Wizard.default_field_excerpt')) ?>, type: 'textarea', required: false }] }] }, null, 2);
            },
            stepLabel() { return <?= json_encode(sprintf(lang('Wizard.step_of'), '%s', '5')) ?>.replace('%s', this.collectionStep); },
            async submitCollection() {
                if (!this.canSubmitCollection()) { this.errorMsg = <?= json_encode(lang('Wizard.wizard_structure_collection_payload_error')) ?>; return; }
                this.saving = true; this.message=''; this.errorMsg='';
                try {
                    const payload = { collection_key: this.form.collection_key, url_prefix: this.form.url_prefix, is_active: this.form.is_active ? 1 : 0, requires_approval: this.form.requires_approval ? 1 : 0, enables_categories: this.form.enables_categories ? 1 : 0, enables_tags: this.form.enables_tags ? 1 : 0, default_sitemap_priority: this.form.default_sitemap_priority, default_changefreq: this.form.default_changefreq, sort_order: this.form.sort_order, translations: [{ language_id: this.translation.language_id, slug: this.translation.slug || this.form.collection_key, name: this.translation.name || this.form.name, description: this.translation.description || '' }] };
                    if (this.form.wizard_config_mode === 'base') payload.wizard_config = { icon: '🗂️', label: this.form.name || <?= json_encode(lang('Wizard.create_collection')) ?>, description: this.translation.description || '', steps: [{ step_title: <?= json_encode(lang('Wizard.default_step1_title')) ?>, step_hint: <?= json_encode(lang('Wizard.default_step1_hint')) ?>, fields: [{ key: 'title', label: <?= json_encode(lang('Wizard.default_field_title')) ?>, type: 'text', required: true }] }, { step_title: <?= json_encode(lang('Wizard.default_step3_title')) ?>, step_hint: <?= json_encode(lang('Wizard.default_step3_hint')) ?>, fields: [{ key: 'excerpt', label: <?= json_encode(lang('Wizard.default_field_excerpt')) ?>, type: 'textarea', required: false }] }] };
                    const res = await req('<?= route_to('admin.cms.wizard.structure.create_collection') ?>', payload); const json = await res.json();
                    if (!json.ok) throw new Error(json.message || <?= json_encode(lang('Wizard.wizard_structure_error_collection')) ?>);
                    const id = json.data?.id || '';
                    this.message = <?= json_encode(lang('Wizard.wizard_structure_collection_ready')) ?>;
                    this.createdCollectionId = id ? String(id) : '';
                } catch (e) { this.errorMsg = e.message || <?= json_encode(lang('Wizard.wizard_structure_error_collection')) ?>; } finally { this.saving = false; }
            },
            async submitPage() {
                this.message=''; this.errorMsg='';
                try {
                    const payload = { page_type: this.page.page_type, status: this.page.status, parent_id: null, sort_order: this.page.sort_order, is_in_sitemap: this.page.is_in_sitemap ? 1 : 0, sitemap_priority: this.page.sitemap_priority, sitemap_changefreq: this.page.sitemap_changefreq, translations: [{ language_id: this.translation.language_id, slug: slugify(this.page.slug || this.page.title || <?= json_encode(lang('Wizard.wizard_structure_page_default_title')) ?>), title: this.page.title || <?= json_encode(lang('Wizard.wizard_structure_page_default_title')) ?>, excerpt: '', meta_title: '', meta_description: '' }] };
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
