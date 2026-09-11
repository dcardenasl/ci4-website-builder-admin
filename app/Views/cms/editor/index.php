<?php

declare(strict_types=1);

/** @var array<string, mixed> $boot */
$bootJson = json_encode($boot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
?>
<div id="editor-canvas" class="flex h-dvh flex-col" x-data="canvasEditor()" data-no-submit-guard data-editor-owner-type="<?= esc((string) ($boot['owner']['type'] ?? ''), 'attr') ?>" data-editor-owner-id="<?= esc((string) ($boot['owner']['id'] ?? ''), 'attr') ?>" x-cloak>
    <?= $this->include('cms/editor/_partials/topbar') ?>

    <?= $this->include('cms/editor/_partials/mobile_navigation') ?>

    <div class="flex min-h-0 flex-1">
        <?= $this->include('cms/editor/_partials/rail_blocks') ?>
        <?= $this->include('cms/editor/_partials/canvas_frame') ?>
        <?= $this->include('cms/editor/_partials/rail_props') ?>
    </div>

    <?= $this->include('cms/editor/_partials/undo_toast') ?>
</div>

<script <?= csp_script_nonce() ?>>
    window.__canvasBoot = <?= $bootJson ?>;
</script>
