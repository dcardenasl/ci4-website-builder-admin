<?php /** @var array $limitOptions */ ?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= route_to('admin.cms.tags.data') ?>',
        pageUrl: '<?= route_to('admin.cms.tags') ?>',
        routes: {
            showBase: '<?= route_to('admin.cms.tags') ?>',
            editBase: '<?= route_to('admin.cms.tags') ?>'
        },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
    })" x-init="init()">

    <?= view('layouts/partials/table_toolbar', [
        'title'       => lang('Tags.tags_title'),
        'actionsView' => 'cms/tags/partials/toolbar_actions',
    ]) ?>
    

    <?= view('layouts/partials/filter_panel', [
        'actionUrl'          => route_to('admin.cms.tags'),
        'clearUrl'           => route_to('admin.cms.tags'),
        'hasFilters'         => has_active_filters(request()->getGet(), ['limit' => '25']),
        'reactiveHasFilters' => true,
        'filterDefaults'     => ['limit' => '25'],
        'fieldsView'         => 'cms/tags/partials/filters',
        'fieldsData'         => [
            'limitOptions' => $limitOptions ?? [10, 25, 50, 100],

        ],
        'submitLabel' => lang('App.search'),
    ]) ?>

    <div class="mt-6 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600" x-show="loading">
        <?= lang('Tags.tags_loading') ?>
    </div>
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'title' => 'App.no_results',
            'description' => 'App.no_results_desc',
            'actionUrl' => route_to('admin.cms.tags.create'),
            'actionLabel' => 'App.create',
        ]) ?>
    </template>
    <template x-if="!loading && !error && rows.length > 0">
        <div class="<?= esc(table_wrapper_class()) ?>">
            <div class="<?= esc(table_scroll_class()) ?>">
            <table class="<?= esc(table_class()) ?>">
                <thead class="<?= esc(table_head_class()) ?>">
                    <tr>
                        <th class="<?= esc(table_th_class()) ?>">
                            <span><?= lang('Tags.translation_name_label') ?></span>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('is_active')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('is_active')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Tags.field_is_active')])) ?>">
                                <span><?= lang('Tags.field_is_active') ?></span>
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
                            <td class="<?= esc(table_td_class('bold')) ?>" x-text="String(row.name ?? row.slug ?? '-')"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span
                                    :class="row.is_active ? 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800' : 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800'"
                                    x-text="row.is_active ? '<?= esc(lang('App.yes'), 'js') ?>' : '<?= esc(lang('App.no'), 'js') ?>'"
                                ></span>
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
