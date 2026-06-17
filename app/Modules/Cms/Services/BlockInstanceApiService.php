<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\BaseApiService;

class BlockInstanceApiService extends BaseApiService implements BlockInstanceApiServiceInterface
{
    private function buildPath(string|int $ownerId, string $ownerType): string
    {
        $type = $ownerType === 'entry' ? 'entries' : 'pages';
        return "/cms/{$type}/{$ownerId}/blocks";
    }

    public function list(string|int $ownerId, string $ownerType, array $filters = []): array
    {
        return $this->apiClient->get($this->buildPath($ownerId, $ownerType), $filters);
    }

    public function get(string|int $ownerId, string $ownerType, string|int $id): array
    {
        return $this->apiClient->get($this->buildPath($ownerId, $ownerType) . '/' . $id);
    }

    public function create(string|int $ownerId, string $ownerType, array $payload): array
    {
        return $this->apiClient->post($this->buildPath($ownerId, $ownerType), $payload);
    }

    public function update(string|int $ownerId, string $ownerType, string|int $id, array $payload): array
    {
        return $this->apiClient->put($this->buildPath($ownerId, $ownerType) . '/' . $id, $payload);
    }

    public function delete(string|int $ownerId, string $ownerType, string|int $id): array
    {
        return $this->apiClient->delete($this->buildPath($ownerId, $ownerType) . '/' . $id);
    }
}
