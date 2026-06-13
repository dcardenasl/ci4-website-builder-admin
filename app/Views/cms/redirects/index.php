<?php /** @var array $limitOptions */ ?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= route_to('admin.cms.redirects.data') ?>',
        pageUrl: '<?= route_to('admin.cms.redirects') ?>',
        routes: {
            showBase: '<?= route_to('admin.cms.redirects') ?>',
            editBase: '<?= route_to('admin.cms.redirects') ?>'
        },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
    })" x-init="init()">

    <?= view('layouts/partials/table_toolbar', [
        'title'       => lang('Cms.redirects_title'),
        'actionsView' => 'cms/redirects/partials/toolbar_actions',
    ]) ?>
    <?php /* CSV scaffold hooks: components/table/export_button, components/form/export_import, components/form/import_preview */ ?>

    <?= view('layouts/partials/filter_panel', [
        'actionUrl'          => route_to('admin.cms.redirects'),
        'clearUrl'           => route_to('admin.cms.redirects'),
        'hasFilters'         => has_active_filters(request()->getGet(), ['limit' => '25']),
        'reactiveHasFilters' => true,
        'filterDefaults'     => ['limit' => '25'],
        'fieldsView'         => 'cms/redirects/partials/filters',
        'fieldsData'         => [
            'limitOptions' => $limitOptions ?? [10, 25, 50, 100],

        ],
        'submitLabel' => lang('App.search'),
    ]) ?>

    <div class="mt-6 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600" x-show="loading">
        <?= lang('Cms.redirects_loading') ?>
    </div>
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'title' => 'App.no_results',
            'description' => 'App.no_results_desc',
            'actionUrl' => route_to('admin.cms.redirects.create'),
            'actionLabel' => 'App.create',
        ]) ?>
    </template>
    <template x-if="!loading && !error && rows.length > 0">
        <div class="<?= esc(table_wrapper_class()) ?>">
            <div class="<?= esc(table_scroll_class()) ?>">
            <table class="<?= esc(table_class()) ?>">
                <thead class="<?= esc(table_head_class()) ?>">
                    <tr>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('from_path')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('from_path')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Cms.field_from_path')])) ?>">
                                <span><?= lang('Cms.field_from_path') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('from_path')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('to_path')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('to_path')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Cms.field_to_path')])) ?>">
                                <span><?= lang('Cms.field_to_path') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('to_path')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('status_code')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('status_code')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Cms.field_status_code')])) ?>">
                                <span><?= lang('Cms.field_status_code') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('status_code')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('is_active')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('is_active')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Cms.field_is_active')])) ?>">
                                <span><?= lang('Cms.field_is_active') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('is_active')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('created_at')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('created_at')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('TableColumns.created_at')])) ?>">
                                <span><?= lang('TableColumns.created_at') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('created_at')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>"><?= lang('TableColumns.actions') ?></th>
                    </tr>
                </thead>
                <tbody class="<?= esc(table_body_class()) ?>">
                    <template x-for="row in rows" :key="String(row.id ?? Math.random())">
                        <tr class="<?= esc(table_row_class()) ?>">
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.from_path ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.to_path ?? '-')"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10" x-text="String(row.status_code ?? '-')"></span>
                            </td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span class="inline-flex items-center">
                                    <template x-if="row.is_active">
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                    <template x-if="!row.is_active">
                                        <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </template>
                                </span>
                            </td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate(row.created_at)"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <div class="flex items-center gap-2">
                                    <a :href="showUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.view') ?></a>
                                    <a :href="editUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            </div>
        </div>
    </template>

    <?= view('layouts/partials/remote_pagination') ?>
</section>
