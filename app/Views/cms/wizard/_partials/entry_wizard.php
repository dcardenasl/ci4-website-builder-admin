<?php /* Wizard — Entry creation flow (A screens): collection-select, steps, confirm, success */ ?>

<!-- ── SCREEN: COLLECTION SELECT ── -->
<div x-show="screen === 'collection-select'" x-cloak>
    <h2 class="text-lg font-semibold text-gray-900 mb-4"><?= lang('Wizard.select_collection') ?></h2>
    <div x-show="(config?.collections ?? []).length === 0"
         class="rounded-xl border border-dashed border-gray-200 bg-white py-10 text-center text-sm text-gray-400 shadow-sm">
        <?= lang('Wizard.no_collections') ?>
    </div>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
        <template x-for="col in (config?.collections ?? [])" :key="col.id">
            <button @click="selectCollection(col)"
                    class="flex min-h-[120px] flex-col items-start justify-between gap-3 rounded-xl border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-brand-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="text-2xl" x-text="col.icon || '📄'"></span>
                <span class="space-y-1">
                    <span class="block text-sm font-semibold text-gray-900" x-text="collectionDisplayLabel(col)"></span>
                    <span class="block text-xs text-gray-500 line-clamp-2" x-text="col.description"></span>
                </span>
            </button>
        </template>
    </div>
    <button @click="screen = 'home'" class="mt-4 text-sm text-gray-500 hover:text-gray-700"><?= lang('Wizard.btn_back') ?></button>
</div>

<!-- ── SCREEN: STEPS ── -->
<template x-if="screen === 'steps' && currentStepSchema">
    <div>
        <!-- Progress bar -->
        <div class="mb-4">
            <div class="flex items-center justify-between text-xs text-gray-400 mb-1">
                <span x-text="stepLabel()"></span>
                <span x-text="collectionDisplayLabel(selectedCollection)"></span>
            </div>
            <div class="h-2 w-full rounded-full bg-gray-200">
                <div class="h-2 rounded-full bg-brand-600 transition-all"
                     :style="`width: ${Math.round(((currentStep + 1) / totalSteps) * 100)}%`"></div>
            </div>
        </div>

        <!-- Step header -->
        <h2 class="text-lg font-semibold text-gray-900 mb-1" x-text="currentStepSchema.step_title"></h2>
        <p class="text-sm text-gray-500 mb-5" x-text="currentStepSchema.step_hint"></p>

        <!-- Dynamic fields -->
        <template x-for="field in currentStepSchema.fields" :key="field.key">
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1"
                       x-text="field.label + (field.required ? '<?= lang('Wizard.required_suffix') ?>' : '')"></label>

                <template x-if="field.type === 'text'">
                    <input type="text" :placeholder="field.placeholder || ''" x-model="formData[field.key]"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </template>

                <template x-if="field.type === 'textarea'">
                    <textarea :placeholder="field.placeholder || ''" x-model="formData[field.key]" rows="4"
                              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"></textarea>
                </template>

                <template x-if="field.type === 'date'">
                    <input type="date" x-model="formData[field.key]"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </template>

                <template x-if="field.type === 'select'">
                    <select x-model="formData[field.key]"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <template x-for="opt in (field.options || [])" :key="opt.value">
                            <option :value="opt.value" x-text="opt.label"></option>
                        </template>
                    </select>
                </template>

                <template x-if="field.type === 'image'">
                    <div>
                        <template x-if="formData[field.key + '_url']">
                            <div class="relative inline-block">
                                <img :src="formData[field.key + '_url']"
                                     class="rounded-lg max-h-48 object-cover border border-gray-200" />
                                <button type="button"
                                        @click="formData[field.key + '_id'] = null; formData[field.key + '_url'] = null"
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs flex items-center justify-center hover:bg-red-600">✕</button>
                            </div>
                        </template>
                        <template x-if="!formData[field.key + '_url']">
                            <label :class="{'opacity-60': uploading}"
                                   class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 cursor-pointer hover:border-brand-400 hover:bg-brand-50 transition-colors">
                                <span class="text-4xl">📷</span>
                                <span class="text-sm text-gray-500"><?= lang('Wizard.upload_image') ?></span>
                                <span class="text-xs text-gray-400"><?= lang('Wizard.upload_click_hint') ?></span>
                                <span x-show="uploading" class="text-xs text-brand-600"><?= lang('Wizard.upload_uploading') ?></span>
                                <input type="file" accept="image/*" class="hidden"
                                       @change="uploadImage(field, $event.target.files[0])" />
                            </label>
                        </template>
                        <p x-show="uploadError" class="mt-1 text-xs text-red-600" x-text="uploadError"></p>
                    </div>
                </template>

                <template x-if="field.type === 'rich_text'">
                    <textarea :placeholder="field.placeholder || ''" x-model="formData[field.key]" rows="8"
                              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-mono text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"></textarea>
                </template>
            </div>
        </template>

        <!-- Navigation -->
        <div class="flex justify-between mt-6">
            <button @click="prevStep()" class="btn-secondary"><?= lang('Wizard.btn_back') ?></button>
            <button @click="nextStep()"
                    :disabled="!canAdvance()"
                    :class="canAdvance() ? 'btn-primary' : 'btn-primary opacity-50 cursor-not-allowed'">
                <span x-show="currentStep < steps.length - 1"><?= lang('Wizard.btn_next') ?></span>
                <span x-show="currentStep === steps.length - 1"><?= lang('Wizard.btn_review') ?></span>
            </button>
        </div>
    </div>
</template>

<!-- ── SCREEN: CONFIRM ── -->
<div x-show="screen === 'confirm'" x-cloak>
    <h2 class="text-lg font-semibold text-gray-900 mb-4"><?= lang('Wizard.confirm_title') ?></h2>

    <div class="bg-gray-50 rounded-xl border border-gray-200 p-5 space-y-3">
        <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
            <span x-text="selectedCollection?.icon ?? '📄'" class="text-3xl"></span>
            <span class="font-bold text-lg text-gray-800" x-text="collectionDisplayLabel(selectedCollection)"></span>
        </div>

        <template x-for="step in steps" :key="step.step_title">
            <template x-for="field in step.fields" :key="field.key">
                <div x-show="field.type !== 'select' || field.key !== 'status'" class="flex gap-3 text-sm">
                    <span class="text-gray-500 shrink-0 w-32 truncate" x-text="field.label + ':'"></span>
                    <template x-if="field.type === 'image'">
                        <span>
                            <img x-show="formData[field.key + '_url']" :src="formData[field.key + '_url']"
                                 class="rounded max-h-20 object-cover" />
                            <span x-show="!formData[field.key + '_url']"
                                  class="text-gray-300 italic"><?= lang('Wizard.confirm_no_value') ?></span>
                        </span>
                    </template>
                    <template x-if="field.type !== 'image'">
                        <span class="text-gray-800 break-words"
                              x-text="formData[field.key] || '<?= lang('Wizard.confirm_no_value') ?>'"></span>
                    </template>
                </div>
            </template>
        </template>

        <div class="flex gap-3 text-sm pt-3 border-t border-gray-200">
            <span class="text-gray-500 shrink-0 w-32"><?= lang('Wizard.status') ?>:</span>
            <span x-text="formData.status === 'published' ? '<?= lang('Wizard.confirm_status_published') ?>' : '<?= lang('Wizard.confirm_status_draft') ?>'"></span>
        </div>
    </div>

    <p x-show="publishError" class="text-red-600 text-sm mt-3" x-text="publishError"></p>

    <div class="flex gap-3 mt-6">
        <button @click="formData.status = 'draft'; publish()" class="btn-secondary" :disabled="publishing">
            <?= lang('Wizard.btn_draft') ?>
        </button>
        <button @click="formData.status = 'published'; publish()" class="btn-primary" :disabled="publishing">
            <span x-show="!publishing"><?= lang('Wizard.btn_publish') ?></span>
            <span x-show="publishing"><?= lang('Wizard.btn_publishing') ?></span>
        </button>
    </div>

    <button @click="currentStep = steps.length - 1; screen = 'steps'" class="mt-3 text-sm text-gray-500 hover:text-gray-700">
        <?= lang('Wizard.btn_back') ?>
    </button>
</div>

<!-- ── SCREEN: SUCCESS ── -->
<div x-show="screen === 'success'" x-cloak class="rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
    <div class="text-4xl mb-4">✅</div>
    <h2 class="text-lg font-semibold mb-2 text-gray-900"
        x-text="formData.status === 'draft' ? '<?= lang('Wizard.success_title_draft') ?>' : '<?= lang('Wizard.success_title') ?>'"></h2>
    <p class="text-gray-500 mb-6">
        "<span x-text="formData.title"></span>"
        <span x-text="formData.status === 'draft' ? '<?= lang('Wizard.success_subtitle_draft') ?>' : '<?= lang('Wizard.success_subtitle') ?>'"></span>
    </p>
    <div class="flex flex-col gap-3 items-center">
        <a x-show="publishedEntry?.public_url"
           :href="publishedEntry?.public_url ?? '#'" target="_blank" class="btn-primary">
            <?= lang('Wizard.btn_view_site') ?>
        </a>
        <a x-show="publishedEntry?.id"
           :href="'<?= site_url('admin/cms/entries') ?>/' + publishedEntry?.id + '/edit'"
           class="btn-secondary">
            <?= lang('Wizard.btn_edit_entry') ?>
        </a>
        <button @click="restart()" class="text-sm text-brand-600 hover:text-brand-800"><?= lang('Wizard.btn_add_more') ?></button>
    </div>
</div>
