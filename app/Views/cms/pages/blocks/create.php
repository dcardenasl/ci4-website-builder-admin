<?php $page = $page ?? []; $blockTypes = $blockTypes ?? []; $languages = $languages ?? []; ?>
<div class="mb-4">
    <a href="<?= route_to('admin.cms.pages.blocks', (string)$page['id']) ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; Volver a Bloques</a>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 max-w-3xl" 
         x-data="blockBuilder(<?= esc(json_encode($blockTypes), 'attr') ?>, <?= esc(json_encode($languages), 'attr') ?>)">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Añadir Bloque de Contenido</h3>

    <form method="post" action="<?= route_to('admin.cms.pages.blocks.store', (string)$page['id']) ?>" class="space-y-6">
        <?= csrf_field() ?>

        <!-- Block Type Selector -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Bloque</label>
            <select name="block_id" 
                    x-model="selectedBlockTypeId" 
                    @change="onBlockTypeChange()" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm">
                <option value="">Selecciona un tipo de bloque...</option>
                <?php foreach ($blockTypes as $type): ?>
                    <option value="<?= esc($type['id']) ?>"><?= esc($type['name']) ?> (<?= esc($type['block_key']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div x-show="selectedBlockTypeId" x-cloak class="space-y-6">
            <!-- Global Options Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                <?= view('components/form/number', [
                    'name' => 'sort_order',
                    'label' => 'Orden',
                    'required' => true,
                    'value' => 0,
                    'help' => 'Posición del bloque en la página.'
                ]) ?>

                <div class="flex items-center pt-8">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked 
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
                              placeholder='{"css_class": "my-custom-class"}'></textarea>
                </div>
            </details>

            <!-- Language Translation Tabs -->
            <div class="border-t border-gray-100 pt-6">
                <h4 class="text-sm font-semibold text-gray-800 mb-3">Campos de Contenido</h4>

                <!-- Tabs header -->
                <div class="flex border-b border-gray-200 mb-4" role="tablist">
                    <template x-for="(lang, index) in languages" :key="lang.id">
                        <button type="button" 
                                role="tab" 
                                @click="activeLangId = lang.id"
                                :aria-selected="activeLangId == lang.id"
                                :class="activeLangId == lang.id ? 'border-brand-600 text-brand-700 bg-brand-50/40' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                            <span x-text="lang.code.toUpperCase()"></span>
                            <span x-show="lang.is_default == 1" class="ml-1 text-brand-400">★</span>
                        </button>
                    </template>
                </div>

                <!-- Tabs contents -->
                <template x-for="(lang, langIndex) in languages" :key="lang.id">
                    <div x-show="activeLangId == lang.id" class="space-y-4">
                        <input type="hidden" :name="`translations[${langIndex}][language_id]` :value="lang.id">
                        <input type="hidden" :name="`translations[${langIndex}][is_published]`" value="1">

                        <!-- Render dynamic fields defined in the schema -->
                        <template x-for="(field, fieldKey) in fields" :key="fieldKey">
                            <div class="space-y-1">
                                <label class="block text-xs font-semibold text-gray-700" x-text="field.label || fieldKey"></label>
                                
                                <!-- Textarea fields -->
                                <template x-if="field.type === 'text' || field.type === 'wysiwyg' || field.type === 'textarea'">
                                    <textarea :name="`translations[${langIndex}][block_data][${fieldKey}]`"
                                              rows="4"
                                              class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"
                                              :required="field.required"></textarea>
                                </template>

                                <!-- Number / Integer fields -->
                                <template x-if="field.type === 'integer' || field.type === 'int'">
                                    <input type="number" 
                                           :name="`translations[${langIndex}][block_data][${fieldKey}]`"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"
                                           :required="field.required" />
                                </template>

                                <!-- Default string/text input -->
                                <template x-if="field.type !== 'text' && field.type !== 'wysiwyg' && field.type !== 'textarea' && field.type !== 'integer' && field.type !== 'int'">
                                    <input type="text" 
                                           :name="`translations[${langIndex}][block_data][${fieldKey}]`"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm"
                                           :required="field.required" />
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="<?= esc(action_button_class('primary')) ?>">Añadir Bloque</button>
                <a href="<?= route_to('admin.cms.pages.blocks', (string)$page['id']) ?>" class="<?= esc(action_button_class()) ?>">Cancelar</a>
            </div>
        </div>
    </form>
</section>

<script>
function blockBuilder(blockTypes, languages) {
    return {
        blockTypes: blockTypes,
        languages: languages,
        selectedBlockTypeId: '',
        activeLangId: '',
        fields: {},
        init() {
            if (this.languages.length > 0) {
                // Set default active language
                const def = this.languages.find(l => l.is_default == 1);
                this.activeLangId = def ? def.id : this.languages[0].id;
            }
        },
        onBlockTypeChange() {
            this.fields = {};
            if (!this.selectedBlockTypeId) return;
            const type = this.blockTypes.find(t => t.id == this.selectedBlockTypeId);
            if (!type || !type.schema_definition) return;
            
            try {
                const schema = typeof type.schema_definition === 'string' 
                    ? json_decode_fallback(type.schema_definition)
                    : type.schema_definition;
                this.fields = schema.fields || {};
            } catch (e) {
                console.error("Failed to parse schema definition", e);
            }
        }
    }
}

function json_decode_fallback(str) {
    try {
        return JSON.parse(str);
    } catch(e) {
        return {};
    }
}
</script>
