<?php
$menu = $menu ?? [];
$item = $item ?? [];
$itemTranslations = [];
if (! empty($item['translations']) && is_array($item['translations'])) {
    foreach ($item['translations'] as $t) {
        if (! is_array($t)) {
            continue;
        }
        $itemTranslations[(int) $t['language_id']] = $t;
    }
}
$resolveItemTranslation = static function (array $lang) use ($itemTranslations): array {
    $langId = (int) ($lang['id'] ?? 0);
    if (isset($itemTranslations[$langId])) {
        return $itemTranslations[$langId];
    }

    return [];
};
$linkType = old('link_type', 'page');
$selectedPageId = old('page_id', '');
$selectedEntryId = old('entry_id', '');
$selectedCollectionId = old('collection_id', '');

// Build ordered parent options with indentation
function buildParentOptions(array $items, ?int $parentId = null, int $depth = 0): array
{
    $options = [];
    foreach ($items as $item) {
        $pid = isset($item['parent_id']) && $item['parent_id'] !== '' ? (int) $item['parent_id'] : null;
        if ($pid === $parentId) {
            $prefix = str_repeat('  ', $depth) . ($depth > 0 ? '└ ' : '');
            $label = $item['label'] ?? 'Item #' . $item['id'];
            if (! empty($item['translations']) && is_array($item['translations'])) {
                foreach ($item['translations'] as $translation) {
                    if (is_array($translation) && ! empty($translation['label'])) {
                        $label = (string) $translation['label'];
                        break;
                    }
                }
            }
            $options[] = ['id' => (string) $item['id'], 'label' => $prefix . $label];
            $children = buildParentOptions($items, (int) $item['id'], $depth + 1);
            foreach ($children as $child) {
                $options[] = $child;
            }
        }
    }
    return $options;
}
$parentOptions = buildParentOptions($items);
$suggestedSortOrder = count($items);
?>

<div class="mb-4 flex items-center gap-2 text-sm">
    <a href="<?= route_to('admin.cms.menus.show', $menuId) ?>" class="text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('Menus.menus_back_to_detail')) ?></a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-500 font-mono"><?= esc($menu['menu_key'] ?? '') ?></span>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" x-data="{ linkType: '<?= esc($linkType) ?>' }">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-semibold text-gray-900"><?= esc(lang('Menus.items_create_title')) ?></h3>
        </div>

        <?php if (session()->has('error')) : ?>
            <div class="mx-6 mt-4 rounded-lg bg-red-50 p-4 text-sm text-red-700">
                <?= esc(session('error')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= route_to('admin.cms.menus.items.store', $menuId) ?>" method="post" class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
            <?= csrf_field() ?>
            <input type="hidden" name="menu_id" value="<?= esc($menuId) ?>">

            <div class="space-y-6 lg:col-span-2">
            <!-- Translations: labels per language -->
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <?= ui_icon('languages', 'h-4 w-4 text-gray-400') ?>
                    <h4 class="text-sm font-semibold text-gray-700"><?= esc(lang('Menus.items_translations_title')) ?></h4>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 divide-y divide-gray-200 overflow-hidden">
                    <?php foreach ($languages as $key => $lang): ?>
                        <?php
                        $lang = is_array($lang) ? $lang : [];
                        $langId = isset($lang['id']) ? (int) $lang['id'] : (is_numeric($key) ? (int) $key : 0);
                        if ($langId <= 0) {
                            continue;
                        }
                        $langName = (string) ($lang['name'] ?? $lang['label'] ?? $langId);
                        $translation = $resolveItemTranslation($lang);
                        ?>
                        <div class="p-4 space-y-3">
                            <span class="inline-flex items-center rounded-md bg-brand-50 text-brand-700 border border-brand-100 px-2 py-0.5 text-xs font-semibold font-mono"><?= esc($langName) ?></span>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    <?= esc(lang('Menus.items_label_label')) ?> <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="translations[<?= esc($langId) ?>][label]" value="<?= esc(old("translations.{$langId}.label", $translation['label'] ?? '')) ?>" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200"
                                    placeholder="<?= esc(lang('Menus.items_label_placeholder')) ?>">
                            </div>

                            <div x-show="linkType === 'custom_url'" x-cloak>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    <?= esc(lang('Menus.items_custom_url_label')) ?>
                                </label>
                                <input type="text" name="translations[<?= esc($langId) ?>][custom_url]" value="<?= esc(old("translations.{$langId}.custom_url", $translation['custom_url'] ?? '')) ?>"
                                    x-bind:disabled="linkType !== 'custom_url'"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200"
                                    placeholder="<?= esc(lang('Menus.items_custom_url_placeholder')) ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            </div>

            <aside class="space-y-6">
            <!-- Link Type -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_link_type_label')) ?></label>
                <select name="link_type" x-model="linkType" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200">
                    <option value="page"><?= esc(lang('Menus.items_link_type_page')) ?></option>
                    <option value="entry"><?= esc(lang('Menus.items_link_type_entry')) ?></option>
                    <option value="collection_listing"><?= esc(lang('Menus.items_link_type_collection_listing')) ?></option>
                    <option value="custom_url"><?= esc(lang('Menus.items_link_type_custom_url')) ?></option>
                    <option value="no_link"><?= esc(lang('Menus.items_link_type_no_link')) ?></option>
                </select>
            </div>

            <!-- Target selectors (conditional) -->
            <div x-show="linkType === 'page'" x-cloak>
                <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_target_page_label')) ?> <span class="text-red-500">*</span></label>
                <select name="page_id" x-bind:disabled="linkType !== 'page'" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200">
                    <option value=""><?= esc(lang('Menus.items_target_page_placeholder')) ?></option>
                    <?php foreach ($pages as $id => $title): ?>
                        <option value="<?= esc($id) ?>" <?= (string) $selectedPageId === (string) $id ? 'selected' : '' ?>><?= esc($title) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div x-show="linkType === 'entry'" x-cloak>
                <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_target_entry_label')) ?> <span class="text-red-500">*</span></label>
                <select name="entry_id" x-bind:disabled="linkType !== 'entry'" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200">
                    <option value=""><?= esc(lang('Menus.items_target_entry_placeholder')) ?></option>
                    <?php foreach ($entries as $id => $title): ?>
                        <option value="<?= esc($id) ?>" <?= (string) $selectedEntryId === (string) $id ? 'selected' : '' ?>><?= esc($title) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div x-show="linkType === 'collection_listing'" x-cloak>
                <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_target_collection_label')) ?> <span class="text-red-500">*</span></label>
                <select name="collection_id" x-bind:disabled="linkType !== 'collection_listing'" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200">
                    <option value=""><?= esc(lang('Menus.items_target_collection_placeholder')) ?></option>
                    <?php foreach ($collections as $id => $title): ?>
                        <option value="<?= esc($id) ?>" <?= (string) $selectedCollectionId === (string) $id ? 'selected' : '' ?>><?= esc($title) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Structure: parent + sort order -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_parent_label')) ?></label>
                    <select name="parent_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200">
                        <option value=""><?= esc(lang('Menus.items_parent_placeholder')) ?></option>
                        <?php foreach ($parentOptions as $opt): ?>
                            <option value="<?= esc($opt['id']) ?>"><?= esc($opt['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Menus.items_parent_help')) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_sort_order_label')) ?></label>
                    <input type="number" name="sort_order" value="<?= esc(old('sort_order', (string) $suggestedSortOrder)) ?>" min="0" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200">
                    <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Menus.items_sort_order_help')) ?></p>
                </div>
            </div>

            <!-- Appearance: link target + icon + css class -->
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-4">
                <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Appearance</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_link_target_label')) ?></label>
                        <select name="link_target" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200">
                            <option value="_self" <?= old('link_target', '_self') === '_self' ? 'selected' : '' ?>><?= esc(lang('Menus.items_link_target_same')) ?></option>
                            <option value="_blank" <?= old('link_target', '_self') === '_blank' ? 'selected' : '' ?>><?= esc(lang('Menus.items_link_target_new')) ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_icon_label')) ?></label>
                        <input type="text" name="icon" value="<?= esc(old('icon', '')) ?>"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200"
                            placeholder="<?= esc(lang('Menus.items_icon_placeholder')) ?>">
                        <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Menus.items_icon_help')) ?></p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_css_class_label')) ?></label>
                    <input type="text" name="css_class" value="<?= esc(old('css_class', '')) ?>"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200"
                        placeholder="<?= esc(lang('Menus.items_css_class_placeholder')) ?>">
                </div>
            </div>

            <!-- Active toggle -->
            <?= view('components/form/boolean', [
                'name'      => 'is_active',
                'label'     => 'Menus.items_is_active_label',
                'value'     => old('is_active', '1') !== '0',
                'on_label'  => 'Menus.field_is_active_on',
                'off_label' => 'Menus.field_is_active_off',
            ]) ?>

            <!-- Actions -->
            <?= view('components/display/admin_actions_panel', [
                'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('Menus.items_add')) . '</button>'
                    . '<a href="' . esc(route_to('admin.cms.menus.show', $menuId), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('Menus.items_cancel')) . '</a>',
            ]) ?>
            </aside>
        </form>
    </section>
