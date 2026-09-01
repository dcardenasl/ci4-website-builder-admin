<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Services;

use App\Libraries\BffApiClientInterface;
use CodeIgniter\Cache\CacheInterface;

/** Bounded, permission-aware delivery of the BFF dashboard snapshot. */
final readonly class DashboardDataService
{
    private const CACHE_VERSION = 1;

    public function __construct(
        private BffApiClientInterface $bffClient,
        private CacheInterface $cache,
        private DashboardLockInterface $lock,
        private int $freshTtl,
        private int $staleTtl,
        private int $failureCooldownTtl,
        private int $maxRetries,
    ) {
    }

    /**
     * @param list<string> $permissions
     * @return array<string, mixed>
     */
    public function read(int $userId, array $permissions): array
    {
        $scope = array_values(array_filter($permissions, 'is_string'));
        sort($scope);
        $key = 'dashboard_data_v' . self::CACHE_VERSION . '_' . $userId . '_' . hash('sha256', implode('|', $scope));
        $freshKey = $key . '_fresh';
        $staleKey = $key . '_stale';
        $cooldownKey = $key . '_failure_cooldown';

        $fresh = $this->cache->get($freshKey);
        if (is_array($fresh)) {
            return $this->withSourceState($fresh, 'fresh');
        }

        $cooldown = $this->cache->get($cooldownKey);
        if (is_array($cooldown)) {
            $source = is_array($cooldown['source'] ?? null) ? $cooldown['source'] : [];

            return $this->withSourceState($cooldown, is_string($source['state'] ?? null) ? $source['state'] : 'unavailable', 'failure_cooldown');
        }

        $token = $this->lock->acquire($key);
        if ($token === null) {
            $stale = $this->cache->get($staleKey);

            return is_array($stale)
                ? $this->withSourceState($stale, 'stale', 'builder_busy')
                : $this->unavailable('builder_busy');
        }

        try {
            $fresh = $this->cache->get($freshKey);
            if (is_array($fresh)) {
                return $this->withSourceState($fresh, 'fresh');
            }

            try {
                $response = $this->bffClient->getAdminDashboard($this->maxRetries);
            } catch (\Throwable $exception) {
                log_message('error', 'Dashboard BFF read failed: ' . $exception->getMessage());
                $response = ['ok' => false, 'status' => 0, 'data' => []];
            }
            $snapshot = $this->snapshotFromBff($response);
            if ($snapshot === null) {
                $stale = $this->cache->get($staleKey);
                if (((int) ($response['status'] ?? 0) === 0 || (int) ($response['status'] ?? 0) >= 500) && is_array($stale)) {
                    $snapshot = $this->withStaleSourceState($stale, 'bff_unavailable');
                    if ($this->failureCooldownTtl > 0) {
                        $this->cache->save($cooldownKey, $snapshot, $this->failureCooldownTtl);
                    }

                    return $snapshot;
                }

                $snapshot = $this->unavailable('bff_unavailable');
            }

            if (($snapshot['source']['state'] ?? null) === 'fresh') {
                $this->cache->save($freshKey, $snapshot, $this->freshTtl);
                $this->cache->save($staleKey, $snapshot, $this->staleTtl);
            } elseif ($this->failureCooldownTtl > 0) {
                $this->cache->save($cooldownKey, $snapshot, $this->failureCooldownTtl);
            }

            return $snapshot;
        } finally {
            $this->lock->release($key, $token);
        }
    }

    /**
     * @param array<string, mixed> $response
     * @return array<string, mixed>|null
     */
    private function snapshotFromBff(array $response): ?array
    {
        if (($response['ok'] ?? false) !== true) {
            return null;
        }

        $payload = is_array($response['data'] ?? null) ? $response['data'] : [];
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
        $source = is_array($data['source'] ?? null) ? $data['source'] : [];
        $sections = is_array($data['sections'] ?? null) ? $data['sections'] : [];
        $state = ($source['hub'] ?? null) === 'ok' || ($source['hub'] ?? null) === 'fresh' ? 'fresh' : 'unavailable';
        $diagnostics = is_array($source['diagnostics'] ?? null) ? $source['diagnostics'] : [];

        return [
            'version' => self::CACHE_VERSION,
            'generated_at' => is_string($data['generated_at'] ?? null) ? $data['generated_at'] : date(DATE_ATOM),
            'source' => [
                'hub' => $state,
                'state' => $state,
                'diagnostics' => $diagnostics,
            ],
            'sections' => [
                'hub' => is_array($sections['hub'] ?? null) ? $sections['hub'] : [],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $snapshot
     * @return array<string, mixed>
     */
    private function withSourceState(array $snapshot, string $state, ?string $reason = null): array
    {
        $source = is_array($snapshot['source'] ?? null) ? $snapshot['source'] : [];
        $source['state'] = $state;
        if ($reason !== null) {
            $source['reason'] = $reason;
        }
        $snapshot['source'] = $source;

        return $snapshot;
    }

    /**
     * @param array<string, mixed> $snapshot
     * @return array<string, mixed>
     */
    private function withStaleSourceState(array $snapshot, string $reason): array
    {
        $source = is_array($snapshot['source'] ?? null) ? $snapshot['source'] : [];
        $source['hub'] = 'stale';
        $source['state'] = 'stale';
        $source['reason'] = $reason;
        $snapshot['source'] = $source;

        return $snapshot;
    }

    /** @return array<string, mixed> */
    private function unavailable(string $reason): array
    {
        return [
            'version' => self::CACHE_VERSION,
            'generated_at' => date(DATE_ATOM),
            'source' => ['hub' => 'unavailable', 'state' => 'unavailable', 'reason' => $reason],
            'sections' => ['hub' => []],
        ];
    }
}
