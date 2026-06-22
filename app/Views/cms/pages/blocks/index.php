<?php
$page          = $page          ?? [];
$blocks        = $blocks        ?? [];
$blockTypes    = $blockTypes    ?? [];
$publicSiteUrl = $publicSiteUrl ?? '';
$ownerType     = $ownerType     ?? 'page';
$ownerLabel    = $ownerLabel    ?? 'Página';
$ownerShowRoute = $ownerShowRoute ?? 'admin.cms.pages.show';
$ownerBlocksRoute = $ownerBlocksRoute ?? 'admin.cms.pages.blocks';
$ownerCreateRoute = $ownerCreateRoute ?? 'admin.cms.pages.blocks.create';
$ownerEditRoute   = $ownerEditRoute ?? 'admin.cms.pages.blocks.edit';
$ownerDeleteRoute = $ownerDeleteRoute ?? 'admin.cms.pages.blocks.delete';
$ownerChildrenRoute = $ownerChildrenRoute ?? 'admin.cms.pages.blocks.children';
$ownerReorderRoute = $ownerReorderRoute ?? 'admin.cms.pages.blocks.reorder';
$reorderUrl    = route_to($ownerReorderRoute, (string) $page['id']);

// Build preview URL from first translation slug, fall back to page id path
$previewSlug = '';
if (! empty($page['translations']) && is_array($page['translations'])) {
    $previewSlug = (string) ($page['translations'][0]['slug'] ?? '');
}
$previewUrl = $publicSiteUrl !== '' && $previewSlug !== ''
    ? $publicSiteUrl . '/' . ltrim($previewSlug, '/')
    : '';
?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to($ownerShowRoute, (string) $page['id']) ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; Volver a <?= esc($ownerLabel) ?></a>
    <div class="flex items-center gap-2">
        <?php if ($previewUrl !== ''): ?>
        <a href="<?= esc($previewUrl) ?>" target="_blank" rel="noopener noreferrer"
           class="<?= esc(action_button_class('neutral')) ?>">
            <?= ui_icon('external-link', 'h-4 w-4 mr-1') ?> Ver Página
        </a>
        <?php endif; ?>
        <a href="<?= route_to($ownerCreateRoute, (string) $page['id']) ?>" class="<?= esc(action_button_class('primary')) ?>">
            <?= ui_icon('plus', 'h-4 w-4 mr-1') ?> Añadir Bloque
        </a>
    </div>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 max-w-4xl"
         x-data="blockSorter('<?= esc($reorderUrl) ?>')">

    <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-6">
        <div>
            <h3 class="text-xl font-bold text-gray-900">Bloques de Contenido</h3>
            <p class="text-sm text-gray-500 mt-1">Administra y ordena los bloques para: <strong><?= esc($page['title'] ?? '') ?></strong></p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Save status indicators -->
            <span x-show="saving" class="flex items-center gap-1.5 text-xs text-gray-500">
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Guardando…
            </span>
            <span x-show="saved" x-cloak class="flex items-center gap-1 text-xs text-green-600 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                </svg>
                Orden guardado
            </span>

            <!-- Save order button — shown when order has changed -->
            <?php if (! empty($blocks)): ?>
            <button type="button"
                    x-show="dirty && !saving"
                    x-cloak
                    @click="saveOrder()"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold bg-brand-600 text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1 transition-colors shadow-sm">
                <?= ui_icon('save', 'h-3.5 w-3.5') ?>
                Guardar Orden
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($blocks)): ?>
        <div class="text-center py-12 border border-dashed border-gray-200 rounded-xl">
            <?= ui_icon('layout-template', 'h-10 w-10 text-gray-300 mx-auto mb-3') ?>
            <p class="text-sm text-gray-500 font-medium">Esta página aún no tiene bloques de contenido.</p>
            <p class="text-xs text-gray-400 mt-1">Haz clic en "Añadir Bloque" para comenzar a poblar el sitio.</p>
        </div>
    <?php else: ?>
        <div data-sortable-list class="space-y-3">
            <?php foreach ($blocks as $block):
                $blockType = $blockTypes[$block['block_id']] ?? [];
                $isActive  = (bool) ($block['is_active'] ?? true);
                $blockId   = (string) $block['id'];
                ?>
            <div data-block-id="<?= esc($blockId) ?>"
                 class="flex items-center gap-3 p-4 border border-gray-200 rounded-xl bg-gray-50/50 hover:bg-gray-50 transition-colors group">

                <!-- Drag handle -->
                <div data-drag-handle
                     class="cursor-grab active:cursor-grabbing shrink-0 text-gray-300 hover:text-gray-500 transition-colors select-none"
                     title="Arrastrar para reordenar">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm6 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM7 9a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm6 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM7 16a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm6 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/>
                    </svg>
                </div>

                <!-- Block type icon -->
                <div class="bg-brand-50 text-brand-700 p-2.5 rounded-lg border border-brand-100 shrink-0">
                    <i data-lucide="<?= esc($blockType['icon'] ?? 'layout-template') ?>" class="w-5 h-5"></i>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-900 text-sm truncate"><?= esc($blockType['name'] ?? 'Bloque') ?></h4>
                    <p class="text-xs text-gray-500 font-mono mt-0.5"><?= esc($blockType['block_key'] ?? '') ?></p>
                </div>

                <!-- Status badge -->
                <div class="shrink-0">
                    <?php if ($isActive): ?>
                        <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Activo</span>
                    <?php else: ?>
                        <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Inactivo</span>
                    <?php endif; ?>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 shrink-0">
                    <?php if (!empty($blockType['is_container'])): ?>
                <a href="<?= route_to($ownerChildrenRoute, (string) $page['id'], $blockId) ?>"
                           class="<?= esc(action_button_class('primary')) ?> py-1 px-2.5 text-xs">
                            <?= ui_icon('layers', 'h-3.5 w-3.5 mr-1') ?> Slides
                        </a>
                    <?php endif; ?>
                    <a href="<?= route_to($ownerEditRoute, (string) $page['id'], $blockId) ?>"
                       class="<?= esc(action_button_class('neutral')) ?> py-1 px-2.5 text-xs">
                        Editar
                    </a>
                    <form method="post"
                          action="<?= route_to($ownerDeleteRoute, (string) $page['id'], $blockId) ?>"
                          onsubmit="return confirm('¿Seguro que deseas eliminar este bloque?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="<?= esc(action_button_class('danger')) ?> py-1 px-2.5 text-xs">
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <p class="text-xs text-gray-400 mt-4 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5"/>
            </svg>
            Arrastra los bloques para reordenarlos y luego haz clic en <strong class="font-medium text-gray-500">Guardar Orden</strong> para confirmar.
        </p>
    <?php endif; ?>
</section>
