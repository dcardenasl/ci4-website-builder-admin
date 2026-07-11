<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\BaseApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class TranslationAuditApiService extends BaseApiService
{
    /**
     * @return ApiResponse
     */
    public function getStats(): array
    {
        return $this->apiClient->get('/cms/translations/audit/stats');
    }

    /**
     * @param array<string, mixed> $filters
     * @return ApiResponse
     */
    public function getReport(array $filters = []): array
    {
        return $this->apiClient->get('/cms/translations/audit/report', $filters);
    }

    /**
     * @param string $type
     * @param int|string $id
     * @return ApiResponse
     */
    public function auditResource(string $type, int|string $id): array
    {
        return $this->apiClient->get('/cms/translations/audit/resource/' . $type . '/' . $id);
    }
}
