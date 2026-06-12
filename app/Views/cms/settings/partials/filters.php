<?php /** @var array $limitOptions */ ?>

<div class="mt-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('App.search') ?></label>
        <input type="text" name="search" value="<?= esc((string) request()->getGet('search')) ?>"
            placeholder="<?= esc(lang('Cms.settings_search_placeholder')) ?>"
            class="<?= esc(filter_input_class()) ?>" data-table-debounce="350">
    </div>

    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('Cms.field_setting_group') ?></label>
        <input type="text" name="setting_group" value="<?= esc((string) request()->getGet('setting_group')) ?>"
            placeholder="<?= esc(lang('Cms.field_setting_group_placeholder') ?? 'Filter by group') ?>"
            class="<?= esc(filter_input_class()) ?>" data-table-debounce="350">
    </div>

    <?= view('layouts/partials/filter_limit', ['limitOptions' => $limitOptions ?? [10, 25, 50, 100]]) ?>
</div>
