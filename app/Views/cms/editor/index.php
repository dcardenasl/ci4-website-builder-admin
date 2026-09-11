<?php declare(strict_types=1); ?>
<?php $bootJson = json_encode($boot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR); ?>
<div id="editor-canvas" class="flex h-dvh flex-col" data-editor-owner-type="<?= esc((string) ($boot['owner']['type'] ?? ''), 'attr') ?>" data-editor-owner-id="<?= esc((string) ($boot['owner']['id'] ?? ''), 'attr') ?>">
    <header class="flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-600"><?= esc(lang('EditorUi.title')) ?></p>
            <h1 class="text-lg font-semibold text-gray-900"><?= esc((string) ($boot['document']['owner']['title'] ?? lang('EditorUi.title'))) ?></h1>
        </div>
        <a href="<?= esc((string) ($boot['endpoints']['back'] ?? route_to('admin.cms.editor')), 'attr') ?>" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"><?= esc(lang('EditorUi.back')) ?></a>
    </header>
    <main class="flex min-h-0 flex-1 items-center justify-center p-6" data-editor-shell>
        <div class="w-full max-w-3xl rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center shadow-sm">
            <p class="text-sm text-gray-500"><?= esc(lang('EditorUi.canvasReady')) ?></p>
            <p class="mt-2 text-xs text-gray-400"><?= esc(lang('EditorUi.canvasModulesPending')) ?></p>
        </div>
    </main>
</div>
<script <?= csp_script_nonce() ?>>window.__canvasBoot = <?= $bootJson ?>;</script>
