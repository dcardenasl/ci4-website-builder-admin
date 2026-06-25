<?php
/**
 * Reusable block_template JSON editor partial.
 *
 * Variables:
 *   $value   string  Current JSON value (empty = no template)
 *   $errors  array   Validation errors keyed by field name
 */
$value  = $value ?? '';
$errors = $errors ?? [];
$hasError = !empty($errors['block_template']);
?>
<div x-data="blockTemplateEditor(<?= esc(json_encode($value), 'attr') ?>)" class="space-y-3">

    <textarea
        name="block_template"
        x-ref="editor"
        x-model="raw"
        @input="validate()"
        rows="10"
        :class="error ? 'border-red-300 focus:ring-red-500' : (valid ? 'border-green-300' : 'border-gray-300')"
        class="w-full font-mono text-xs leading-relaxed p-3 rounded-lg border bg-white focus:outline-none focus:ring-1 transition-colors resize-y"
        placeholder='{"version":"1.0","blocks":[{"block_key":"hero","sort_order":1,"required":true,"locked":false}]}'
    ><?= esc($value) ?></textarea>

    <!-- Server-side error -->
    <?php if ($hasError): ?>
        <p class="text-xs text-red-600"><?= esc($errors['block_template']) ?></p>
    <?php endif; ?>

    <!-- Client-side feedback -->
    <div x-show="error" x-cloak class="flex items-start gap-2 p-3 bg-red-50 border border-red-200 rounded-lg">
        <svg class="h-4 w-4 text-red-500 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
            <p class="text-xs font-medium text-red-900"><?= esc(lang('Collections.block_template_invalid_json')) ?></p>
            <p class="text-xs text-red-700 mt-0.5" x-text="error"></p>
        </div>
    </div>

    <div x-show="valid && !error" x-cloak class="flex items-center gap-2 p-2.5 bg-green-50 border border-green-200 rounded-lg">
        <svg class="h-4 w-4 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        <p class="text-xs text-green-800">
            <?= esc(lang('Collections.block_template_valid')) ?>
            (<span x-text="blockCount"></span> <?= esc(lang('Collections.block_template_blocks')) ?>)
        </p>
    </div>

    <div x-show="!raw || raw.trim() === ''" x-cloak class="text-xs text-gray-400 italic">
        <?= esc(lang('Collections.block_template_empty_hint')) ?>
    </div>

    <!-- Reference -->
    <details class="text-xs">
        <summary class="cursor-pointer text-brand-600 hover:text-brand-700 select-none"><?= esc(lang('Collections.block_template_see_example')) ?></summary>
        <div class="mt-2 rounded-lg border border-gray-200 bg-gray-900 text-gray-100 p-3 overflow-x-auto">
<pre class="text-xs leading-relaxed">{
  "version": "1.0",
  "blocks": [
    {
      "block_key": "hero",
      "label": "<?= esc(lang('Collections.block_template_example_hero')) ?>",
      "sort_order": 1,
      "required": true,
      "locked": false,
      "help_text": "<?= esc(lang('Collections.block_template_example_hero_help')) ?>",
      "block_config_defaults": {}
    },
    {
      "block_key": "rich_text",
      "label": "<?= esc(lang('Collections.block_template_example_body')) ?>",
      "sort_order": 2,
      "required": true,
      "locked": true
    }
  ]
}</pre>
        </div>
        <p class="mt-1.5 text-gray-500"><?= esc(lang('Collections.block_template_field_reference')) ?></p>
        <ul class="mt-1 ml-4 list-disc space-y-0.5 text-gray-500">
            <li><strong>block_key</strong> — <?= esc(lang('Collections.block_template_ref_block_key')) ?></li>
            <li><strong>sort_order</strong> — <?= esc(lang('Collections.block_template_ref_sort_order')) ?></li>
            <li><strong>required</strong> — <?= esc(lang('Collections.block_template_ref_required')) ?></li>
            <li><strong>locked</strong> — <?= esc(lang('Collections.block_template_ref_locked')) ?></li>
            <li><strong>block_config_defaults</strong> — <?= esc(lang('Collections.block_template_ref_defaults')) ?></li>
        </ul>
    </details>
</div>

<script>
function blockTemplateEditor(initial) {
    return {
        raw: initial || '',
        error: '',
        valid: false,
        blockCount: 0,

        init() {
            if (this.raw.trim()) {
                this.validate();
            }
        },

        validate() {
            const input = this.raw.trim();
            this.error  = '';
            this.valid  = false;
            this.blockCount = 0;

            if (!input) {
                return;
            }

            let data;
            try {
                data = JSON.parse(input);
            } catch (e) {
                this.error = e.message;
                return;
            }

            if (!data || typeof data !== 'object') {
                this.error = 'Must be a JSON object';
                return;
            }

            if (data.version !== '1.0') {
                this.error = 'version must be "1.0"';
                return;
            }

            if (!Array.isArray(data.blocks)) {
                this.error = 'blocks must be an array';
                return;
            }

            if (data.blocks.length === 0) {
                this.error = 'blocks must have at least one item';
                return;
            }

            const sortOrders = [];
            for (let i = 0; i < data.blocks.length; i++) {
                const block = data.blocks[i];

                if (!block.block_key || typeof block.block_key !== 'string') {
                    this.error = `Block ${i}: block_key is required`;
                    return;
                }

                if (!/^[a-z][a-z0-9_]*$/.test(block.block_key)) {
                    this.error = `Block ${i}: block_key must match ^[a-z][a-z0-9_]*$`;
                    return;
                }

                if (typeof block.sort_order !== 'number' || !Number.isInteger(block.sort_order) || block.sort_order < 1) {
                    this.error = `Block ${i}: sort_order must be a positive integer`;
                    return;
                }

                if (sortOrders.includes(block.sort_order)) {
                    this.error = `Duplicate sort_order ${block.sort_order}`;
                    return;
                }

                sortOrders.push(block.sort_order);
            }

            this.valid      = true;
            this.blockCount = data.blocks.length;
        }
    };
}
</script>
