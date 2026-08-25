<?php

declare(strict_types=1);

namespace App\Modules\System\Controllers;

use App\Controllers\BaseWebController;
use CodeIgniter\HTTP\RedirectResponse;

final class CacheController extends BaseWebController
{
    /** @var list<string> */
    private const PUBLIC_SCOPES = [
        'settings', 'menus', 'pages', 'collections', 'entries', 'taxonomies', 'redirects', 'forms',
    ];

    public function index(): string
    {
        $result = $this->safeApiCall(static fn (): array => service('publicSiteCacheInvalidator')->status());
        $status = is_array($result['data'] ?? null) ? $result['data'] : [];

        return $this->render('system/cache', [
            'title' => lang('System.cache_title'),
            'status' => $status,
            'statusError' => ! ($result['ok'] ?? false)
                ? ((string) ($result['message'] ?? lang('System.cache_status_unavailable')))
                : null,
            'scopes' => self::PUBLIC_SCOPES,
        ]);
    }

    public function invalidate(): RedirectResponse
    {
        $result = $this->safeApiCall(static fn (): array => service('publicSiteCacheInvalidator')->invalidateWithResult(
            self::PUBLIC_SCOPES,
            'admin_manual',
        ));

        if (($result['ok'] ?? false) === true) {
            return redirect()->to(route_to('admin.system.cache'))->with(
                'success',
                lang('System.cache_invalidated', ['deleted' => (string) ($result['deleted'] ?? 0)])
            );
        }

        return redirect()->to(route_to('admin.system.cache'))->with('error', lang('System.cache_invalidation_failed'));
    }
}
