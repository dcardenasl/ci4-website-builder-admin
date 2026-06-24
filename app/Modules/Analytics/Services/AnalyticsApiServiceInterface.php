<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Services;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
interface AnalyticsApiServiceInterface
{
    /**
     * @param array<string, mixed> $params
     * @return ApiResponse
     */
    public function overview(array $params = []): array;

    /**
     * @param array<string, mixed> $params
     * @return ApiResponse
     */
    public function pages(array $params = []): array;

    /**
     * @param array<string, mixed> $params
     * @return ApiResponse
     */
    public function referrers(array $params = []): array;

    /**
     * @param array<string, mixed> $params
     * @return ApiResponse
     */
    public function devices(array $params = []): array;

    /**
     * @param array<string, mixed> $params
     * @return ApiResponse
     */
    public function timeseries(array $params = []): array;
}
