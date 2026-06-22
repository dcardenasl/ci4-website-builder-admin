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
}
