<?php $page = $page ?? []; $blocks = $blocks ?? []; $blockTypes = $blockTypes ?? []; ?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to('admin.cms.pages.show', (string)$page['id']) ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; Volver a la Página</a>
    <a href="<?= route_to('admin.cms.pages.blocks.create', (string)$page['id']) ?>" class="<?= esc(action_button_class('primary')) ?>">
        <?= ui_icon('plus', 'h-4 w-4 mr-1') ?> Añadir Bloque
    </a>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 max-w-4xl">
    <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-6">
        <div>
            <h3 class="text-xl font-bold text-gray-900">Bloques de Contenido</h3>
            <p class="text-sm text-gray-500 mt-1">Administra y ordena los bloques para: <strong><?= esc($page['title'] ?? '') ?></strong></p>
        </div>
    </div>

    <?php if (empty($blocks)): ?>
        <div class="text-center py-12 border border-dashed border-gray-200 rounded-xl">
            <?= ui_icon('layout-template', 'h-10 w-10 text-gray-300 mx-auto mb-3') ?>
            <p class="text-sm text-gray-500 font-medium">Esta página aún no tiene bloques de contenido.</p>
            <p class="text-xs text-gray-400 mt-1">Haz clic en "Añadir Bloque" para comenzar a poblar el sitio.</p>
        </div>
    <?php else: ?>
        <form method="post" action="<?= route_to('admin.cms.pages.blocks.reorder', (string)$page['id']) ?>">
            <?= csrf_field() ?>
            
            <div class="space-y-4">
                <?php foreach ($blocks as $index => $block): ?>
                    <?php 
                        $blockType = $blockTypes[$block['block_id']] ?? []; 
                        $isActive = (bool)($block['is_active'] ?? true);
                        $id = (string)$block['id'];
                    ?>
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl bg-gray-50/50 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="bg-brand-50 text-brand-700 p-2.5 rounded-lg border border-brand-100">
                                <?= ui_icon('layout-template', 'h-5 w-5') ?>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 text-sm"><?= esc($blockType['name'] ?? 'Bloque') ?></h4>
                                <p class="text-xs text-gray-500 font-mono mt-0.5"><?= esc($blockType['block_key'] ?? '') ?></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <!-- Sort Order input -->
                            <div class="flex items-center gap-1.5">
                                <label class="text-xs text-gray-500 font-medium">Orden:</label>
                                <input type="number" 
                                       name="orders[<?= $id ?>]" 
                                       value="<?= esc($block['sort_order'] ?? 0) ?>" 
                                       class="w-16 rounded-md border-gray-200 text-center text-xs font-semibold py-1 focus:ring-brand-500 focus:border-brand-500" />
                            </div>

                            <!-- Status Indicator -->
                            <div>
                                <?php if ($isActive): ?>
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Activo</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Inactivo</span>
                                <?php endif; ?>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                <a href="<?= route_to('admin.cms.pages.blocks.edit', (string)$page['id'], $id) ?>" 
                                   class="<?= esc(action_button_class('neutral')) ?> py-1 px-2.5 text-xs">
                                    Editar
                                </a>
                                
                                <button type="submit" 
                                        formaction="<?= route_to('admin.cms.pages.blocks.delete', (string)$page['id'], $id) ?>" 
                                        onclick="return confirm('¿Seguro que deseas eliminar este bloque de contenido?');"
                                        class="<?= esc(action_button_class('danger')) ?> py-1 px-2.5 text-xs">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center gap-3 pt-6 mt-6 border-t border-gray-100">
                <button type="submit" class="<?= esc(action_button_class('primary')) ?>">
                    Guardar Orden
                </button>
            </div>
        </form>
    <?php endif; ?>
</section>
