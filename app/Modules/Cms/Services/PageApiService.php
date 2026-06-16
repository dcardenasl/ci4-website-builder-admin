<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class PageApiService extends ResourceApiService implements PageApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/cms/pages';
    }


    public function publish(int|string $id): array
    {
        return $this->apiClient->put($this->resourcePath() . '/' . $id, ['status' => 'published']);
    }

    public function archive(int|string $id): array
    {
        return $this->apiClient->put($this->resourcePath() . '/' . $id, ['status' => 'archived']);
    }



    /**
     * @param array<string, mixed> $filters
     * @return ApiResponse
     */
    public function pages(array $filters = []): array
    {
        return $this->apiClient->get('/cms/pages', $filters);
    }

    public function checkSlug(string $slug, int $languageId, string $currentId = ''): array
    {
        $params = ['slug' => $slug, 'language_id' => $languageId];
        if ($currentId !== '') {
            $params['current_id'] = $currentId;
        }
        return $this->apiClient->get('/cms/pages/check-slug', $params);
    }
}
