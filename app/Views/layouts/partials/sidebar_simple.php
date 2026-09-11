<?php declare(strict_types=1); ?>
<?php
/**
 * Small shell for content editors. Permission checks remain in the list: this
 * file never grants an action merely because a link is visible.
 */
$navItemClass = 'flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors';
$navItemIdleClass = 'text-gray-300 hover:bg-gray-800 hover:text-white';
$navItemActiveClass = 'bg-brand-50 text-brand-700 shadow-sm';
$sections = [
    ['route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'dashboard', 'label' => lang('EditorUi.home'), 'permission' => null],
    ['route' => 'admin.cms.pages', 'match' => 'admin/cms/pages*', 'icon' => 'cms-page', 'label' => lang('EditorUi.pages'), 'permission' => 'cms.pages.read'],
    ['route' => 'admin.cms.collections', 'match' => 'admin/cms/collections', 'icon' => 'layers', 'label' => lang('EditorUi.collections'), 'permission' => 'cms.collections.read'],
    ['route' => 'admin.cms.form_submissions', 'match' => 'admin/cms/form-submissions*', 'icon' => 'mail', 'label' => lang('EditorUi.messages'), 'permission' => 'cms.submissions.read'],
];
?>
<aside id="app-sidebar" class="bg-gray-900 text-gray-200 w-72 fixed inset-y-0 left-0 z-40 transform transition-transform duration-200 md:translate-x-0 flex flex-col"
    :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
    <div class="h-16 px-4 border-b border-gray-800 flex items-center justify-between flex-shrink-0">
        <span class="text-sm uppercase tracking-widest text-gray-400"><?= lang('App.menu') ?></span>
        <button class="md:hidden text-gray-400 hover:text-white" @click="sidebarOpen = false" aria-label="<?= esc(lang('App.close_navigation')) ?>">
            <span aria-hidden="true">x</span>
        </button>
    </div>
    <nav class="p-3 space-y-1 flex-1 overflow-y-auto overscroll-contain">
        <?php foreach ($sections as $section): ?>
            <?php if ($section['permission'] !== null && ! has_permission($section['permission'])) {
                continue;
            } ?>
            <a href="<?= route_to($section['route']) ?>" class="<?= esc($navItemClass) ?> <?= url_is($section['match']) ? $navItemActiveClass : $navItemIdleClass ?>">
                <?= ui_icon($section['icon']) ?>
                <span><?= esc($section['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="border-t border-gray-800 p-3">
        <a href="<?= route_to('profile') ?>" class="<?= esc($navItemClass) ?> <?= esc($navItemIdleClass) ?>">
            <?= ui_icon('profile') ?>
            <span><?= lang('App.profile') ?></span>
        </a>
    </div>
</aside>
