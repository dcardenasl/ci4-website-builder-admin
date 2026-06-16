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

    public function checkSlug(string $slug, int $languageId, string $currentId = ''): array
    {
        $params = ['slug' => $slug, 'language_id' => $languageId];
        if ($currentId !== '') {
            $params['current_id'] = $currentId;
        }
        return $this->apiClient->get('/cms/entries/check-slug', $params);
    }
}
