# TASKS — ci4-website-builder-admin

> Fuente de verdad para trabajo abierto en este repositorio.
> Los entregables cerrados están en [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md).
> Seguimiento global: [`../TASKS.md`](../TASKS.md).
> Tracker depurado el 2026-07-21; no se conservan notas de conversación ni bitácoras de participantes.

## 🔴 En progreso

### Backport de mejoras de Teatro Museo (parte Admin)

> Plan completo (contexto, decisiones de alcance, todas las fases, todos los repos):
> [`../docs/plans/2026-08-24-plan-backport-teatromuseo.md`](../docs/plans/2026-08-24-plan-backport-teatromuseo.md).
> Tracker cross-repo: [`../TASKS.md`](../TASKS.md).

- [x] **BACKPORT-00-admin — Fase 0:** `SecondaryApiClient` (fix real: `Domain/Bff/WebApiClient`
      refrescaban token contra su propio host en vez del Hub), `ApiClientInterface` +
      `Config/Services.php` actualizados, fix de guard en `filter_panel.php`. Código y tests
      (`tests/unit/Libraries/SecondaryApiClientTest.php`) verificados en verde; **pendiente de
      commit**. Ver plan §Fase 0.
- [x] **BACKPORT-CVE-admin:** bump `codeigniter4/framework` → v4.7.4; 2 fixes de tipo en
      `BaseWebController::jsonRequestPayload()` y `FormController::jsonOrPost()` expuestos por el
      bump. Verificado; pendiente de commit. Ver plan §Remediación de CVEs.
- [ ] **BACKPORT-01-admin — Fase 1:** toggle de vista/densidad en remote-table +
      `listResponse.js`, sub-grupos colapsables en `bin/register-sidebar.sh`, componentes de
      formulario nuevos (`password.php`, `server_field_errors.php`, `file_gallery.php`,
      `text_input.php`), fix de doble-fetch por `x-init`, fixes de CSP, aplanado de
      `fieldErrors`/`errors` anidados, `DownloadResponse` en streaming,
      `AdminRouteAuthorizationTest`. Ver plan §Fase 1.

## 🟡 Próximo

### Backport de mejoras de Teatro Museo — fases posteriores (parte Admin)

- [ ] **BACKPORT-03-admin — Fase 3:** componente de UI para reordenamiento atómico por lotes
      (sort-orders), consumiendo el endpoint que agrega Fase 3 en domain. Ver plan §Fase 3.

### TRN-006 — Estados editoriales, permisos y controles de publicación

- [ ] Decidir el modelo de estados por idioma (`in_review`, `approved`, `published`).
- [ ] Definir la relación con `status` de páginas y entradas.
- [ ] Definir roles/permisos de aprobación antes de implementar migraciones, servicios y UI.

## ⚪ Backlog

### ADM-DEP-002 — lint-staged 16 → 17

- [ ] Esperar el baseline Node 22 (`>=22.22.1`), actualizar `lint-staged`, ejecutar `npm audit` y
  verificar el hook `pre-commit`.

## 🏗️ Contratos de arquitectura

- **DTO-First:** Controllers y Services intercambian DTOs con contratos explícitos.
- **Controllers delgados:** delegar lógica de negocio a Services y usar `DomainApiClient`.
- **Permisos:** usar códigos separados por punto, por ejemplo `cms.pages.read`.
- **Componentes compartidos:** reutilizar helpers de traducción, estados, formularios y media.
- **i18n:** mantener paridad en `app/Language/en` y `app/Language/es`.
- **Calidad:** cerrar tareas solo con tests, PHPStan/CS-Fixer, i18n y build aplicables en verde.

## 🔧 Referencias

- Plan editorial: [`../docs/plans/2026-07-20-translation-workbench-plan.es.md`](../docs/plans/2026-07-20-translation-workbench-plan.es.md)
- Tracker global: [`../TASKS.md`](../TASKS.md)
- Histórico: [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md)
