<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$quote = $data['quote'] ?? '';
$author = $data['author'] ?? '';
$role = $data['role'] ?? '';
$avatar = $data['avatar_url'] ?? '';
$rating = (int) ($data['rating'] ?? 5);
?>
<div class="border border-slate-200 bg-white rounded-lg p-3">
    <div class="text-[10px] font-bold text-violet-500 uppercase mb-1">Tarjeta de Slider</div>
    <div class="flex gap-0.5 text-amber-400 text-[9px] mb-2">
        <?php for ($i = 0; $i < 5; $i++): ?>
            <span><?= $i < $rating ? '★' : '☆' ?></span>
        <?php endfor; ?>
    </div>
    <p class="text-[10px] italic text-slate-600 line-clamp-3">
        <?= $quote !== '' ? '"' . esc($quote) . '"' : 'Sin contenido.' ?>
    </p>
    <div class="flex items-center gap-2 mt-3 pt-2 border-t border-slate-50">
        <?php if ($avatar !== ''): ?>
            <img src="<?= esc($avatar) ?>" class="h-6 w-6 rounded-full object-cover" />
        <?php else: ?>
            <div class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center text-[9px] font-bold text-slate-500">
                <?= $author !== '' ? esc(substr($author, 0, 1)) : 'T' ?>
            </div>
        <?php endif; ?>
        <div class="min-w-0">
            <cite class="not-italic text-[10px] font-bold text-slate-800 block truncate"><?= $author !== '' ? esc($author) : 'Autor Desconocido' ?></cite>
            <?php if ($role !== ''): ?>
                <span class="text-[8px] text-slate-400 block truncate"><?= esc($role) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>
