<?php
$menu = $menu ?? [];
$item = $item ?? [];
$itemTranslations = [];
if (!empty($item['translations']) && is_array($item['translations'])) {
    foreach ($item['translations'] as $t) {
        $itemTranslations[(int)$t['language_id']] = $t;
    }
}
?>
<div class="mb-4">
    <a href="<?= route_to('admin.cms.menus.show', $menuId) ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= lang('Menus.details') ?></a>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 max-w-2xl" x-data="{ linkType: '<?= esc($item['link_type'] ?? 'page') ?>' }">
    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?= lang('Menus.items_edit') ?? 'Edit Menu Item' ?> (Menu: <?= esc($menu['menu_key'] ?? '') ?>)</h3>

    <?php if (session()->has('error')) : ?>
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700">
            <?= esc(session('error')) ?>
        </div>
    <?php endif; ?>

    <form action="<?= route_to('admin.cms.menus.items.update', $menuId, $itemId) ?>" method="post" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="menu_id" value="<?= esc($menuId) ?>">

        <!-- Translations Fields (Labels & URL for Custom) -->
        <div class="space-y-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
            <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Translations & Labels</h4>
            <?php foreach ($languages as $lang): ?>
                <?php
                $langId = (int) $lang['id'];
                $labelVal = $itemTranslations[$langId]['label'] ?? '';
                $urlVal = $itemTranslations[$langId]['custom_url'] ?? '';
                ?>
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700">Label (<?= esc($lang['name']) ?>) <span class="text-red-500">*</span></label>
                    <input type="text" name="translations[<?= esc($langId) ?>][label]" value="<?= esc($labelVal) ?>" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200" placeholder="e.g. Home, Contact Us">
                    
                    <div x-show="linkType === 'custom_url'" class="mt-2 space-y-1">
                        <label class="block text-[11px] font-semibold text-gray-600">Custom URL (<?= esc($lang['name']) ?>)</label>
                        <input type="text" name="translations[<?= esc($langId) ?>][custom_url]" value="<?= esc($urlVal) ?>" class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-1.5 text-xs text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200" placeholder="e.g. /my-custom-page, https://example.com">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Link Type Selector -->
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Link Type</label>
            <select name="link_type" x-model="linkType" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200">
                <option value="page">Page</option>
                <option value="custom_url">Custom URL</option>
                <option value="no_link">No Link (Placeholder)</option>
            </select>
        </div>

        <!-- Page Selector (visible only when linkType === 'page') -->
        <div x-show="linkType === 'page'" class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">Target Page <span class="text-red-500">*</span></label>
            <select name="page_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200">
                <option value="">-- Select Page --</option>
                <?php foreach ($pages as $id => $title): ?>
                    <option value="<?= esc($id) ?>" <?= (string)($item['page_id'] ?? '') === (string)$id ? 'selected' : '' ?>><?= esc($title) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Parent Item (Optional) -->
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Parent Item (Optional)</label>
            <select name="parent_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200">
                <option value="">-- Root Level --</option>
                <?php foreach ($items as $itm): ?>
                    <?php if ((string)$itm['id'] !== (string)$itemId): ?>
                        <option value="<?= esc((string)$itm['id']) ?>" <?= (string)($item['parent_id'] ?? '') === (string)$itm['id'] ? 'selected' : '' ?>><?= esc($itm['translations'][0]['label'] ?? $itm['label'] ?? 'Item #' . $itm['id']) ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Link Target -->
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Link Target</label>
                <select name="link_target" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200">
                    <option value="_self" <?= ($item['link_target'] ?? '_self') === '_self' ? 'selected' : '' ?>>Same Tab (_self)</option>
                    <option value="_blank" <?= ($item['link_target'] ?? '_self') === '_blank' ? 'selected' : '' ?>>New Tab (_blank)</option>
                </select>
            </div>

            <!-- Sort Order -->
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Sort Order</label>
                <input type="number" name="sort_order" value="<?= esc((string)($item['sort_order'] ?? 0)) ?>" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Icon -->
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Icon (optional)</label>
                <input type="text" name="icon" value="<?= esc($item['icon'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200" placeholder="e.g. home, settings">
            </div>

            <!-- CSS Class -->
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">CSS Class (optional)</label>
                <input type="text" name="css_class" value="<?= esc($item['css_class'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200" placeholder="e.g. nav-highlight font-bold">
            </div>
        </div>

        <!-- Active Toggle -->
        <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" name="is_active" value="1" <?= !empty($item['is_active']) ? 'checked' : '' ?> id="is_active" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
            <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-150">
            <a href="<?= route_to('admin.cms.menus.show', $menuId) ?>" class="px-4 py-2 text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg shadow-sm">Cancel</a>
            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-sm">Save Changes</button>
        </div>
    </form>
</section>
