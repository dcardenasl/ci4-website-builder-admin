<?php
/**
 * @var list<array{label: string, count: int, url: string, icon: string}> $items
 */
?>
<?php if (empty($items)): ?>
    <div class="text-center py-6">
        <?= ui_icon('circle-check', 'h-8 w-8 mx-auto mb-2 text-green-400') ?>
        <p class="text-sm text-gray-500"><?= esc(lang('Dashboard.all_clear')) ?></p>
    </div>
<?php else: ?>
    <ul class="divide-y divide-gray-100">
        <?php foreach ($items as $item): ?>
            <li>
                <a href="<?= esc($item['url']) ?>" class="flex items-center justify-between gap-3 py-3 group">
                    <span class="flex items-center gap-2 text-sm text-gray-700 group-hover:text-brand-700">
                        <?= ui_icon($item['icon'], 'h-4 w-4 text-gray-400') ?>
                        <?= esc($item['label']) ?>
                    </span>
                    <span class="inline-flex items-center justify-center min-w-[1.5rem] px-1.5 py-0.5 rounded-full bg-red-50 text-red-700 text-xs font-bold">
                        <?= esc((string) $item['count']) ?>
                    </span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
