<?php
/**
 * Color Picker Component
 *
 * Renders an interactive color picker with preset palette and custom input.
 * Uses Alpine.js for interactivity.
 *
 * @var string $inputName The name attribute for the input element
 * @var string $inputValue The current color value (hex, rgb, etc)
 * @var bool $required Whether the field is required
 * @var string $placeholder Placeholder text for manual input
 */
$inputName = $inputName ?? '';
$inputValue = $inputValue ?? '';
$required = $required ?? false;
$placeholder = $placeholder ?? '#ffffff o rgb(...)';
$presets = [
    ['hex' => '', 'name' => 'Transparente'],
    ['hex' => '#ffffff', 'name' => 'Blanco'],
    ['hex' => '#000000', 'name' => 'Negro'],
    ['hex' => '#3b82f6', 'name' => 'Azul'],
    ['hex' => '#10b981', 'name' => 'Verde'],
    ['hex' => '#ef4444', 'name' => 'Rojo'],
    ['hex' => '#f59e0b', 'name' => 'Naranja'],
    ['hex' => '#8b5cf6', 'name' => 'Violeta'],
    ['hex' => '#6b7280', 'name' => 'Gris'],
    ['hex' => '#f3f4f6', 'name' => 'Gris Claro'],
    ['hex' => '#1e3a8a', 'name' => 'Azul Oscuro'],
    ['hex' => '#065f46', 'name' => 'Verde Oscuro'],
    ['hex' => '#991b1b', 'name' => 'Rojo Oscuro'],
];
?>

<div x-data="{
    value: '<?= esc($inputValue, 'attr') ?>',
    open: false,
    presets: <?= json_encode($presets) ?>
}" @click.outside="open = false" class="relative">
    <div class="mt-1 flex items-center gap-2">
        <!-- Color swatch button -->
        <button
            type="button"
            @click="open = !open"
            class="h-10 w-10 shrink-0 rounded-lg border border-gray-300 shadow-sm transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-brand-500"
            :style="value ? `background-color: ${value}` : 'background-image: linear-gradient(45deg, #ccc 25%, transparent 25%), linear-gradient(-45deg, #ccc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #ccc 75%), linear-gradient(-45deg, transparent 75%, #ccc 75%); background-size: 8px 8px; background-position: 0 0, 0 4px, 4px -4px, -4px 0px; background-color: #fff;'"
            aria-label="Open color picker"
        ></button>

        <!-- Text input -->
        <div class="flex-1 relative">
            <input
                type="text"
                name="<?= esc($inputName, 'attr') ?>"
                x-model="value"
                placeholder="<?= esc($placeholder, 'attr') ?>"
                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-mono text-gray-900 uppercase shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 pl-3 pr-10"
                <?php if ($required): ?>required<?php endif; ?>
            >
            <!-- Dropdown toggle -->
            <button
                type="button"
                @click="open = !open"
                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600"
                aria-label="Toggle color preset palette"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Color picker dropdown -->
    <div
        x-show="open"
        class="absolute left-0 z-50 mt-2 p-3 bg-white border border-gray-200 rounded-xl shadow-xl w-64 max-w-sm"
        x-cloak
    >
        <!-- Preset label -->
        <span class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">Paleta Predefinida</span>

        <!-- Preset grid -->
        <div class="grid grid-cols-5 gap-2 mb-3">
            <template x-for="p in presets" :key="p.hex">
                <button
                    type="button"
                    @click="value = p.hex; open = false"
                    :title="p.name"
                    class="h-8 w-8 rounded-lg border border-gray-200 shadow-sm transition hover:scale-110 focus:outline-none focus:ring-2 focus:ring-brand-500"
                    :class="value === p.hex ? 'ring-2 ring-brand-500 scale-105 border-brand-500' : ''"
                    :style="p.hex ? `background-color: ${p.hex}` : 'background-image: linear-gradient(45deg, #ccc 25%, transparent 25%), linear-gradient(-45deg, #ccc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #ccc 75%), linear-gradient(-45deg, transparent 75%, #ccc 75%); background-size: 8px 8px; background-position: 0 0, 0 4px, 4px -4px, -4px 0px; background-color: #fff;'"
                ></button>
            </template>
        </div>

        <!-- Custom color picker -->
        <div class="border-t border-gray-100 pt-3 flex items-center justify-between gap-2">
            <span class="text-xs text-gray-500">Personalizado:</span>
            <input
                type="color"
                x-model="value"
                class="h-8 w-8 cursor-pointer rounded border border-gray-200 p-0 bg-transparent"
                aria-label="Custom color picker"
            >
        </div>
    </div>
</div>
