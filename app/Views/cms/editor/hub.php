<?php declare(strict_types=1); ?>
<div class="mx-auto max-w-6xl space-y-8">
    <header>
        <h1 class="text-2xl font-bold text-gray-900"><?= esc(lang('EditorUi.title')) ?></h1>
        <p class="mt-2 text-sm text-gray-500"><?= esc(lang('EditorUi.hubLead')) ?></p>
    </header>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <?php if ($canEditPages): ?>
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900"><?= esc(lang('EditorUi.pages')) ?></h2>
                <ul class="mt-4 divide-y divide-gray-100">
                    <?php foreach ($pages as $item): ?>
                        <li><a class="block rounded px-2 py-3 hover:bg-gray-50" href="<?= esc(route_to('admin.cms.editor.pages', (string) $item['id']), 'attr') ?>"><span class="font-medium text-gray-800"><?= esc($item['title']) ?></span><span class="ml-2 text-xs text-gray-500"><?= esc($item['meta']) ?></span></a></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>
        <?php if ($canEditEntries): ?>
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900"><?= esc(lang('EditorUi.entries')) ?></h2>
                <ul class="mt-4 divide-y divide-gray-100">
                    <?php foreach ($entries as $item): ?>
                        <li><a class="block rounded px-2 py-3 hover:bg-gray-50" href="<?= esc(route_to('admin.cms.editor.entries', (string) $item['id']), 'attr') ?>"><span class="font-medium text-gray-800"><?= esc($item['title']) ?></span><span class="ml-2 text-xs text-gray-500"><?= esc($item['meta']) ?></span></a></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>
    </div>
</div>
