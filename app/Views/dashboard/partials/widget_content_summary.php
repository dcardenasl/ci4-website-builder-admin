<?php
/**
 * @var list<array{label: string, count: int, url: string, icon: string}> $items
 */
?>
<?php if (empty($items)): ?>
    <p class="text-sm text-gray-500 text-center py-4"><?= esc(lang('Dashboard.no_content_visible')) ?></p>
<?php else: ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        <?php foreach ($items as $item): ?>
            <a href="<?= esc($item['url']) ?>" class="flex flex-col items-center justify-center gap-1 rounded-lg border border-gray-200 p-4 text-center hover:bg-gray-50 hover:border-brand-200 transition-colors">
                <?= ui_icon($item['icon'], 'h-5 w-5 text-brand-500') ?>
                <span class="text-xl font-bold text-gray-900"><?= esc((string) $item['count']) ?></span>
                <span class="text-xs font-medium text-gray-500"><?= esc($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
