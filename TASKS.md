# TASKS — ci4-website-builder-admin

> Trabajo abierto de este repositorio. Lo cerrado está en [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md).
> Plan cross-repo: [`../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md`](../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md).

## 🔴 En progreso

*(vacío)*

## 🟡 Próximo

*(vacío; la autorización por recurso es Domain-owned y no se duplica en Admin)*

## ✅ Cerrado con evidencia

- **CNV-007-A1/A2 — Canvas Admin.** Commits `7ca7dc5` y `97eabe9`; shell, layout, módulos JS,
  bridge y adaptador CSRF local con PHPStan/PHPUnit/Vitest/build verdes.
- **CNV-007-A3 — Modo simple.** Commit `28d32ab`; `UiMode`, filtro server-side deny-by-default,
  sidebar reducido, contrato de roles y regresiones sin bypass de permisos.
- **GAP-02-admin — Paginación.** Commit `ee30cfb`; consumidor `page/limit/meta`, DataTables
  envelope, total estable y tests de filtros/página/límite.
- **CNV-007-F6 — Smoke real.** Editor visual verificado en navegador: preview firmado ES/EN,
  selección de bloque raíz e hijo, renovación de token, CORS/CSP y consola limpia en carga fresca.
- **CNV-007-F9 — Reconciliación de alcance.** Admin consume el filtrado/404 seguro de Domain y no
  mantiene una ACL paralela; ocultar UI no sustituye la autorización del servicio. Evidencia
  Domain: `729aa89`.

## ⚪ Fuera del plan actual

- [ ] **TRN-006** — estados editoriales y controles de publicación.

## 🏗️ Contratos

- Controllers delgados, `DomainApiClient`, DTO-first e i18n `en`/`es`.
- Cada cambio requiere tests, build, guardrails de seguridad y evidencia antes de marcarlo cerrado.

## 🔧 Referencias

- Plan: [`../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md`](../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md)
- Histórico: [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md)
