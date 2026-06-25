<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

interface BlockCatalogServiceInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function indexed(): array;
}
