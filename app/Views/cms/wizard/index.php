<?php
/**
 * @var string $title
 * @var string $csrfName
 * @var string $csrfToken
 */
$csrfName  ??= csrf_token();
$csrfToken ??= csrf_hash();
?>
<div class="max-w-6xl mx-auto space-y-6" x-data="wizard()" x-init="init()">

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= lang('Wizard.home_heading') ?></p>
                <h1 class="text-2xl font-semibold text-gray-900"><?= lang('Wizard.home_heading') ?></h1>
                <p class="text-sm text-gray-600"><?= lang('Wizard.structure_intro') ?></p>
            </div>
            <a href="<?= site_url('dashboard') ?>" class="btn-secondary"><?= lang('Wizard.btn_back_panel') ?></a>
        </div>
    </div>

    <!-- Loading screen -->
    <div x-show="screen === 'loading'" x-cloak class="rounded-xl border border-gray-200 bg-white p-10 text-center shadow-sm">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand-600 mx-auto mb-4"></div>
        <p class="text-sm text-gray-500"><?= lang('Wizard.loading') ?></p>
    </div>

    <!-- Error screen -->
    <div x-show="screen === 'error'" x-cloak class="rounded-xl border border-red-200 bg-red-50 p-6 shadow-sm">
        <p class="text-sm font-medium text-red-700" x-text="errorMsg"></p>
        <div class="mt-4 flex flex-wrap gap-3">
            <button @click="init()" class="btn-secondary"><?= lang('Wizard.btn_retry') ?></button>
            <button @click="goHome()" class="btn-primary"><?= lang('Wizard.btn_back_panel') ?></button>
        </div>
    </div>

    <!-- ── Home screen ── -->
    <?= view('cms/wizard/_partials/home') ?>

    <!-- ── Entry creation flow (A screens) ── -->
    <?= view('cms/wizard/_partials/entry_wizard') ?>

    <!-- ── Page selection (B1) ── -->
    <?= view('cms/wizard/_partials/page_list') ?>

    <!-- ── Page layout / block tree (B2) ── -->
    <?= view('cms/wizard/_partials/page_layout') ?>

    <!-- ── Block type catalog (new) ── -->
    <?= view('cms/wizard/_partials/block_catalog') ?>

    <!-- ── Block editor (B3) ── -->
    <?= view('cms/wizard/_partials/block_edit') ?>

    <!-- ── Block saved (B4) ── -->
    <?= view('cms/wizard/_partials/block_saved') ?>

    <!-- ── Menu edit (C screens) ── -->
    <?= view('cms/wizard/_partials/menu_edit') ?>

</div>

<script <?= csp_script_nonce() ?>>
// ── Wizard Alpine.js component ────────────────────────────────────────────────
(function () {
    'use strict';

    const CSRF_NAME  = <?= json_encode($csrfName) ?>;
    const CSRF_TOKEN = <?= json_encode($csrfToken) ?>;
    const NATIVE_KEYS = ['title', 'excerpt', 'featured_image', 'body', 'status'];
    const WIZARD_BASE = '<?= site_url('admin/cms/wizard') ?>';
    const ADMIN_CMS_BASE = '<?= site_url('admin/cms') ?>';
    const PUBLIC_SITE_URL = '<?= rtrim((string) env('PUBLIC_SITE_URL'), '/') ?>';
    const TRANSLATE_URL = '<?= route_to('admin.cms.translate') ?>';

    const DEFAULT_STEPS = [
        {
            step_title: <?= json_encode(lang('Wizard.default_step1_title')) ?>,
            step_hint:  <?= json_encode(lang('Wizard.default_step1_hint')) ?>,
            fields: [{ key: 'title', label: <?= json_encode(lang('Wizard.default_field_title')) ?>, type: 'text', required: true }],
        },
        {
            step_title: <?= json_encode(lang('Wizard.default_step2_title')) ?>,
            step_hint:  <?= json_encode(lang('Wizard.default_step2_hint')) ?>,
            fields: [{ key: 'featured_image', label: <?= json_encode(lang('Wizard.default_field_image')) ?>, type: 'image', required: false }],
        },
        {
            step_title: <?= json_encode(lang('Wizard.default_step3_title')) ?>,
            step_hint:  <?= json_encode(lang('Wizard.default_step3_hint')) ?>,
            fields: [{ key: 'excerpt', label: <?= json_encode(lang('Wizard.default_field_excerpt')) ?>, type: 'textarea', required: false }],
        },
    ];

    const STRINGS = {
        step_of:               <?= json_encode(lang('Wizard.step_of')) ?>,
        page_fallback:         <?= json_encode(lang('Wizard.page_fallback')) ?>,
        menu_fallback:         <?= json_encode(lang('Wizard.menu_fallback')) ?>,
        delete_confirm:        <?= json_encode(lang('Wizard.delete_item_confirm')) ?>,
        add_content_desc:      <?= json_encode(lang('Wizard.add_content_desc')) ?>,
        add_content_desc_empty:<?= json_encode(lang('Wizard.add_content_desc_empty')) ?>,
        error_no_pages:        <?= json_encode(lang('Wizard.error_no_pages')) ?>,
        error_no_menus:        <?= json_encode(lang('Wizard.error_no_menus')) ?>,
        error_blocks_load:     <?= json_encode(lang('Wizard.error_blocks_load')) ?>,
        error_items_load:      <?= json_encode(lang('Wizard.error_items_load')) ?>,
        error_block_save:      <?= json_encode(lang('Wizard.error_block_save')) ?>,
        error_item_save:       <?= json_encode(lang('Wizard.error_item_save')) ?>,
        error_item_delete:     <?= json_encode(lang('Wizard.error_item_delete')) ?>,
        error_upload:          <?= json_encode(lang('Wizard.error_upload')) ?>,
        error_publish:         <?= json_encode(lang('Wizard.error_publish')) ?>,
        error_load:            <?= json_encode(lang('Wizard.error_load')) ?>,
        error_block_type_missing: <?= json_encode(lang('Wizard.error_block_type_missing')) ?>,
        error_block_delete:    <?= json_encode(lang('Wizard.error_block_delete')) ?>,
        error_upload_failed:   <?= json_encode(lang('Wizard.error_upload_failed')) ?>,
        add_child:             <?= json_encode(lang('Wizard.add_child')) ?>,
        add_block:             <?= json_encode(lang('Wizard.add_block')) ?>,
        block_fallback:        <?= json_encode(lang('Wizard.block_fallback')) ?>,
        content_fallback:      <?= json_encode(lang('Wizard.content_fallback')) ?>,
    };

    // Block type → display emoji
    const BLOCK_ICONS = {
        hero_slider:          '🎠',
        hero_banner:          '🖼️',
        slide_banner:         '🖼️',
        rich_text:            '📝',
        image:                '🖼️',
        news_grid:            '📰',
        events_grid:          '📅',
        cta:                  '📢',
        contact_form:         '✉️',
        video_player:         '🎬',
        faq_accordion:        '❓',
        faq_item:             '❓',
        features_grid:        '⭐',
        feature_card:         '⭐',
        stats_section:        '📊',
        stat_item:            '🔢',
        testimonials_slider:  '💬',
        testimonial_card:     '💬',
        logo_showcase:        '🏢',
        logo_item:            '🏢',
        location_info:        '📍',
        social_links:         '🔗',
        container:            '📦',
        page_header:          '📄',
    };

    function csrfHeaders() {
        return { 'X-CSRF-TOKEN': CSRF_TOKEN, [CSRF_NAME]: CSRF_TOKEN };
    }

    async function adminFetch(url, opts = {}) {
        const isFormData = opts.body instanceof FormData;
        const headers = isFormData
            ? csrfHeaders()
            : { 'Content-Type': 'application/json', ...csrfHeaders() };
        return fetch(url, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', ...headers, ...(opts.headers || {}) },
            ...opts,
        });
    }

    function slugify(str) {
        return str
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .toLowerCase().replace(/[^a-z0-9\s-]/g, '').trim()
            .replace(/\s+/g, '-').replace(/-+/g, '-').substring(0, 100);
    }

    function truncateText(value, maxLength) {
        const text = String(value ?? '').trim();
        if (text === '' || text.length <= maxLength) {
            return text;
        }

        return text.slice(0, maxLength).trim();
    }

    function schemaTypeToUiType(schemaType, accept) {
        if (schemaType === 'file')    return accept === 'image' ? 'image' : 'text';
        if (schemaType === 'richtext' || schemaType === 'rich_text') return 'richtext';
        if (schemaType === 'string')  return 'text';
        if (schemaType === 'number')  return 'number';
        if (schemaType === 'boolean') return 'boolean';
        if (schemaType === 'url')     return 'url';
        if (schemaType === 'select')  return 'select';
        return 'textarea';
    }

    function humanizeKey(key) {
        if (!key) return '';
        return key
            .replace(/_/g, ' ')
            .replace(/([a-z])([A-Z])/g, '$1 $2')
            .replace(/\b\w/g, l => l.toUpperCase());
    }

    window.wizard = function () {
        return {
            // ── State ─────────────────────────────────────────────────────────
            screen: 'loading',
            config: null,
            defaultLangId: 0,
            strings: STRINGS,
            errorMsg: '',

            // Add-content flow
            selectedCollection: null,
            currentStep: 0,
            formData: {},
            publishedEntry: null,
            publishing: false,
            publishError: '',
            entryReviewLoading: false,
            entryReviewError: '',
            entryTranslationRows: [],

            // Image upload (shared)
            uploading: false,
            uploadError: '',

            // Draft
            draft: null,

            // Edit page flow (B screens)
            selectedPage: null,
            selectedOwnerType: 'page',
            pageBlocks: [],          // tree-structured (built from flat API response)
            pageBlocksLoading: false,
            pageBlocksError: '',
            blocksBackScreen: 'page-select',

            // Block editing / creating
            selectedBlock: null,
            blockEditData: {},
            blockSaving: false,
            blockSaveError: '',
            editMode: 'edit',        // 'edit' | 'create'
            editParentBlock: null,   // parent block when adding a child
            editBlockTypeKey: null,  // block_key selected from catalog

            // Block delete confirmation
            deleteBlockTarget: null,

            // Block catalog (picker)
            catalogContext: null,    // null = top-level, block = adding child

            // Edit menu flow (C screens)
            selectedMenu: null,
            menuItems: [],
            menuItemsLoading: false,
            menuItemsError: '',
            menuItemsSaving: false,
            menuSaveError: '',
            newItemLabel: '',
            newItemUrl: '',
            deleteItemTarget: null,

            // ── Computed ──────────────────────────────────────────────────────
            get steps() {
                return this.selectedCollection?.wizard_config?.steps ?? DEFAULT_STEPS;
            },
            get currentStepSchema() {
                return this.steps[this.currentStep] ?? null;
            },
            get totalSteps() {
                return this.steps.length;
            },
            get defaultLanguage() {
                const languages = Array.isArray(this.config?.languages) ? this.config.languages : [];
                return languages.find((language) => language?.is_default) || languages[0] || null;
            },
            get defaultLanguageId() {
                return Number(this.defaultLanguage?.id || this.resolveDefaultLanguageId());
            },
            get defaultLanguageCode() {
                return String(this.defaultLanguage?.code || '').trim().toUpperCase();
            },

            // ── Helpers ───────────────────────────────────────────────────────
            stepLabel() {
                return STRINGS.step_of.replace('%s', this.currentStep + 1).replace('%s', this.totalSteps);
            },

            deleteConfirmText() {
                const label = this.deleteItemTarget?._label ?? '';
                return STRINGS.delete_confirm.replace('%s', label);
            },

            addContentDesc() {
                const cols = this.config?.collections ?? [];
                if (cols.length === 0) return STRINGS.add_content_desc_empty;
                const names = cols.slice(0, 4).map(c => this.collectionDisplayLabel(c));
                return names.join(', ') + (cols.length > 4 ? '…' : '');
            },

            collectionDisplayLabel(collection) {
                if (!collection) return STRINGS.content_fallback;

                const name = String(collection.name ?? '').trim() || STRINGS.content_fallback;
                const key = String(collection.collection_key ?? '').trim();
                const prefix = String(collection.url_prefix ?? '').trim();
                const descriptor = key || prefix;

                if (!descriptor || descriptor === name.toLowerCase()) {
                    return name;
                }

                return `${name} · ${descriptor}`;
            },

            // ── Block type helpers ────────────────────────────────────────────
            blockIcon(blockKey) {
                return BLOCK_ICONS[blockKey] ?? '📦';
            },

            blockTypeInfo(blockKey) {
                if (!blockKey || !this.config?.block_types) return null;
                return this.config.block_types[blockKey] ?? null;
            },

            entryReviewLanguages() {
                return (Array.isArray(this.config?.languages) ? this.config.languages : [])
                    .filter((language) => Number(language?.id || 0) > 0);
            },

            entryTranslationCount() {
                return this.entryTranslationRows.length;
            },

            entryTranslationBusy() {
                return this.entryReviewLoading || this.entryTranslationRows.some((row) => Boolean(row?.translating));
            },

            entryReviewSourceContent() {
                return {
                    title: String(this.formData.title || this.selectedCollection?.name || STRINGS.content_fallback || '').trim(),
                    excerpt: String(this.formData.excerpt || '').trim(),
                    meta_title: String(this.formData.meta_title || '').trim(),
                    meta_description: String(this.formData.meta_description || '').trim(),
                    featured_file_id: this.formData.featured_image_id ?? null,
                    featured_image_url: this.formData.featured_image_url ?? null,
                };
            },

            entryReviewFields() {
                return this.steps.flatMap((step) => Array.isArray(step.fields) ? step.fields : []);
            },

            entryReviewPreviewFields() {
                const values = this.entryReviewSourceContent();
                return this.entryReviewFields()
                    .filter((field) => !['date', 'select'].includes(String(field?.type || '')))
                    .map((field) => ({
                        key: field.key,
                        label: field.label,
                        value: values[field.key] || this.formData[field.key] || '',
                    }))
                    .filter((field) => String(field.value || '').trim() !== '');
            },

            entryBaseSlug() {
                return slugify(this.formData.title || 'entry');
            },

            async translateEntryText(text, sourceLang, targetLang) {
                const value = String(text || '').trim();
                const source = String(sourceLang || '').trim().toUpperCase();
                const target = String(targetLang || '').trim().toUpperCase();
                if (value === '' || source === '' || target === '') {
                    return '';
                }

                const url = new URL(TRANSLATE_URL, window.location.origin);
                url.searchParams.set('text', value);
                url.searchParams.set('source_lang', source);
                url.searchParams.set('target_lang', target);

                const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
                const contentType = String(response.headers.get('content-type') || '').toLowerCase();
                const rawBody = await response.text();
                let json = null;

                if (contentType.includes('application/json')) {
                    try {
                        json = rawBody ? JSON.parse(rawBody) : null;
                    } catch (_) {
                        json = null;
                    }
                }

                if (response.ok && json && typeof json.translated === 'string' && json.translated.trim() !== '') {
                    return json.translated.trim();
                }

                if (rawBody.trim().startsWith('<')) {
                    throw new Error(<?= json_encode(lang('Wizard.wizard_structure_languages_translate_error')) ?>);
                }

                throw new Error(json?.error || json?.message || <?= json_encode(lang('Wizard.wizard_structure_languages_translate_error')) ?>);
            },

            async prepareEntryReview() {
                if (this.entryReviewLoading) {
                    return;
                }

                this.entryReviewLoading = true;
                this.entryReviewError = '';
                this.entryTranslationRows = [];

                try {
                    const sourceLanguage = this.defaultLanguageCode || 'EN';
                    const source = this.entryReviewSourceContent();
                    const defaultLanguageId = this.defaultLanguageId;
                    const defaultLanguageCode = sourceLanguage;
                    const defaultLanguageLabel = String(this.defaultLanguage?.label || this.defaultLanguage?.name || this.defaultLanguage?.code || '—').trim() || '—';
                    const translatedRows = [];
                    const targets = this.entryReviewLanguages()
                        .filter((language) => Number(language?.id || 0) !== defaultLanguageId)
                        .map((language) => ({
                            id: Number(language.id || 0),
                            code: String(language.code || '').trim().toUpperCase(),
                            label: String(language.label || language.name || language.code || '').trim() || '—',
                        }));

                    const tasks = targets.map(async (target) => {
                        const row = {
                            language_id: target.id,
                            code: target.code,
                            label: target.label,
                            title: '',
                            slug: '',
                            excerpt: '',
                            meta_title: '',
                            meta_description: '',
                            translating: true,
                            error: '',
                        };

                        try {
                            row.title = source.title ? await this.translateEntryText(source.title, sourceLanguage, target.code) : '';
                            row.excerpt = source.excerpt ? await this.translateEntryText(source.excerpt, sourceLanguage, target.code) : '';
                            row.meta_title = source.meta_title ? await this.translateEntryText(source.meta_title, sourceLanguage, target.code) : '';
                            row.meta_description = source.meta_description ? await this.translateEntryText(source.meta_description, sourceLanguage, target.code) : '';
                            const slugBase = slugify(row.title || source.title || <?= json_encode(lang('Wizard.wizard_structure_page_default_title')) ?>);
                            row.slug = `${slugBase}-${target.code.toLowerCase()}`;
                        } catch (error) {
                            row.error = error instanceof Error ? error.message : String(error);
                            row.title = source.title;
                            row.excerpt = source.excerpt;
                            row.meta_title = source.meta_title;
                            row.meta_description = source.meta_description;
                            row.slug = `${slugify(source.title || <?= json_encode(lang('Wizard.wizard_structure_page_default_title')) ?>)}-${target.code.toLowerCase()}`;
                        } finally {
                            row.translating = false;
                            translatedRows.push(row);
                        }
                    });

                    await Promise.all(tasks);
                    this.entryTranslationRows = translatedRows.sort((a, b) => a.label.localeCompare(b.label));

                    if (translatedRows.some((row) => row.error)) {
                        this.entryReviewError = <?= json_encode(lang('Wizard.wizard_content_review_translation_partial')) ?>;
                    }

                    if (defaultLanguageId > 0) {
                        this.entryTranslationRows.unshift({
                            language_id: defaultLanguageId,
                            code: defaultLanguageCode,
                            label: defaultLanguageLabel,
                            title: source.title,
                            slug: slugify(source.title || <?= json_encode(lang('Wizard.wizard_structure_page_default_title')) ?>),
                            excerpt: source.excerpt,
                            meta_title: source.meta_title,
                            meta_description: source.meta_description,
                            translating: false,
                            error: '',
                            is_base: true,
                        });
                    }
                } catch (error) {
                    this.entryReviewError = error instanceof Error ? error.message : String(error);
                } finally {
                    this.entryReviewLoading = false;
                }
            },

            blockIsContainer(block) {
                const bkey = block?.block_config?.block_key ?? null;
                const typeInfo = this.blockTypeInfo(bkey);
                // A block is a container if its type is flagged OR if it already has children
                return (typeInfo?.is_container === true) || ((block?._children ?? []).length > 0);
            },

            blockAllowedChildren(block) {
                const bkey = block?.block_config?.block_key ?? null;
                const typeInfo = this.blockTypeInfo(bkey);
                return typeInfo?.allowed_children ?? [];
            },

            addChildLabel(block) {
                const allowed = this.blockAllowedChildren(block);
                if (allowed.length === 0) return '+ ' + STRINGS.add_child;
                if (allowed.length === 1) {
                    const typeInfo = this.blockTypeInfo(allowed[0]);
                    const name = typeInfo?.name ?? humanizeKey(allowed[0]);
                    return '+ ' + STRINGS.add_child + ' (' + name + ')';
                }
                return '+ ' + STRINGS.add_child;
            },

            availableBlockTypes() {
                if (!this.config?.block_types) return [];
                const allTypes = Object.entries(this.config.block_types);

                if (this.catalogContext) {
                    const allowed = this.blockAllowedChildren(this.catalogContext);

                    if (allowed.length > 0) {
                        return allTypes
                            .filter(([key]) => allowed.includes(key))
                            .map(([key, info]) => ({ key, ...info }));
                    }

                    // allowed_children not configured — infer from existing children's block keys
                    const existingKeys = [...new Set(
                        (this.catalogContext._children ?? [])
                            .map(c => c.block_config?.block_key)
                            .filter(Boolean)
                    )];
                    if (existingKeys.length > 0) {
                        return allTypes
                            .filter(([key]) => existingKeys.includes(key))
                            .map(([key, info]) => ({ key, ...info }));
                    }

                    // Last resort: all non-container types
                    return allTypes
                        .filter(([, info]) => !info.is_container)
                        .map(([key, info]) => ({ key, ...info }));
                }

                // Top-level: show block types that support the current owner type
                return allTypes
                    .filter(([, info]) => this.selectedOwnerType === 'entry'
                        ? info.supports_entries !== false && !info.is_child_only
                        : info.supports_pages !== false && !info.is_child_only)
                    .map(([key, info]) => ({ key, ...info }));
            },

            // ── Block tree ────────────────────────────────────────────────────
            buildBlockTree(flatBlocks) {
                const topLevel   = flatBlocks.filter(b => !b.parent_instance_id);
                const childrenOf = (parentId) =>
                    flatBlocks
                        .filter(b => b.parent_instance_id === parentId)
                        .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));

                const addChildren = (block) => ({
                    ...block,
                    _children: childrenOf(block.id).map(addChildren),
                });

                return topLevel
                    .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0))
                    .map(addChildren);
            },

            nextSortOrder(parentBlock = null) {
                const siblings = parentBlock ? (parentBlock._children ?? []) : this.pageBlocks;
                if (siblings.length === 0) return 0;
                return Math.max(...siblings.map(b => b.sort_order ?? 0)) + 1;
            },

            // ── Block display helpers ─────────────────────────────────────────
            blockLabel(block, idx) {
                if (!block) return '';
                const cfg  = block.block_config ?? {};
                const bkey = cfg.block_key ?? cfg.name ?? null;
                if (bkey) return humanizeKey(bkey);
                return STRINGS.block_fallback + ' ' + (block.sort_order ?? (idx + 1));
            },

            blockPreview(block) {
                if (!block) return '';
                const t = (block.translations ?? [])[0];
                if (!t?.block_data) return '';
                const vals = Object.values(t.block_data).filter(v => {
                    if (typeof v !== 'string' || !v) return false;
                    if (v.startsWith('http') || v.startsWith('/') || /^\d+$/.test(v)) return false;
                    return true;
                });
                if (vals.length === 0) return '';
                const plain = vals[0].replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                return plain.substring(0, 60) || '';
            },

            blockThumbUrl(block) {
                if (!block) return null;
                const t = (block.translations ?? [])[0];
                if (!t?.block_data) return null;
                const data = t.block_data;
                // Prefer explicit image URLs first so blocks created before the file-id convention
                // still show a useful thumbnail in the wizard.
                const explicitImageEntry = Object.entries(data).find(([k, v]) => {
                    if (typeof v !== 'string' || v.length === 0) return false;
                    if (!v.startsWith('http') && !v.startsWith('/')) return false;
                    return k === 'image_url' || k.endsWith('_image_url') || /(^|_)(photo|picture|thumbnail|thumb|cover|poster|banner)(_url)?$/i.test(k);
                });
                if (explicitImageEntry) {
                    return explicitImageEntry[1];
                }

                // Fallback: pick *_url fields that have a matching *_file_id — that confirms it's an image field,
                // not a link/button URL.
                const entry = Object.entries(data).find(([k, v]) => {
                    if (!k.endsWith('_url')) return false;
                    if (typeof v !== 'string' || v.length === 0) return false;
                    if (!v.startsWith('http') && !v.startsWith('/')) return false;
                    const fileIdKey = k.replace(/_url$/, '_file_id');
                    return fileIdKey in data;
                });
                return entry ? entry[1] : null;
            },

            blockEditTitle() {
                if (this.editMode === 'create') {
                    const typeInfo = this.blockTypeInfo(this.editBlockTypeKey);
                    return typeInfo?.name ?? humanizeKey(this.editBlockTypeKey ?? '');
                }
                return this.blockLabel(this.selectedBlock, 0);
            },

            // Returns enriched field descriptors for the block editor
            blockFields() {
                let bkey;
                if (this.editMode === 'create') {
                    bkey = this.editBlockTypeKey;
                } else {
                    if (!this.selectedBlock) return [];
                    bkey = this.selectedBlock.block_config?.block_key ?? null;
                }

                const schemaFields = bkey ? (this.config?.block_types?.[bkey]?.fields ?? null) : null;

                if (schemaFields && Object.keys(schemaFields).length > 0) {
                    return Object.entries(schemaFields).map(([k, def]) => ({
                        key:      k,
                        label:    def.label ?? humanizeKey(k),
                        required: def.required ?? false,
                        uiType:   schemaTypeToUiType(def.type ?? '', def.accept ?? ''),
                        options:  def.options ?? [],
                    }));
                }

                if (this.editMode === 'create') return [];

                // Fallback: derive from existing block_data keys
                const visibleKeys = Object.keys(this.blockEditData)
                    .filter(k => !k.endsWith('_file_id') && !k.endsWith('_url'));
                return visibleKeys.map(k => ({
                    key:      k,
                    label:    humanizeKey(k),
                    required: false,
                    uiType:   'textarea',
                    options:  [],
                }));
            },

            syncRichTextFields() {
                const fields = document.querySelectorAll('[data-wizard-richtext-field]');
                fields.forEach((node) => {
                    const key = node?.dataset?.fieldKey;
                    if (!key) return;
                    const input = node.querySelector('input[type="hidden"]');
                    if (!input) return;
                    this.blockEditData[key] = input.value ?? '';
                });
            },

            ownerTypeLabel() {
                return this.selectedOwnerType === 'entry'
                    ? '<?= esc(lang('Pages.owner_label_entry'), 'js') ?>'
                    : '<?= esc(lang('Pages.owner_label_page'), 'js') ?>';
            },

            blocksDescription() {
                return this.selectedOwnerType === 'entry'
                    ? '<?= esc(lang('Wizard.blocks_description_entry'), 'js') ?>'
                    : '<?= esc(lang('Wizard.blocks_description_page'), 'js') ?>';
            },

            emptyBlocksText() {
                return this.selectedOwnerType === 'entry'
                    ? '<?= esc(lang('Wizard.no_blocks_entry'), 'js') ?>'
                    : '<?= esc(lang('Wizard.no_blocks_page'), 'js') ?>';
            },

            ownerPreviewUrl() {
                if (this.selectedOwnerType !== 'page' || !PUBLIC_SITE_URL || !this.selectedPage?.translations?.length) return '';
                const slug = this.selectedPage.translations.find(t => t?.slug)?.slug || '';
                return slug ? `${PUBLIC_SITE_URL}/${String(slug).replace(/^\//, '')}` : '';
            },

            ownerEditUrl() {
                if (!this.selectedPage?.id) return '';
                const segment = this.selectedOwnerType === 'entry' ? 'entries' : 'pages';
                return `${ADMIN_CMS_BASE}/${segment}/${this.selectedPage.id}/edit`;
            },

            ownerBlocksUrl(suffix = '') {
                if (!this.selectedPage?.id) return '';
                const segment = this.selectedOwnerType === 'entry' ? 'entries' : 'pages';
                return `${WIZARD_BASE}/${segment}/${this.selectedPage.id}/blocks${suffix}`;
            },

            setBlockOwner(owner, ownerType, backScreen = 'page-select') {
                this.selectedPage = owner;
                this.selectedOwnerType = ownerType;
                this.blocksBackScreen = backScreen;
                this.pageBlocks = [];
                this.pageBlocksError = '';
            },

            async openOwnerBlocks(owner, ownerType = 'page', backScreen = 'page-select') {
                this.setBlockOwner(owner, ownerType, backScreen);
                this.pageBlocksLoading = true;
                this.screen = 'page-blocks';
                await this.refreshPageBlocks();
            },

            // ── Lifecycle ─────────────────────────────────────────────────────
            async init() {
                this.screen = 'loading';
                this.draft = this.loadDraft();

                try {
                    const res = await adminFetch(WIZARD_BASE + '/config');
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const data = await res.json();
                    this.config = data;
                    this.defaultLangId = this.resolveDefaultLanguageId();
                    this.screen = 'home';
                } catch (e) {
                    this.errorMsg = STRINGS.error_load;
                    this.screen = 'error';
                }
            },

            resolveDefaultLanguageId() {
                const languages = Array.isArray(this.config?.languages) ? this.config.languages : [];
                const defaultLanguage = languages.find((language) => language?.is_default);
                return Number(defaultLanguage?.id || languages[0]?.id || 1);
            },

            goHome() {
                this.errorMsg = '';
                this.screen = 'home';
            },

            // ── Navigation ────────────────────────────────────────────────────
            goAddContent() {
                this.screen = 'collection-select';
            },

            applyIntentDefaults(option) {
                this.selectedIntent = option;
                const suggestions = option?.suggestions ?? {};
                const name = option?.label?.trim() || 'Colección';
                const key = option?.key?.trim() || slugify(name);

                this.form.name = name;
                this.form.collection_key = key;
                this.form.url_prefix = key;
                this.form.requires_approval = !!suggestions.requires_approval;
                this.form.enables_categories = suggestions.enables_categories !== undefined ? !!suggestions.enables_categories : true;
                this.form.enables_tags = suggestions.enables_tags !== undefined ? !!suggestions.enables_tags : true;
                this.form.default_sitemap_priority = suggestions.default_sitemap_priority ?? 0.5;
                this.form.default_changefreq = suggestions.default_changefreq ?? 'weekly';

                this.translation.name = name;
                this.translation.slug = slugify(name);
            },

            selectCollection(col) {
                this.selectedCollection = col;
                this.currentStep = 0;
                this.formData = { status: 'published' };
                this.entryReviewLoading = false;
                this.entryReviewError = '';
                this.entryTranslationRows = [];
                for (const step of this.steps) {
                    for (const field of step.fields) {
                        if (field.default !== undefined && field.default !== null) {
                            this.formData[field.key] = field.default;
                        }
                    }
                }
                this.publishError = '';
                this.screen = 'steps';
            },

            prevStep() {
                if (this.currentStep > 0) {
                    this.currentStep--;
                } else {
                    this.screen = 'collection-select';
                }
            },

            async nextStep() {
                if (!this.canAdvance()) return;
                if (this.currentStep < this.steps.length - 1) {
                    this.currentStep++;
                    this.saveDraft();
                } else {
                    this.screen = 'confirm';
                    await this.prepareEntryReview();
                }
            },

            canAdvance() {
                const step = this.currentStepSchema;
                if (!step) return false;
                return step.fields
                    .filter(f => f.required)
                    .every(f => {
                        if (f.type === 'image') return Boolean(this.formData[f.key + '_id']);
                        const val = this.formData[f.key];
                        return val !== undefined && val !== null && String(val).trim() !== '';
                    });
            },

            // ── Image upload (entry wizard) ────────────────────────────────────
            async uploadImage(field, file) {
                if (!file) return;
                this.uploading = true;
                this.uploadError = '';
                try {
                    const fd = new FormData();
                    fd.append('file', file);
                    const res  = await adminFetch(WIZARD_BASE + '/upload', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data?.message ?? STRINGS.error_upload_failed);
                    const fileData = data?.file ?? data;
                    this.formData[field.key + '_id']  = fileData?.id ?? null;
                    this.formData[field.key + '_url'] = fileData?.url ?? fileData?.variants?.md?.url ?? null;
                } catch (e) {
                    this.uploadError = STRINGS.error_upload;
                } finally {
                    this.uploading = false;
                }
            },

            // ── Image upload (block editor) ────────────────────────────────────
            async uploadBlockImage(field, file) {
                if (!file) return;
                this.uploading = true;
                this.uploadError = '';
                try {
                    const fd = new FormData();
                    fd.append('file', file);
                    const res  = await adminFetch(WIZARD_BASE + '/upload', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data?.message ?? STRINGS.error_upload_failed);
                    const fileData = data?.file ?? data;
                    this.blockEditData[field.key + '_file_id'] = fileData?.id ?? null;
                    this.blockEditData[field.key + '_url']     = fileData?.url ?? fileData?.variants?.md?.url ?? null;
                } catch (e) {
                    this.uploadError = STRINGS.error_upload;
                } finally {
                    this.uploading = false;
                }
            },

            // ── Publish (entry wizard) ─────────────────────────────────────────
            async publish() {
                this.publishing = true;
                this.publishError = '';
                try {
                    if (this.entryReviewLoading) {
                        throw new Error(<?= json_encode(lang('Wizard.wizard_content_confirm_translations_loading')) ?>);
                    }
                    if (this.entryTranslationRows.length === 0) {
                        await this.prepareEntryReview();
                    }
                    const payload = this.buildEntryPayload();
                    const clientErrors = this.validateEntryPayload(payload);
                    if (clientErrors.length > 0) {
                        throw new Error(clientErrors.join(' '));
                    }
                    const res  = await adminFetch(WIZARD_BASE + '/publish', { method: 'POST', body: JSON.stringify(payload) });
                    const raw = await res.text();
                    let data = {};
                    try {
                        data = raw ? JSON.parse(raw) : {};
                    } catch (_) {
                        data = { message: raw || STRINGS.error_publish };
                    }
                    if (!res.ok) {
                        const msg = data?.message
                            ?? data?.messages?.[0]
                            ?? (raw ? raw : `${STRINGS.error_publish} (HTTP ${res.status})`);
                        const fieldErrors = data?.errors && typeof data.errors === 'object'
                            ? Object.entries(data.errors).map(([key, value]) => `${key}: ${Array.isArray(value) ? value.join(', ') : String(value)}`)
                            : [];

                        throw new Error([msg, ...fieldErrors].filter(Boolean).join(' '));
                    }
                    this.publishedEntry = data;
                    this.clearDraft();
                    this.screen = 'success';
                } catch (e) {
                    this.publishError = e.message ?? STRINGS.error_publish;
                } finally {
                    this.publishing = false;
                }
            },

            buildEntryPayload() {
                const extra = {};
                for (const step of this.steps) {
                    for (const field of step.fields) {
                        if (NATIVE_KEYS.includes(field.key)) continue;
                        if (field.type === 'image') {
                            const fileId = this.formData[field.key + '_id'] ?? null;
                            if (fileId !== null) {
                                extra[field.key + '_file_id'] = fileId;
                                extra[field.key + '_url']     = this.formData[field.key + '_url'] ?? null;
                            }
                        } else if (this.formData[field.key] !== undefined) {
                            extra[field.key] = this.formData[field.key];
                        }
                    }
                }

                const payload = {
                    collection_id:   this.selectedCollection.id,
                    title:           this.formData.title ?? this.selectedCollection?.name ?? STRINGS.content_fallback,
                    status:          this.formData.status ?? 'published',
                    workflow_status:  this.formData.status ?? 'published',
                    sort_order: 0, view_count: 0, is_featured: false, is_in_sitemap: true,
                    translations: [],
                };

                if (Object.keys(extra).length > 0) payload.wizard_extra = extra;

                payload.translations = this.buildEntryTranslations();

                return payload;
            },

            buildEntryTranslations() {
                const source = this.entryReviewSourceContent();
                const defaultLanguageId = this.defaultLanguageId || this.resolveDefaultLanguageId();
                const baseSlug = this.entryBaseSlug() + '-' + Date.now();
                const translations = [];

                if (defaultLanguageId > 0) {
                    translations.push({
                        language_id: defaultLanguageId,
                        slug: truncateText(baseSlug, 150),
                        title: truncateText(source.title, 255),
                        excerpt: truncateText(source.excerpt, 500),
                        meta_title: truncateText(source.meta_title, 255),
                        meta_description: truncateText(source.meta_description, 500),
                        featured_file_id: source.featured_file_id,
                        featured_image_url: source.featured_image_url,
                    });
                }

                if (Array.isArray(this.entryTranslationRows) && this.entryTranslationRows.length > 0) {
                    this.entryTranslationRows
                        .filter((row) => !row?.is_base && Number(row?.language_id || 0) > 0)
                        .forEach((row) => {
                            const title = truncateText(row.title || source.title, 255);
                            const slugBase = slugify(title || source.title || 'entry');
                            translations.push({
                                language_id: Number(row.language_id || 0),
                                slug: truncateText(String(row.slug || '').trim() || `${slugBase}-${String(row.code || '').toLowerCase()}`, 150),
                                title,
                                excerpt: truncateText(row.excerpt, 500),
                                meta_title: truncateText(row.meta_title, 255),
                                meta_description: truncateText(row.meta_description, 500),
                                featured_file_id: source.featured_file_id,
                                featured_image_url: source.featured_image_url,
                            });
                        });
                }

                if (translations.length === 0) {
                    translations.push({
                        language_id: defaultLanguageId || 1,
                        slug: truncateText(baseSlug, 150),
                        title: truncateText(source.title, 255),
                        excerpt: truncateText(source.excerpt, 500),
                        meta_title: truncateText(source.meta_title, 255),
                        meta_description: truncateText(source.meta_description, 500),
                        featured_file_id: source.featured_file_id,
                        featured_image_url: source.featured_image_url,
                    });
                }

                return translations;
            },

            validateEntryPayload(payload) {
                const errors = [];

                if (!payload || typeof payload !== 'object') {
                    return ['<?= esc(lang('Wizard.error_publish'), 'js') ?>'];
                }

                if (!payload.collection_id || Number(payload.collection_id) <= 0) {
                    errors.push('<?= esc(lang('Entries.collection_not_exists'), 'js') ?>');
                }

                if (String(payload.title || '').trim() === '') {
                    errors.push('<?= esc(lang('Wizard.default_field_title'), 'js') ?>');
                }

                const translations = Array.isArray(payload.translations) ? payload.translations : [];
                translations.forEach((translation) => {
                    const languageId = Number(translation?.language_id || 0);
                    const prefix = languageId > 0 ? `translation[${languageId}]` : 'translation';
                    const title = String(translation?.title || '').trim();
                    const slug = String(translation?.slug || '').trim();

                    if (title === '') {
                        errors.push(`${prefix}.title required`);
                    }
                    if (title.length > 255) {
                        errors.push(`${prefix}.title max_length 255`);
                    }
                    if (slug === '') {
                        errors.push(`${prefix}.slug required`);
                    }
                    if (slug.length > 150) {
                        errors.push(`${prefix}.slug max_length 150`);
                    }
                });

                return errors;
            },

            buildPublishedEntryPreview(payload, response) {
                const translations = Array.isArray(payload?.translations) ? payload.translations : [];
                const defaultLangId = this.defaultLangId || this.resolveDefaultLanguageId();
                const defaultTranslation = translations.find(t => t?.language_id === defaultLangId)
                    ?? translations[0]
                    ?? null;

                return {
                    ...(response ?? {}),
                    title: response?.title ?? this.formData.title ?? this.selectedCollection?.name ?? STRINGS.content_fallback,
                    slug: response?.slug ?? defaultTranslation?.slug ?? '',
                    translations: response?.translations ?? translations,
                };
            },

            restart() {
                this.clearDraft();
                this.selectedCollection = null;
                this.formData = {};
                this.currentStep = 0;
                this.publishedEntry = null;
                this.publishError = '';
                this.entryReviewLoading = false;
                this.entryReviewError = '';
                this.entryTranslationRows = [];
                this.selectedPage = null;
                this.selectedOwnerType = 'page';
                this.pageBlocks = [];
                this.pageBlocksError = '';
                this.pageBlocksLoading = false;
                this.blocksBackScreen = 'page-select';
                this.screen = 'home';
            },

            // ── WIZ-007: Edit page ────────────────────────────────────────────
            goEditPage() {
                if (!this.config) return;
                if ((this.config.pages ?? []).length === 0) {
                    this.errorMsg = STRINGS.error_no_pages;
                    this.screen = 'error';
                    return;
                }
                this.screen = 'page-select';
            },

            async selectPage(page) {
                await this.openOwnerBlocks(page, 'page', 'page-select');
            },

            async selectPublishedEntry(entry) {
                await this.openOwnerBlocks(entry, 'entry', 'success');
            },

            async refreshPageBlocks() {
                if (!this.selectedPage) return;
                this.pageBlocksLoading = true;
                this.pageBlocksError = '';
                try {
                    const res  = await adminFetch(this.ownerBlocksUrl());
                    const data = await res.json();
                    if (!res.ok) throw new Error(data?.message ?? STRINGS.error_blocks_load);
                    const items = data?.items ?? data?.data ?? (Array.isArray(data) ? data : []);
                    this.pageBlocks = this.buildBlockTree(items);
                } catch (e) {
                    this.pageBlocksError = e.message ?? STRINGS.error_blocks_load;
                } finally {
                    this.pageBlocksLoading = false;
                }
            },

            // ── Block editing ─────────────────────────────────────────────────
            editBlock(block, parentBlock = null) {
                this.selectedBlock  = block;
                this.editMode       = 'edit';
                this.editParentBlock = parentBlock;
                this.editBlockTypeKey = null;
                this.blockSaveError = '';
                this.uploadError    = '';
                const t = (block.translations ?? [])[0];
                this.blockEditData = t?.block_data ? { ...t.block_data } : {};
                this.screen = 'block-edit';
            },

            // ── Block catalog ─────────────────────────────────────────────────
            openBlockCatalog(parentBlock = null) {
                this.catalogContext = parentBlock;
                const available = this.availableBlockTypes();

                if (available.length === 0) return;

                // If only one option (e.g. hero_slider allows only slide_banner) — skip catalog
                if (available.length === 1) {
                    this.selectBlockType(available[0]);
                    return;
                }

                this.screen = 'block-catalog';
            },

            selectBlockType(blockType) {
                this.editMode        = 'create';
                this.editBlockTypeKey = blockType.key;
                this.editParentBlock = this.catalogContext;
                this.selectedBlock   = null;
                this.blockEditData   = {};
                this.blockSaveError  = '';
                this.uploadError     = '';
                this.screen = 'block-edit';
            },

            // ── Block CRUD ────────────────────────────────────────────────────
            async saveBlock() {
                this.syncRichTextFields();
                if (this.editMode === 'create') {
                    await this._createBlock();
                    return;
                }
                await this._updateBlock();
            },

            async _updateBlock() {
                if (!this.selectedBlock || !this.selectedPage) return;
                this.blockSaving    = true;
                this.blockSaveError = '';
                try {
                    const t = (this.selectedBlock.translations ?? [])[0] ?? {};
                    const blockData = this.normalizeBlockPayload(this.blockEditData);
                    // is_active ensures data is never empty after the domain extracts translations,
                    // which would otherwise trigger BaseCrudService's noFieldsToUpdate check.
                    const payload = {
                        is_active: true,
                        translations: [{
                            language_id:  t.language_id ?? (this.defaultLangId || this.resolveDefaultLanguageId()),
                            block_data:   blockData,
                            is_published: t.is_published ?? true,
                        }],
                    };
                    const res  = await adminFetch(
                        this.ownerBlocksUrl(`/${this.selectedBlock.id}`),
                        { method: 'POST', body: JSON.stringify(payload) }
                    );
                    const data = await res.json();
                    if (!res.ok) throw new Error(data?.message ?? STRINGS.error_block_save);
                    this.screen = 'block-saved';
                } catch (e) {
                    this.blockSaveError = e.message ?? STRINGS.error_block_save;
                } finally {
                    this.blockSaving = false;
                }
            },

            async _createBlock() {
                if (!this.editBlockTypeKey || !this.selectedPage) return;
                this.blockSaving    = true;
                this.blockSaveError = '';
                try {
                    const typeInfo = this.blockTypeInfo(this.editBlockTypeKey);
                    if (!typeInfo?.id) throw new Error(STRINGS.error_block_type_missing);
                    const blockData = this.normalizeBlockPayload(this.blockEditData);

                    const payload = {
                        block_id:           typeInfo.id,
                        owner_type:         this.selectedOwnerType,
                        owner_id:           this.selectedPage.id,
                        parent_instance_id: this.editParentBlock?.id ?? null,
                        sort_order:         this.nextSortOrder(this.editParentBlock),
                        is_active:          true,
                        block_config:       {},
                        translations: [{
                            language_id:  this.defaultLangId || this.resolveDefaultLanguageId(),
                            block_data:   blockData,
                            is_published: true,
                        }],
                    };

                    const res  = await adminFetch(
                        this.ownerBlocksUrl(),
                        { method: 'POST', body: JSON.stringify(payload) }
                    );
                    const data = await res.json();
                    if (!res.ok) throw new Error(data?.message ?? STRINGS.error_block_save);

                    // Reset create state and refresh tree
                    this.editMode        = 'edit';
                    this.editBlockTypeKey = null;
                    this.editParentBlock = null;
                    await this.refreshPageBlocks();
                    this.screen = 'block-saved';
                } catch (e) {
                    this.blockSaveError = e.message ?? STRINGS.error_block_save;
                } finally {
                    this.blockSaving = false;
                }
            },

            normalizeBlockPayload(blockData) {
                const normalized = { ...(blockData ?? {}) };
                for (const [key, value] of Object.entries(normalized)) {
                    if (!key.endsWith('_url')) continue;
                    if (typeof value !== 'string' || value.trim() === '') continue;

                    const fileIdKey = key.replace(/_url$/, '_file_id');
                    if (normalized[fileIdKey] === undefined || normalized[fileIdKey] === null || normalized[fileIdKey] === '') {
                        // Preserve image URLs even when the picker did not provide a file id.
                        normalized[fileIdKey] = normalized[fileIdKey] ?? null;
                    }
                }
                return normalized;
            },

            confirmDeleteBlock(block) {
                this.deleteBlockTarget = block;
            },

            async deleteBlock() {
                const block = this.deleteBlockTarget;
                if (!block || !this.selectedPage) return;
                this.deleteBlockTarget = null;
                try {
                    const res = await adminFetch(
                        this.ownerBlocksUrl(`/${block.id}/delete`),
                        { method: 'POST' }
                    );
                    if (!res.ok) {
                        const data = await res.json().catch(() => ({}));
                        this.pageBlocksError = data?.message ?? STRINGS.error_block_delete;
                        return;
                    }
                    await this.refreshPageBlocks();
                } catch (_) {
                    this.pageBlocksError = STRINGS.error_block_delete;
                }
            },

            async moveBlock(block, direction, parentBlock = null) {
                const siblings = parentBlock ? (parentBlock._children ?? []) : this.pageBlocks;
                const idx      = siblings.findIndex(b => b.id === block.id);
                if (idx < 0) return;
                const targetIdx = idx + direction;
                if (targetIdx < 0 || targetIdx >= siblings.length) return;

                const targetBlock = siblings[targetIdx];
                const orderA = targetBlock.sort_order ?? targetIdx;
                const orderB = block.sort_order ?? idx;

                try {
                    await Promise.all([
                        adminFetch(
                            this.ownerBlocksUrl(`/${block.id}`),
                            { method: 'POST', body: JSON.stringify({ sort_order: orderA }) }
                        ),
                        adminFetch(
                            this.ownerBlocksUrl(`/${targetBlock.id}`),
                            { method: 'POST', body: JSON.stringify({ sort_order: orderB }) }
                        ),
                    ]);
                    await this.refreshPageBlocks();
                } catch (_) { /* silent — tree not refreshed, next reload will fix */ }
            },

            // ── WIZ-008: Edit menu ────────────────────────────────────────────
            goEditMenu() {
                if (!this.config) return;
                if ((this.config.menus ?? []).length === 0) {
                    this.errorMsg = STRINGS.error_no_menus;
                    this.screen = 'error';
                    return;
                }
                if (this.config.menus.length === 1) {
                    this.selectMenu(this.config.menus[0]);
                    return;
                }
                this.screen = 'menu-select';
            },

            async selectMenu(menu) {
                this.selectedMenu = menu;
                this.menuItems = [];
                this.menuItemsError = '';
                this.menuItemsLoading = true;
                this.screen = 'menu-items';
                try {
                    const res  = await adminFetch(`${WIZARD_BASE}/menus/${menu.id}/items`);
                    const data = await res.json();
                    if (!res.ok) throw new Error(data?.message ?? STRINGS.error_items_load);
                    const items = data?.items ?? data?.data ?? (Array.isArray(data) ? data : []);
                    this.menuItems = items.map(item => ({
                        ...item,
                        _label: this.itemLabel(item),
                        _url:   this.itemUrl(item),
                    }));
                } catch (e) {
                    this.menuItemsError = e.message ?? STRINGS.error_items_load;
                } finally {
                    this.menuItemsLoading = false;
                }
            },

            itemLabel(item) {
                if (!item) return '';
                const t = (item.translations ?? [])[0];
                return t?.label ?? t?.title ?? '';
            },

            itemUrl(item) {
                if (!item) return '';
                const t = (item.translations ?? [])[0];
                return t?.custom_url ?? t?.url ?? '';
            },

            moveItem(idx, delta) {
                const target = idx + delta;
                if (target < 0 || target >= this.menuItems.length) return;
                const temp = this.menuItems[idx];
                this.menuItems[idx] = this.menuItems[target];
                this.menuItems[target] = temp;
            },

            async patchItem(item) {
                try {
                    const t   = (item.translations ?? [])[0] ?? {};
                    const res = await adminFetch(
                        `${WIZARD_BASE}/menus/items/${item.id}`,
                        { method: 'POST', body: JSON.stringify({
                            translations: [{ language_id: t.language_id ?? (this.defaultLangId || this.resolveDefaultLanguageId()), label: item._label, custom_url: item._url }],
                        })}
                    );
                    if (!res.ok) {
                        const data = await res.json().catch(() => ({}));
                        this.menuSaveError = data?.message ?? STRINGS.error_item_save;
                    }
                } catch (_) { /* network error — non-blocking */ }
            },

            async saveMenuOrder() {
                this.menuItemsSaving = true;
                this.menuSaveError   = '';
                try {
                    const updates = this.menuItems.map((item, idx) =>
                        adminFetch(
                            `${WIZARD_BASE}/menus/items/${item.id}`,
                            { method: 'POST', body: JSON.stringify({ sort_order: idx }) }
                        )
                    );
                    await Promise.all(updates);
                } catch (e) {
                    this.menuSaveError = STRINGS.error_item_save;
                } finally {
                    this.menuItemsSaving = false;
                }
            },

            async addItem() {
                if (!this.newItemLabel || !this.selectedMenu) return;
                this.menuItemsSaving = true;
                try {
                    const res  = await adminFetch(
                        `${WIZARD_BASE}/menus/${this.selectedMenu.id}/items`,
                        { method: 'POST', body: JSON.stringify({
                            link_type: 'custom_url', link_target: '_self',
                            sort_order: this.menuItems.length, is_active: true,
                            translations: [{ language_id: this.defaultLangId || this.resolveDefaultLanguageId(), label: this.newItemLabel, custom_url: this.newItemUrl || '#' }],
                        })}
                    );
                    const data = await res.json();
                    if (!res.ok) throw new Error(data?.message ?? STRINGS.error_item_save);
                    const newItem = data?.data ?? data;
                    this.menuItems.push({ ...newItem, _label: this.newItemLabel, _url: this.newItemUrl || '#' });
                    this.newItemLabel = '';
                    this.newItemUrl   = '';
                } catch (e) {
                    this.menuSaveError = e.message ?? STRINGS.error_item_save;
                } finally {
                    this.menuItemsSaving = false;
                }
            },

            confirmDeleteItem(item) {
                this.deleteItemTarget = item;
            },

            async deleteItem() {
                if (!this.deleteItemTarget) return;
                const item = this.deleteItemTarget;
                this.deleteItemTarget = null;
                try {
                    await adminFetch(`${WIZARD_BASE}/menus/items/${item.id}/delete`, { method: 'POST' });
                    this.menuItems = this.menuItems.filter(i => i.id !== item.id);
                } catch (_) {
                    this.menuSaveError = STRINGS.error_item_delete;
                }
            },

            // ── Draft persistence (localStorage) ─────────────────────────────
            saveDraft() {
                try {
                    localStorage.setItem('cms_wizard_draft', JSON.stringify({
                        collectionId: this.selectedCollection?.id,
                        step: this.currentStep,
                        formData: this.formData,
                        savedAt: new Date().toISOString(),
                    }));
                } catch (_) {}
            },

            loadDraft() {
                try {
                    const raw = localStorage.getItem('cms_wizard_draft');
                    return raw ? JSON.parse(raw) : null;
                } catch (_) { return null; }
            },

            clearDraft()   { try { localStorage.removeItem('cms_wizard_draft'); } catch (_) {} this.draft = null; },
            discardDraft() { this.clearDraft(); },

            resumeDraft() {
                if (!this.draft || !this.config) return;
                const col = (this.config.collections ?? []).find(c => c.id === this.draft.collectionId);
                if (!col) { this.discardDraft(); return; }
                this.selectedCollection = col;
                this.formData  = this.draft.formData ?? {};
                this.currentStep = this.draft.step ?? 0;
                this.draft = null;
                this.screen = 'steps';
            },

            // ── Expose utility for partials ───────────────────────────────────
            humanizeKey,
        };
    };
}());
</script>
