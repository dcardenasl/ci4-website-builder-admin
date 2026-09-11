<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Dashboard\Services\DashboardDataService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/** Renders one permission-aware dashboard snapshot per request. */
final class DashboardController extends BaseWebController
{
    private DashboardDataService $dashboardDataService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->dashboardDataService = service('dashboardDataService');
    }

    public function index(): string
    {
        $user = is_array(session('user')) ? session('user') : [];
        $dashboard = $this->snapshot();
        $displayName = trim((string) ($user['first_name'] ?? ''))
            ?: trim((string) ($user['username'] ?? ''))
            ?: trim((string) ($user['email'] ?? ''))
            ?: lang('Dashboard.user_fallback');

        $widgets = $this->widgets($dashboard);
        $this->closeSessionSafely();

        return $this->render('dashboard/index', [
            'title' => lang('Dashboard.title'),
            'user' => $user,
            'displayName' => $displayName,
            'widgets' => $widgets,
            'canViewAnalytics' => false,
        ]);
    }

    /** Legacy widget routes remain safe projections of the same aggregate. */
    public function widgetStats(): ResponseInterface
    {
        return $this->widget('stats');
    }
    public function widgetHealth(): ResponseInterface
    {
        return $this->widget('health');
    }
    public function widgetRecentFiles(): ResponseInterface
    {
        return $this->widget('recentFiles');
    }
    public function widgetTranslations(): ResponseInterface
    {
        return $this->widget('translations');
    }
    public function widgetSummary(): ResponseInterface
    {
        return $this->widget('summary');
    }
    public function widgetCmsActivity(): ResponseInterface
    {
        return $this->widget('activity');
    }
    public function widgetAnalytics(): ResponseInterface
    {
        return $this->widget('analytics');
    }

    /**
     * @param array<string, mixed> $dashboard
     * @return array<string, string>
     */
    private function widgets(array $dashboard): array
    {
        return [
            'stats' => $this->renderStats($dashboard),
            'summary' => $this->renderSummary($dashboard),
            'translations' => view('dashboard/partials/widget_translations', ['stats' => null]),
            'activity' => view('dashboard/partials/widget_cms_activity', ['items' => [], 'sourceStates' => []]),
            'analytics' => view('dashboard/partials/widget_analytics', ['overview' => null]),
            'health' => $this->renderHealth($dashboard),
            'recentFiles' => $this->renderRecentFiles($dashboard),
        ];
    }

    private function widget(string $name): ResponseInterface
    {
        $html = $this->widgets($this->snapshot())[$name] ?? '';
        $this->closeSessionSafely();

        return $this->response->setBody($html);
    }

    /** @return array<string, mixed> */
    private function snapshot(): array
    {
        $user = session('user');
        $permissions = is_array($user) && is_array($user['permissions'] ?? null)
            ? array_values(array_filter($user['permissions'], 'is_string'))
            : [];

        return $this->dashboardDataService->read(
            (int) (is_array($user) ? ($user['id'] ?? 0) : 0),
            $permissions,
        );
    }

    /** @param array<string, mixed> $dashboard */
    private function renderStats(array $dashboard): string
    {
        $hub = $this->section($dashboard, 'hub');
        $state = $this->state($dashboard, 'hub');
        $stats = [];

        if ($state !== 'unavailable') {
            if (has_permission('users.read')) {
                $stats['users'] = ['label' => lang('Dashboard.total_users'), 'value' => (int) ($hub['users']['total'] ?? 0), 'icon' => 'users'];
            }
            if (has_permission('files.read')) {
                $stats['files'] = ['label' => lang('Dashboard.total_files'), 'value' => (int) ($hub['files']['total'] ?? 0), 'icon' => 'files'];
            }
        }

        $metrics = is_array($hub['metrics'] ?? null) ? $hub['metrics'] : [];
        $uptime = $metrics['request_stats']['availability_percent'] ?? $metrics['slo']['availability_percent'] ?? null;
        if ($uptime !== null) {
            $stats['uptime'] = ['label' => lang('Dashboard.api_uptime'), 'value' => $uptime . '%', 'icon' => 'activity'];
        }

        return view('dashboard/partials/widget_stats', ['stats' => $stats, 'sourceState' => $state]);
    }

    /** @param array<string, mixed> $dashboard */
    private function renderSummary(array $dashboard): string
    {
        $hub = $this->section($dashboard, 'hub');
        $state = $this->state($dashboard, 'hub');
        $items = [];
        if ($state !== 'unavailable') {
            if (has_permission('users.read')) {
                $items[] = ['label' => lang('Users.title'), 'count' => (int) ($hub['users']['total'] ?? 0), 'url' => route_to('admin.users'), 'icon' => 'users', 'badge' => null];
            }
            if (has_permission('files.read')) {
                $items[] = ['label' => lang('Files.title'), 'count' => (int) ($hub['files']['total'] ?? 0), 'url' => route_to('files'), 'icon' => 'files', 'badge' => null];
            }
        }

        return view('dashboard/partials/widget_summary', [
            'items' => $items,
            'warnings' => $state === 'unavailable' ? [lang('Dashboard.source_unavailable')] : [],
        ]);
    }

    /** @param array<string, mixed> $dashboard */
    private function renderRecentFiles(array $dashboard): string
    {
        $hub = $this->section($dashboard, 'hub');

        return view('dashboard/partials/widget_recent_files', [
            'recentFiles' => is_array($hub['files']['recent'] ?? null) ? $hub['files']['recent'] : [],
            'sourceState' => $this->state($dashboard, 'hub'),
        ]);
    }

    /** @param array<string, mixed> $dashboard */
    private function renderHealth(array $dashboard): string
    {
        $state = $this->state($dashboard, 'hub');

        return view('dashboard/partials/widget_health', [
            'healthServices' => [[
                'name' => lang('Dashboard.service_bff'),
                'health' => [
                    'state' => $state === 'fresh' ? 'up' : ($state === 'stale' ? 'degraded' : 'down'),
                    'status' => $state === 'unavailable' ? 503 : 200,
                    'latency_ms' => 0,
                    'data' => ['timestamp' => $dashboard['generated_at'] ?? null],
                ],
            ]],
        ]);
    }

    /**
     * @param array<string, mixed> $dashboard
     * @return array<string, mixed>
     */
    private function section(array $dashboard, string $name): array
    {
        $sections = $dashboard['sections'] ?? [];
        $value = is_array($sections) ? ($sections[$name] ?? []) : [];

        return is_array($value) ? $value : [];
    }

    /** @param array<string, mixed> $dashboard */
    private function state(array $dashboard, string $name): string
    {
        $source = $dashboard['source'] ?? [];
        $value = is_array($source) ? ($source[$name] ?? null) : null;

        return is_string($value) && in_array($value, ['fresh', 'stale', 'unavailable'], true) ? $value : 'unavailable';
    }

    private function closeSessionSafely(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session()->close();
        }
    }
}
