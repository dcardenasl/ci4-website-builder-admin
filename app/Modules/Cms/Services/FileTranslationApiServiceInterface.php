<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
interface FileTranslationApiServiceInterface
{
    /**
     * @return ApiResponse
     */
    public function getForFile(int $fileId): array;

    /**
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function createForFile(int $fileId, array $payload): array;

    /**
     * @param array<string, mixed> $payload
     * @return ApiResponse
     */
    public function updateForFile(int $fileId, int $translationId, array $payload): array;
}
