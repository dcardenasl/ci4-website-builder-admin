# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
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
- **Confirm modal — accepting state with spinner** — `$store.confirm.accept()` now sets `accepting: true` before calling the callback; the "Confirmar" button shows an animated spinner and is disabled while accepting; backdrop and Escape key blocked during action; 5-second safety timeout auto-closes if navigation does not occur (`app.js`, `confirm_modal.php`)
- **Destructive confirmations — 4 views migrated to modal pattern** — `cms/pages/blocks/index.php`, `cms/pages/blocks/children/index.php`, `cms/menus/show.php`, and `admin/universal/index.php` replaced native `onsubmit="return confirm()"` with `@submit.prevent="$store.confirm.show(...)"` using the exact resource name
- **Role-permissions matrix — orientation and change tracking** — added a per-role orientation callout with reactive permissions counter (`selectedCount`) updated on every checkbox change; `isDirty` flag shows "Tienes cambios sin guardar" warning next to the save button; new language keys `role_permissions_hint`, `role_permissions_selected_label`, `unsaved_changes` added in `es`/`en` (`Iam.php`, `App.php`)
- **Block list — expandable block type preview** — each block row in `blocks/index.php` now has a chevron toggle that reveals a larger icon, description, category badge, and block_key for the block type; new language keys `blocks_action_preview`, `blocks_action_collapse` in `Pages.php`
- **Applications index — read-only clarity** — removed duplicate subtitle text; `empty_state` now uses `Iam.applications_empty` and `Iam.applications_managed_server_side` as title/description instead of generic defaults

### Fixed
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

