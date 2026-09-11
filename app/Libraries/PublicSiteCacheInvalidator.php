<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;

/**
 * Best-effort bridge from Admin to the generic public-site cache endpoint.
 * Content writes remain successful when the optional public site is down.
 */
final class PublicSiteCacheInvalidator
{
    /** @var list<string> */
    public const VALID_SCOPES = [
        'settings', 'menus', 'pages', 'collections', 'entries', 'taxonomies', 'redirects', 'forms',
    ];

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $invalidateKey,
        private readonly int $timeout = 5,
    ) {
    }

    /** @param list<string> $scopes */
    public function invalidate(array $scopes): bool
    {
        return $this->invalidateWithResult($scopes)['ok'];
    }

    /**
     * @param list<string> $scopes
     * @return array{ok: bool, status: int, invalidated: list<string>, deleted: int, message: string|null}
     */
    public function invalidateWithResult(array $scopes, string $source = 'admin_manual'): array
    {
        $normalizedScopes = $this->normalizeScopes($scopes);
        if ($normalizedScopes === []) {
            return ['ok' => true, 'status' => 200, 'invalidated' => [], 'deleted' => 0, 'message' => null];
        }

        if ($this->baseUrl === '' || $this->invalidateKey === '') {
            log_message(
                'warning',
                '[PublicSiteCacheInvalidator] Missing PUBLIC_SITE_URL or CACHE_INVALIDATE_KEY. '
                . 'Skipping cache invalidation for scopes: ' . implode(', ', $normalizedScopes)
            );

            return ['ok' => false, 'status' => 0, 'invalidated' => [], 'deleted' => 0, 'message' => 'Cache invalidation is not configured.'];
        }

        try {
            $response = $this->client()->request('POST', '/cache/invalidate', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-Invalidate-Key' => $this->invalidateKey,
                    'X-Cache-Invalidation-Source' => trim($source) !== '' ? trim($source) : 'admin_manual',
                ],
                'json' => ['scopes' => $normalizedScopes],
            ]);
        } catch (\Throwable $e) {
            log_message(
                'warning',
                '[PublicSiteCacheInvalidator] Cache invalidation request failed: ' . $e->getMessage()
            );

            return ['ok' => false, 'status' => 0, 'invalidated' => [], 'deleted' => 0, 'message' => $e->getMessage()];
        }

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            $body = trim((string) $response->getBody());
            log_message(
                'warning',
                '[PublicSiteCacheInvalidator] Public cache invalidation failed with HTTP ' . $status
                . ' for scopes: ' . implode(', ', $normalizedScopes)
                . ($body !== '' ? '. Body: ' . $this->compactBody($body) : '')
            );
        } else {
            log_message(
                'info',
                '[PublicSiteCacheInvalidator] Invalidated scopes: ' . implode(', ', $normalizedScopes)
            );
        }

        $decoded = json_decode((string) $response->getBody(), true);
        $decoded = is_array($decoded) ? $decoded : [];
        if ($status < 200 || $status >= 300) {
            return [
                'ok' => false,
                'status' => $status,
                'invalidated' => [],
                'deleted' => 0,
                'message' => is_string($decoded['message'] ?? null) ? $decoded['message'] : 'Public cache invalidation failed.',
            ];
        }

        return [
            'ok' => true,
            'status' => $status,
            'invalidated' => $this->stringList($decoded['invalidated'] ?? $normalizedScopes),
            'deleted' => max(0, (int) ($decoded['deleted'] ?? 0)),
            'message' => null,
        ];
    }

    /** @return array{ok: bool, status: int, data: array<string, mixed>, message: string|null} */
    public function status(): array
    {
        if ($this->baseUrl === '' || $this->invalidateKey === '') {
            return ['ok' => false, 'status' => 0, 'data' => [], 'message' => 'Public site cache invalidation is not configured.'];
        }

        try {
            $response = $this->client()->request('GET', '/cache/status', [
                'headers' => ['Accept' => 'application/json', 'X-Invalidate-Key' => $this->invalidateKey],
            ]);
        } catch (\Throwable $e) {
            log_message('warning', '[PublicSiteCacheInvalidator] Cache status request failed: ' . $e->getMessage());

            return ['ok' => false, 'status' => 0, 'data' => [], 'message' => $e->getMessage()];
        }

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            $body = trim((string) $response->getBody());
            log_message(
                'warning',
                '[PublicSiteCacheInvalidator] Public cache status failed with HTTP ' . $status
                . ($body !== '' ? '. Body: ' . $this->compactBody($body) : '')
            );
        }

        $decoded = json_decode((string) $response->getBody(), true);
        $decoded = is_array($decoded) ? $decoded : [];

        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'data' => is_array($decoded['data'] ?? null) ? $decoded['data'] : [],
            'message' => $status >= 200 && $status < 300 ? null : 'Could not read public-site cache status.',
        ];
    }

    private function client(): CURLRequest
    {
        return \Config\Services::curlrequest([
            'baseURI' => rtrim($this->baseUrl, '/'),
            'timeout' => max(1, $this->timeout),
            'connect_timeout' => max(1, $this->timeout),
            'http_errors' => false,
        ]);
    }

    /**
     * @param list<string> $scopes
     * @return list<string>
     */
    private function normalizeScopes(array $scopes): array
    {
        $normalized = [];
        foreach ($scopes as $scope) {
            $scope = trim((string) $scope);
            if ($scope !== '' && in_array($scope, self::VALID_SCOPES, true)) {
                $normalized[$scope] = true;
            } elseif ($scope !== '') {
                log_message('warning', '[PublicSiteCacheInvalidator] Invalid scope omitted: ' . $scope);
            }
        }

        return array_keys($normalized);
    }

    private function compactBody(string $body): string
    {
        if (strlen($body) <= 500) {
            return $body;
        }

        return substr($body, 0, 500) . '...';
    }

    /** @return list<string> */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            array_map(static fn (mixed $item): string => trim((string) $item), $value),
            static fn (string $item): bool => $item !== '',
        ));
    }
}
