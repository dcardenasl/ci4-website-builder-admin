<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\DomainApiClientInterface;

/**
 * Single transport seam for atomic Admin reorder operations.
 *
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
final class SortOrderApiService implements SortOrderApiServiceInterface
{
    public function __construct(private readonly DomainApiClientInterface $domainClient)
    {
    }

    /**
     * @param list<array{id: int|string, sort_order: int}> $items
     * @return ApiResponse
     */
    public function cms(string $resource, array $items, array $scope = []): array
    {
        return $this->domainClient->post('/cms/sort-orders', [
            'resource' => $resource,
            'items' => $items,
            'scope' => $scope,
        ]);
    }
}
