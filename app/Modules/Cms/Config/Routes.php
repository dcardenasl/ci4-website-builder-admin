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
});
