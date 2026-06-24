<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Services;

use App\Services\BaseApiService;

class AnalyticsApiService extends BaseApiService implements AnalyticsApiServiceInterface
{
    public function overview(array $params = []): array
    {
        return $this->apiClient->get('/cms/analytics/overview', $params);
    }

    public function pages(array $params = []): array
    {
        return $this->apiClient->get('/cms/analytics/pages', $params);
    }

    public function referrers(array $params = []): array
    {
        return $this->apiClient->get('/cms/analytics/referrers', $params);
    }

    public function devices(array $params = []): array
    {
        return $this->apiClient->get('/cms/analytics/devices', $params);
    }

    public function timeseries(array $params = []): array
    {
        return $this->apiClient->get('/cms/analytics/timeseries', $params);
    }
}
