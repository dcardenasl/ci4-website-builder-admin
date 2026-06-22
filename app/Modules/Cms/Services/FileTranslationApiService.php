<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\BaseApiService;

class FileTranslationApiService extends BaseApiService implements FileTranslationApiServiceInterface
{
    public function getForFile(int $fileId): array
    {
        return $this->apiClient->get("/cms/files/{$fileId}/translations");
    }

    public function createForFile(int $fileId, array $payload): array
    {
        return $this->apiClient->post("/cms/files/{$fileId}/translations", $payload);
    }

    public function updateForFile(int $fileId, int $translationId, array $payload): array
    {
        return $this->apiClient->put("/cms/files/{$fileId}/translations/{$translationId}", $payload);
    }
}
