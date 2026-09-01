<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\BffApiClient as BffApiClientConfig;

/**
 * HTTP client targeting a ci4-bff-starter gateway.
 *
 * Reuses every behaviour of {@see ApiClient} (auth header injection,
 * token refresh, app-key forwarding, upload handling) but defaults to the
 * `BffApiClient` config instead of `ApiClient`. Token refresh is delegated
 * to the Hub client via {@see SecondaryApiClient} — the BFF is stateless
 * and never exposes its own `/auth/refresh` endpoint.
 */
class BffApiClient extends SecondaryApiClient implements BffApiClientInterface
{
    public function __construct(?BffApiClientConfig $config = null, ?ApiClientInterface $hubClient = null)
    {
        parent::__construct($config ?? config(BffApiClientConfig::class), $hubClient ?? service('apiClient'));
    }

    /**
     * Read the authenticated dashboard aggregate. The BFF owns the upstream
     * permission check; the Admin owns cache, stale and cooldown policy.
     *
     * @return array<string, mixed>
     */
    public function getAdminDashboard(int $maxRetries = 0): array
    {
        return $this->request('GET', '/me/admin-dashboard', [
            'max_retries' => max(0, min(1, $maxRetries)),
        ], true);
    }
}
