<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

class MenuApiService extends ResourceApiService implements MenuApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/cms/menus';
    }

    public function listItems(array $filters = []): array
    {
        return $this->apiClient->get('/cms/menu-items', $filters);
    }

    public function getItem(int|string $id): array
    {
        return $this->apiClient->get('/cms/menu-items/' . $id);
    }

    public function createItem(array $payload): array
    {
        return $this->apiClient->post('/cms/menu-items', $payload);
    }

    public function updateItem(int|string $id, array $payload): array
    {
        return $this->apiClient->put('/cms/menu-items/' . $id, $payload);
    }

    public function deleteItem(int|string $id): array
    {
        return $this->apiClient->delete('/cms/menu-items/' . $id);
    }



}
