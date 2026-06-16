# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed
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

