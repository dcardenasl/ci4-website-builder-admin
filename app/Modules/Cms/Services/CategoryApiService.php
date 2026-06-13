<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class CategoryApiService extends ResourceApiService implements CategoryApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/cms/categories';
    }




    /**
     * @param array<string, mixed> $filters
     * @return ApiResponse
     */
    public function collections(array $filters = []): array
    {
        return $this->apiClient->get('/cms/collections', $filters);
    }

    /**
     * @param array<string, mixed> $filters
     * @return ApiResponse
     */
    public function categories(array $filters = []): array
    {
        return $this->apiClient->get('/cms/categories', $filters);
    }
}
