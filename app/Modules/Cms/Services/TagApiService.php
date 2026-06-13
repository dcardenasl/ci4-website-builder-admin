<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

class TagApiService extends ResourceApiService implements TagApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/cms/tags';
    }



}
