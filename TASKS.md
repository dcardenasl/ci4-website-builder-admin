# TASKS — ci4-admin-starter

> Fuente de verdad para trabajo en este repo.
> Historial de completadas: ver `TASKS_ARCHIVE.md`.
> Cross-repo: ver `../TASKS.md`.
> Plan detallado CMS admin: ver [`../docs/cms_integration_plan.md`](../docs/cms_integration_plan.md).
> Última actualización: 2026-07-20 (dashboard rediseñado con widgets de traducciones/atención/contenido/actividad CMS — ver DASH-001 en Completadas)

---

## 🔴 Bloqueante para v2.0.0 — implementar antes de publicar

*(vacío — ADM-005 ya quedó completado y documentado en `TASKS_ARCHIVE.md`)*

---

## 🟡 Próximo (ordenado por prioridad)

*(vacío — AUD-001 cerrado, ver Completadas)*

### 🌐 Flujo editorial de traducciones — Plan 2026-07-20
> Plan: [`../docs/plans/2026-07-20-translation-workbench-plan.es.md`](../docs/plans/2026-07-20-translation-workbench-plan.es.md)
> Seguimiento cross-repo: [`../TASKS.md`](../TASKS.md), TRN-001..TRN-007.

- [x] **TRN-001** — Normalizador común y resolver de navegación contextual (`focus_lang`).
  - [x] Eliminado el resolver global obsoleto de estados; las tablas usan exclusivamente `remoteTable.translationStatus`.
  - [x] Navegación de badges centralizada en `translationEditUrl`, evitando concatenación insegura de query strings.
  - [x] Resolver de auditoría extraído a utilidad testeada para recursos normales, ítems anidados y bloques; falla cerrado sin propietario.
  - [x] **2026-07-20 — consolidación post-revisión:** `TranslationStatus::badgeClasses()`/`editUrl()` (PHP) reemplazan las 5 copias de la cadena de colores por estado y la concatenación manual de `?focus_lang=` repartidas en `translation_status_panel.php`, `categories/tags/collections/pages/show.php` y el árbol de `menus/show.php`. El árbol de `menu_item` ahora reutiliza `TranslationStatus::evaluate()` en vez de reimplementar la regla ad hoc.
  - [x] **2026-07-20 — `return_to` implementado de verdad (ya no es parámetro decorativo).** `BaseWebController::resolveReturnUrl()`/`incomingReturnTo()` validan que el destino sea una ruta local absoluta (rechaza `//host`, `scheme://`, backslash-trick, CRLF) antes de redirigir — con tests unitarios cubriendo cada vector de open-redirect. Cableado en los 10 pares edit/update reachable desde la auditoría: Page, Entry, Category, Tag, Collection, Form, Menu, MenuItem, Setting, BlockInstance (cada `edit()` pasa `returnTo` a la vista vía un hidden input; cada `update()` envuelve su redirect de éxito con `resolveReturnUrl()`). `resolveCmsTranslationEditUrl` vuelve a incluir `return_to`, ahora construido como ruta relativa con los filtros activos (`this.pageUrl` + `this.query`), no con `buildUrl()` (que resuelve a URL absoluta y sería rechazada por el guard). Verificado end-to-end en navegador real: filtrar por `resource=setting` → Traducir → guardar → vuelve a `/admin/cms/translations/audit?resource=setting` con el filtro aplicado.
- [x] **TRN-002** — Auditoría/workbench con nombres, filtros, faltantes y acciones. Validado con filtros server-side, i18n ES/EN y tests de servicio.
  - [x] Filtros server-side por idioma, recurso, estado y búsqueda; i18n ES/EN.
  - [x] **2026-07-20 — bug crítico encontrado y corregido en navegador real:** los filtros `resource`, `status`, `search` (y el ya existente `language_id`) llamaban a `filter('key', value)` en Alpine, un método que **no existe** en `remoteTable.js` (confirmado: `typeof data.filter === 'undefined'`, `Alpine Expression Error: filter is not defined` en consola). Los filtros nunca llegaban a golpear el backend a pesar de que el backend y sus tests sí funcionaban. Corregido envolviendo los campos en `<form data-table-filter-form="1">` con `data-table-debounce` en la búsqueda, siguiendo el mismo patrón que ya usan todas las demás tablas del admin (`filter_panel.php`). Verificado end-to-end: `GET .../audit/data?search=site_name&resource=setting` ahora se dispara realmente y filtra.
- [x] **TRN-003** — Panel consistente de traducciones en vistas “Ver”.
  - [x] Colecciones, categorías y etiquetas muestran código de idioma legible y enlace de edición con `focus_lang`.
  - [x] Esas vistas iteran idiomas activos y muestran explícitamente idiomas faltantes.
  - [x] Componente compartido de idiomas faltantes integrado en páginas, entradas, formularios, menús y settings.
  - [x] `composer quality` pasa: PHPStan, formato, i18n y fixture policy.
  - [x] El componente compartido distingue `missing`, `incomplete` y `complete` según campos obligatorios.
  - [x] Evaluador PHP compartido con contrato explícito para idioma base, fila vacía, faltante y desactualizada; tests unitarios agregados.
  - [x] El idioma predeterminado se considera completo desde los campos canónicos del recurso y no se marca como traducción faltante.
  - [x] Colecciones, categorías y etiquetas usan la misma semántica de estados; PHPStan y PHP CS Fixer secuencial pasan.
  - [x] Las tarjetas detalladas de colecciones, categorías y etiquetas respetan el idioma predeterminado como completo.
  - [x] Estado `outdated` implementado comparando `updated_at` de traducción contra el recurso base.
  - [x] Panel de detalle muestra campos obligatorios faltantes por idioma con etiquetas i18n.
  - [x] Componente renombrado a `translation_status_panel` para evitar nomenclatura legacy.
  - [x] **2026-07-20 — bug crítico encontrado y corregido probando en navegador real: "idioma por defecto completo" era falso para 4 de 8 recursos.** `TranslationStatus::evaluate()` asumía que el idioma por defecto siempre vive en campos canónicos del recurso (`$page['title']`) — cierto para Settings/Entry/Category/Tag, **falso** para Page/Collection/Menu/Form (confirmado contra sus Response DTOs en el dominio: no tienen `title`/`name` a nivel raíz, todo vive en `translations`). Antes del fix, `/admin/cms/pages/1` mostraba "ES Faltante (slug, título)" con una página 100% traducida. Corregido: una fila de traducción real siempre gana sobre los campos canónicos; estos solo se consultan si no existe fila, o para completar un campo que la fila dejó vacío (denormalización de Category/Tag). El mismo bug existía en paralelo en `remoteTable.js::translationStatus()` (usado por las columnas de traducción en las listas) y se corrigió con la misma lógica — confirmado visualmente que la columna ES en el listado de Páginas pasó de rojo a verde. Se agregaron el panel `translation_status_panel` a categorías/etiquetas/colecciones (antes solo tenían su tarjeta detallada) y tests unitarios nuevos en ambos lados (PHP y JS).
- [x] **TRN-004** — Traducir todo, copiar base y siguiente pendiente como borradores.
  - [x] Auditoría incluye “Abrir siguiente pendiente”, conserva filtros y abre edición con `focus_lang` sin escribir ni publicar.
  - [x] Páginas y entradas incluyen “Copiar idioma base” con confirmación; solo modifica el formulario y requiere guardado explícito.
  - [x] Colecciones, categorías, etiquetas y formularios incluyen la misma acción con campos declarados y confirmación.
  - [x] Colecciones y menús incluyen “Copiar idioma base”; PHPStan y PHP CS Fixer pasan.
  - [x] Settings incluye copia compatible por tipo (`string`, `int`, `bool`, `json`, `file_id`) con confirmación y guardado explícito.
  - [x] Confirmaciones de copia externalizadas a i18n ES/EN; sin texto hardcodeado en las acciones.
  - [x] Tests JS de confirmación/copia agregados; suite Vitest completa: 10 archivos, 61 tests.
  - [x] Auditoría incorpora `outdated` en su diccionario y visualización; contrato de estados consistente.
  - [x] Suite PHP admin completa: 579 tests, 2074 assertions; 0 errores (1 warning de cobertura y 1 skip conocidos).
  - [x] Suite PHP admin actualizada: 582 tests, 2079 assertions; 0 errores (1 warning de cobertura y 1 skip conocidos).
  - [x] Feature tests de auditoría cubren filtros contextuales y denegación sin `cms.languages.read`.
  - [x] Tests de auditoría del dominio: 11 tests, 48 assertions; 0 errores.
  - [x] **2026-07-20:** `copySettingDefaultToAll` (implementación propia, sin test, duplicada) reemplazada por el `copyDefaultToAll`/`copyFieldToAll` compartido de `src/js/utils/translationCopy.js`, expuesto como `window.copyDefaultToAll` para vistas sin `langTabs()`. De paso se corrigió un bug real: el binding `:name` de los campos base de Settings nunca funcionaba (CI4 `esc(..., 'attr')` escapa el `:` a `&#x3a;name`, dejando el atributo inerte para Alpine), por lo que el selector `[name="setting_value"]` jamás encontraba nada. `copyFieldToAll` ahora resuelve el candidato visible entre varios elementos que comparten selector (`querySelectorAll` + chequeo de `disabled`/`offsetParent`), robusto también si el usuario cambia el tipo de configuración sin recargar. Verificado en navegador real (`ChangeMe123!`): copiar "Mi Sitio" desde el valor base escribe correctamente en `translations[2]` (EN) y `translations[3]` (FR).
  - [x] **2026-07-20 — regresión propia encontrada y corregida antes de cerrar la tarea:** el chequeo de visibilidad agregado al punto anterior (`offsetParent !== null`) rompía "Copiar idioma base" para TODOS los recursos con pestañas por idioma (páginas, entradas, categorías, etiquetas, colecciones, formularios, menús) — un campo de una pestaña inactiva (oculta vía `x-show`, no `disabled`) es un destino legítimo y dejó de recibir el valor copiado. Detectado probando en navegador real (Etiquetas: el botón no escribía nada). Corregido: la verificación de visibilidad solo aplica cuando un selector resuelve a *varios* elementos hermanos que comparten nombre (el caso real de Settings); con un único match (el caso normal de `langTabs`) siempre se usa, esté oculto o no. Test de regresión agregado; verificado de nuevo en navegador para Etiquetas, Páginas y Menús.
  - [x] El bloque `$copyMappings` (7 copias casi idénticas en categories/collections/tags/entries/forms/pages `edit.php` y `menus/_translations.php`) se extrajo a `cms_translation_copy_mappings()` en `app/Helpers/cms_translations_helper.php` (autoloaded).
  - [x] `SettingController` deduplicado: los 6 `service('languageApiService')->list(...)` inline (incluida la llamada nueva en `show()`, que además se saltaba `maybeFlashDevError`) se reemplazaron por el mismo `getLanguages()` privado que ya usan Category/Collection/Tag/Menu/Page/Entry.
- [x] **TRN-005** — Menús, ítems, hijos y navegación contextual anidada.
  - [x] Árbol de `menu_item` muestra estado por idioma y abre el primer idioma pendiente con `focus_lang`.
  - [x] Árbol de `menu_item` distingue también traducciones `outdated`; lint/build JS y PHP CS Fixer pasan.
  - [x] Renderizado recursivo convertido de función global a closure local; PHPStan y PHP CS Fixer pasan.
  - [x] Eliminada la declaración global legacy del árbol de menús sin cambiar su contrato visual.
  - [x] **2026-07-20:** cálculo de estado por `menu_item` reemplazado por `TranslationStatus::evaluate()` compartido (antes reimplementaba la regla a mano); verificado en navegador que el menú "legal" muestra correctamente "FR Faltante (nombre)" y los 7 ítems conservan su estado real.
- [ ] **TRN-006** — Estados editoriales, outdated, permisos y controles de publicación.
  - [x] `outdated` real (backend + UI) — ver TRN-002/003 arriba.
  - [x] Permisos de escritura: toda acción de traducción (copiar, traducir, editar) ya vive detrás del filtro de ruta `permission:cms.*.write` existente — no hay pantalla de traducción alcanzable sin permiso de escritura.
  - [ ] **Deliberadamente NO implementado — requiere decisión de producto, no es una corrección de algo roto:** el modelo de estados editoriales (`in_review`, `approved`, `published` *por idioma*) del plan original. Antes de construirlo hay que decidir: (1) ¿es una columna nueva por fila de traducción, y en qué migración/tabla — página, entrada, categoría, etiqueta, colección, formulario, menú, ítem de menú, configuración y bloque son 10 tipos distintos en el dominio? (2) ¿cómo convive con el `status` (publicado/borrador/archivado) que Page/Entry YA tienen a nivel de recurso completo, no por idioma — pueden pisarse o generar dos fuentes de verdad contradictorias? (3) ¿quién puede aprobar? ¿hace falta un rol/permiso nuevo distinto de `cms.*.write`? Construir esto sin esas respuestas sería inventar producto, no corregir deuda técnica. Recomendación: tratarlo como una iniciativa aparte con su propio diseño, no como parte de este cierre.
- [x] **TRN-007** — Tests, i18n, rendimiento, documentación y validación browser.
  - [x] Admin: `composer quality` (PHPStan 204 archivos, CS-Fixer, i18n-check, fixture policy) + suite completa 602 tests / 2116 assertions, todo en verde.
  - [x] Domain: `composer quality` (PHPStan, CS-Fixer) + Unit/Architecture/Integration/Feature/SeederContracts, 429+18+13 tests, todo en verde.
  - [x] Rendimiento: sin queries nuevas — el fix de `outdated` reutiliza datos ya cargados (`$resourceRow['updated_at']`), la consolidación en admin es puramente in-memory.
  - [x] Validación browser real con credenciales del README en: Configuración (lista, detalle, editar, copiar, filtros de auditoría + `return_to`), Categorías, Etiquetas, Colecciones, Páginas (lista + detalle + editar), Entradas (lista), Menús (detalle + árbol de ítems + editar), Formularios (detalle + editar) — 2 bugs críticos preexistentes y 1 regresión propia encontrados y corregidos solo gracias a esta validación (ver notas en TRN-003/004).
  - [x] Documentación: este archivo y `../TASKS.md` actualizados con cada hallazgo, causa raíz y verificación.

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
- **Taxonomy**: tags y categorías asignados como badges en show y editables desde el formulario de entrada tras CMS-017/018.
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

### [DASH-001] Rediseño del dashboard/escritorio con información relevante del proyecto (2026-07-20)

El escritorio del admin solo mostraba stats genéricos (usuarios, archivos, uptime API) y un feed de "actividad" que ya no aportaba señal real de negocio. A pedido explícito del usuario ("construye todo eso", tras preguntarle qué tarjetas/paneles recomendaba), se reconstruyó `DashboardController` y `dashboard/index.php` con 5 zonas nuevas, todas gateadas por permiso y con caché (60–300s) sobre `safeApiCall()`:

- **Traducciones** (`widgetTranslations`) — barra de cobertura por idioma activo (`TranslationAuditService::getStats()`), gateada por `cms.languages.read`.
- **Necesita tu atención** (`widgetAttention`) — traducciones pendientes + envíos de formulario sin leer, cada ítem enlaza directo a la pantalla de acción; oculta la sección entera si no hay pendientes.
- **Resumen de contenido** (`widgetContentSummary`) — grid de conteos por recurso CMS (páginas, entradas, colecciones, menús, categorías, etiquetas, formularios), array-driven, cada tarjeta gateada por su propio permiso.
- **Actividad CMS reciente** (`widgetCmsActivity`) — reemplaza el widget `activity` genérico eliminado; combina páginas + entradas recientes por `updated_at`, resolviendo el título real desde `translations[0]['title']` (nunca "Page #N").
- Health/uptime/usuarios/archivos **demovidos** a una columna lateral secundaria y a una fila de stats al final, en vez de ser lo primero que se ve.

Verificación: `composer test` (607/607), `composer quality` (PHPStan nivel 8 sobre 204 archivos, CS-Fixer, i18n-check, fixture policy) todo en verde. Se detectó y corrigió a tiempo que las nuevas clases Tailwind (`min-w-[1.5rem]`, `xl:grid-cols-3`, `lg:grid-cols-4`, etc.) no estaban compiladas — `npm run build:css` no corre con el alias `npm=pnpm` de esta máquina; hay que invocar el binario real (`/opt/homebrew/bin/npm run build:css`). Validado en navegador real en desktop (1440px), tablet y mobile (375px): layout se apila correctamente, sin errores de consola, datos reales cargando en cada widget.

### [AUD-001] Cierre de la auditoría de filtros/buscador del admin (2026-07-18)

Referencia: [`../docs/audits/2026-06-29-admin-filters-search-audit.md`](../docs/audits/2026-06-29-admin-filters-search-audit.md), §9 Closure. Las 3 correcciones de código de la auditoría original (§5, ya aplicadas el 2026-06-29) se re-verificaron intactas: `data-table-filter` sigue ausente de las vistas, `files`/`files/trash` mantienen la duplicidad resuelta vía `$showCategoryFilter ?? false`, y `components/table/filter_panel.php` sigue eliminado. De los ítems de `## 7. Pending Work`, 3 ya estaban superados por esas correcciones (no eran pendientes reales); los 2 genuinos se documentaron como desviaciones intencionales en vez de forzarlos al contrato compartido: `admin/universal` es infraestructura "Zero-Code Admin" deliberadamente sin entrada de menú (escape hatch por URL directa), y el selector custom `language_id` de `cms/translations/audit` es apropiado para una pantalla de un solo eje de filtro. Sin cambios de código — la auditoría queda cerrada por documentación.

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

**Renombrado de labels (misma sesión, 2026-07-12)** — "Cambiar el menú" (wizard de contenido) y "Crear menú" (wizard de estructura) compartían nombre e ícono (🔗) pese a ser features distintas (una edita ítems de un menú existente, la otra crea el contenedor de un menú nuevo). Renombrados a **"Editar enlaces del menú"** (🔗) y **"Crear menú nuevo"** (🧭) en `es`/`en`, incluyendo el heading del selector de menú. `assertSee('Crear menú')` en el test sigue pasando (substring match).

**`BLOCK_ICONS` hardcodeado eliminado (misma sesión, 2026-07-12)** — el usuario detectó que `bootStrings.js` tenía un mapa estático `block_key → emoji` para los íconos del árbol de bloques y el catálogo, que no escala: cada bloque nuevo creado dinámicamente desde el módulo canónico "Tipos de Bloque" (`/admin/cms/block-types`) requeriría editar y redesplegar este archivo para tener ícono. Verificado con datos reales: **`WizardController::config()` ya enriquece cada block type con su propio campo `icon`** (nombre de ícono Lucide, configurable en el admin) — de los 45 tipos de bloque reales, 9 ni siquiera estaban cubiertos por el mapa hardcodeado (`tabs`, `alert`, `gallery`, `timeline`, `pricing_grid`, `features_grid`, `anchor_nav`, `process_steps`, `team_grid`), cayendo siempre al 📦 genérico.
- `blocks.js::blockIcon(blockKey)` ahora lee `this.blockTypeInfo(blockKey)?.icon` (dinámico, dominio) con fallback a `'layout-template'` (mismo default que usa el propio formulario de creación de tipos de bloque). `BLOCK_ICONS` eliminado de `bootStrings.js`.
- Las 3 vistas que renderizaban el ícono como texto emoji (`block_catalog.php`, `page_layout.php` ×2) pasaron a renderizar `<i :data-lucide="blockIcon(...)">`, seteando el atributo aquí y reutilizando `bootLucideIcons()` con `this.$nextTick()` tras `refreshPageBlocks()`/`openBlockCatalog()` — mismo patrón ya usado en `remoteTable.js` para íconos Lucide dinámicos en `x-for`.
- Verificado en navegador contra datos reales: árbol de bloques y catálogo muestran el ícono Lucide correcto y distinto por tipo (`hero_slider`→`gallery-horizontal`, `collection_grid`→`layout-grid`, `cta`→`mouse-pointer`, etc.), incluyendo tipos que antes no tenían representación en el mapa hardcodeado. 573/573 PHPUnit, PHPStan/CS-Fixer/ESLint limpios, 49/49 Vitest.

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
