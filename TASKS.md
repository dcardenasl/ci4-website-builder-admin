# TASKS — ci4-website-builder-admin

> Trabajo abierto de este repositorio. Lo cerrado está en [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md).
> Plan cross-repo: [`../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md`](../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md).

## 🔴 En progreso

*(vacío)*

## 🟡 Próximo

- [ ] **CNV-007-A1 — Shell del canvas.** `EditorController`, layout a sangre, vistas parciales y
      contrato de navegación.
- [ ] **CNV-007-A2 — Módulos JS.** Portar/adaptar canvas y utilidades usando `csrfHeaders()` local;
      no introducir el helper de cookies de la suite.
- [ ] **CNV-007-A3 — Modo simple.** Consumir `roles.ui_mode` del Hub y aplicar deny-by-default a
      `admin/*` con allowlist, después de H1.
- [ ] **GAP-02-admin — Paginación.** Integrar `page`/`limit`/`meta` del Domain, con estados de
      carga/error/vacío y pruebas de límites; no duplicar la regla de paginación.
- [ ] **CNV-007-F6 — Smoke real.** Validar selección, scope, preview, locale, renovación de token,
      CSP/CORS y consola en navegador real.
- [ ] **CNV-007-F9 — Autorización por recurso.** Solo después de la nivelación completa.

## ⚪ Fuera del plan actual

- [ ] **TRN-006** — estados editoriales y controles de publicación.

## 🏗️ Contratos

- Controllers delgados, `DomainApiClient`, DTO-first e i18n `en`/`es`.
- Cada cambio requiere tests, build, guardrails de seguridad y evidencia antes de marcarlo cerrado.

## 🔧 Referencias

- Plan: [`../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md`](../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md)
- Histórico: [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md)
