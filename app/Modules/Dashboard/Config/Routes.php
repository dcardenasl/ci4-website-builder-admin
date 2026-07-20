<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('/dashboard', '\App\Modules\Dashboard\Controllers\DashboardController::index', ['as' => 'dashboard']);
    $routes->get('/dashboard/widgets/stats', '\App\Modules\Dashboard\Controllers\DashboardController::widgetStats', ['as' => 'dashboard.widgets.stats']);
    $routes->get('/dashboard/widgets/health', '\App\Modules\Dashboard\Controllers\DashboardController::widgetHealth', ['as' => 'dashboard.widgets.health']);
    $routes->get('/dashboard/widgets/recent-files', '\App\Modules\Dashboard\Controllers\DashboardController::widgetRecentFiles', ['as' => 'dashboard.widgets.recent-files']);
    $routes->get('/dashboard/widgets/translations', '\App\Modules\Dashboard\Controllers\DashboardController::widgetTranslations', ['as' => 'dashboard.widgets.translations']);
    $routes->get('/dashboard/widgets/attention', '\App\Modules\Dashboard\Controllers\DashboardController::widgetAttention', ['as' => 'dashboard.widgets.attention']);
    $routes->get('/dashboard/widgets/content-summary', '\App\Modules\Dashboard\Controllers\DashboardController::widgetContentSummary', ['as' => 'dashboard.widgets.content-summary']);
    $routes->get('/dashboard/widgets/cms-activity', '\App\Modules\Dashboard\Controllers\DashboardController::widgetCmsActivity', ['as' => 'dashboard.widgets.cms-activity']);
});
