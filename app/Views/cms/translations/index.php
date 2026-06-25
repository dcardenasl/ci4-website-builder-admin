<?php /** @var array $stats */ ?>
<?php /** @var array $languages */ ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= esc($title) ?></h1>
            <p class="text-sm text-gray-500 mt-1"><?= esc(lang('Translations.audit_subtitle')) ?></p>
        </div>
    </div>

    <!-- Stats / Progress Section -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($stats as $stat): ?>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2 min-h-[32px]">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="inline-flex items-center justify-center font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-xs border border-blue-200 shrink-0">
                                <?= esc(strtoupper($stat['code'])) ?>
                            </span>
                            <span class="text-sm font-semibold text-gray-700 truncate"><?= esc($stat['name']) ?></span>
                        </div>
                        <span class="text-lg font-bold text-gray-900 shrink-0"><?= esc($stat['percentage']) ?>%</span>
                    </div>

                    <?php if ($stat['is_default']): ?>
                        <div class="flex">
                            <span class="inline-flex items-center rounded-full px-1.5 py-0.5 font-semibold bg-green-50 text-green-700 border border-green-200 text-[10px] leading-3 whitespace-nowrap">
                                <?= esc(lang('CmsLanguages.field_is_default')) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Progress bar -->
                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-blue-600 h-full transition-all duration-500" style="width: <?= esc($stat['percentage']) ?>%; background-color: #2563eb;"></div>
                    </div>
                </div>

                <div class="flex justify-between text-xs text-gray-500 pt-1">
                    <span><?= esc(lang('Translations.translated') ?? 'Translated') ?>: <?= esc($stat['completed_elements']) ?> / <?= esc($stat['total_elements']) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Audit Issues Table Section -->
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
        x-data="{
            ...remoteTable({
                apiUrl: '<?= route_to('admin.cms.translations.audit.data') ?>',
                pageUrl: '<?= route_to('admin.cms.translations.audit') ?>',
                limitOptions: ['10', '25', '50', '100']
            }),
            dict: <?= esc(json_encode([
                'resources' => [
                    'page' => lang('Translations.resource_page'),
                    'menu_item' => lang('Translations.resource_menu_item'),
                    'setting' => lang('Translations.resource_setting'),
                    'category' => lang('Translations.resource_category'),
                    'collection' => lang('Translations.resource_collection'),
                    'tag' => lang('Translations.resource_tag'),
                    'entry' => lang('Translations.resource_entry'),
                    'form' => lang('Translations.resource_form'),
                    'block_instance' => lang('Translations.resource_block_instance'),
                ],
                'statuses' => [
                    'missing' => lang('Translations.status_missing'),
                    'incomplete' => lang('Translations.status_incomplete'),
                ],
                'details' => [
                    'missing_all' => lang('Translations.detail_missing_all'),
                    'missing_fields' => lang('Translations.detail_missing_fields'),
                ],
                'fields' => [
                    'title' => lang('Translations.field_title'),
                    'slug' => lang('Translations.field_slug'),
                    'label' => lang('Translations.field_label'),
                    'setting_value' => lang('Translations.field_setting_value'),
                    'name' => lang('Translations.field_name'),
                    'block_data' => lang('Translations.field_block_data'),
                ]
            ])) ?>,
            translateResource(res) {
                return this.dict.resources[res] || res;
            },
            translateStatus(status) {
                return this.dict.statuses[status] || status;
            },
            translateDetail(row) {
                if (row.status === 'missing') {
                    return this.dict.details.missing_all;
                }
                const parts = row.detail.split(': ');
                if (parts.length > 1) {
                    const fields = parts[1].split(', ').map(f => this.dict.fields[f.trim()] || f.trim()).join(', ');
                    return this.dict.details.missing_fields.replace('{fields}', fields);
                }
                return row.detail;
            }
        }" x-init="init()">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-5 border-b border-gray-200 gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900"><?= esc(lang('Translations.missing_incomplete')) ?></h2>
                <p class="text-xs text-gray-500 mt-0.5"><?= esc(lang('Translations.missing_incomplete_desc')) ?></p>
            </div>
            
            <!-- Filters -->
            <div class="flex items-center gap-2">
                <select name="language_id" class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" 
                    @change="filter('language_id', $event.target.value)">
                    <option value=""><?= esc(lang('Translations.all_active_languages')) ?></option>
                    <?php foreach ($languages as $lang): ?>
                        <option value="<?= esc($lang['id']) ?>"><?= esc($lang['native_name'] ?? $lang['name']) ?> (<?= esc(strtoupper($lang['code'])) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mt-6 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600 text-center" x-show="loading">
            <?= esc(lang('Translations.loading_report')) ?>
        </div>
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

        <template x-if="!loading && !error && rows.length === 0">
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900"><?= esc(lang('Translations.all_complete')) ?></h3>
                <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Translations.no_missing_elements')) ?></p>
            </div>
        </template>

        <template x-if="!loading && !error && rows.length > 0">
            <div class="<?= esc(table_wrapper_class()) ?>">
                <div class="<?= esc(table_scroll_class()) ?>">
                    <table class="<?= esc(table_class()) ?>">
                        <thead class="<?= esc(table_head_class()) ?>">
                            <tr>
                                <th class="<?= esc(table_th_class()) ?>"><?= esc(lang('Translations.field_type')) ?></th>
                                <th class="<?= esc(table_th_class()) ?>"><?= esc(lang('Translations.field_item_name')) ?></th>
                                <th class="<?= esc(table_th_class()) ?>"><?= esc(lang('Translations.field_language')) ?></th>
                                <th class="<?= esc(table_th_class()) ?>"><?= esc(lang('Translations.field_status')) ?></th>
                                <th class="<?= esc(table_th_class()) ?>"><?= esc(lang('Translations.field_details')) ?></th>
                                <th class="<?= esc(table_th_class()) ?>"><?= esc(lang('Translations.field_action')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="<?= esc(table_body_class()) ?>">
                            <template x-for="row in rows" :key="String(row.resource + '-' + row.resource_id + '-' + row.language_id)">
                                <tr class="<?= esc(table_row_class()) ?>">
                                    <td class="<?= esc(table_td_class('muted')) ?>">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 capitalize" x-text="translateResource(row.resource)"></span>
                                    </td>
                                    <td class="<?= esc(table_td_class('primary')) ?>" x-text="row.reference_name"></td>
                                    <td class="<?= esc(table_td_class()) ?>">
                                        <span class="font-bold text-xs uppercase bg-blue-50 text-blue-700 px-2 py-0.5 rounded border border-blue-200" x-text="row.language_code"></span>
                                    </td>
                                    <td class="<?= esc(table_td_class()) ?>">
                                        <span :class="row.status === 'missing' ? 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800' : 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800'" x-text="translateStatus(row.status)"></span>
                                    </td>
                                    <td class="<?= esc(table_td_class('muted')) ?>" x-text="translateDetail(row)"></td>
                                    <td class="<?= esc(table_td_class()) ?>">
                                        <template x-if="row.resource === 'page'">
                                            <a :href="'<?= route_to('admin.cms.pages') ?>/' + row.resource_id + '/edit'" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-900">
                                                <?= esc(lang('Translations.action_translate')) ?>
                                            </a>
                                        </template>
                                        <template x-if="row.resource === 'menu_item'">
                                            <a :href="'<?= route_to('admin.cms.menus') ?>/' + row.extra_data.menu_id + '/items/' + row.resource_id + '/edit'" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-900">
                                                <?= esc(lang('Translations.action_translate')) ?>
                                            </a>
                                        </template>
                                        <template x-if="row.resource === 'setting'">
                                            <a :href="'<?= route_to('admin.cms.settings') ?>/' + row.resource_id + '/edit'" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-900">
                                                <?= esc(lang('Translations.action_translate')) ?>
                                            </a>
                                        </template>
                                        <template x-if="row.resource === 'category'">
                                            <a :href="'<?= route_to('admin.cms.categories') ?>/' + row.resource_id + '/edit'" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-900">
                                                <?= esc(lang('Translations.action_translate')) ?>
                                            </a>
                                        </template>
                                        <template x-if="row.resource === 'tag'">
                                            <a :href="'<?= route_to('admin.cms.tags') ?>/' + row.resource_id + '/edit'" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-900">
                                                <?= esc(lang('Translations.action_translate')) ?>
                                            </a>
                                        </template>
                                        <template x-if="row.resource === 'collection'">
                                            <a :href="'<?= route_to('admin.cms.collections') ?>/' + row.resource_id + '/edit'" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-900">
                                                <?= esc(lang('Translations.action_translate')) ?>
                                            </a>
                                        </template>
                                        <template x-if="row.resource === 'entry'">
                                            <a :href="'<?= route_to('admin.cms.entries') ?>/' + row.resource_id + '/edit'" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-900">
                                                <?= esc(lang('Translations.action_translate')) ?>
                                            </a>
                                        </template>
                                        <template x-if="row.resource === 'form'">
                                            <a :href="'<?= route_to('admin.cms.forms') ?>/' + row.resource_id + '/edit'" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-900">
                                                <?= esc(lang('Translations.action_translate')) ?>
                                            </a>
                                        </template>
                                        <template x-if="row.resource === 'block_instance'">
                                            <a :href="row.extra_data.owner_type === 'page' ? '<?= route_to('admin.cms.pages') ?>/' + row.extra_data.owner_id + '/blocks/' + row.resource_id + '/edit' : '<?= route_to('admin.cms.entries') ?>/' + row.extra_data.owner_id + '/blocks/' + row.resource_id + '/edit'" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-900">
                                                <?= esc(lang('Translations.action_translate')) ?>
                                            </a>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </section>
</div>
