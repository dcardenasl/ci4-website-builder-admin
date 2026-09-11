<?php declare(strict_types=1); ?>
<template x-if="field.control === 'richtext'">
    <div x-data="richTextEditor(field.value, '', {
             label: field.label,
             linkUrlPrompt: <?= esc(json_encode(lang('Labels.link_url_prompt'), JSON_THROW_ON_ERROR), 'attr') ?>,
             placeholder: <?= esc(json_encode(lang('Labels.rich_text_placeholder'), JSON_THROW_ON_ERROR), 'attr') ?>,
             onChange: value => edit(field, value)
         })"
         x-init="$watch('field.value', value => applyContent(value))"
         class="editor-properties__richtext overflow-hidden rounded-lg border border-gray-300 bg-white focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500">
        <?= view('partials/richtext_toolbar') ?>
        <div x-ref="editorEl" :id="'f-' + field.identity"
             class="richtext-content min-h-[130px] cursor-text px-3 py-2.5 text-sm text-gray-800"></div>
    </div>
</template>
