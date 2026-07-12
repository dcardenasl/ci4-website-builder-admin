# TASKS — ci4-admin-starter

> Fuente de verdad para trabajo en este repo.
> Historial de completadas: ver `TASKS_ARCHIVE.md`.
> Cross-repo: ver `../TASKS.md`.
> Plan detallado CMS admin: ver [`../docs/cms_integration_plan.md`](../docs/cms_integration_plan.md).
> Última actualización: 2026-07-12 (refactor profundo del Wizard CMS completado — ver DEEP-WIZ-01..05 en Completadas)

---

## 🔴 Bloqueante para v2.0.0 — implementar antes de publicar

*(vacío — ADM-005 ya quedó completado y documentado en `TASKS_ARCHIVE.md`)*

---

## 🟡 Próximo (ordenado por prioridad)

- **[AUD-001] Revisar y cerrar los hallazgos de la auditoría de filtros/buscador del admin**
  - Referencia: [`../docs/audits/2026-06-29-admin-filters-search-audit.md`](../docs/audits/2026-06-29-admin-filters-search-audit.md)
  - Alcance:
    - decidir si `data-table-filter` se elimina o se implementa como auto-submit real;
    - unificar o justificar la duplicidad de categoría en `files` / `files/trash`;
    - normalizar o documentar las excepciones (`analytics`, `metrics`, `form-submissions`, `translations/audit`, `admin/universal`).
  - Criterio de cierre:
    - cada excepción queda corregida o documentada;
    - la próxima sesión arranca desde este ítem y no desde la nota de auditoría.

---

## 🟡 CMS Integration — Admin Modules

> Tareas gateadas por fase del domain. No iniciar una fase hasta que su CMS-0XX esté ✅ en `../TASKS.md`.
> Todos los módulos usan `--service=domain` (apuntan a `DomainApiClient` → puerto 8090).
> Los comandos se corren desde la raíz del repo `ci4-website-builder-admin/`.
>
> **Prerequisito transversal (una vez, antes del primer módulo):**
> - Añadir iconos al mapa en `app/Helpers/ui_helper.php`: `'cms-page' => 'file-text'`, `'cms-menu' => 'navigation'`, `'cms-block-type' => 'layout-template'`, `'cms-entry' => 'newspaper'`, `'cms-language' => 'globe'`, `'cms-redirect' => 'corner-up-right'`.
> - Crear bloque CMS en `app/Views/layouts/partials/sidebar.php` antes del anchor `<!-- [DYNAMIC_MODULES_ANCHOR] -->` con guard `$hasCmsItem`.
>
> **Pasos post-scaffold comunes a todos los módulos:**
> 1. Registrar factory en `app/Config/Services.php` usando `domainApiClient()`.
> 2. Añadir link de navegación bajo el bloque CMS gateado con `has_permission('cms.{resource}.read')`.
> 3. Verificar prefijo `cms.` en filtros de ruta (el scaffold puede generar `permission:languages.read` sin prefijo).
> 4. Pulir language strings `en/es`.
> 5. `vendor/bin/phpunit tests/unit` y `tests/feature` antes de marcar ✅.

---

### Fase A — Disponible al terminar CMS-005 (Menus API)

---

#### [CMS-019] Admin module: Language
**Bloqueante:** CMS-002 ✅ — disponible ahora.

**Scaffold:**
```bash
bash bin/make-module.sh Language Cms /cms/languages \
  'code:string:required,name:string:required,native_name:string,is_default:boolean,is_active:boolean,sort_order:int' \
  --service=domain
```

**Trabajo manual específico:**
- **`fallback_language_id`** (auto-relación, no entra en scaffold): añadir `<select>` en create/edit que cargue `GET /cms/languages`; el controller pasa `$languages` a la vista y excluye el idioma actual al editar.
- **Acción "Set as Default"**: añadir manualmente `POST /{id}/set-default` en controller y routes → envía `PUT /cms/languages/{id}` con `{"is_default": true}`.

**Criterio de done:**
- [x] Index lista idiomas con code, name, is_default, is_active.
- [x] Select de `fallback_language_id` funciona y excluye el idioma actual al editar.
- [x] Botón "Set Default" actualiza con flash success.
- [x] Sidebar link gateado con `cms.languages.read`.
- [x] Tests pasan + `composer quality` limpio.

---

#### [CMS-020] Admin module: Setting
**Bloqueante:** CMS-002 ✅ — disponible ahora.

**Scaffold:**
```bash
bash bin/make-module.sh Setting Cms /cms/settings \
  'setting_key:string:required,setting_value:text,setting_type:enum:string|int|bool|json|file_id,setting_group:string,is_translatable:boolean,sort_order:int,description:text' \
  --service=domain
```

**Trabajo manual específico:**
- **`setting_value` dinámico**: usar Alpine `x-show` para alternar entre `<input type="text">`, `<input type="number">`, checkbox y `<textarea class="font-mono">` según `setting_type` seleccionado.
- **Filtro por `setting_group`**: añadir select en el panel de filtros del index.
- **Traducciones (v1 read-only)**: settings con `is_translatable=true` muestran tabla de traducciones en `show`; edición per-idioma es trabajo futuro.

**Criterio de done:**
- [x] Index filtrable por `setting_group`.
- [x] Campo value cambia de componente según `setting_type`.
- [x] Settings translatable muestran traducciones en show (read-only).
- [x] Sidebar link gateado con `cms.settings.read`.
- [x] Tests pasan + `composer quality` limpio.

---

#### [CMS-012] Admin module: Page
**Bloqueante:** CMS-004 ✅ — disponible ahora.

**Scaffold:**
```bash
bash bin/make-module.sh Page Cms /cms/pages \
  'page_type:enum:home|generic|contact|privacy|terms|404|500|maintenance,status:enum:draft|published|archived,parent_id:relation:pages,sort_order:int,is_in_sitemap:boolean,sitemap_priority:string,published_at:datetime,scheduled_at:datetime' \
  --service=domain \
  --action=publish \
  --action=archive
```

**Trabajo manual específico:**
- **Status badge**: añadir `cms_status_badge(string $status): string` en `app/Helpers/ui_helper.php` (reutilizado en Entries). Draft → gris, published → verde, archived → naranja.
- **Traducciones**: fieldset por idioma activo (cargar vía `GET /cms/languages`) con `slug`, `title`, `excerpt`, `meta_title`, `meta_description`. Enviar como `translations[{n}][language_id]`, `translations[{n}][slug]`, etc. SEO avanzado (`og_image_file_id`, `og_type`, `canonical_url`, `schema_data`) → TODO v2.
- **`parent_id`**: el option loader excluye la página actual al editar. El select muestra `title` del idioma por defecto.
- **Filtros de index**: `status` (select) y `page_type` (select) — mapear a `?status=&page_type=`.
- **Acciones publish/archive**: verificar que el service envía `PUT /cms/pages/{id}` con `{"status":"published"}` / `{"status":"archived"}`.

**Criterio de done:**
- [x] Index con status badge y filtros por status y page_type.
- [x] Create/Edit envía traducciones del idioma por defecto (slug, title obligatorios).
- [x] `parent_id` excluye la página actual al editar.
- [x] Publish y Archive cambian status y muestran flash.
- [x] Sidebar link gateado con `cms.pages.read`.
- [x] Tests pasan + `composer quality` limpio.

---

#### [CMS-013] Admin module: Menu
**Bloqueante:** CMS-005 — esperar ✅ en `../TASKS.md`.

**Scaffold:**
```bash
bash bin/make-module.sh Menu Cms /cms/menus \
  'menu_key:string:required,is_active:boolean' \
  --service=domain
```

**Trabajo manual específico:**
- **Sub-recurso MenuItem**: añadir manualmente en `app/Modules/Cms/Config/Routes.php`:
  ```
  GET  admin/cms/menus/(:num)/items/create       → MenuController::createItem/$1
  POST admin/cms/menus/(:num)/items              → MenuController::storeItem/$1
  GET  admin/cms/menus/(:num)/items/(:num)/edit  → MenuController::editItem/$1/$2
  POST admin/cms/menus/(:num)/items/(:num)       → MenuController::updateItem/$1/$2
  POST admin/cms/menus/(:num)/items/(:num)/delete → MenuController::deleteItem/$1/$2
  ```
  + métodos en controller. La vista `show` llama `GET /cms/menus/{id}/items` y recibe `$items`.
- **Lista anidada en show**: renderizar items indentados por `parent_id` (árbol simple, sin drag-drop).
- **Exclusión mutua `page_id` / `url_override`**: Alpine en form + validación server en Request.
- **`sort_order`**: campo numérico editable; drag-drop queda como TODO v2.
- **Traducciones**: tabla read-only en show (v1).

**Criterio de done:**
- [x] Index lista menús con `menu_key`, is_active, conteo de items.
- [x] CRUD básico de items funciona (sin drag-drop).
- [x] Vista show muestra árbol de items anidados.
- [x] Validación `page_id` / `url_override` mutuamente excluyentes.
- [x] Sidebar link gateado con `cms.menus.read`.
- [x] Tests pasan + `composer quality` limpio.

---

### Fase B — Disponible al terminar CMS-006 (Block system)

---

#### [CMS-014] Admin module: BlockType
**Bloqueante:** CMS-006 — esperar ✅ en `../TASKS.md`.

**Scaffold:**
```bash
bash bin/make-module.sh BlockType Cms /cms/block-types block-types \
  'type_key:string:required,name:string:required,description:text,icon:string,is_active:boolean' \
  --service=domain
```

**Trabajo manual específico:**
- **Campo `block_schema` (JSON)**: añadir `<textarea class="font-mono text-xs">` en create/edit con validación `json_decode($value) !== null`. Renderizar en show como `<pre><code>` con `json_encode(..., JSON_PRETTY_PRINT)`.
- **Permiso correcto**: rutas deben usar `cms.blocks.read/write`, no `blocks.read` (sin prefijo).
- **Seeds read-only**: anotar en show que `rich_text`, `image`, `cta` son tipos del sistema.

**Criterio de done:**
- [x] Index lista block types con `block_key`, `name`, `is_active`.
- [x] Campo JSON válida y renderiza como pretty-print en show.
- [x] Permisos `cms.blocks.*` correctos en rutas.
- [x] Sidebar link gateado con `cms.blocks.read`.
- [x] Tests pasan + `composer quality` limpio.

---

### Fase C — Disponible al terminar CMS-007 + CMS-008 + CMS-009

---

#### [CMS-015] Admin module: Collection
**Bloqueante:** CMS-007 — esperar ✅ en `../TASKS.md`.

**Scaffold:**
```bash
bash bin/make-module.sh Collection Cms /cms/collections \
  'collection_key:string:required,is_active:boolean' \
  --service=domain
```

**Trabajo manual específico:**
- **Traducciones (name, description)**: fieldset por idioma activo en create/edit → `translations[{n}][language_id]`, `translations[{n}][name]`, `translations[{n}][description]`.
- **Link a Entries**: en show, añadir botón "Ver entries" → `/admin/cms/entries?collection_id={id}` (añadir al finalizar CMS-016).

**Criterio de done:**
- [x] Index con `collection_key`, is_active.
- [x] Create/Edit envía traducción del idioma por defecto.
- [x] Sidebar link gateado con `cms.collections.read`.
- [x] Tests pasan + `composer quality` limpio.

---

#### [CMS-016] Admin module: Entry
**Bloqueante:** CMS-008 ✅ + CMS-015 ✅ — esperar ambos.

**Scaffold:**
```bash
bash bin/make-module.sh Entry Cms /cms/entries \
  'collection_id:relation:collections,status:enum:draft|published|archived,sort_order:int,published_at:datetime,scheduled_at:datetime' \
  --service=domain \
  --action=publish \
  --action=archive
```

**Trabajo manual específico:**
- **Filtro por `collection_id`** en index: select de colecciones activas — esencial para usabilidad.
- **Relation loader `collection_id`**: `GET /cms/collections?is_active=true`, mostrar `collection_key` como label.
- **Traducciones**: fieldset por idioma con `slug`, `title`, `excerpt`, `meta_title`, `meta_description`.
- **Block instances en show**: lista read-only (tipo + id). Edición de blocks → TODO v2.
- **Taxonomy en show**: tags y categorías asignados como badges read-only. Edición → tras CMS-017/018.
- **Status badge**: reutilizar `cms_status_badge()` de CMS-012.

**Criterio de done:**
- [x] Index filtrable por `collection_id` y `status`.
- [x] Publish y Archive funcionan con flash.
- [x] Block instances y taxonomías visibles en show (read-only).
- [x] Sidebar link gateado con `cms.entries.read`.
- [x] Tests pasan + `composer quality` limpio.

---

#### [CMS-017] Admin module: Category
**Bloqueante:** CMS-009 ✅ + CMS-015 ✅ — esperar ambos.

**Scaffold:**
```bash
bash bin/make-module.sh Category Cms /cms/categories \
  'collection_id:relation:collections,parent_id:relation:categories,sort_order:int,is_active:boolean' \
  --service=domain
```

**Trabajo manual específico:**
- **Scope por `collection_id`**: filtro prominente en index. Option loader de `parent_id` carga `GET /cms/categories?collection_id={id}` (misma colección).
- **Auto-referencia `parent_id`**: excluir la categoría actual al editar.
- **Traducciones (name, slug)**: fieldset por idioma.

**Criterio de done:**
- [x] Index filtrable por `collection_id`.
- [x] `parent_id` scoped a la misma colección, excluye la actual al editar.
- [x] Create/Edit envía traducción del idioma por defecto.
- [x] Sidebar link gateado con `cms.categories.read`.
- [x] Tests pasan + `composer quality` limpio.

---

#### [CMS-018] Admin module: Tag
**Bloqueante:** CMS-009 — esperar ✅ en `../TASKS.md`.

**Scaffold:**
```bash
bash bin/make-module.sh Tag Cms /cms/tags \
  'is_active:boolean' \
  --service=domain
```

**Trabajo manual específico:**
- **Traducciones (name, slug)**: fieldset por idioma — tags son globales, sin scope de colección.
- **Link a Entries desde show**: `GET /admin/cms/entries?tag_id={id}` — añadir tras finalizar CMS-016.

**Criterio de done:**
- [x] Index lista tags con name (idioma por defecto), slug, is_active.
- [x] Create/Edit envía traducción del idioma por defecto.
- [x] Sidebar link gateado con `cms.tags.read`.
- [x] Tests pasan + `composer quality` limpio.

---

### Bonus — Al terminar CMS-010 (Redirects)

---

#### [CMS-020b] Admin module: Redirect
**Bloqueante:** CMS-010 — esperar ✅ en `../TASKS.md`.

**Scaffold:**
```bash
bash bin/make-module.sh Redirect Cms /cms/redirects \
  'from_path:string:required,to_path:string:required,status_code:enum:301|302,is_active:boolean' \
  --service=domain \
  --csv
```

**Trabajo manual específico:**
- Validar en Request que `from_path` empiece con `/`.
- Manejar conflicto 409 (from_path duplicado) con flash error descriptivo.
- Validar `status_code` `301|302` por fila en import CSV.
- Añadir `'cms-redirect' => 'corner-up-right'` al mapa de iconos (parte del prerequisito transversal).

**Criterio de done:**
- [x] CRUD completo con import/export CSV funcionales.
- [x] Conflicto `from_path` duplicado muestra error claro.
- [x] Sidebar link gateado with `cms.redirects.read`.
- [x] Tests pasan + `composer quality` limpio.

---

## ⚪ Backlog

### [ADM-DEP-002] lint-staged 16 → 17 (espera Node 22 baseline)

**Contexto:** `lint-staged@17.x` requiere Node `>=22.22.1`. El admin pinea `engines.node` en `^20.19.0 || ^22.13.0 || >=24` y `lint-staged@16.4.0` (última v16) ya da todo lo que necesitamos (no hay features nuevas relevantes en v17).

**Señal de activación:** Cuando el baseline de Node del repo (CI, prod, dev) suba a 22 LTS por otra razón (p. ej. al alinear con otros repos del kit o por requerimiento del hosting).

**Acción:** `npm install --save-dev lint-staged@^17` · bump `engines.node` a `>=22.22.1` · `npm audit` · verificar que el hook `pre-commit` sigue corriendo `eslint --fix` sobre `public/assets/js/**/*.js`.

---

## ✅ Completadas

### [DEEP-WIZ-01..05] Refactor profundo del Wizard CMS — contenido + estructura (2026-07-12)

Continuación del pendiente dejado en `[DEEP-ADM-01..04, DEEP-ADM-05..07]` (2026-07-11). Refactor de preservación de contrato: mismas rutas, mismos payloads, mismo comportamiento visible.

- **Backend:** `WizardController` (525→~300 líneas) dejó de duplicar 6 métodos page/entry y de reimplementar servicios ya existentes — ahora inyecta y usa `blockInstanceApiService`, `menuApiService`, `entryApiService`, `fileApiService` (todos ya registrados, usados por otros controllers). `proxyBlockRequest()` eliminado. `StructureWizardController` y `WizardController` dejaron de duplicar `collectionTypeOptions()`/`pageTypeOptions()` — movidos a `CmsPresetCatalog`. `BaseWebController` ganó `normalizeUpstreamStatus()`/`jsonRequestPayload()` (colapsan ~14 repeticiones verbatim).
- **Frontend:** `index.php` (1748 líneas, ~96% JS inline) y `structure.php` (875 líneas) extraídos a `src/js/components/wizard/` (10 módulos) + `src/js/utils/wizard/` (8 funciones puras), siguiendo la convención `Alpine.data()` + esbuild ya usada por el resto del admin. Config servidor→JS vía `window.__wizardBoot`/`window.__structureWizardBoot` (mismo patrón que `window.__componentConfig` en `head.php`). Triplicación de `uploadImage`/`uploadBlockImage`/`uploadBlockContentImage` colapsada a un `_uploadFile()` interno. `slugify`/`adminFetch` unificados entre ambos wizards (antes divergían).
- **`applyIntentDefaults()` eliminado** — código muerto confirmado (nunca invocado, referenciaba `this.form`/`this.translation` inexistentes).
- **Vitest añadido** (no existía ningún test JS en el repo): 49 tests sobre las 8 utilidades puras extraídas.
- **Bug real encontrado y corregido durante la extracción:** el spread `{...navigation}` en la composición del factory `wizard()` evaluaba los getters (`get steps()`, etc.) inmediatamente con `this` incorrecto, lanzando una excepción que abortaba `wizard()` en silencio. Corregido con `Object.defineProperties(instance, Object.getOwnPropertyDescriptors(navigation))`, que preserva los descriptores de accessor sin invocarlos.
- **Verificación inicial:** 573/573 PHPUnit (1 ajuste en `WizardFlowTest.php` — una aserción que grepeaba `index.php` ahora grepea `entryPublish.js`, donde vive el código real), PHPStan y CS-Fixer limpios, 49/49 Vitest, ESLint limpio, `npm run build:all` sin errores.
- **Hallazgo preexistente documentado, no corregido (fuera de alcance):** `Wizard.strings.error_collection_required` usa `lang('Entries.collection_not_exists')`, una clave que no existe en ningún archivo de idioma — bug preexistente ya presente en el código original (CI4 hace fallback a la clave cruda). Preservado tal cual para no cambiar comportamiento visible.

**Prueba exhaustiva post-refactor (misma sesión, 2026-07-12)** — se probó manualmente en navegador CADA flujo con opción en el wizard, no solo "crear página":
- Wizard de contenido: **Agregar contenido** (selección de colección "Noticias", pasos dinámicos con rich text, paso de contenido de bloque, traducción automática ES/EN, publicación real — `POST /wizard/publish` 200); **Editar página** (árbol de bloques con hijos anidados reales, crear bloque nuevo, mover/reordenar con swap de `sort_order`, eliminar bloque); **Cambiar menú** (agregar ítem, editar con guardado automático al perder foco, reordenar, guardar orden, eliminar ítem).
- Wizard de estructura: **Crear colección** (2 pasos, slug check en vivo, traducción automática, preset de bloques, creación real) y **Crear menú** (con slug derivado del nombre).
- **2 bugs reales encontrados, confirmados preexistentes (idénticos en el HEAD previo al refactor, no introducidos por él) y corregidos con aprobación explícita del usuario para cada uno:**
  1. **Crear bloque nuevo daba 400** "block_type_key is required": el JS siempre envió `block_id` (nunca `block_type_key`), pero `WizardController::handleCreateBlock()` validaba el campo equivocado — "+ Agregar bloque" nunca funcionó, ni antes ni después del refactor. Corregido: la validación ahora exige `block_id` numérico. Tests `testCreateBlock(Entry)?RejectsMissingBlockId`/`ForwardsValidPayloadToDomain` actualizados para reflejar el payload real.
  2. **Editar ítem de menú (guardado automático) daba 400** "No se proporcionaron campos válidos para actualizar": `patchItem()` solo enviaba `{ translations: [...] }`, y el dominio vacía ese campo al extraerlo, dejando el payload sin campos de nivel superior — mismo patrón de `BaseCrudService::noFieldsToUpdate` que `_updateBlock()` ya evita agregando `is_active: true`. Corregido aplicando el mismo patrón en `menu.js::patchItem()`.
- **Verificación final tras los 2 fixes:** 573/573 PHPUnit, PHPStan y CS-Fixer limpios, 49/49 Vitest, ESLint limpio. Todos los flujos re-verificados en navegador tras cada fix con `POST` 200 confirmado por request.

### [DEEP-ADM-01..04, DEEP-ADM-05..07] Hardening arquitectónico de Ola 4/5 (2026-07-11)

Ejecutado tras la auditoría de robustez del 2026-07-10 (`../docs/audits/2026-07-10-auditoria-profunda-robustez.md`, `../docs/plans/2026-07-10-plan-maestro-robustez-mantenibilidad.md`).

- **H-012 (código fantasma):** eliminados `BaseCrudController.php` (222 líneas, 0 subclases) y los aliases JS `catalogMetadataField`/`catalogItemMedia` (0 consumidores), reverificados con grep antes de borrar.
- **H-014 (duplicación):** `safe_lang()` consolidado de 6 declaraciones inline a un único helper en `app/Helpers/ui_helper.php`.
- **DEEP-ADM-05/06/07 (deletion test):** las 28 interfaces `*ApiServiceInterface.php` bajo `app/Modules/*/Services/` tenían exactamente 1 implementación cada una — eliminadas todas, tipos de retorno de `Config/Services.php` y de cada Controller apuntando ahora a la clase concreta. `composer analyse`/`format:check`/tests: verde (572/572).
- **DEEP-ADM-01/02 (BlockInstanceController):** el controller tenía 998 líneas mezclando HTTP con composición de schemas dinámicos y llamadas remotas (fetchBlockTypes/injectDynamicFormOptions/collectionsMap/pagesForIds/entriesForIds/entriesForCollection, ~300 líneas). Extraído a `app/Modules/Cms/Services/BlockTypeOptionsResolver.php` (inyectado vía `Config/Services.php::blockTypeOptionsResolver()`), controller quedó en 681 líneas. Verificado con la suite completa (41 tests de BlockInstanceFlow/WizardFlow) y manualmente en el navegador contra la app corriendo: los selects dinámicos de `form_key` (form_embed) y `collection_key` (collection_grid) funcionan correctamente.
- **`BlockOwnerRouting`** ya estaba correctamente extraído de una sesión previa (DEEP-ADM-03 esencialmente ya cerrado) — no requirió cambios.
- **Bug real encontrado:** `image.php` (preview fallback de block types) leía `data['url']` en vez de `data['image_url']`, mismo bug de convención de campo `file` que el catálogo de Domain (nunca mostraba la imagen real, siempre caía al placeholder). Corregido con 2 tests nuevos.
- **Pendiente, fuera de este alcance:** DEEP-WIZ-01..05 (wizard, `Views/cms/wizard/index.php`, 1748 líneas de JS inline). No hay test runner JS configurado en este repo (sin vitest/jest) — extraerlo con seguridad requiere primero decidir e instalar un framework de test JS. Big-bang rewrite está explícitamente prohibido por el plan; debe hacerse incremental, en su propia sesión. **Completado 2026-07-12, ver `[DEEP-WIZ-01..05]` arriba.**

### [ADM-010] CSV export/import scaffold opcional para módulos admin (2026-06-10)

- `bin/make-module.sh` now supports `--csv` so generated admin modules can include export/import hooks without hand-writing the boilerplate each time.
- The scaffold emits the export route/button tied to the current index filters, plus import preview and row-level validation feedback for rejected rows.
- Documentation was updated in `README.md` and `CLAUDE.md` to keep the scope clear: CSV is an optional extension for repetitive admin ingestion, not a replacement for domain-specific workflows.

### [ADM-009] Scaffolding relation-aware end-to-end (2026-06-10)

- `bin/make-module.sh` now supports basic `relation` fields end-to-end: relation-aware form components, option loaders in the generated controller/service, index filters, and relation lookups in `show` views.
- The scaffold accepts an explicit relation path override with `categories=/catalog/categories` when the related endpoint does not follow the default derived path.
- Coverage was extended in `tests/unit/Support/ScaffoldingScriptsTest.php` to validate the generated controller, service, views, and filter contract for relation fields.

### [FAQ-010] Reemplazar texto plano de tablas vacías con empty_state (2026-06-04)

- Replaced plain `<p>` empty-state text with the existing `components/display/empty_state` component in all legacy index views: `users`, `api_keys`, `audit`, `iam/applications`, `iam/roles`, `iam/permissions`, `files/trash`, `files/partials/list_section`, `admin/universal`.
- Each view now uses the appropriate Lucide icon. Modules with a create action (users, api_keys, roles, permissions) pass `actionUrl` and `actionLabel` so the component renders a primary button.
- Read-only views (audit, applications) and the files list (drag-and-drop upload) receive context-appropriate icons and descriptions without an action button.
- `make-module.sh` was already generating the component correctly for new modules (KICK-018); this closes the gap for all pre-existing starter modules.

### [ADM-008] Diccionario de iconos y logs de scaffolding (2026-05-25)

- **Iconos**: Añadidos términos de negocio comunes (`cart`, `warehouse`, `box`, `truck`, `wallet`, `bank`, `settings`, `mail`, `bell`, `calendar`, `map-pin`, `tag`, `ticket`, `store`) al mapa de Lucide en `ui_helper.php`. Esto evita que módulos generados para eCommerce o logística lancen `InvalidArgumentException`.
- **Logs**: `bin/make-module.sh` ahora emite bloques de código coloreados y listos para copiar al fallar la auto-inyección de servicios en `Config/Services.php`, guiando mejor al desarrollador en los pasos manuales restantes.

### [ADM-007] Hooks mínimos de extensión para módulos admin (2026-05-24)

- `bin/make-module.sh` ahora acepta `--action=<verb>` repetible para scaffoldear acciones POST por item sin rehacer a mano service/controller/routes/views/lang. Cubre el caso común `approve/publish/archive/restore`.
- Cada acción genera: método extra en `*ApiServiceInterface`, implementación en `*ApiService`, action en controller, route `POST .../{id}/{verb}`, botón en `show.php` y keys base de i18n.
- Cobertura añadida en `tests/unit/Support/ScaffoldingScriptsTest.php`: scaffold end-to-end con `--action=approve --action=publish` + rechazo de `--action` inválido.

### [ADM-006] Clarificar alcance de `make-module.sh` (2026-05-24)

- `CLAUDE.md`, `README.md` y el help/banner de `bin/make-module.sh` ahora dejan explícito que el scaffold genera un **CRUD shell**: controller, service, requests, routes, views y tests base alineados con el backend elegido (`hub` o `domain`).
- Se documentó también lo que **no** hace: no modela aggregates complejos, nested resources, custom actions, relation arrays, option loaders, file pickers ni formularios ricos de dominio.
- El banner final del script ahora empuja al dev a completar manualmente la integración real del módulo en vez de sugerir implícitamente que el scaffold ya dejó una UI de producción.

...
### 🏗️ Contratos de arquitectura

- **Módulos en `app/Modules/{Nombre}/`:** Controllers + Services + Requests + Language + Config/Routes.php. Views en `app/Views/{nombre}/`.
- **Services extienden `BaseApiService`:** toda comunicación con la API pasa por `ApiClient` (hub) o `DomainApiClient` (domain apps). Nunca llamadas HTTP directas.
- **Dos clientes HTTP:** `apiClient` (factory `Services::apiClient()`, config `Config\ApiClient`, target hub `:8080`) y `domainApiClient` (factory `Services::domainApiClient()`, config `Config\DomainApiClient`, target domain `:8090`). Scaffolding selector: `bash bin/make-module.sh ... --service=hub|domain` (default `hub`).
- **`make-module.sh` genera un shell, no un aggregate listo para producción:** úsalo para establecer estructura, wiring y tests base. Si el módulo necesita acciones custom, nested resources, dropdowns dependientes, relation arrays o media/file-picker flows, la extensión manual sigue siendo obligatoria.
- **Hook mínimo soportado:** `make-module.sh --action=<verb>` añade wiring completo para acciones POST por item. Úsalo para workflows simples sobre un recurso ya existente; no reemplaza módulos aggregate con read models, loaders auxiliares o nested resources.
- **Tokens solo en sesión PHP:** nunca localStorage, nunca en JS. `ApiClient` inyecta el header automáticamente.
- **CSRF activo por defecto:** no desactivar. Usar `csrf_field()` en todos los forms.
- **Permisos en UI:** usar `has_permission(string $code)` (no `has_admin_access()` — legacy removido).
- **CSS:** Tailwind v4 (no hay `tailwind.config.js` — toda la config vive en `src/css/app.css` vía `@import "tailwindcss"`, `@theme`, `@source` y `@source inline()`). Brand colors en `@theme` y overridables en runtime desde `app/Views/layouts/partials/head.php`. Watcher: `npm run dev:css`. Build: `npm run build:css`. Binario `tailwindcss` lo provee `@tailwindcss/cli`.
- **Módulo nuevo:** usar `bash bin/make-module.sh {Resource} {Module} /api/v1/path`. Registrar el service en `app/Config/Services.php` manualmente.
- **Tests:** `vendor/bin/phpunit tests/unit` + `vendor/bin/phpunit tests/feature`. Correr antes de hacer merge.

### 🚧 Technical Debt (Scaffolding)
- [x] **Service Auto-Injection**: Update make-module.sh to use AST-based editing for automatic registration of generated services in Config/Services.php. ✅ 2026-05-28
- [x] **Icon Dictionary**: Add missing business icons (cart, warehouse, etc.) to ui_helper.php to avoid InvalidArgumentException during UI generation. ✅ 2026-05-25
