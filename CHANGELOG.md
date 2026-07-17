# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Enhanced block preview system with document and multimedia block types** — added preview views for document_download, document_gallery, pdf_viewer, and video_gallery blocks; improved `form_helper.php` normalize_media_reference_value() to handle multiple field name variations (camelCase, snake_case, external_url, file_url); added preview_mode parameter to BlockPreviewController for sample/live preview toggling; enhanced blockPreview.js to manage preview modes transparently
- **Reusable media reference field component** — new `media_reference.php` component and `mediaReferenceField.js` Alpine.js component for unified handling of media selection across blocks; supports both Hub-managed files and external URLs with automatic source detection; includes preview, source toggling, file picker integration, and copy-to-all-languages support; replaces ad-hoc media handling in block forms with standardized, reusable UI
- **Menu item create/edit views** — full CRUD interface for menu items with create and edit forms supporting link configuration, label, and sorting; integrated with MenuApiService for item creation and update workflows
- **Automatic translation for block instances** — block creation now supports automatic translation of block content across all active languages via "Translate All" button and per-file copy-to-languages feature; language context (default language code and ID) passed to the UI for seamless multilingual block management.
- **Exception handling for unavailable services** — `PermissionsSessionRefresher` and `BlockCatalogService` now gracefully handle timeouts and connection errors when Hub or Domain APIs are unavailable; permission refresh and block catalog loading log warnings and continue gracefully instead of crashing, improving admin resilience during infrastructure incidents.
- **Block catalog templates and filtering** — `BlockCatalogService` now provides `templates()` and `selectableForEntries()` methods for retrieving block type templates and filtering selectable blocks (excluding container-only types)
- **Site identity translation panel** — new `cms_settings_build_translation_panel()` helper and `translatable_settings_panel` component for managing multilingual site settings across active languages

### Fixed
- **Improved API error debugging in development** — enhanced `BaseWebController` with `flashDevError()` and `renderDevApiErrorPanel()` methods for standardized error snapshot storage; the dev error panel (`dev_api_error_panel.php`) now displays normalized API failures with exact HTTP status, request body, field errors, and messages; accessible on read-only screens (dashboard, metrics, analytics) without requiring a form submission
- **CMS validation consistency** — all CMS Request DTOs now normalize empty translation rows before validation, preventing spurious validation errors on optional blank language tabs; updated validation messages for consistency across store/update forms
- **Media reference field URL persistence on source switching** — when toggling between hub file and external URL sources, the media reference field now preserves visible URLs as fallbacks instead of clearing them; added `_visibleReferenceFallback()` to maintain visual continuity across source changes, preventing data loss when switching between file library and external URL modes
- **Form translation field indexing** — form submission now uses sequential array indices (`translations[0]`, `translations[1]`, …) instead of language IDs (`translations[language_id]`) to ensure proper form data serialization and backend processing; `FormController` now intelligently resolves `language_id` from either the field name or the nested data, ensuring backward compatibility and correct translation grouping regardless of input structure.
- **Form field validation styling** — all form inputs in create and edit forms now use `input_class()` helper to apply dynamic validation error styling (red border on failed validation), improving visual feedback for users when form submission errors occur; applied to form key, notification email, and all multilingual translation fields.
- **Lucide icon rendering in dynamic form options** — when adding or removing field options in the form builder, icons on dynamically created rows would remain unrendered (raw `<i data-lucide>` markup) until the field was saved. Now properly re-scans DOM for new Lucide markup via `$nextTick()` after option add/remove/edit operations, ensuring icons render immediately. Also improved visual feedback for missing option labels in languages other than the default (displays in amber instead of gray).

### Added
- **Multilingual form field option labels** — enhanced form field builder UI to support per-language option labels for select, checkbox, and radio fields; `formFieldBuilder.js` now resolves option display labels from translation per-language `option_labels` map; improved language key structure for expanded form field options
- **Expanded form field builder UI** — enhanced `formFieldBuilder.js` component with support for new form field types; improved field validation and configuration UI; updated language strings in English and Spanish for new field type labels and help text; form submission detail view now displays expanded field type information

### Changed
- **Block sorter with inline move operations** — enhanced `blockSorter.js` component with `moveBlock()`, `moveUp()`, and `moveDown()` methods for direct block reordering without drag-and-drop; improved block list UI with responsive layout (mobile buttons, desktop drag handle) and better visual hierarchy; refactored component structure with private helper methods for improved maintainability
- **Generic block naming normalization** — consolidated domain-specific block preview views (faq_accordion, contact_form, location_info, logo_showcase, etc.) into unified generic names (accordion, form_embed, contact_info, asset_showcase, etc.); updated block instance controller, preset catalog, and wizard UI to reflect new names; improves consistency and reduces block type proliferation

### Added
- **Unified collection grid block** — consolidated a set of collection-specific grid block types into a single `collection_grid` block with flexible configuration (order_by, order_direction, layout_variant); updated preset catalog and block type previews to use the new unified block
- **Collection management UI with dynamic types** — new collection CRUD interface (create, edit, list, show) supporting dynamic collection types; enhanced `CollectionStoreRequest` with improved validation; added block template editor partial for configuring collection block structures; multilingual UI support with Spanish/English translations
- **Enhanced entry wizard with structure and block templates** — redesigned wizard flow with structure management UI; enhanced `WizardController` and `StructureWizardController` to support collection-aware entry creation; improved entry wizard partial with block template configuration; enriched language keys for wizard workflow
- **CMS preset catalog and block template system** — new `CmsPresetCatalog` for managing preset configurations; enhanced `richTextEditor.js` component for improved content editing; updated JavaScript modules to support dynamic block templates in the wizard interface
- **Menu items reordering interface** — new reorder view for menu items with hierarchical display, drag-and-drop capability, and AJAX persistence; simplified create/edit forms by moving sort_order to dedicated reorder interface
- **Entry reordering — collection filtering** — added dropdown filter to the entry reorder view to filter entries by collection; when a collection is selected, the reorder list displays only entries belonging to that collection; improved portfolio preset labels for better clarity
- **CMS wizard — permission enforcement** — added permission validation to the structure wizard controller (`cms.pages.write`, `cms.menus.write`, `cms.collections.write`) and reorganized CMS view controls to gate edit/delete buttons and the drag-hint prompt within permission checks, ensuring only authorized users can manage blocks and drag operations
- **Structure wizard — multilingual support with automatic translation proposals** — extended the structure wizard with a dedicated language selection step featuring active languages from the hub, automatic translation proposals via MyMemory API, inline editing of translated names and slugs, and language inclusion/exclusion toggles before final collection submission
- **Interactive multi-step structure wizard** — enhanced wizard with Alpine.js-driven step-by-step flow, intent presets (blog, news, portfolio, services, custom), dynamic form validation, and multi-language support for guided collection creation
- **Structure wizard** — new guided interface to create and manage pages, collections, menus, and redirects with modal dialogs and hierarchical organization
- **Default sort on remote tables** — added `defaultSort` configuration to remoteTable component; all data tables now sort by newest records first by default (created_at descending), improving UX by showing the most recently created/updated items at the top
- **Locale in user registration** — automatically include the current application locale in the registration payload to ensure new users inherit the language preference from their signup context
- **Setting connections management UI** — enhanced settings forms with support for `input_type` (e.g. text, textarea, select, json), `options_json` for custom field options, `is_required` and `is_readonly` flags, and `ui_meta` for rich field metadata; added SettingConnectionController integration for managing setting relationships
- **File usage tracking with multi-source API integration** — enhanced FileApiService to aggregate file usages from both Hub and Domain APIs, added `resolveEditUrl()` in FileController to generate contextual edit links for each usage (pages, entries, blocks, users, settings), improving file lifecycle visibility in the admin UI
- **Wizard UI refactor with modular partials and block CRUD** — decomposed wizard view into 8 reusable partial components (block_catalog, block_edit, page_layout, menu_edit, entry_wizard, etc.), added `createBlock()` and `deleteBlock()` controller methods, enhanced block type metadata enrichment from Domain API
- **Entry block support** — extended wizard with `createEntryBlock()`, `entryBlocks()`, and `deleteEntryBlock()` endpoints; refactored block proxy logic via `proxyBlockRequest()` method to unify page and entry block handling with hierarchical tree rendering
- **Automatic form field translation** — new `buildTranslateTargets()` helper and Alpine.js integration for automatic synchronization of field values between language tabs in CMS form editors
- **Forms module** — new admin interface to manage CMS forms with field configuration, multilingual support, and form submission tracking (CMS-012)
- **Analytics module** — new admin dashboard to view page view analytics with customizable period filters (1h, 24h, 7d, 30d), overview charts, and traffic insights; integrates with domain CMS analytics API
- **Form submissions module** — new admin interface to manage form submissions with status tracking (new, read, replied, spam, archived) and submission details view
- **Collections and menus UI enhancements** — improved form fields, validation, and sidebar navigation for better UX
- **File translation module** — new admin interface to manage file translations with localized URLs, MIME types, and metadata
- **Site identity module** — new admin interface for managing site-wide identity settings, branding, and metadata
- **Menu item linking** — support entries and collections as menu link targets alongside pages and custom URLs
- **Translation audit module** — new `TranslationAuditController` with audit dashboard for multi-language translation coverage, completeness tracking, and language statistics
- **Block composition hierarchy enforcement** — validate and filter child blocks based on parent's `allowed_children` schema, with fallback to slide_banner nesting for backward compatibility
- **Translation endpoint** — new `TranslateController` with MyMemory API integration for content translation between languages
- **Slug availability validation** — `checkSlug()` method to validate page slug uniqueness across languages

### Changed
- **Content Security Policy (CSP) headers** — added conservative CSP headers to admin panel for enhanced security; restricts inline scripts, external stylesheets to allowlist, and frames to same-origin only; enables CSRF token injection protection via CSP nonce mechanism
- **Category form and reorder UI** — removed `sort_order` field from category form (order managed via reorder interface); enhanced reorder view to display category names with subtitles showing collection and parent category labels for improved relational context
- **Confirm modal — accepting state with spinner** — `$store.confirm.accept()` now sets `accepting: true` before calling the callback; the "Confirmar" button shows an animated spinner and is disabled while accepting; backdrop and Escape key blocked during action; 5-second safety timeout auto-closes if navigation does not occur (`app.js`, `confirm_modal.php`)
- **Destructive confirmations — 4 views migrated to modal pattern** — `cms/pages/blocks/index.php`, `cms/pages/blocks/children/index.php`, `cms/menus/show.php`, and `admin/universal/index.php` replaced native `onsubmit="return confirm()"` with `@submit.prevent="$store.confirm.show(...)"` using the exact resource name
- **Role-permissions matrix — orientation and change tracking** — added a per-role orientation callout with reactive permissions counter (`selectedCount`) updated on every checkbox change; `isDirty` flag shows "Tienes cambios sin guardar" warning next to the save button; new language keys `role_permissions_hint`, `role_permissions_selected_label`, `unsaved_changes` added in `es`/`en` (`Iam.php`, `App.php`)
- **Block list — expandable block type preview** — each block row in `blocks/index.php` now has a chevron toggle that reveals a larger icon, description, category badge, and block_key for the block type; new language keys `blocks_action_preview`, `blocks_action_collapse` in `Pages.php`
- **Applications index — read-only clarity** — removed duplicate subtitle text; `empty_state` now uses `Iam.applications_empty` and `Iam.applications_managed_server_side` as title/description instead of generic defaults

### Fixed
- **Type safety in CMS controllers and views** — improved null handling and type hinting in `cms` module controllers and views; added proper type declarations to prevent type-related errors and improve PHPStan compliance
- **File deletion protection** — admin now prevents deletion of files with active references (used by pages, blocks, collections); displays user-friendly error messages and bulk operation feedback when deletion is blocked due to active usage
- **Block type preview** — the rich-text block preview now falls back to legacy `body`/`html` payload keys via `block_text_content()` when `content` is empty, matching the domain's normalized rich-text contract
- **Auto-translate** — switched the `TranslateController` provider from MyMemory to Google Translate's unofficial endpoint for more reliable results; translated text now propagates into rich-text editors (`richTextEditor.js`) and keeps file field previews in sync (`translatableFileField.js`); the "Traducir automáticamente" button and its error banner are hidden when there are no translation targets
- **CMS wizard — entry publish payload** — client-side validation now blocks publish when required fields (collection, title, per-language slug/title) are missing or exceed API limits, and long text fields (title, excerpt, meta_title, meta_description, slug) are truncated before submission to prevent 422s from the domain API
- **CMS request validation** — strengthen translation normalization in `CategoryStoreRequest`, `CollectionStoreRequest`, `EntryStoreRequest`, and `PageStoreRequest` with proper trimming and empty-value filtering
- **API call logging in development** — add `ApiCallsCollector` to track method, URL, status, latency, and body for debugging API interactions locally
- **Domain API configuration** — enforce required `DOMAIN_API_BASE_URL` with clear error messages when missing
- **BlockType request validation** — update `schema_definition` validation from `json` to `string` type
- **CMS form views** — Improved language strings and form field labels across all CMS modules (Pages, Entries, Menus, Categories, Collections, Tags, BlockTypes, Languages, Settings)
- **Setting request validation** — Enhanced validation rules for system settings with comprehensive test coverage

### Added
- [CMS-012] Page admin module to manage multi-language hierarchical pages, publishing, and sitemap settings.
- [CMS-013] Menu admin module to manage menus and nested menu items with hierarchical tree views and link options.
- [CMS-014] BlockType admin module to manage block configurations and localized schemas.
- [CMS-015] Collection admin module to manage translatable system collections and metadata settings.
- [CMS-016] Entry admin module to manage entries with multi-language translation support, custom block instances, categories/tags badges, status filtering, and scheduled publishing options.
- [CMS-017] Category admin module to manage hierarchical, translatable categories scoped by collection.
- [CMS-018] Tag admin module to manage global, translatable tags.
- [CMS-020b] Redirect admin module to manage system redirects with CSV import/export capabilities.
- [CMS-019] Language admin module to list, create, edit, delete, and set default languages.
- [CMS-020] Setting admin module to manage translatable system configuration variables.
- Transversal CMS sidebar section header and Lucide icons in navigation.

