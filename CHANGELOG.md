# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **File translation module** — new admin interface to manage file translations with localized URLs, MIME types, and metadata
- **Site identity module** — new admin interface for managing site-wide identity settings, branding, and metadata
- **Menu item linking** — support entries and collections as menu link targets alongside pages and custom URLs
- **Translation audit module** — new `TranslationAuditController` with audit dashboard for multi-language translation coverage, completeness tracking, and language statistics
- **Block composition hierarchy enforcement** — validate and filter child blocks based on parent's `allowed_children` schema, with fallback to slide_banner nesting for backward compatibility
- **Translation endpoint** — new `TranslateController` with MyMemory API integration for content translation between languages
- **Slug availability validation** — `checkSlug()` method to validate page slug uniqueness across languages

### Changed
- **Block UI and confirm modal enhancements** — improve block list item layout, enhance confirm modal with better state tracking and safety timeout, add "unsaved changes" warning notification

### Fixed
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

