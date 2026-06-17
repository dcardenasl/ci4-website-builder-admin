<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
interface BlockInstanceApiServiceInterface
{
    /**
     * @param string|int $ownerId
     * @param string $ownerType 'page' or 'entry'
     * @param array<string, mixed> $filters
     * @return ApiResponse
     */
    public function list(string|int $ownerId, string $ownerType, array $filters = []): array;

    /**
     * @param string|int $ownerId
     * @param string $ownerType
     * @param string|int $id
     * @return ApiResponse
     */
    public function get(string|int $ownerId, string $ownerType, string|int $id): array;

    /**
     * @param string|int $ownerId
     * @param string $ownerType
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function create(string|int $ownerId, string $ownerType, array $payload): array;

    /**
     * @param string|int $ownerId
     * @param string $ownerType
     * @param string|int $id
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function update(string|int $ownerId, string $ownerType, string|int $id, array $payload): array;

    /**
     * @param string|int $ownerId
     * @param string $ownerType
     * @param string|int $id
     * @return ApiResponse
     */
    public function delete(string|int $ownerId, string $ownerType, string|int $id): array;
}
