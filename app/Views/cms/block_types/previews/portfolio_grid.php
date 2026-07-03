<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$sectionTitle = $data['section_title'] ?? '';
$collectionKey = $config['collection_key'] ?? 'portafolio';
$itemsLimit = $config['items_limit'] ?? 6;
?>
<div class="border border-dashed border-violet-300 bg-violet-50/20 rounded-xl p-4">
    <div class="flex items-center justify-between mb-3 border-b border-violet-100 pb-2">
        <span class="text-xs font-semibold text-violet-600 uppercase tracking-wider flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-briefcase"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            Grilla de Portafolio
        </span>
        <span class="text-xxs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full font-medium">Colección: <?= esc($collectionKey) ?></span>
    </div>

    <?php if ($sectionTitle !== ''): ?>
        <div class="text-sm font-bold text-slate-800 mb-2"><?= esc($sectionTitle) ?></div>
    <?php endif; ?>

    <!-- Mock projects grid -->
    <div class="grid grid-cols-2 gap-3">
        <div class="border border-slate-200 bg-white rounded-lg p-2.5">
            <div class="aspect-video bg-slate-100 rounded mb-1.5 flex items-center justify-center text-[10px] text-slate-400">Mock Imagen 1</div>
            <div class="text-xs font-bold text-slate-700 truncate">Proyecto de Ejemplo A</div>
            <div class="text-[9px] text-slate-400 mt-0.5 line-clamp-1">Caso de éxito o trabajo de muestra.</div>
        </div>
        <div class="border border-slate-200 bg-white rounded-lg p-2.5">
            <div class="aspect-video bg-slate-100 rounded mb-1.5 flex items-center justify-center text-[10px] text-slate-400">Mock Imagen 2</div>
            <div class="text-xs font-bold text-slate-700 truncate">Proyecto de Ejemplo B</div>
            <div class="text-[9px] text-slate-400 mt-0.5 line-clamp-1">Caso de éxito o trabajo de muestra.</div>
        </div>
    </div>
</div>
