# TASKS — ci4-website-builder-admin

> Trabajo abierto de este repositorio. Lo cerrado está en [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md).
> Plan cross-repo: [`../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md`](../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md).

## 🔴 En progreso

*(vacío)*

## 🟡 Próximo

- [ ] **CNV-007-F9 — Autorización por recurso.** Solo después de la nivelación completa.

## ✅ Cerrado con evidencia

- **CNV-007-A1/A2 — Canvas Admin.** Commits `7ca7dc5` y `97eabe9`; shell, layout, módulos JS,
  bridge y adaptador CSRF local con PHPStan/PHPUnit/Vitest/build verdes.
- **CNV-007-A3 — Modo simple.** Commit `28d32ab`; `UiMode`, filtro server-side deny-by-default,
  sidebar reducido, contrato de roles y regresiones sin bypass de permisos.
- **GAP-02-admin — Paginación.** Commit `ee30cfb`; consumidor `page/limit/meta`, DataTables
  envelope, total estable y tests de filtros/página/límite.
- **CNV-007-F6 — Smoke real.** Editor visual verificado en navegador: preview firmado ES/EN,
  selección de bloque raíz e hijo, renovación de token, CORS/CSP y consola limpia en carga fresca.

## ⚪ Fuera del plan actual

- [ ] **TRN-006** — estados editoriales y controles de publicación.

## 🏗️ Contratos

- Controllers delgados, `DomainApiClient`, DTO-first e i18n `en`/`es`.
- Cada cambio requiere tests, build, guardrails de seguridad y evidencia antes de marcarlo cerrado.

## 🔧 Referencias

- Plan: [`../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md`](../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md)
- Histórico: [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md)
