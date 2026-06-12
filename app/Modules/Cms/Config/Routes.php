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


});
