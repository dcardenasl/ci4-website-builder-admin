<?php
$page          = $page          ?? [];
$publicSiteUrl = $publicSiteUrl ?? '';
$blocks        = $blocks        ?? [];
$blockTypes    = $blockTypes    ?? [];
$languages     = $languages     ?? [];

// Build preview URL from the first translation slug
$previewSlug = '';
if (! empty($page['translations']) && is_array($page['translations'])) {
    $previewSlug = (string) ($page['translations'][0]['slug'] ?? '');
}
$previewUrl = $publicSiteUrl !== '' && $previewSlug !== ''
    ? $publicSiteUrl . '/' . ltrim($previewSlug, '/')
    : '';

$status = $page['status'] ?? '';

// Build language code map: id → uppercase code (e.g. 3 → 'ES')
$langCodeMap = [];
foreach ($languages as $l) {
    if (is_array($l) && isset($l['id'], $l['code'])) {
        $langCodeMap[(int) $l['id']] = strtoupper((string) $l['code']);
    }
}
?>

<div class="mb-4">
    <a href="<?= route_to('admin.cms.pages') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= lang('Pages.pages_title') ?></a>
</div>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($page)): ?>
    <?php $itemId = (string) ($page['id'] ?? ''); ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <section class="lg:col-span-2 bg-white border border-gray-200 rounded-xl shadow-sm p-5">

        <!-- Header: title + actions -->
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900"><?= lang('Pages.pages_details') ?></h3>
                <?php if (! empty($page['title'])): ?>
                    <p class="text-sm text-gray-500 mt-0.5"><?= esc($page['title']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Action buttons — grouped by intent -->
            <div class="hidden">

                <!-- Group 1: View / navigate -->
                <?php if ($previewUrl !== ''): ?>
                <a href="<?= esc($previewUrl) ?>" target="_blank" rel="noopener noreferrer"
                   class="<?= esc(action_button_class('neutral')) ?>">
                    <?= ui_icon('external-link', 'h-3.5 w-3.5') ?>
                    <span><?= esc(lang('Pages.blocks_view_page')) ?></span>
                </a>
                <?php endif; ?>

                <a href="<?= route_to('admin.cms.pages.blocks', $itemId) ?>" class="<?= esc(action_button_class('neutral')) ?>">
                    <?= ui_icon('layout-template', 'h-3.5 w-3.5') ?>
                    <span><?= esc(lang('Pages.manage_blocks')) ?></span>
                </a>

                <!-- Separator -->
                <span class="w-px h-5 bg-gray-200 self-center"></span>

                <!-- Group 2: Edit -->
                <a href="<?= route_to('admin.cms.pages.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?>">
                    <?= ui_icon('pencil', 'h-3.5 w-3.5') ?>
                    <span><?= lang('App.edit') ?></span>
                </a>

                <!-- Separator -->
                <span class="w-px h-5 bg-gray-200 self-center"></span>

                <!-- Group 3: Status transitions -->
                <?php if ($status !== 'published'): ?>
                <form method="post" action="<?= route_to('admin.cms.pages.publish', $itemId) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="<?= esc(action_button_class('neutral')) ?>">
                        <?= ui_icon('globe', 'h-3.5 w-3.5') ?>
                        <span><?= esc(lang('Pages.pages_publish')) ?></span>
                    </button>
                </form>
                <?php endif; ?>

                <?php if ($status !== 'archived'): ?>
                <form method="post" action="<?= route_to('admin.cms.pages.archive', $itemId) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="<?= esc(action_button_class('neutral')) ?>">
                        <?= ui_icon('archive', 'h-3.5 w-3.5') ?>
                        <span><?= esc(lang('Pages.pages_archive')) ?></span>
                    </button>
                </form>
                <?php endif; ?>

                <!-- Separator -->
                <span class="w-px h-5 bg-gray-200 self-center"></span>

                <!-- Group 4: Destructive -->
                <form method="post" action="<?= route_to('admin.cms.pages.delete', $itemId) ?>"
                      x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($page['title'] ?? $page['slug'] ?? null), 'js') ?>', () => $el.submit())">
                    <?= csrf_field() ?>
                    <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
                        <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                        <span><?= esc(lang('App.delete')) ?></span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Page metadata -->
        <dl class="hidden">
            <?= view('components/display/field_row', [
                'label' => 'Pages.field_page_type',
                'value' => ! empty($page['page_type'])
                    ? '<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">' . esc($page['page_type']) . '</span>'
                    : '—',
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Pages.field_status',
                'value' => ! empty($page['status']) ? cms_status_badge($page['status']) : '—',
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Pages.field_parent_id',
                'value' => ($pages[(string) ($page['parent_id'] ?? '')] ?? ($page['parent_id'] ?? '—'))
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Pages.field_is_in_sitemap',
                'value' => view('components/table/boolean_cell', ['value' => $page['is_in_sitemap'] ?? false]),
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Pages.field_sitemap_priority',
                'value' => $page['sitemap_priority'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Pages.field_published_at',
                'value' => $page['published_at'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Pages.field_scheduled_at',
                'value' => $page['scheduled_at'] ?? '—'
            ]) ?>
            <div>
                <dt class="text-gray-500"><?= lang('TableColumns.created_at') ?></dt>
                <dd class="mt-1 text-gray-900"><?= esc((string) ($page['created_at'] ?? '-')) ?></dd>
            </div>
        </dl>

        <!-- Translations -->
        <?php if (! empty($page['translations']) && is_array($page['translations'])): ?>
            <div class="mt-6 border-t border-gray-200 pt-6">
                <h4 class="text-md font-semibold text-gray-800"><?= esc(lang('Pages.translations_title')) ?></h4>
                <div class="mt-4 space-y-4">
                    <?php foreach ($page['translations'] as $t):
                        $tLangId  = (int) ($t['language_id'] ?? 0);
                        $tLangCode = $langCodeMap[$tLangId] ?? (string) $tLangId;
                        $tSlug    = (string) ($t['slug'] ?? '');
                        $tPreview = $publicSiteUrl !== '' && $tSlug !== ''
                            ? $publicSiteUrl . '/' . ltrim($tSlug, '/')
                            : '';
                        ?>
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50">
                            <div class="font-bold text-sm text-brand-700 pb-2 border-b border-gray-200 flex justify-between items-center">
                                <span><?= esc(lang('CmsLanguages.field_code')) ?>: <?= esc($tLangCode) ?></span>
                                <div class="flex items-center gap-3">
                                    <span class="text-gray-500 font-mono text-xs">/<?= esc($tSlug) ?></span>
                                    <?php if ($tPreview !== ''): ?>
                                    <a href="<?= esc($tPreview) ?>" target="_blank" rel="noopener noreferrer"
                                       class="text-xs text-brand-600 hover:text-brand-700 flex items-center gap-1">
                                        <?= ui_icon('external-link', 'h-3 w-3') ?>
                                        <?= esc(lang('Pages.blocks_view_page')) ?>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= route_to('admin.cms.pages.edit', $itemId) ?>?focus_lang=<?= $tLangId ?>"
                                       class="<?= esc(action_button_class('neutral')) ?> !text-xs !py-1 !px-2">
                                        <?= ui_icon('pencil', 'h-3 w-3') ?>
                                        <?= esc(lang('Pages.edit_in_language', ['lang' => $tLangCode])) ?>
                                    </a>
                                </div>
                            </div>
                            <dl class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-xs">
                                <div>
                                    <dt class="text-gray-500 font-semibold"><?= esc(lang('Pages.translation_title_label')) ?></dt>
                                    <dd class="text-gray-900 mt-0.5 font-medium"><?= esc($t['title'] ?? '—') ?></dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-semibold"><?= esc(lang('Pages.translation_excerpt_label')) ?></dt>
                                    <dd class="text-gray-900 mt-0.5"><?= esc($t['excerpt'] ?? '—') ?></dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-semibold"><?= esc(lang('Pages.translation_meta_title_label')) ?></dt>
                                    <dd class="text-gray-900 mt-0.5"><?= esc($t['meta_title'] ?? '—') ?></dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-semibold"><?= esc(lang('Pages.translation_meta_description_label')) ?></dt>
                                    <dd class="text-gray-900 mt-0.5"><?= esc($t['meta_description'] ?? '—') ?></dd>
                                </div>
                            </dl>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Inline content blocks panel -->
        <?php
        $inlineReorderUrl = route_to('admin.cms.pages.blocks.reorder', $itemId);
?>
        <div class="mt-6 border-t border-gray-200 pt-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="text-base font-semibold text-gray-900">
                        <?= esc(lang('Pages.blocks_section_title')) ?>
                    </h4>
                    <p class="text-sm text-gray-500 mt-0.5">
                        <?= esc(lang('Pages.blocks_section_desc')) ?>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= route_to('admin.cms.pages.blocks', $itemId) ?>"
                       class="<?= esc(action_button_class('neutral')) ?>">
                        <?= ui_icon('layout-template', 'h-3.5 w-3.5') ?>
                        <?= esc(lang('Pages.manage_blocks')) ?>
                    </a>
                    <?php if (has_permission('cms.pages.write')): ?>
                    <a href="<?= route_to('admin.cms.pages.blocks.create', $itemId) ?>"
                       class="<?= esc(action_button_class('primary')) ?>">
                        <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
                        <?= esc(lang('Pages.blocks_add')) ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div x-data="blockSorter('<?= esc($inlineReorderUrl) ?>')">
                <!-- Saving indicator -->
                <div class="flex items-center justify-between mb-3">
                    <span x-show="saving" x-cloak class="text-xs text-gray-400 italic">
                        <?= esc(lang('Pages.blocks_saving')) ?>
                    </span>
                    <span x-show="saved && !saving" x-cloak class="text-xs text-green-600">
                        <?= esc(lang('Pages.blocks_saved')) ?>
                    </span>
                    <span class="flex-1"></span>
                    <button x-show="dirty && !saving" x-cloak type="button"
                            @click="saveOrder()"
                            class="<?= esc(action_button_class('primary')) ?> !text-xs !py-1.5 !px-3">
                        <?= ui_icon('save', 'h-3.5 w-3.5') ?>
                        <?= esc(lang('Pages.blocks_save_order')) ?>
                    </button>
                </div>

                <?php if (empty($blocks)): ?>
                    <!-- Empty state -->
                    <div class="text-center py-10 border border-dashed border-gray-200 rounded-xl">
                        <?= ui_icon('layout-template', 'h-8 w-8 mx-auto text-gray-300 mb-2') ?>
                        <p class="text-sm font-medium text-gray-500"><?= esc(lang('Pages.blocks_empty_title')) ?></p>
                        <p class="text-xs text-gray-400 mt-1"><?= esc(lang('Pages.blocks_empty_desc')) ?></p>
                    </div>
                <?php else: ?>
                    <ul id="block-sortable-list" class="space-y-2">
                        <?php foreach ($blocks as $block):
                            $blockTypeData = $blockTypes[(int) ($block['block_id'] ?? 0)] ?? [];
                            $blockTypeName = $blockTypeData['name'] ?? 'Block #' . ($block['block_id'] ?? '?');
                            $isActive      = ! empty($block['is_active']);
                            $blockId       = (string) ($block['id'] ?? '');

                            // Content preview: first non-empty string from the first translation's block_data
                            $previewText = '';
                            $firstTrans  = is_array($block['translations'] ?? null) ? ($block['translations'][0] ?? []) : [];
                            $blockData   = is_array($firstTrans['block_data'] ?? null) ? $firstTrans['block_data'] : [];
                            foreach ($blockData as $val) {
                                if (is_string($val) && trim(strip_tags($val)) !== '') {
                                    $previewText = mb_strimwidth(strip_tags($val), 0, 80, '…');
                                    break;
                                }
                            }
                            ?>
                        <li data-id="<?= esc($blockId) ?>"
                            class="flex items-center gap-3 bg-white border border-gray-200 rounded-lg px-3 py-2.5 cursor-grab active:cursor-grabbing shadow-sm hover:shadow-md transition-shadow">
                            <!-- Drag handle -->
                            <span data-drag-handle class="text-gray-300 hover:text-gray-500 flex-shrink-0 cursor-grab">
                                <?= ui_icon('grip-vertical', 'h-4 w-4') ?>
                            </span>

                            <!-- Block info -->
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-semibold text-gray-700"><?= esc($blockTypeName) ?></span>
                                <?php if ($previewText !== ''): ?>
                                    <p class="text-xs text-gray-400 mt-0.5 truncate italic"><?= esc($previewText) ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Status badge -->
                            <span class="text-xs px-1.5 py-0.5 rounded-full flex-shrink-0 <?= $isActive ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                                <?= esc($isActive ? lang('Pages.blocks_status_active') : lang('Pages.blocks_status_inactive')) ?>
                            </span>

                            <!-- Quick actions -->
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <?php if (has_permission('cms.pages.write')): ?>
                                    <?php if (! empty($blockTypeData['supports_children'])): ?>
                                    <a href="<?= route_to('admin.cms.pages.blocks.children', $itemId, $blockId) ?>"
                                       class="<?= esc(action_button_class('neutral')) ?> !text-xs !py-1 !px-2">
                                        <?= ui_icon('layers', 'h-3 w-3') ?>
                                        <?= esc(lang('Pages.blocks_action_slides')) ?>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= route_to('admin.cms.pages.blocks.edit', $itemId, $blockId) ?>"
                                       class="<?= esc(action_button_class('neutral')) ?> !text-xs !py-1 !px-2">
                                        <?= ui_icon('pencil', 'h-3 w-3') ?>
                                        <?= esc(lang('Pages.blocks_action_edit')) ?>
                                    </a>
                                    <form method="post" action="<?= route_to('admin.cms.pages.blocks.delete', $itemId, $blockId) ?>"
                                          x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($blockTypeData['name'] ?? $blockTypeData['block_key'] ?? $blockId), 'js') ?>', () => $el.submit())">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="<?= esc(action_button_class('danger')) ?> !text-xs !py-1 !px-2">
                                            <?= ui_icon('trash', 'h-3 w-3') ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Drag hint -->
                    <?php if (has_permission('cms.pages.write')): ?>
                        <p class="mt-3 text-xs text-gray-400 text-center">
                            <?= esc(lang('Pages.blocks_drag_hint')) ?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

    </section>

    <aside class="space-y-6">
        <?= view('components/display/admin_meta_panel', [
            'title' => 'Pages.pages_details',
            'items' => [
                ['label' => 'Pages.field_page_type', 'value' => $page['page_type'] ?? '—'],
                ['label' => 'Pages.field_status', 'value' => ! empty($page['status']) ? cms_status_badge($page['status']) : '—', 'isHtml' => true],
                ['label' => 'Pages.field_parent_id', 'value' => ($pages[(string) ($page['parent_id'] ?? '')] ?? ($page['parent_id'] ?? '—'))],
                ['label' => 'Pages.field_is_in_sitemap', 'value' => view('components/table/boolean_cell', ['value' => $page['is_in_sitemap'] ?? false]), 'isHtml' => true],
                ['label' => 'Pages.field_sitemap_priority', 'value' => $page['sitemap_priority'] ?? '—'],
                ['label' => 'Pages.field_published_at', 'value' => $page['published_at'] ?? '—'],
                ['label' => 'Pages.field_scheduled_at', 'value' => $page['scheduled_at'] ?? '—'],
                ['label' => 'TableColumns.created_at', 'value' => (string) ($page['created_at'] ?? '-')],
            ],
        ]) ?>

        <?php ob_start(); ?>
        <?php if ($previewUrl !== ''): ?>
            <a href="<?= esc($previewUrl) ?>" target="_blank" rel="noopener noreferrer" class="<?= esc(action_button_class()) ?> w-full justify-center text-center">
                <?= ui_icon('external-link', 'h-3.5 w-3.5') ?>
                <span><?= esc(lang('Pages.blocks_view_page')) ?></span>
            </a>
        <?php endif; ?>
        <?php if (has_permission('cms.pages.write')): ?>
            <a href="<?= route_to('admin.cms.pages.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center">
                <?= ui_icon('pencil', 'h-3.5 w-3.5') ?>
                <span><?= lang('App.edit') ?></span>
            </a>
            <a href="<?= route_to('admin.cms.pages.blocks', $itemId) ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center">
                <?= ui_icon('layout-template', 'h-3.5 w-3.5') ?>
                <span><?= esc(lang('Pages.manage_blocks')) ?></span>
            </a>
            <?php if ($status !== 'published'): ?>
                <form method="post" action="<?= route_to('admin.cms.pages.publish', $itemId) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="<?= esc(action_button_class()) ?> w-full justify-center">
                        <?= ui_icon('globe', 'h-3.5 w-3.5') ?>
                        <span><?= esc(lang('Pages.pages_publish')) ?></span>
                    </button>
                </form>
            <?php endif; ?>
            <?php if ($status !== 'archived'): ?>
                <form method="post" action="<?= route_to('admin.cms.pages.archive', $itemId) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="<?= esc(action_button_class()) ?> w-full justify-center">
                        <?= ui_icon('archive', 'h-3.5 w-3.5') ?>
                        <span><?= esc(lang('Pages.pages_archive')) ?></span>
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
        <?php $actionsContent = ob_get_clean(); ?>

        <?php ob_start(); ?>
        <?php if (has_permission('cms.pages.write')): ?>
            <form method="post" action="<?= route_to('admin.cms.pages.delete', $itemId) ?>"
                  x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($page['title'] ?? $page['slug'] ?? null), 'js') ?>', () => $el.submit())">
                <?= csrf_field() ?>
                <button type="submit" class="<?= esc(action_button_class('danger')) ?> w-full justify-center">
                    <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                    <span><?= esc(lang('App.delete')) ?></span>
                </button>
            </form>
        <?php endif; ?>
        <?php $dangerContent = ob_get_clean(); ?>

        <?= view('components/display/admin_actions_panel', [
            'content' => $actionsContent,
            'dangerContent' => $dangerContent,
        ]) ?>
    </aside>
    </div>
<?php endif; ?>
