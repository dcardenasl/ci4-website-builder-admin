<?php declare(strict_types=1); ?>
<main id="canvas-panel-preview" class="min-w-0 flex-1 justify-center overflow-y-auto bg-gray-200 p-3 lg:p-6"
      :class="mobilePanel === 'preview' ? 'flex' : 'hidden lg:flex'">
    <div x-show="!previewAuthorized" class="m-auto max-w-md rounded-lg bg-white p-6 text-center text-sm text-gray-600">
        <p x-text="t('previewUnavailable')"></p>
    </div>

    <div x-show="previewAuthorized" class="w-full transition-all duration-300"
         :class="viewport === 'mobile' ? 'max-w-sm' : 'max-w-5xl'">
        <?php /* A named target: the draft is delivered by a real form POST, so the
                 iframe loads a same-origin document instead of injected markup. */ ?>
        <form method="post" :action="previewUrl" target="editorCanvasFrame" x-ref="previewForm" class="hidden" data-no-submit-guard="1">
            <input type="hidden" name="owner_type" :value="owner.type">
            <input type="hidden" name="owner_id" :value="owner.id">
            <input type="hidden" name="expires" :value="preview.expires">
            <input type="hidden" name="sig" :value="preview.sig">
            <input type="hidden" name="channel" :value="preview.channel">
            <input type="hidden" name="payload" x-ref="previewPayload">
        </form>

        <iframe name="editorCanvasFrame" x-ref="previewFrame" title="<?= esc(lang('EditorUi.title')) ?>"
                class="h-[calc(100vh-7rem)] w-full rounded-xl border border-gray-300 bg-white shadow-lg"
                x-bind:sandbox="previewSandbox"></iframe>
    </div>
</main>
