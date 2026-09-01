# TASKS — ci4-website-builder-admin

> Fuente de verdad para trabajo abierto en este repositorio.
> Los entregables cerrados están en [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md).
> Seguimiento global: [`../TASKS.md`](../TASKS.md).
> Tracker depurado el 2026-07-21; no se conservan notas de conversación ni bitácoras de participantes.

## 🔴 En progreso

### Backport selectivo desde `teatromuseo-admin`

> Plan detallado (v2, verificado contra el código): [`../docs/plans/2026-08-31-plan-backport-admin-teatromuseo.md`](../docs/plans/2026-08-31-plan-backport-admin-teatromuseo.md).
> Origen fijado a `teatromuseo-admin@44c0e6c`. Un commit por tarea, con sus tests y el cierre
> aquí mismo, siempre vía `/commit-flow`.
>
> **Antes de diseñar cualquier tarea de la suite, leer §1 del plan**: R1–R6 de fronteras de
> módulo, `ViewSecurityRulesTest` (escapado y `json_for_script()`), los dos guardarraíles de
> idioma y la prohibición de CDNs por CSP. Son cuatro puertas que fallan el build y ya costaron
> un rediseño a mitad de tarea el 2026-08-31.
>
> **Orden recomendado por valor y riesgo** (§2): 04 → 02 → 05/03/06 → 01.

- [x] **TM-BLD-04 — Dashboard agregado mediante BFF.** Portar `BffApiClientInterface`,
      `BffApiClient`, `DashboardDataService`, lock y configuración. `getAdminDashboard()` contra
      `GET /api/v1/me/admin-dashboard`. **Validar el BFF del Builder contra el contrato de
      teatromuseo antes de activar el flujo nuevo.** Rutas antiguas como wrappers. Cerrada
      2026-09-01: snapshot server-rendered, caché fresh/stale/unavailable por permisos, single
      flight, fallback visible y contrato BFF validado.

- [x] **TM-BLD-02 — Proyección configurable de listados.** Igual contrato que la suite; además
      adaptar el lector público en `ci4-website-builder-web` y, si hace falta, el resolver del
      Domain. Mantener compatibilidad con `collection_listing` y `collection_grid`. Cerrada
      2026-09-01: DTO normalizado, catálogo cerrado, frontera compartida para ambos bloques y
      regresión contra campos desconocidos.

- [x] **TM-BLD-05 — Observabilidad de invalidación automática de cache.** Productor en
      `ci4-website-builder-web` + `cacheElapsed` en el admin. Cerrada 2026-09-01: fuentes
      automática/manual separadas, estado persistente acotado y UI degradable.

- [x] **TM-BLD-03 — Panel de calidad SEO en edición.** Parcial reutilizable + `PageQuality.php`
      en es/en; panel de `show` usando el mismo parcial. Cerrada 2026-09-01: estados y caída
      del servicio visibles sin fatal.

- [x] **TM-BLD-08 — Vista HTML legible de la configuración del Wizard.** Cerrada 2026-09-01:
      negociación explícita HTML para navegador, JSON por defecto y valores escapados.

- [x] **TM-BLD-01 — Tipos de archivo y densidad visual.** Aquí **sí** se conserva la lectura de
      `filesViewMode` como compatibilidad, sin volver a escribirla. Cerrada 2026-09-01:
      resolución fija MIME/extensión/categoría, densidades sm/md/lg y presentación segura en
      tabla, grid y picker.

- [x] **TM-BLD-06 — Internacionalización de previews de bloques.** Copiar
      `app/Language/{en,es}/BlockPreview.php` y adaptar las vistas. Test que detecte textos
      visibles hardcodeados. No aplica a la suite, que ya los tiene localizados. Cerrada
      2026-09-01: paridad es/en y guardarraíl estático sobre todas las previews.

- [x] **TM-BLD-07 — Desacoplamiento local de `CmsFieldEnums`.** Copiar al Builder, cambiar
      imports, eliminar el mapeo Composer al sibling si no quedan consumidores, y **ejecutar sin
      `ci4-website-builder-domain` disponible**. No aplica a la suite. Cerrada 2026-09-01:
      enums locales, autoload sibling eliminado y guardrail arquitectónico añadido.

- [x] **TM-BLD-09 — `password_label` en inglés dentro del fichero español.** *(nueva, salida de
      la verificación)* `app/Modules/Auth/Language/es/Auth.php:7` define
      `'password_label' => 'Password'`, el mismo defecto que la suite ya corrigió. Conviene
      arrastrar también el guardarraíl `LanguageParityTest`. Cerrada 2026-09-01.


### Remediación de huecos profundos (parte Admin)

> Plan completo: [`../docs/plans/2026-08-25-plan-remediacion-huecos-profundos.md`](../docs/plans/2026-08-25-plan-remediacion-huecos-profundos.md).
> Auditoría origen: [`../docs/audits/2026-08-25-auditoria-profunda-backport-git-history.md`](../docs/audits/2026-08-25-auditoria-profunda-backport-git-history.md).
> Tracker cross-repo: [`../TASKS.md`](../TASKS.md).

- [ ] **GAP-02-admin:** queda 1 ítem pendiente del plan §Fase 2: paginación de la auditoría de
      traducciones. El endpoint Domain actual todavía devuelve el informe completo sin `meta`,
      por lo que Admin no debe enviar `page`/`limit` hasta que exista ese consumidor real; se
      retomará junto con la fase Domain. El resto de los 12 ítems aplicables quedó cerrado en
      commits separados. `components/forms/` fue verificado como consumidor real del wizard y
      no es un duplicado muerto.

## 🟡 Próximo

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
