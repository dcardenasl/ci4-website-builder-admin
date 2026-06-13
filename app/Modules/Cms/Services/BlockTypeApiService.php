<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

class BlockTypeApiService extends ResourceApiService implements BlockTypeApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/cms/block-types';
    }



}
