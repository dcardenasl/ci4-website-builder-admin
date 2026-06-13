<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/cms', ['filter' => ['auth', 'admin']], static function (RouteCollection $routes): void {
    // Language
    $routes->get('languages', '\\App\\Modules\\Cms\\Controllers\\LanguageController::index', ['as' => 'admin.cms.languages', 'filter' => 'permission:cms.languages.read']);
    $routes->get('languages/data', '\\App\\Modules\\Cms\\Controllers\\LanguageController::data', ['as' => 'admin.cms.languages.data', 'filter' => 'permission:cms.languages.read']);
    $routes->get('languages/create', '\\App\\Modules\\Cms\\Controllers\\LanguageController::create', ['as' => 'admin.cms.languages.create', 'filter' => 'permission:cms.languages.write']);
    $routes->post('languages', '\\App\\Modules\\Cms\\Controllers\\LanguageController::store', ['as' => 'admin.cms.languages.store', 'filter' => 'permission:cms.languages.write']);
    $routes->get('languages/(:segment)', '\\App\\Modules\\Cms\\Controllers\\LanguageController::show/$1', ['as' => 'admin.cms.languages.show', 'filter' => 'permission:cms.languages.read']);
    $routes->get('languages/(:segment)/edit', '\\App\\Modules\\Cms\\Controllers\\LanguageController::edit/$1', ['as' => 'admin.cms.languages.edit', 'filter' => 'permission:cms.languages.write']);
    $routes->post('languages/(:segment)', '\\App\\Modules\\Cms\\Controllers\\LanguageController::update/$1', ['as' => 'admin.cms.languages.update', 'filter' => 'permission:cms.languages.write']);
    $routes->post('languages/(:segment)/delete', '\\App\\Modules\\Cms\\Controllers\\LanguageController::delete/$1', ['as' => 'admin.cms.languages.delete', 'filter' => 'permission:cms.languages.write']);
    $routes->post('languages/(:segment)/set-default', '\\App\\Modules\\Cms\\Controllers\\LanguageController::setDefault/$1', ['as' => 'admin.cms.languages.set_default', 'filter' => 'permission:cms.languages.write']);

    $routes->get('languages/reorder', '\\App\\Modules\\Cms\\Controllers\\LanguageController::reorder', ['as' => 'admin.cms.languages.reorder', 'filter' => 'permission:cms.languages.write']);
    $routes->post('languages/reorder', '\\App\\Modules\\Cms\\Controllers\\LanguageController::saveOrder', ['as' => 'admin.cms.languages.save_order', 'filter' => 'permission:cms.languages.write']);



    // Setting
    $routes->get('settings', '\App\Modules\Cms\Controllers\SettingController::index', ['as' => 'admin.cms.settings', 'filter' => 'permission:cms.settings.read']);
    $routes->get('settings/data', '\App\Modules\Cms\Controllers\SettingController::data', ['as' => 'admin.cms.settings.data', 'filter' => 'permission:cms.settings.read']);
    $routes->get('settings/create', '\App\Modules\Cms\Controllers\SettingController::create', ['as' => 'admin.cms.settings.create', 'filter' => 'permission:cms.settings.write']);
    $routes->post('settings', '\App\Modules\Cms\Controllers\SettingController::store', ['as' => 'admin.cms.settings.store', 'filter' => 'permission:cms.settings.write']);
    $routes->get('settings/(:segment)', '\App\Modules\Cms\Controllers\SettingController::show/$1', ['as' => 'admin.cms.settings.show', 'filter' => 'permission:cms.settings.read']);
    $routes->get('settings/(:segment)/edit', '\App\Modules\Cms\Controllers\SettingController::edit/$1', ['as' => 'admin.cms.settings.edit', 'filter' => 'permission:cms.settings.write']);
    $routes->post('settings/(:segment)', '\App\Modules\Cms\Controllers\SettingController::update/$1', ['as' => 'admin.cms.settings.update', 'filter' => 'permission:cms.settings.write']);
    $routes->post('settings/(:segment)/delete', '\App\Modules\Cms\Controllers\SettingController::delete/$1', ['as' => 'admin.cms.settings.delete', 'filter' => 'permission:cms.settings.write']);

    // Page
    $routes->get('pages', '\App\Modules\Cms\Controllers\PageController::index', ['as' => 'admin.cms.pages', 'filter' => 'permission:cms.pages.read']);
    $routes->get('pages/data', '\App\Modules\Cms\Controllers\PageController::data', ['as' => 'admin.cms.pages.data', 'filter' => 'permission:cms.pages.read']);
    $routes->get('pages/create', '\App\Modules\Cms\Controllers\PageController::create', ['as' => 'admin.cms.pages.create', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages', '\App\Modules\Cms\Controllers\PageController::store', ['as' => 'admin.cms.pages.store', 'filter' => 'permission:cms.pages.write']);
    $routes->get('pages/(:segment)', '\App\Modules\Cms\Controllers\PageController::show/$1', ['as' => 'admin.cms.pages.show', 'filter' => 'permission:cms.pages.read']);
    $routes->get('pages/(:segment)/edit', '\App\Modules\Cms\Controllers\PageController::edit/$1', ['as' => 'admin.cms.pages.edit', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages/(:segment)', '\App\Modules\Cms\Controllers\PageController::update/$1', ['as' => 'admin.cms.pages.update', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages/(:segment)/delete', '\App\Modules\Cms\Controllers\PageController::delete/$1', ['as' => 'admin.cms.pages.delete', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages/(:segment)/publish', '\App\Modules\Cms\Controllers\PageController::publish/$1', ['as' => 'admin.cms.pages.publish', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages/(:segment)/archive', '\App\Modules\Cms\Controllers\PageController::archive/$1', ['as' => 'admin.cms.pages.archive', 'filter' => 'permission:cms.pages.write']);
    $routes->get('pages/reorder', '\App\Modules\Cms\Controllers\PageController::reorder', ['as' => 'admin.cms.pages.reorder', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages/reorder', '\App\Modules\Cms\Controllers\PageController::saveOrder', ['as' => 'admin.cms.pages.save_order', 'filter' => 'permission:cms.pages.write']);

    // Menu
    $routes->get('menus', '\App\Modules\Cms\Controllers\MenuController::index', ['as' => 'admin.cms.menus', 'filter' => 'permission:cms.menus.read']);
    $routes->get('menus/data', '\App\Modules\Cms\Controllers\MenuController::data', ['as' => 'admin.cms.menus.data', 'filter' => 'permission:cms.menus.read']);
    $routes->get('menus/create', '\App\Modules\Cms\Controllers\MenuController::create', ['as' => 'admin.cms.menus.create', 'filter' => 'permission:cms.menus.write']);
    $routes->post('menus', '\App\Modules\Cms\Controllers\MenuController::store', ['as' => 'admin.cms.menus.store', 'filter' => 'permission:cms.menus.write']);
    $routes->get('menus/(:segment)', '\App\Modules\Cms\Controllers\MenuController::show/$1', ['as' => 'admin.cms.menus.show', 'filter' => 'permission:cms.menus.read']);
    $routes->get('menus/(:segment)/edit', '\App\Modules\Cms\Controllers\MenuController::edit/$1', ['as' => 'admin.cms.menus.edit', 'filter' => 'permission:cms.menus.write']);
    $routes->post('menus/(:segment)', '\App\Modules\Cms\Controllers\MenuController::update/$1', ['as' => 'admin.cms.menus.update', 'filter' => 'permission:cms.menus.write']);
    $routes->post('menus/(:segment)/delete', '\App\Modules\Cms\Controllers\MenuController::delete/$1', ['as' => 'admin.cms.menus.delete', 'filter' => 'permission:cms.menus.write']);

    // MenuItem
    $routes->get('menus/(:num)/items/create', '\App\Modules\Cms\Controllers\MenuController::createItem/$1', ['as' => 'admin.cms.menus.items.create', 'filter' => 'permission:cms.menus.write']);
    $routes->post('menus/(:num)/items', '\App\Modules\Cms\Controllers\MenuController::storeItem/$1', ['as' => 'admin.cms.menus.items.store', 'filter' => 'permission:cms.menus.write']);
    $routes->get('menus/(:num)/items/(:num)/edit', '\App\Modules\Cms\Controllers\MenuController::editItem/$1/$2', ['as' => 'admin.cms.menus.items.edit', 'filter' => 'permission:cms.menus.write']);
    $routes->post('menus/(:num)/items/(:num)', '\App\Modules\Cms\Controllers\MenuController::updateItem/$1/$2', ['as' => 'admin.cms.menus.items.update', 'filter' => 'permission:cms.menus.write']);
    $routes->post('menus/(:num)/items/(:num)/delete', '\App\Modules\Cms\Controllers\MenuController::deleteItem/$1/$2', ['as' => 'admin.cms.menus.items.delete', 'filter' => 'permission:cms.menus.write']);

    // BlockType
    $routes->get('block-types', '\App\Modules\Cms\Controllers\BlockTypeController::index', ['as' => 'admin.cms.block_types', 'filter' => 'permission:cms.blocks.read']);
    $routes->get('block-types/data', '\App\Modules\Cms\Controllers\BlockTypeController::data', ['as' => 'admin.cms.block_types.data', 'filter' => 'permission:cms.blocks.read']);
    $routes->get('block-types/create', '\App\Modules\Cms\Controllers\BlockTypeController::create', ['as' => 'admin.cms.block_types.create', 'filter' => 'permission:cms.blocks.write']);
    $routes->post('block-types', '\App\Modules\Cms\Controllers\BlockTypeController::store', ['as' => 'admin.cms.block_types.store', 'filter' => 'permission:cms.blocks.write']);
    $routes->get('block-types/(:segment)', '\App\Modules\Cms\Controllers\BlockTypeController::show/$1', ['as' => 'admin.cms.block_types.show', 'filter' => 'permission:cms.blocks.read']);
    $routes->get('block-types/(:segment)/edit', '\App\Modules\Cms\Controllers\BlockTypeController::edit/$1', ['as' => 'admin.cms.block_types.edit', 'filter' => 'permission:cms.blocks.write']);
    $routes->post('block-types/(:segment)', '\App\Modules\Cms\Controllers\BlockTypeController::update/$1', ['as' => 'admin.cms.block_types.update', 'filter' => 'permission:cms.blocks.write']);
    $routes->post('block-types/(:segment)/delete', '\App\Modules\Cms\Controllers\BlockTypeController::delete/$1', ['as' => 'admin.cms.block_types.delete', 'filter' => 'permission:cms.blocks.write']);

    // Collection
    $routes->get('collections', '\App\Modules\Cms\Controllers\CollectionController::index', ['as' => 'admin.cms.collections', 'filter' => 'permission:cms.collections.read']);
    $routes->get('collections/data', '\App\Modules\Cms\Controllers\CollectionController::data', ['as' => 'admin.cms.collections.data', 'filter' => 'permission:cms.collections.read']);
    $routes->get('collections/create', '\App\Modules\Cms\Controllers\CollectionController::create', ['as' => 'admin.cms.collections.create', 'filter' => 'permission:cms.collections.write']);
    $routes->post('collections', '\App\Modules\Cms\Controllers\CollectionController::store', ['as' => 'admin.cms.collections.store', 'filter' => 'permission:cms.collections.write']);
    $routes->get('collections/(:segment)', '\App\Modules\Cms\Controllers\CollectionController::show/$1', ['as' => 'admin.cms.collections.show', 'filter' => 'permission:cms.collections.read']);
    $routes->get('collections/(:segment)/edit', '\App\Modules\Cms\Controllers\CollectionController::edit/$1', ['as' => 'admin.cms.collections.edit', 'filter' => 'permission:cms.collections.write']);
    $routes->post('collections/(:segment)', '\App\Modules\Cms\Controllers\CollectionController::update/$1', ['as' => 'admin.cms.collections.update', 'filter' => 'permission:cms.collections.write']);
    $routes->post('collections/(:segment)/delete', '\App\Modules\Cms\Controllers\CollectionController::delete/$1', ['as' => 'admin.cms.collections.delete', 'filter' => 'permission:cms.collections.write']);

    // Entry
    $routes->get('entries', '\App\Modules\Cms\Controllers\EntryController::index', ['as' => 'admin.cms.entries', 'filter' => 'permission:cms.entries.read']);
    $routes->get('entries/data', '\App\Modules\Cms\Controllers\EntryController::data', ['as' => 'admin.cms.entries.data', 'filter' => 'permission:cms.entries.read']);
    $routes->get('entries/create', '\App\Modules\Cms\Controllers\EntryController::create', ['as' => 'admin.cms.entries.create', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries', '\App\Modules\Cms\Controllers\EntryController::store', ['as' => 'admin.cms.entries.store', 'filter' => 'permission:cms.entries.write']);
    $routes->get('entries/(:segment)', '\App\Modules\Cms\Controllers\EntryController::show/$1', ['as' => 'admin.cms.entries.show', 'filter' => 'permission:cms.entries.read']);
    $routes->get('entries/(:segment)/edit', '\App\Modules\Cms\Controllers\EntryController::edit/$1', ['as' => 'admin.cms.entries.edit', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries/(:segment)', '\App\Modules\Cms\Controllers\EntryController::update/$1', ['as' => 'admin.cms.entries.update', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries/(:segment)/delete', '\App\Modules\Cms\Controllers\EntryController::delete/$1', ['as' => 'admin.cms.entries.delete', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries/(:segment)/publish', '\App\Modules\Cms\Controllers\EntryController::publish/$1', ['as' => 'admin.cms.entries.publish', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries/(:segment)/archive', '\App\Modules\Cms\Controllers\EntryController::archive/$1', ['as' => 'admin.cms.entries.archive', 'filter' => 'permission:cms.entries.write']);
    $routes->get('entries/reorder', '\App\Modules\Cms\Controllers\EntryController::reorder', ['as' => 'admin.cms.entries.reorder', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries/reorder', '\App\Modules\Cms\Controllers\EntryController::saveOrder', ['as' => 'admin.cms.entries.save_order', 'filter' => 'permission:cms.entries.write']);
});
