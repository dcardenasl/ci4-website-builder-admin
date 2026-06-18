<?php
/**
 * Schema field row partial — used in both create and edit BlockType forms.
 * Requires Alpine.js context with `schemaFields` / `configFields` arrays.
 * @var string $section  'content' | 'config'
 */
$isConfig = ($section ?? 'content') === 'config';
$arrayVar = $isConfig ? 'configFields' : 'schemaFields';
$sectionKey = $isConfig ? 'config' : 'content';
?>
<div class="flex items-start gap-2 p-3 border border-gray-200 rounded-lg bg-gray-50 hover:bg-white transition-colors">

    <!-- Key -->
    <div class="flex-1 min-w-0">
        <label class="block text-[10px] font-medium text-gray-500 mb-1">Clave</label>
        <input type="text"
               x-model="field.key"
               @input="rebuildJson()"
               placeholder="ej: heading"
               class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 font-mono focus:ring-brand-500 focus:border-brand-500">
    </div>

    <!-- Tipo -->
    <div class="w-28 shrink-0">
        <label class="block text-[10px] font-medium text-gray-500 mb-1">Tipo</label>
        <select x-model="field.type"
                @change="rebuildJson()"
                class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-brand-500 focus:border-brand-500">
            <option value="string">string</option>
            <option value="text">text</option>
            <option value="richtext">richtext</option>
            <option value="url">url</option>
            <option value="integer">integer</option>
            <option value="boolean">boolean</option>
            <option value="select">select</option>
        </select>
    </div>

    <!-- Label -->
    <div class="flex-1 min-w-0">
        <label class="block text-[10px] font-medium text-gray-500 mb-1">Etiqueta</label>
        <input type="text"
               x-model="field.label"
               @input="rebuildJson()"
               placeholder="ej: Título"
               class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-brand-500 focus:border-brand-500">
    </div>

    <!-- Opciones (solo select) -->
    <div class="flex-1 min-w-0" x-show="field.type === 'select'">
        <label class="block text-[10px] font-medium text-gray-500 mb-1">Opciones (coma)</label>
        <input type="text"
               x-model="field.options"
               @input="rebuildJson()"
               placeholder="opción1, opción2"
               class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-brand-500 focus:border-brand-500">
    </div>

    <!-- Requerido -->
    <div class="pt-4 shrink-0">
        <label class="flex items-center gap-1 text-[10px] text-gray-500 cursor-pointer">
            <input type="checkbox" x-model="field.required" @change="rebuildJson()" class="rounded border-gray-300 text-brand-600">
            Req.
        </label>
    </div>

    <!-- Eliminar -->
    <button type="button"
            @click="removeField('<?= $sectionKey ?>', index)"
            class="pt-4 shrink-0 text-gray-400 hover:text-red-500 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
        </svg>
    </button>
</div>
