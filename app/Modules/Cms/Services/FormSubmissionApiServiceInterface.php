<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

interface FormSubmissionApiServiceInterface
{
    /** @return array<string, mixed> */
    public function list(array $filters = []): array;

    /** @return array<string, mixed> */
    public function get(int|string $id): array;

    /** @return array<string, mixed> */
    public function counts(): array;

    /** @return array<string, mixed> */
    public function updateStatus(int|string $id, string $status): array;
}
