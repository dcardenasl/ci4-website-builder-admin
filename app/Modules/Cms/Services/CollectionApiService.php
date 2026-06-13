<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

class CollectionApiService extends ResourceApiService implements CollectionApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/cms/collections';
    }



}
