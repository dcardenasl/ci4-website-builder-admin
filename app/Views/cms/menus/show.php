<?php $menu = $menu ?? []; ?>
<div class="mb-4">
    <a href="<?= route_to('admin.cms.menus') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= lang('Menus.menus_title') ?></a>
</div>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($menu)): ?>
    <?php $itemId = (string) ($menu['id'] ?? ''); ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Menu Details Card -->
        <div class="lg:col-span-1 space-y-6">
            <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900"><?= lang('Menus.menus_details') ?></h3>
                    <div class="flex items-center gap-2">
                        <a href="<?= route_to('admin.cms.menus.edit', $itemId) ?>" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>

                        <form method="post" action="<?= route_to('admin.cms.menus.delete', $itemId) ?>" onsubmit="return confirm('<?= esc(confirm_delete_message($menu['name'] ?? $menu['menu_key'] ?? null), 'js') ?>');">
                            <?= csrf_field() ?>
                            <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
                                <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                                <?= esc(lang('App.delete')) ?>
                            </button>
                        </form>
                    </div>
                </div>

                <dl class="mt-4 space-y-3 text-sm">
                    <?= view('components/display/field_row', [
                        'label' => 'Menus.field_menu_key',
                        'value' => $menu['menu_key'] ?? '—'
                    ]) ?>
                    <?= view('components/display/field_row', [
                        'label' => 'Menus.field_is_active',
                        'value' => view('components/table/boolean_cell', ['value' => $menu['is_active'] ?? false]),
                        'isHtml' => true
                    ]) ?>
                    <div>
                        <dt class="text-gray-500 font-medium"><?= lang('TableColumns.created_at') ?></dt>
                        <dd class="mt-1 text-gray-900"><?= esc((string) ($menu['created_at'] ?? '-')) ?></dd>
                    </div>
                </dl>
            </section>

            <!-- Translations List -->
            <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3"><?= esc(lang('Menus.menus_translations_title')) ?></h4>
                <?php if (! empty($menu['translations']) && is_array($menu['translations'])): ?>
                    <div class="divide-y divide-gray-150">
                        <?php foreach ($menu['translations'] as $t): ?>
                            <div class="py-2 text-sm">
                                <span class="font-semibold text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded text-xs mr-2"><?= esc(lang('Menus.menus_lang_id_label')) ?>: <?= esc((string)$t['language_id']) ?></span>
                                <span class="text-gray-900 font-medium"><?= esc($t['name'] ?? '—') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-xs text-gray-500"><?= esc(lang('Menus.menus_no_translations')) ?></p>
                <?php endif; ?>
            </section>
        </div>

        <!-- Menu Items Hierarchy Card -->
        <div class="lg:col-span-2">
            <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900"><?= lang('Menus.menus_items_title') ?></h3>
                    <a href="<?= route_to('admin.cms.menus.items.create', $itemId) ?>" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-700">
                        <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
                        <?= lang('Menus.menus_items_create') ?>
                    </a>
                </div>

                <?php if (empty($items)): ?>
                    <?= view('components/display/empty_state', [
                        'title' => 'Menus.menus_items_empty' ?? 'No items inside this menu',
                        'description' => 'Menus.menus_items_empty_desc' ?? 'Get started by creating a new link for this menu.',
                        'actionUrl' => route_to('admin.cms.menus.items.create', $itemId),
                        'actionLabel' => 'Menus.menus_items_create' ?? 'Add Item',
                    ]) ?>
                <?php else: ?>
                    <div class="border border-gray-200 rounded-xl overflow-hidden divide-y divide-gray-150">
                        <?php
                        if (! function_exists('renderMenuTree')):
                            function renderMenuTree(array $items, array $languages, ?int $parentId = null, int $depth = 0): void
                            {
                                foreach ($items as $item) {
                                    $itemParentId = isset($item['parent_id']) && $item['parent_id'] !== '' ? (int) $item['parent_id'] : null;
                                    if ($itemParentId === $parentId) {
                                        // Find label
                                        $label = $item['label'] ?? '';
                                        if (empty($label) && !empty($item['translations']) && is_array($item['translations'])) {
                                            $defaultLangId = null;
                                            foreach ($languages as $lang) {
                                                if (!empty($lang['is_default'])) {
                                                    $defaultLangId = (int)$lang['id'];
                                                    break;
                                                }
                                            }
                                            foreach ($item['translations'] as $t) {
                                                if ($defaultLangId !== null && (int)($t['language_id'] ?? 0) === $defaultLangId) {
                                                    $label = $t['label'];
                                                    break;
                                                }
                                            }
                                            if (empty($label)) {
                                                $label = $item['translations'][0]['label'] ?? '';
                                            }
                                        }
                                        ?>
                                        <div class="flex items-center justify-between py-3 px-4 hover:bg-gray-50/50" style="padding-left: <?= 1 + ($depth * 1.5) ?>rem">
                                            <div class="flex items-center gap-2">
                                                <?php if ($depth > 0): ?>
                                                    <span class="text-gray-400">└─</span>
                                                <?php endif; ?>
                                                <span class="font-medium text-gray-900"><?= esc($label ?: 'Untitled') ?></span>
                                                <span class="text-xs text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded font-mono">
                                                    <?php if ($item['link_type'] === 'page'): ?>
                                                        <?= esc(lang('Menus.items_link_type_page')) ?>
                                                        <?php if (!empty($item['page_id'])): ?>
                                                            : <?= esc((string) $item['page_id']) ?>
                                                        <?php endif; ?>
                                                    <?php elseif ($item['link_type'] === 'entry'): ?>
                                                        <?= esc(lang('Menus.items_link_type_entry')) ?>
                                                        <?php if (!empty($item['entry_id'])): ?>
                                                            : <?= esc((string) $item['entry_id']) ?>
                                                        <?php endif; ?>
                                                    <?php elseif ($item['link_type'] === 'collection_listing'): ?>
                                                        <?= esc(lang('Menus.items_link_type_collection_listing')) ?>
                                                        <?php if (!empty($item['collection_id'])): ?>
                                                            : <?= esc((string) $item['collection_id']) ?>
                                                        <?php endif; ?>
                                                    <?php elseif ($item['link_type'] === 'custom_url'): ?>
                                                        <?= esc(lang('Menus.items_link_type_custom_url')) ?>
                                                        : <?= esc($item['translations'][0]['custom_url'] ?? '') ?>
                                                    <?php else: ?>
                                                        <?= esc(lang('Menus.items_link_type_no_link')) ?>
                                                    <?php endif; ?>
                                                </span>
                                                <?php if (empty($item['is_active'])): ?>
                                                <span class="text-[10px] font-bold text-red-600 bg-red-50 border border-red-100 px-1.5 py-0.5 rounded uppercase"><?= esc(lang('Menus.menus_item_inactive')) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex items-center gap-2">
                                                <a href="<?= route_to('admin.cms.menus.items.edit', $item['menu_id'], $item['id']) ?>" class="px-2.5 py-1 text-xs border border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100 rounded-lg shadow-sm transition"><?= esc(lang('Menus.menus_item_edit')) ?></a>
                                                <form method="post" action="<?= route_to('admin.cms.menus.items.delete', $item['menu_id'], $item['id']) ?>" onsubmit="return confirm('<?= esc(confirm_delete_message($label ?: ($item['translations'][0]['label'] ?? null)), 'js') ?>');" class="inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="px-2.5 py-1 text-xs border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg shadow-sm transition"><?= esc(lang('Menus.menus_item_delete')) ?></button>
                                                </form>
                                            </div>
                                        </div>
                                        <?php
                                        renderMenuTree($items, $languages, (int)$item['id'], $depth + 1);
                                    }
                                }
                            }
endif;

renderMenuTree($items, $languages);
?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
<?php endif; ?>
