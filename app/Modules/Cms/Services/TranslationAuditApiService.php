<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\BaseApiService;

class TranslationAuditApiService extends BaseApiService implements TranslationAuditApiServiceInterface
{
    public function getStats(): array
    {
        return $this->apiClient->get('/cms/translations/audit/stats');
    }

    public function getReport(array $filters = []): array
    {
        return $this->apiClient->get('/cms/translations/audit/report', $filters);
    }

    public function auditResource(string $type, int|string $id): array
    {
        return $this->apiClient->get('/cms/translations/audit/resource/' . $type . '/' . $id);
    }
}
