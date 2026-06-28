<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

class SettingApiService extends ResourceApiService implements SettingApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/cms/settings';
    }

    public function getByGroup(string $group): array
    {
        return $this->apiClient->get($this->resourcePath(), ['filter[setting_group]' => $group, 'per_page' => 100]);
    }

    public function getConnections(int $settingId): array
    {
        return $this->apiClient->get("{$this->resourcePath()}/{$settingId}/connections");
    }

    public function createConnection(int $settingId, array $data): array
    {
        return $this->apiClient->post("{$this->resourcePath()}/{$settingId}/connections", $data);
    }

    public function deleteConnection(int $settingId, int $connectionId): array
    {
        return $this->apiClient->delete("{$this->resourcePath()}/{$settingId}/connections/{$connectionId}");
    }
}
