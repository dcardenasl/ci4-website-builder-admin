<?php
/**
 * @var string $title
 * @var string $csrfName
 * @var string $csrfToken
 */
$csrfName  ??= csrf_token();
$csrfToken ??= csrf_hash();
?>
<div class="max-w-2xl mx-auto" x-data="wizard()" x-init="init()">

    <!-- Loading screen -->
    <div x-show="screen === 'loading'" x-cloak class="text-center py-16 text-gray-400">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand-600 mx-auto mb-4"></div>
        <p><?= lang('Wizard.loading') ?></p>
    </div>

    <!-- Error screen -->
    <div x-show="screen === 'error'" x-cloak class="text-center py-16">
        <p class="text-red-600 text-sm mb-4" x-text="errorMsg"></p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <button @click="init()" class="btn-secondary"><?= lang('Wizard.btn_retry') ?></button>
            <button @click="goHome()" class="btn-primary"><?= lang('Wizard.btn_back_panel') ?></button>
        </div>
    </div>

    <!-- Draft banner (shown on home screen only) -->
    <div x-show="screen === 'home' && draft"
         x-cloak
         class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4 flex justify-between items-center">
        <div>
            <p class="font-medium text-sm"><?= lang('Wizard.draft_banner_title') ?></p>
            <p class="text-xs text-gray-500" x-text="draft ? new Date(draft.savedAt).toLocaleString() : ''"></p>
        </div>
        <div class="flex gap-2">
            <button @click="resumeDraft()" class="btn-primary text-sm"><?= lang('Wizard.draft_continue') ?></button>
            <button @click="discardDraft()" class="btn-secondary text-sm"><?= lang('Wizard.draft_discard') ?></button>
        </div>
    </div>

    <!-- ── SCREEN: HOME ── -->
    <div x-show="screen === 'home'" x-cloak>
        <h1 class="text-2xl font-bold mb-6"><?= lang('Wizard.home_heading') ?></h1>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <button @click="goAddContent()"
                    class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-white p-6 text-center hover:border-brand-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="text-4xl">📝</span>
                <span class="font-semibold text-gray-800"><?= lang('Wizard.add_content') ?></span>
                <span class="text-xs text-gray-500" x-text="addContentDesc()"></span>
            </button>
            <button @click="goEditPage()"
                    class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-white p-6 text-center hover:border-brand-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="text-4xl">✏️</span>
                <span class="font-semibold text-gray-800"><?= lang('Wizard.edit_page') ?></span>
                <span class="text-xs text-gray-500"><?= lang('Wizard.edit_page_desc') ?></span>
            </button>
            <button @click="goEditMenu()"
                    class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-white p-6 text-center hover:border-brand-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="text-4xl">🔗</span>
                <span class="font-semibold text-gray-800"><?= lang('Wizard.edit_menu') ?></span>
                <span class="text-xs text-gray-500"><?= lang('Wizard.edit_menu_desc') ?></span>
            </button>
        </div>
    </div>

    <!-- ── SCREEN: COLLECTION SELECT ── -->
    <div x-show="screen === 'collection-select'" x-cloak>
        <h2 class="text-xl font-bold mb-4"><?= lang('Wizard.select_collection') ?></h2>
        <div x-show="(config?.collections ?? []).length === 0"
             class="text-gray-400 text-sm py-8 text-center">
            <?= lang('Wizard.no_collections') ?>
        </div>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
            <template x-for="col in (config?.collections ?? [])" :key="col.id">
                <button @click="selectCollection(col)"
                        class="flex flex-col items-center justify-center gap-1 rounded-xl border-2 border-gray-200 bg-white p-5 text-center hover:border-brand-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <span class="text-3xl" x-text="col.icon || '📄'"></span>
                    <span class="font-semibold text-sm text-gray-800" x-text="col.name"></span>
                    <span class="text-xs text-gray-400 line-clamp-2" x-text="col.description"></span>
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
                    <span x-text="selectedCollection?.name ?? ''"></span>
                </div>
                <div class="h-2 w-full rounded-full bg-gray-200">
                    <div class="h-2 rounded-full bg-brand-600 transition-all"
                         :style="`width: ${Math.round(((currentStep + 1) / totalSteps) * 100)}%`"></div>
                </div>
            </div>

            <!-- Step header -->
            <h2 class="text-xl font-bold mb-1" x-text="currentStepSchema.step_title"></h2>
            <p class="text-sm text-gray-500 mb-5" x-text="currentStepSchema.step_hint"></p>

            <!-- Dynamic fields -->
            <template x-for="field in currentStepSchema.fields" :key="field.key">
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                           x-text="field.label + (field.required ? '<?= lang('Wizard.required_suffix') ?>' : '')"></label>

                    <!-- text -->
                    <template x-if="field.type === 'text'">
                        <input type="text"
                               :placeholder="field.placeholder || ''"
                               x-model="formData[field.key]"
                               class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                    </template>

                    <!-- textarea -->
                    <template x-if="field.type === 'textarea'">
                        <textarea :placeholder="field.placeholder || ''"
                                  x-model="formData[field.key]"
                                  rows="4"
                                  class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"></textarea>
                    </template>

                    <!-- date -->
                    <template x-if="field.type === 'date'">
                        <input type="date"
                               x-model="formData[field.key]"
                               class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                    </template>

                    <!-- select -->
                    <template x-if="field.type === 'select'">
                        <select x-model="formData[field.key]"
                                class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                            <template x-for="opt in (field.options || [])" :key="opt.value">
                                <option :value="opt.value" x-text="opt.label"></option>
                            </template>
                        </select>
                    </template>

                    <!-- image upload -->
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
                                       class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8 cursor-pointer hover:border-brand-400 hover:bg-brand-50 transition-colors">
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

                    <!-- rich_text -->
                    <template x-if="field.type === 'rich_text'">
                        <textarea :placeholder="field.placeholder || ''"
                                  x-model="formData[field.key]"
                                  rows="8"
                                  class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm font-mono shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"></textarea>
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
        <h2 class="text-xl font-bold mb-4"><?= lang('Wizard.confirm_title') ?></h2>

        <div class="bg-gray-50 rounded-xl border border-gray-200 p-5 space-y-3">
            <!-- Collection identity -->
            <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
                <span x-text="selectedCollection?.icon ?? '📄'" class="text-3xl"></span>
                <span class="font-bold text-lg text-gray-800" x-text="selectedCollection?.name ?? ''"></span>
            </div>

            <!-- All wizard fields, dynamically rendered -->
            <template x-for="step in steps" :key="step.step_title">
                <template x-for="field in step.fields" :key="field.key">
                    <div x-show="field.type !== 'select' || field.key !== 'status'" class="flex gap-3 text-sm">
                        <span class="text-gray-500 shrink-0 w-32 truncate" x-text="field.label + ':'"></span>

                        <!-- image preview -->
                        <template x-if="field.type === 'image'">
                            <span>
                                <img x-show="formData[field.key + '_url']"
                                     :src="formData[field.key + '_url']"
                                     class="rounded max-h-20 object-cover" />
                                <span x-show="!formData[field.key + '_url']"
                                      class="text-gray-300 italic"><?= lang('Wizard.confirm_no_value') ?></span>
                            </span>
                        </template>

                        <!-- text/textarea/date/rich_text value -->
                        <template x-if="field.type !== 'image'">
                            <span class="text-gray-800 break-words"
                                  x-text="formData[field.key] || '<?= lang('Wizard.confirm_no_value') ?>'"></span>
                        </template>
                    </div>
                </template>
            </template>

            <!-- Status row -->
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
    <div x-show="screen === 'success'" x-cloak class="text-center py-8">
        <div class="text-6xl mb-4">✅</div>
        <h2 class="text-2xl font-bold mb-2"><?= lang('Wizard.success_title') ?></h2>
        <p class="text-gray-500 mb-6">
            "<span x-text="formData.title"></span>" <?= lang('Wizard.success_subtitle') ?>
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
            <a href="<?= route_to('admin.cms.entries') ?>" class="text-sm text-gray-500 hover:text-gray-700">
                <?= lang('Wizard.btn_back_panel') ?>
            </a>
        </div>
    </div>

    <!-- ── SCREEN: PAGE SELECT (B1) ── -->
    <div x-show="screen === 'page-select'" x-cloak>
        <h2 class="text-xl font-bold mb-4"><?= lang('Wizard.page_select_heading') ?></h2>
        <div x-show="(config?.pages ?? []).length === 0"
             class="text-gray-400 text-sm py-8 text-center">
            <?= lang('Wizard.no_pages') ?>
        </div>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
            <template x-for="page in (config?.pages ?? [])" :key="page.id">
                <button @click="selectPage(page)"
                        class="flex flex-col items-center justify-center gap-1 rounded-xl border-2 border-gray-200 bg-white p-5 text-center hover:border-brand-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <span class="text-3xl">📄</span>
                    <span class="font-semibold text-sm text-gray-800" x-text="page.title || page.slug || strings.page_fallback"></span>
                    <span class="text-xs text-gray-400" x-text="page.slug ? '/' + page.slug : ''"></span>
                </button>
            </template>
        </div>
        <button @click="screen = 'home'" class="mt-4 text-sm text-gray-500 hover:text-gray-700"><?= lang('Wizard.btn_back') ?></button>
    </div>

    <!-- ── SCREEN: PAGE BLOCKS (B2) ── -->
    <div x-show="screen === 'page-blocks'" x-cloak>
        <div class="flex items-center gap-2 mb-4">
            <button @click="screen = 'page-select'" class="text-sm text-gray-500 hover:text-gray-700"><?= lang('Wizard.btn_back') ?></button>
            <span class="text-gray-300">/</span>
            <h2 class="text-xl font-bold" x-text="selectedPage?.title || selectedPage?.slug || strings.page_fallback"></h2>
        </div>

        <div x-show="pageBlocksLoading" class="text-center py-8 text-gray-400">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-600 mx-auto mb-2"></div>
            <p class="text-sm"><?= lang('Wizard.blocks_loading') ?></p>
        </div>

        <div x-show="!pageBlocksLoading && pageBlocks.length === 0 && !pageBlocksError"
             class="text-center py-8 text-gray-400 text-sm">
            <?= lang('Wizard.no_blocks') ?>
        </div>

        <p x-show="pageBlocksError" class="text-red-600 text-sm mb-4" x-text="pageBlocksError"></p>

        <div class="space-y-3" x-show="!pageBlocksLoading && pageBlocks.length > 0">
            <template x-for="(block, idx) in pageBlocks" :key="block.id">
                <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <div>
                        <p class="font-medium text-sm text-gray-800" x-text="blockLabel(block, idx)"></p>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="blockPreview(block)"></p>
                    </div>
                    <button @click="editBlock(block)"
                            class="ml-3 rounded-lg border border-brand-300 px-3 py-1.5 text-xs font-medium text-brand-700 hover:bg-brand-50 transition-colors">
                        <?= lang('Wizard.btn_edit_block') ?>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- ── SCREEN: BLOCK EDIT (B3) ── -->
    <div x-show="screen === 'block-edit'" x-cloak>
        <div class="flex items-center gap-2 mb-4">
            <button @click="screen = 'page-blocks'" class="text-sm text-gray-500 hover:text-gray-700"><?= lang('Wizard.btn_back') ?></button>
            <span class="text-gray-300">/</span>
            <h2 class="text-xl font-bold" x-text="blockLabel(selectedBlock, 0)"></h2>
        </div>

        <template x-if="selectedBlock && blockFields().length === 0">
            <p class="text-sm text-gray-400 py-6 text-center"><?= lang('Wizard.no_block_fields') ?></p>
        </template>

        <template x-for="field in blockFields()" :key="field.key">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"
                       x-text="field.label + (field.required ? '<?= lang('Wizard.required_suffix') ?>' : '')"></label>

                <!-- string / text -->
                <template x-if="field.uiType === 'text'">
                    <input type="text"
                           x-model="blockEditData[field.key]"
                           class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </template>

                <!-- date -->
                <template x-if="field.uiType === 'date'">
                    <input type="date"
                           x-model="blockEditData[field.key]"
                           class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </template>

                <!-- number -->
                <template x-if="field.uiType === 'number'">
                    <input type="number"
                           x-model="blockEditData[field.key]"
                           class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </template>

                <!-- image upload -->
                <template x-if="field.uiType === 'image'">
                    <div>
                        <template x-if="blockEditData[field.key + '_url']">
                            <div class="relative inline-block">
                                <img :src="blockEditData[field.key + '_url']"
                                     class="rounded-lg max-h-48 object-cover border border-gray-200" />
                                <button type="button"
                                        @click="blockEditData[field.key + '_file_id'] = null; blockEditData[field.key + '_url'] = null"
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs flex items-center justify-center hover:bg-red-600">✕</button>
                            </div>
                        </template>
                        <template x-if="!blockEditData[field.key + '_url']">
                            <label :class="{'opacity-60': uploading}"
                                   class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8 cursor-pointer hover:border-brand-400 hover:bg-brand-50 transition-colors">
                                <span class="text-4xl">📷</span>
                                <span class="text-sm text-gray-500"><?= lang('Wizard.upload_image') ?></span>
                                <span class="text-xs text-gray-400"><?= lang('Wizard.upload_click_hint') ?></span>
                                <span x-show="uploading" class="text-xs text-brand-600"><?= lang('Wizard.upload_uploading') ?></span>
                                <input type="file" accept="image/*" class="hidden"
                                       @change="uploadBlockImage(field, $event.target.files[0])" />
                            </label>
                        </template>
                        <p x-show="uploadError" class="mt-1 text-xs text-red-600" x-text="uploadError"></p>
                    </div>
                </template>

                <!-- richtext / textarea / fallback -->
                <template x-if="field.uiType === 'textarea'">
                    <textarea x-model="blockEditData[field.key]"
                              rows="3"
                              class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"></textarea>
                </template>
            </div>
        </template>

        <p x-show="blockSaveError" class="text-red-600 text-sm mt-2" x-text="blockSaveError"></p>

        <div class="flex gap-3 mt-6">
            <button @click="screen = 'page-blocks'" class="btn-secondary"><?= lang('Wizard.btn_back') ?></button>
            <button @click="saveBlock()" :disabled="blockSaving" class="btn-primary">
                <span x-show="!blockSaving"><?= lang('Wizard.btn_save_block') ?></span>
                <span x-show="blockSaving"><?= lang('Wizard.btn_saving') ?></span>
            </button>
        </div>
    </div>

    <!-- ── SCREEN: BLOCK SAVE SUCCESS ── -->
    <div x-show="screen === 'block-saved'" x-cloak class="text-center py-10">
        <div class="text-5xl mb-3">✅</div>
        <h2 class="text-xl font-bold mb-2"><?= lang('Wizard.block_saved_title') ?></h2>
        <p class="text-gray-500 text-sm mb-6"><?= lang('Wizard.block_saved_subtitle') ?></p>
        <div class="flex flex-col gap-3 items-center">
            <button @click="screen = 'page-blocks'" class="btn-primary"><?= lang('Wizard.btn_view_blocks') ?></button>
            <button @click="screen = 'home'" class="btn-secondary"><?= lang('Wizard.btn_back_panel') ?></button>
        </div>
    </div>

    <!-- ── SCREEN: MENU SELECT (C1) ── -->
    <div x-show="screen === 'menu-select'" x-cloak>
        <h2 class="text-xl font-bold mb-4"><?= lang('Wizard.menu_select_heading') ?></h2>
        <div x-show="(config?.menus ?? []).length === 0"
             class="text-gray-400 text-sm py-8 text-center">
            <?= lang('Wizard.no_menus') ?>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <template x-for="menu in (config?.menus ?? [])" :key="menu.id">
                <button @click="selectMenu(menu)"
                        class="flex flex-col items-center justify-center gap-1 rounded-xl border-2 border-gray-200 bg-white p-5 text-center hover:border-brand-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <span class="text-3xl">🔗</span>
                    <span class="font-semibold text-sm text-gray-800" x-text="menu.name || menu.menu_key || strings.menu_fallback"></span>
                </button>
            </template>
        </div>
        <button @click="screen = 'home'" class="mt-4 text-sm text-gray-500 hover:text-gray-700"><?= lang('Wizard.btn_back') ?></button>
    </div>

    <!-- ── SCREEN: MENU ITEMS (C2) ── -->
    <div x-show="screen === 'menu-items'" x-cloak>
        <div class="flex items-center gap-2 mb-4">
            <button @click="screen = 'menu-select'" class="text-sm text-gray-500 hover:text-gray-700"><?= lang('Wizard.btn_back') ?></button>
            <span class="text-gray-300">/</span>
            <h2 class="text-xl font-bold" x-text="selectedMenu?.name || selectedMenu?.menu_key || strings.menu_fallback"></h2>
        </div>

        <div x-show="menuItemsLoading" class="text-center py-8 text-gray-400">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-600 mx-auto mb-2"></div>
            <p class="text-sm"><?= lang('Wizard.items_loading') ?></p>
        </div>

        <p x-show="menuItemsError" class="text-red-600 text-sm mb-4" x-text="menuItemsError"></p>

        <div class="space-y-2" x-show="!menuItemsLoading">
            <template x-for="(item, idx) in menuItems" :key="item.id">
                <div class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm group">
                    <!-- Reorder buttons -->
                    <div class="flex flex-col gap-0.5">
                        <button @click="moveItem(idx, -1)" :disabled="idx === 0"
                                class="text-gray-300 hover:text-gray-600 disabled:opacity-30 text-xs leading-none">▲</button>
                        <button @click="moveItem(idx, 1)" :disabled="idx === menuItems.length - 1"
                                class="text-gray-300 hover:text-gray-600 disabled:opacity-30 text-xs leading-none">▼</button>
                    </div>
                    <!-- Label / URL -->
                    <div class="flex-1 min-w-0">
                        <input type="text"
                               x-model="item._label"
                               @blur="patchItem(item)"
                               placeholder="<?= lang('Wizard.menu_item_label_placeholder') ?>"
                               class="w-full text-sm font-medium text-gray-800 border-0 bg-transparent focus:outline-none focus:ring-1 focus:ring-brand-300 rounded px-1" />
                        <input type="text"
                               x-model="item._url"
                               @blur="patchItem(item)"
                               placeholder="<?= lang('Wizard.menu_item_url_placeholder') ?>"
                               class="w-full text-xs text-gray-400 border-0 bg-transparent focus:outline-none focus:ring-1 focus:ring-brand-300 rounded px-1 mt-0.5" />
                    </div>
                    <!-- Delete -->
                    <button @click="confirmDeleteItem(item)"
                            class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 text-xs transition-opacity">✕</button>
                </div>
            </template>

            <!-- Add item -->
            <div class="mt-4 border-t pt-4">
                <p class="text-xs font-medium text-gray-500 mb-2"><?= lang('Wizard.add_item_heading') ?></p>
                <div class="flex gap-2">
                    <input type="text" x-model="newItemLabel"
                           placeholder="<?= lang('Wizard.menu_item_label_placeholder') ?>"
                           class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                    <input type="text" x-model="newItemUrl"
                           placeholder="<?= lang('Wizard.menu_item_url_placeholder') ?>"
                           class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                    <button @click="addItem()" :disabled="!newItemLabel || menuItemsSaving"
                            class="btn-primary text-sm whitespace-nowrap"><?= lang('Wizard.btn_add_item') ?></button>
                </div>
            </div>
        </div>

        <!-- Delete confirm modal -->
        <div x-show="deleteItemTarget" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
                <h3 class="font-bold mb-2"><?= lang('Wizard.delete_item_title') ?></h3>
                <p class="text-sm text-gray-500 mb-4" x-text="deleteConfirmText()"></p>
                <div class="flex gap-3 justify-end">
                    <button @click="deleteItemTarget = null" class="btn-secondary text-sm"><?= lang('Wizard.btn_cancel') ?></button>
                    <button @click="deleteItem()" class="btn-danger text-sm"><?= lang('Wizard.btn_delete') ?></button>
                </div>
            </div>
        </div>

        <p x-show="menuSaveError" class="text-red-600 text-sm mt-3" x-text="menuSaveError"></p>

        <div class="mt-6 flex gap-3">
            <button @click="screen = 'home'" class="btn-secondary"><?= lang('Wizard.btn_back_panel') ?></button>
            <button @click="saveMenuOrder()" :disabled="menuItemsSaving" class="btn-primary">
                <span x-show="!menuItemsSaving"><?= lang('Wizard.btn_save_order') ?></span>
                <span x-show="menuItemsSaving"><?= lang('Wizard.btn_saving') ?></span>
            </button>
        </div>
    </div>

</div>

<script <?= csp_script_nonce() ?>>
// ── Wizard Alpine.js component ────────────────────────────────────────────────
(function () {
    'use strict';

    const CSRF_NAME  = <?= json_encode($csrfName) ?>;
    const CSRF_TOKEN = <?= json_encode($csrfToken) ?>;
    const NATIVE_KEYS = ['title', 'excerpt', 'featured_image', 'body', 'status'];

    // Default wizard steps injected from PHP (respects i18n — no Spanish hardcoded in JS)
    const DEFAULT_STEPS = [
        {
            step_title: <?= json_encode(lang('Wizard.default_step1_title')) ?>,
            step_hint:  <?= json_encode(lang('Wizard.default_step1_hint')) ?>,
            fields: [{ key: 'title', label: <?= json_encode(lang('Wizard.default_field_title')) ?>, type: 'text', required: true }],
        },
        {
            step_title: <?= json_encode(lang('Wizard.default_step2_title')) ?>,
            step_hint:  <?= json_encode(lang('Wizard.default_step2_hint')) ?>,
            fields: [{ key: 'featured_image', label: <?= json_encode(lang('Wizard.default_field_image')) ?>, type: 'image', required: false }],
        },
        {
            step_title: <?= json_encode(lang('Wizard.default_step3_title')) ?>,
            step_hint:  <?= json_encode(lang('Wizard.default_step3_hint')) ?>,
            fields: [{ key: 'excerpt', label: <?= json_encode(lang('Wizard.default_field_excerpt')) ?>, type: 'textarea', required: false }],
        },
    ];

    // PHP-injected translatable strings used inside JS handlers
    const STRINGS = {
        step_of:               <?= json_encode(lang('Wizard.step_of')) ?>,
        page_fallback:         <?= json_encode(lang('Wizard.page_fallback')) ?>,
        menu_fallback:         <?= json_encode(lang('Wizard.menu_fallback')) ?>,
        delete_confirm:        <?= json_encode(lang('Wizard.delete_item_confirm')) ?>,
        add_content_desc:      <?= json_encode(lang('Wizard.add_content_desc')) ?>,
        add_content_desc_empty:<?= json_encode(lang('Wizard.add_content_desc_empty')) ?>,
        error_no_pages:        <?= json_encode(lang('Wizard.error_no_pages')) ?>,
        error_no_menus:        <?= json_encode(lang('Wizard.error_no_menus')) ?>,
        error_blocks_load:     <?= json_encode(lang('Wizard.error_blocks_load')) ?>,
        error_items_load:      <?= json_encode(lang('Wizard.error_items_load')) ?>,
        error_block_save:      <?= json_encode(lang('Wizard.error_block_save')) ?>,
        error_item_save:       <?= json_encode(lang('Wizard.error_item_save')) ?>,
        error_item_delete:     <?= json_encode(lang('Wizard.error_item_delete')) ?>,
        error_upload:          <?= json_encode(lang('Wizard.error_upload')) ?>,
        error_publish:         <?= json_encode(lang('Wizard.error_publish')) ?>,
        error_load:            <?= json_encode(lang('Wizard.error_load')) ?>,
    };

    function csrfHeaders() {
        return {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            [CSRF_NAME]: CSRF_TOKEN,
        };
    }

    async function adminFetch(url, opts = {}) {
        const isFormData = opts.body instanceof FormData;
        const headers = isFormData
            ? csrfHeaders()
            : { 'Content-Type': 'application/json', ...csrfHeaders() };

        return fetch(url, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', ...headers, ...(opts.headers || {}) },
            ...opts,
        });
    }

    function slugify(str) {
        return str
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '')
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .substring(0, 100);
    }

    // Map block type schema field types to wizard UI types
    function schemaTypeToUiType(schemaType, accept) {
        if (schemaType === 'file') return (accept === 'image') ? 'image' : 'text';
        if (schemaType === 'richtext') return 'textarea';
        if (schemaType === 'string') return 'text';
        if (schemaType === 'number') return 'number';
        if (schemaType === 'boolean') return 'text';
        return 'textarea';
    }

    // Humanize a snake_case or camelCase key into a readable label
    function humanizeKey(key) {
        return key
            .replace(/_/g, ' ')
            .replace(/([a-z])([A-Z])/g, '$1 $2')
            .replace(/\b\w/g, l => l.toUpperCase());
    }

    window.wizard = function () {
        return {
            // ── State ─────────────────────────────────────────────────────────
            screen: 'loading',
            config: null,
            defaultLangId: 1,
            strings: STRINGS,
            errorMsg: '',

            // Add-content flow
            selectedCollection: null,
            currentStep: 0,
            formData: {},
            publishedEntry: null,
            publishing: false,
            publishError: '',

            // Image upload (shared between entry wizard and block editor)
            uploading: false,
            uploadError: '',

            // Draft
            draft: null,

            // Edit page flow (B screens)
            selectedPage: null,
            pageBlocks: [],
            pageBlocksLoading: false,
            pageBlocksError: '',
            selectedBlock: null,
            blockEditData: {},
            blockSaving: false,
            blockSaveError: '',

            // Edit menu flow (C screens)
            selectedMenu: null,
            menuItems: [],
            menuItemsLoading: false,
            menuItemsError: '',
            menuItemsSaving: false,
            menuSaveError: '',
            newItemLabel: '',
            newItemUrl: '',
            deleteItemTarget: null,

            // ── Computed ──────────────────────────────────────────────────────
            get steps() {
                return this.selectedCollection?.wizard_config?.steps ?? DEFAULT_STEPS;
            },

            get currentStepSchema() {
                return this.steps[this.currentStep] ?? null;
            },

            get totalSteps() {
                return this.steps.length;
            },

            // ── Helpers ───────────────────────────────────────────────────────
            stepLabel() {
                return STRINGS.step_of
                    .replace('%s', this.currentStep + 1)
                    .replace('%s', this.totalSteps);
            },

            deleteConfirmText() {
                const label = this.deleteItemTarget?._label ?? '';
                return STRINGS.delete_confirm.replace('%s', label);
            },

            // Dynamic subtitle for the "Add content" home card
            addContentDesc() {
                const cols = this.config?.collections ?? [];
                if (cols.length === 0) return STRINGS.add_content_desc_empty;
                const names = cols.slice(0, 4).map(c => c.name);
                return names.join(', ') + (cols.length > 4 ? '…' : '');
            },

            // ── Lifecycle ─────────────────────────────────────────────────────
            async init() {
                this.screen = 'loading';
                this.draft = this.loadDraft();

                try {
                    const res = await adminFetch('<?= site_url('admin/cms/wizard/config') ?>');
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const data = await res.json();
                    this.config = data;
                    this.defaultLangId = data.default_lang_id ?? 1;
                    this.screen = 'home';
                } catch (e) {
                    this.errorMsg = STRINGS.error_load;
                    this.screen = 'error';
                }
            },

            goHome() {
                this.errorMsg = '';
                this.screen = 'home';
            },

            // ── Navigation ────────────────────────────────────────────────────
            goAddContent() {
                this.screen = 'collection-select';
            },

            selectCollection(col) {
                this.selectedCollection = col;
                this.currentStep = 0;
                this.formData = { status: 'published' };
                const allSteps = this.steps;
                for (const step of allSteps) {
                    for (const field of step.fields) {
                        if (field.default !== undefined && field.default !== null) {
                            this.formData[field.key] = field.default;
                        }
                    }
                }
                this.publishError = '';
                this.screen = 'steps';
            },

            prevStep() {
                if (this.currentStep > 0) {
                    this.currentStep--;
                } else {
                    this.screen = 'collection-select';
                }
            },

            nextStep() {
                if (!this.canAdvance()) return;
                if (this.currentStep < this.steps.length - 1) {
                    this.currentStep++;
                    this.saveDraft();
                } else {
                    this.screen = 'confirm';
                }
            },

            canAdvance() {
                const step = this.currentStepSchema;
                if (!step) return false;
                return step.fields
                    .filter(f => f.required)
                    .every(f => {
                        if (f.type === 'image') {
                            return Boolean(this.formData[f.key + '_id']);
                        }
                        const val = this.formData[f.key];
                        return val !== undefined && val !== null && String(val).trim() !== '';
                    });
            },

            // ── Image upload (entry wizard) ────────────────────────────────────
            async uploadImage(field, file) {
                if (!file) return;
                this.uploading = true;
                this.uploadError = '';
                try {
                    const fd = new FormData();
                    fd.append('file', file);
                    const res = await adminFetch('<?= site_url('admin/cms/wizard/upload') ?>', {
                        method: 'POST',
                        body: fd,
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data?.message ?? 'Upload failed');

                    const fileData = data?.file ?? data;
                    this.formData[field.key + '_id']  = fileData?.id ?? null;
                    this.formData[field.key + '_url'] = fileData?.url ?? fileData?.variants?.md?.url ?? null;
                } catch (e) {
                    this.uploadError = STRINGS.error_upload;
                } finally {
                    this.uploading = false;
                }
            },

            // ── Image upload (block editor) ────────────────────────────────────
            async uploadBlockImage(field, file) {
                if (!file) return;
                this.uploading = true;
                this.uploadError = '';
                try {
                    const fd = new FormData();
                    fd.append('file', file);
                    const res = await adminFetch('<?= site_url('admin/cms/wizard/upload') ?>', {
                        method: 'POST',
                        body: fd,
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data?.message ?? 'Upload failed');

                    const fileData = data?.file ?? data;
                    this.blockEditData[field.key + '_file_id'] = fileData?.id ?? null;
                    this.blockEditData[field.key + '_url']     = fileData?.url ?? fileData?.variants?.md?.url ?? null;
                } catch (e) {
                    this.uploadError = STRINGS.error_upload;
                } finally {
                    this.uploading = false;
                }
            },

            // ── Publish ───────────────────────────────────────────────────────
            async publish() {
                this.publishing = true;
                this.publishError = '';
                try {
                    const payload = this.buildEntryPayload();
                    const res = await adminFetch('<?= site_url('admin/cms/wizard/publish') ?>', {
                        method: 'POST',
                        body: JSON.stringify(payload),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        const msg = data?.messages?.[0] ?? data?.message ?? STRINGS.error_publish;
                        throw new Error(msg);
                    }
                    this.publishedEntry = data;
                    this.clearDraft();
                    this.screen = 'success';
                } catch (e) {
                    this.publishError = e.message ?? STRINGS.error_publish;
                } finally {
                    this.publishing = false;
                }
            },

            buildEntryPayload() {
                // ── Collect non-native fields into wizard_extra ──────────────────
                // Image fields (type === 'image') are stored as {key}_file_id / {key}_url
                // to match the schema_definition convention and allow EntryService to
                // auto-populate matching block_data fields on entry creation.
                const extra = {};
                for (const step of this.steps) {
                    for (const field of step.fields) {
                        if (NATIVE_KEYS.includes(field.key)) continue;

                        if (field.type === 'image') {
                            const fileId = this.formData[field.key + '_id'] ?? null;
                            if (fileId !== null) {
                                extra[field.key + '_file_id'] = fileId;
                                extra[field.key + '_url']     = this.formData[field.key + '_url'] ?? null;
                            }
                        } else if (this.formData[field.key] !== undefined) {
                            extra[field.key] = this.formData[field.key];
                        }
                    }
                }

                const payload = {
                    collection_id:   this.selectedCollection.id,
                    workflow_status: this.formData.status ?? 'published',
                    sort_order:      0,
                    view_count:      0,
                    is_featured:     false,
                    is_in_sitemap:   true,
                    translations:    [],
                };

                if (Object.keys(extra).length > 0) {
                    payload.wizard_extra = extra;
                }

                // ── Create a translation for every active language ───────────────
                // Non-default languages get the same title/excerpt (user can translate later)
                // with a lang-code suffix on the slug to satisfy the unique-per-language index.
                const baseSlug  = slugify(this.formData.title || 'entry') + '-' + Date.now();
                const languages = this.config?.languages ?? [];
                const sharedTranslationData = {
                    title:            this.formData.title ?? '',
                    excerpt:          this.formData.excerpt ?? '',
                    featured_file_id: this.formData.featured_image_id ?? null,
                };

                payload.translations = languages.length > 0
                    ? languages.map(lang => ({
                        language_id: lang.id,
                        slug: lang.id === this.defaultLangId
                            ? baseSlug
                            : baseSlug + '-' + lang.code,
                        ...sharedTranslationData,
                    }))
                    : [{
                        language_id: this.defaultLangId,
                        slug: baseSlug,
                        ...sharedTranslationData,
                    }];

                return payload;
            },

            restart() {
                this.clearDraft();
                this.selectedCollection = null;
                this.formData = {};
                this.currentStep = 0;
                this.publishedEntry = null;
                this.publishError = '';
                this.screen = 'home';
            },

            // ── WIZ-007: Edit page ────────────────────────────────────────────
            goEditPage() {
                if (!this.config) return;
                if ((this.config.pages ?? []).length === 0) {
                    this.errorMsg = STRINGS.error_no_pages;
                    this.screen = 'error';
                    return;
                }
                this.screen = 'page-select';
            },

            async selectPage(page) {
                this.selectedPage = page;
                this.pageBlocks = [];
                this.pageBlocksError = '';
                this.pageBlocksLoading = true;
                this.screen = 'page-blocks';

                try {
                    const res = await adminFetch(`<?= site_url('admin/cms/wizard/pages') ?>/${page.id}/blocks`);
                    const data = await res.json();
                    if (!res.ok) throw new Error(data?.message ?? STRINGS.error_blocks_load);
                    const items = data?.items ?? data?.data ?? (Array.isArray(data) ? data : []);
                    this.pageBlocks = items;
                } catch (e) {
                    this.pageBlocksError = e.message ?? STRINGS.error_blocks_load;
                } finally {
                    this.pageBlocksLoading = false;
                }
            },

            blockLabel(block, idx) {
                if (!block) return '';
                const cfg = block.block_config ?? {};
                const bkey = cfg.block_key ?? cfg.name ?? null;
                // Always humanize the block_key — whether it's in the schema map or not.
                // Falls back to "Block N" only when there is truly no key at all.
                if (bkey) return humanizeKey(bkey);
                return STRINGS.page_fallback + ' ' + (block.sort_order ?? (idx + 1));
            },

            blockPreview(block) {
                if (!block) return '';
                const t = (block.translations ?? [])[0];
                if (!t?.block_data) return '';
                const vals = Object.values(t.block_data).filter(v => {
                    if (typeof v !== 'string' || !v) return false;
                    // Skip internal storage keys (file IDs, URLs, slugs, paths)
                    if (v.startsWith('http') || v.startsWith('/') || /^\d+$/.test(v)) return false;
                    // Skip values that look like IDs or are purely numeric
                    return true;
                });
                if (vals.length === 0) return '';
                // Strip HTML tags so rich-text values render as plain text
                const plain = vals[0].replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                return plain.substring(0, 60) || '';
            },

            editBlock(block) {
                this.selectedBlock = block;
                this.blockSaveError = '';
                this.uploadError = '';
                const t = (block.translations ?? [])[0];
                this.blockEditData = t?.block_data ? { ...t.block_data } : {};
                this.screen = 'block-edit';
            },

            // Returns enriched field descriptors for the block editor.
            // When a BlockType schema is available, it is the source of truth for
            // field order, labels, and types. Image fields in the schema use key
            // "image" but block_data stores "image_file_id"+"image_url" — the
            // schema-first path handles this correctly via uploadBlockImage().
            // When no schema is known, we fall back to block_data keys (textarea only).
            blockFields() {
                if (!this.selectedBlock) return [];
                const bkey = this.selectedBlock.block_config?.block_key ?? null;
                const schemaFields = bkey ? (this.config?.block_types?.[bkey]?.fields ?? null) : null;

                if (schemaFields && Object.keys(schemaFields).length > 0) {
                    return Object.entries(schemaFields).map(([k, def]) => ({
                        key:      k,
                        label:    def.label ?? humanizeKey(k),
                        required: def.required ?? false,
                        uiType:   schemaTypeToUiType(def.type ?? '', def.accept ?? ''),
                    }));
                }

                // Fallback: derive from existing block_data keys (skip internal storage keys)
                const visibleKeys = Object.keys(this.blockEditData)
                    .filter(k => !k.endsWith('_file_id') && !k.endsWith('_url'));

                return visibleKeys.map(k => ({
                    key:      k,
                    label:    humanizeKey(k),
                    required: false,
                    uiType:   'textarea',
                }));
            },

            async saveBlock() {
                if (!this.selectedBlock || !this.selectedPage) return;
                this.blockSaving = true;
                this.blockSaveError = '';

                try {
                    const t = (this.selectedBlock.translations ?? [])[0] ?? {};
                    const payload = {
                        translations: [{
                            language_id:  t.language_id ?? this.defaultLangId,
                            block_data:   this.blockEditData,
                            is_published: t.is_published ?? true,
                        }],
                    };

                    const res = await adminFetch(
                        `<?= site_url('admin/cms/wizard/pages') ?>/${this.selectedPage.id}/blocks/${this.selectedBlock.id}`,
                        { method: 'POST', body: JSON.stringify(payload) }
                    );
                    const data = await res.json();
                    if (!res.ok) throw new Error(data?.message ?? STRINGS.error_block_save);

                    this.screen = 'block-saved';
                } catch (e) {
                    this.blockSaveError = e.message ?? STRINGS.error_block_save;
                } finally {
                    this.blockSaving = false;
                }
            },

            // ── WIZ-008: Edit menu ────────────────────────────────────────────
            goEditMenu() {
                if (!this.config) return;
                if ((this.config.menus ?? []).length === 0) {
                    this.errorMsg = STRINGS.error_no_menus;
                    this.screen = 'error';
                    return;
                }
                if (this.config.menus.length === 1) {
                    this.selectMenu(this.config.menus[0]);
                    return;
                }
                this.screen = 'menu-select';
            },

            async selectMenu(menu) {
                this.selectedMenu = menu;
                this.menuItems = [];
                this.menuItemsError = '';
                this.menuItemsLoading = true;
                this.screen = 'menu-items';

                try {
                    const res = await adminFetch(`<?= site_url('admin/cms/wizard/menus') ?>/${menu.id}/items`);
                    const data = await res.json();
                    if (!res.ok) throw new Error(data?.message ?? STRINGS.error_items_load);
                    const items = data?.items ?? data?.data ?? (Array.isArray(data) ? data : []);
                    this.menuItems = items.map(item => ({
                        ...item,
                        _label: this.itemLabel(item),
                        _url:   this.itemUrl(item),
                    }));
                } catch (e) {
                    this.menuItemsError = e.message ?? STRINGS.error_items_load;
                } finally {
                    this.menuItemsLoading = false;
                }
            },

            itemLabel(item) {
                if (!item) return '';
                const t = (item.translations ?? [])[0];
                return t?.label ?? t?.title ?? '';
            },

            itemUrl(item) {
                if (!item) return '';
                const t = (item.translations ?? [])[0];
                return t?.custom_url ?? t?.url ?? '';
            },

            moveItem(idx, delta) {
                const target = idx + delta;
                if (target < 0 || target >= this.menuItems.length) return;
                const temp = this.menuItems[idx];
                this.menuItems[idx] = this.menuItems[target];
                this.menuItems[target] = temp;
            },

            async patchItem(item) {
                try {
                    const t = (item.translations ?? [])[0] ?? {};
                    const res = await adminFetch(
                        `<?= site_url('admin/cms/wizard/menus/items') ?>/${item.id}`,
                        {
                            method: 'POST',
                            body: JSON.stringify({
                                translations: [{
                                    language_id: t.language_id ?? this.defaultLangId,
                                    label:       item._label,
                                    custom_url:  item._url,
                                }],
                            }),
                        }
                    );
                    if (!res.ok) {
                        const data = await res.json().catch(() => ({}));
                        this.menuSaveError = data?.message ?? STRINGS.error_item_save;
                    }
                } catch (_) { /* network error — non-blocking */ }
            },

            async saveMenuOrder() {
                this.menuItemsSaving = true;
                this.menuSaveError = '';
                try {
                    const updates = this.menuItems.map((item, idx) =>
                        adminFetch(
                            `<?= site_url('admin/cms/wizard/menus/items') ?>/${item.id}`,
                            { method: 'POST', body: JSON.stringify({ sort_order: idx }) }
                        )
                    );
                    await Promise.all(updates);
                } catch (e) {
                    this.menuSaveError = STRINGS.error_item_save;
                } finally {
                    this.menuItemsSaving = false;
                }
            },

            async addItem() {
                if (!this.newItemLabel || !this.selectedMenu) return;
                this.menuItemsSaving = true;
                try {
                    const res = await adminFetch(
                        `<?= site_url('admin/cms/wizard/menus') ?>/${this.selectedMenu.id}/items`,
                        {
                            method: 'POST',
                            body: JSON.stringify({
                                link_type:   'custom_url',
                                link_target: '_self',
                                sort_order:  this.menuItems.length,
                                is_active:   true,
                                translations: [{
                                    language_id: this.defaultLangId,
                                    label:       this.newItemLabel,
                                    custom_url:  this.newItemUrl || '#',
                                }],
                            }),
                        }
                    );
                    const data = await res.json();
                    if (!res.ok) throw new Error(data?.message ?? STRINGS.error_item_save);
                    const newItem = data?.data ?? data;
                    this.menuItems.push({
                        ...newItem,
                        _label: this.newItemLabel,
                        _url:   this.newItemUrl || '#',
                    });
                    this.newItemLabel = '';
                    this.newItemUrl   = '';
                } catch (e) {
                    this.menuSaveError = e.message ?? STRINGS.error_item_save;
                } finally {
                    this.menuItemsSaving = false;
                }
            },

            confirmDeleteItem(item) {
                this.deleteItemTarget = item;
            },

            async deleteItem() {
                if (!this.deleteItemTarget) return;
                const item = this.deleteItemTarget;
                this.deleteItemTarget = null;
                try {
                    await adminFetch(
                        `<?= site_url('admin/cms/wizard/menus/items') ?>/${item.id}/delete`,
                        { method: 'POST' }
                    );
                    this.menuItems = this.menuItems.filter(i => i.id !== item.id);
                } catch (_) {
                    this.menuSaveError = STRINGS.error_item_delete;
                }
            },

            // ── Draft persistence (localStorage) ─────────────────────────────
            saveDraft() {
                try {
                    const draft = {
                        collectionId: this.selectedCollection?.id,
                        step: this.currentStep,
                        formData: this.formData,
                        savedAt: new Date().toISOString(),
                    };
                    localStorage.setItem('cms_wizard_draft', JSON.stringify(draft));
                } catch (_) { /* quota exceeded — ignore */ }
            },

            loadDraft() {
                try {
                    const raw = localStorage.getItem('cms_wizard_draft');
                    return raw ? JSON.parse(raw) : null;
                } catch (_) { return null; }
            },

            clearDraft() {
                try { localStorage.removeItem('cms_wizard_draft'); } catch (_) {}
                this.draft = null;
            },

            discardDraft() {
                this.clearDraft();
            },

            resumeDraft() {
                if (!this.draft || !this.config) return;
                const col = (this.config.collections ?? []).find(c => c.id === this.draft.collectionId);
                if (!col) { this.discardDraft(); return; }
                this.selectedCollection = col;
                this.formData = this.draft.formData ?? {};
                this.currentStep = this.draft.step ?? 0;
                this.draft = null;
                this.screen = 'steps';
            },
        };
    };
}());
</script>
