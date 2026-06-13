<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class EntryApiService extends ResourceApiService implements EntryApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/cms/entries';
    }


    public function publish(int|string $id): array
    {
        return $this->apiClient->post($this->resourcePath() . '/' . $id . '/publish');
    }

    public function archive(int|string $id): array
    {
        return $this->apiClient->post($this->resourcePath() . '/' . $id . '/archive');
    }



    /**
     * @param array<string, mixed> $filters
     * @return ApiResponse
     */
    public function collections(array $filters = []): array
    {
        return $this->apiClient->get('/cms/collections', $filters);
    }
}
