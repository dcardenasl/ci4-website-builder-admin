<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
interface TranslationAuditApiServiceInterface
{
    /**
     * @return ApiResponse
     */
    public function getStats(): array;

    /**
     * @param array<string, mixed> $filters
     * @return ApiResponse
     */
    public function getReport(array $filters = []): array;

    /**
     * @param string $type
     * @param int|string $id
     * @return ApiResponse
     */
    public function auditResource(string $type, int|string $id): array;
}
