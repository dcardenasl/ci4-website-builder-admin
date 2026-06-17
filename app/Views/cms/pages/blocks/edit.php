<?php 
$page = $page ?? []; 
$block = $block ?? []; 
$blockType = $blockType ?? []; 
$languages = $languages ?? []; 

// Parse the schema fields
$schema = [];
if (!empty($blockType['schema_definition'])) {
    $schema = is_string($blockType['schema_definition']) 
        ? json_decode($blockType['schema_definition'], true) 
        : $blockType['schema_definition'];
}
$fields = $schema['fields'] ?? [];
$defaultLangId = 0;
foreach ($languages as $l) {
    if (!empty($l['is_default'])) {
        $defaultLangId = (int)$l['id'];
        break;
    }
}
if ($defaultLangId === 0 && !empty($languages)) {
    $defaultLangId = (int)$languages[0]['id'];
}
?>
<div class="mb-4">
    <a href="<?= route_to('admin.cms.pages.blocks', (string)$page['id']) ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; Volver a Bloques</a>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 max-w-3xl">
    <h3 class="text-lg font-semibold text-gray-900 mb-2">Editar Bloque: <?= esc($blockType['name'] ?? 'Bloque') ?></h3>
    <p class="text-xs text-gray-500 font-mono mb-6">Clave: <?= esc($blockType['block_key'] ?? '') ?></p>

    <form method="post" action="<?= route_to('admin.cms.pages.blocks.update', (string)$page['id'], (string)$block['id']) ?>" class="space-y-6">
        <?= csrf_field() ?>
        
        <input type="hidden" name="block_id" value="<?= esc($block['block_id']) ?>" />

        <!-- Global Options Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
            <?= view('components/form/number', [
                'name' => 'sort_order',
                'label' => 'Orden',
                'required' => true,
                'value' => $block['sort_order'] ?? 0,
                'help' => 'Posición del bloque en la página.'
            ]) ?>

            <div class="flex items-center pt-8">
                <input type="checkbox" name="is_active" id="is_active" value="1" <?= !empty($block['is_active']) ? 'checked' : '' ?> 
                       class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                <label for="is_active" class="ml-2 block text-sm font-medium text-gray-700">Activo / Visible</label>
            </div>
        </div>

        <!-- Block Config raw (JSON) -->
        <details class="group border border-gray-200 rounded-lg">
            <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-xs font-medium text-gray-700 hover:bg-gray-50 rounded-lg select-none">
                <span>Configuración Avanzada (JSON)</span>
                <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 pt-2 border-t border-gray-100">
                <textarea name="block_config" 
                          rows="3" 
                          class="font-mono text-xs block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500" 
                          placeholder='{"css_class": "my-custom-class"}'><?= esc(!empty($block['block_config']) ? json_encode($block['block_config']) : '') ?></textarea>
            </div>
        </details>

        <!-- Language Translation Tabs -->
        <div class="border-t border-gray-100 pt-6" x-data="langTabs(<?= $defaultLangId ?>)">
            <h4 class="text-sm font-semibold text-gray-800 mb-3">Campos de Contenido</h4>

            <!-- Tab bar -->
            <div class="flex border-b border-gray-200 mb-4" role="tablist">
                <?php foreach ($languages as $lang): ?>
                    <button type="button" 
                            role="tab" 
                            @click="setTab(<?= (int)$lang['id'] ?>)"
                            :aria-selected="isActive(<?= (int)$lang['id'] ?>)"
                            :class="isActive(<?= (int)$lang['id'] ?>) ? 'border-brand-600 text-brand-700 bg-brand-50/40' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                        <?= esc(strtoupper($lang['code'])) ?>
                        <?php if (!empty($lang['is_default'])): ?>
                            <span class="ml-1 text-brand-400">★</span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Tab panels -->
            <?php foreach ($languages as $index => $lang): ?>
                <?php
                    $transValue = [];
                    if (!empty($block['translations']) && is_array($block['translations'])) {
                        foreach ($block['translations'] as $t) {
                            if (is_array($t) && (int)($t['language_id'] ?? 0) === (int)$lang['id']) {
                                $transValue = $t;
                                break;
                            }
                        }
                    }
                ?>
                <div x-show="isActive(<?= (int)$lang['id'] ?>)" class="space-y-4">
                    <input type="hidden" name="translations[<?= $index ?>][language_id]" value="<?= esc($lang['id']) ?>">
                    <input type="hidden" name="translations[<?= $index ?>][is_published]" value="1">

                    <!-- Render dynamic fields defined in the schema -->
                    <?php foreach ($fields as $fieldKey => $field): ?>
                        <?php 
                            $val = $transValue['block_data'][$fieldKey] ?? ''; 
                            $fieldType = $field['type'] ?? 'string';
                            $label = $field['label'] ?? $fieldKey;
                            $required = !empty($field['required']) && !empty($lang['is_default']);
                        ?>
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-gray-700"><?= esc($label) ?></label>
                            
                            <?php if (in_array($fieldType, ['text', 'wysiwyg', 'textarea'])): ?>
                                <textarea name="translations[<?= $index ?>][block_data][<?= esc($fieldKey) ?>]"
                                          rows="4"
                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"
                                          <?= $required ? 'required' : '' ?>><?= esc($val) ?></textarea>
                            
                            <?php elseif (in_array($fieldType, ['integer', 'int'])): ?>
                                <input type="number" 
                                       name="translations[<?= $index ?>][block_data][<?= esc($fieldKey) ?>]"
                                       value="<?= esc($val) ?>"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"
                                       <?= $required ? 'required' : '' ?> />
                            
                            <?php else: ?>
                                <input type="text" 
                                       name="translations[<?= $index ?>][block_data][<?= esc($fieldKey) ?>]"
                                       value="<?= esc($val) ?>"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"
                                       <?= $required ? 'required' : '' ?> />
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
            <button type="submit" class="<?= esc(action_button_class('primary')) ?>">Actualizar Bloque</button>
            <a href="<?= route_to('admin.cms.pages.blocks', (string)$page['id']) ?>" class="<?= esc(action_button_class()) ?>">Cancelar</a>
        </div>
    </form>
</section>

<script>
function langTabs(defaultLangId) {
    return {
        activeTabId: defaultLangId,
        setTab(id) {
            this.activeTabId = id;
        },
        isActive(id) {
            return this.activeTabId === id;
        }
    }
}
</script>
