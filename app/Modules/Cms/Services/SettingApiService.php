<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

class SettingApiService extends ResourceApiService implements SettingApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/cms/settings';
    }



}
