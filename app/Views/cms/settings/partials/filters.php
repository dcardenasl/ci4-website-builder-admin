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
        <select name="setting_group" class="<?= esc(filter_input_class()) ?>" data-table-filter>
            <option value=""><?= lang('Cms.filter_all_groups') ?></option>
            <option value="general" <?= request()->getGet('setting_group') === 'general' ? 'selected' : '' ?>><?= lang('Cms.group_general') ?></option>
            <option value="seo" <?= request()->getGet('setting_group') === 'seo' ? 'selected' : '' ?>><?= lang('Cms.group_seo') ?></option>
            <option value="cms_meta" <?= request()->getGet('setting_group') === 'cms_meta' ? 'selected' : '' ?>><?= lang('Cms.group_cms_meta') ?></option>
        </select>
    </div>

    <?= view('layouts/partials/filter_limit', ['limitOptions' => $limitOptions ?? [10, 25, 50, 100]]) ?>
</div>
