<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

class LanguageApiService extends ResourceApiService implements LanguageApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/cms/languages';
    }



}
