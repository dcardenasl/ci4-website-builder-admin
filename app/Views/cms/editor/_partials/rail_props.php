<?php declare(strict_types=1); ?>
<aside id="canvas-panel-properties" class="editor-properties w-full flex-none flex-col border-l border-gray-200 bg-white lg:w-80"
       :class="mobilePanel === 'properties' ? 'flex' : 'hidden lg:flex'">
    <div class="editor-properties__header">
        <div class="flex items-center justify-between gap-2">
            <h2 class="editor-properties__eyebrow" x-text="t('properties')"></h2>
            <span x-show="selected" class="editor-properties__locale" x-text="activeLocale.toUpperCase()"></span>
        </div>
    </div>

    <div class="min-h-0 flex-1 overflow-y-auto px-4 pb-6 pt-4">
        <p x-show="!selected" class="editor-properties__empty"
           x-text="blocks.length ? t('selectBlock') : t('noBlocksHint')"></p>

        <template x-if="selected">
            <div>
                <div class="editor-properties__selected">
                    <p class="editor-properties__selected-name" x-text="blockName(selected)"></p>
                    <p class="editor-properties__selected-key" x-text="selected.block_key"></p>
                </div>

                <template x-for="group in fieldGroups" :key="group.key">
                    <section class="editor-properties__group" x-show="group.fields.length">
                        <h3 class="editor-properties__group-title" x-text="group.label"></h3>

                        <template x-for="field in group.fields" :key="field.identity">
                            <div class="editor-properties__field">
                                <template x-if="field.control !== 'boolean'">
                                    <div class="editor-properties__label-row">
                                        <label class="editor-properties__label" :for="'f-' + field.identity" x-text="field.label"></label>
                                        <button type="button" x-show="field.canCopyFallback"
                                                class="editor-properties__copy"
                                                :aria-label="copyLabel"
                                                x-text="copyLabel" @click="copyFallback(field)"></button>
                                    </div>
                                </template>
                                <p x-show="field.canCopyFallback" class="editor-properties__hint"
                                   :id="'h-' + field.identity" x-text="t('untranslated')"></p>

                                <?= view('cms/editor/_partials/richtext_field') ?>
                                <template x-if="field.control === 'textarea'">
                                    <textarea :id="'f-' + field.identity" rows="3" class="form-input editor-properties__control"
                                              :aria-describedby="field.canCopyFallback ? 'h-' + field.identity : null"
                                              :value="field.value" @input="edit(field, $event.target.value)"></textarea>
                                </template>
                                <template x-if="field.control === 'text'">
                                    <input :id="'f-' + field.identity" type="text" class="form-input editor-properties__control"
                                           :aria-describedby="field.canCopyFallback ? 'h-' + field.identity : null"
                                           :value="field.value" @input="edit(field, $event.target.value)">
                                </template>
                                <template x-if="field.control === 'number'">
                                    <input :id="'f-' + field.identity" type="number" class="form-input editor-properties__control"
                                           :aria-describedby="field.canCopyFallback ? 'h-' + field.identity : null"
                                           :value="field.value" @input="edit(field, $event.target.value === '' ? null : Number($event.target.value))">
                                </template>
                                <template x-if="field.control === 'boolean'">
                                    <label class="editor-properties__boolean" :for="'f-' + field.identity">
                                        <span class="editor-properties__label" x-text="field.label"></span>
                                        <input :id="'f-' + field.identity" type="checkbox" class="editor-properties__checkbox"
                                               :checked="field.value === true" @change="edit(field, $event.target.checked)">
                                    </label>
                                </template>
                                <template x-if="field.control === 'select'">
                                    <select :id="'f-' + field.identity" class="form-input editor-properties__control"
                                            :aria-describedby="field.canCopyFallback ? 'h-' + field.identity : null"
                                            @change="edit(field, $event.target.value)">
                                        <template x-for="option in field.options" :key="option">
                                            <option :value="option" :selected="option === field.value" x-text="option"></option>
                                        </template>
                                    </select>
                                </template>
                                <template x-if="field.control === 'color'">
                                    <div class="editor-properties__color">
                                        <input :id="'f-' + field.identity" type="color" class="form-input w-14"
                                               :value="swatchColor(field.value)" @input="edit(field, $event.target.value)">
                                        <span class="editor-properties__color-value" x-text="field.value"></span>
                                    </div>
                                </template>
                                <template x-if="field.control === 'media'">
                                    <div class="editor-properties__media">
                                        <button type="button" class="btn-secondary editor-properties__media-button text-xs" @click="pickMedia(field)"
                                                x-text="mediaLabel(field) ? mediaLabel(field) : t('selectMedia')"></button>
                                        <button type="button" x-show="mediaLabel(field)" class="editor-properties__media-remove"
                                                :aria-label="t('removeMedia')" @click="clearMedia(field)">&times;</button>
                                    </div>
                                </template>
                                <template x-if="field.control === 'unsupported'">
                                    <div class="editor-properties__unsupported">
                                        <p x-text="t('unsupportedField')"></p>
                                        <a :href="endpoints.classic" class="mt-1 inline-block text-[11px] font-bold text-brand-600 hover:underline"
                                           x-text="t('classicEditor')"></a>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </section>
                </template>
            </div>
        </template>
    </div>
</aside>
