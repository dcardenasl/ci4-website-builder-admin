<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

class RedirectApiService extends ResourceApiService implements RedirectApiServiceInterface
{
    protected function resourcePath(): string
    {
        return '/cms/redirects';
    }



    public function exportCsv(array $filters = []): array
    {
        return $this->list($filters);
    }

    public function importCsv(array $rows): array
    {
        $created = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $created[] = $this->create($row);
        }

        return [
            'ok'          => true,
            'status'      => 200,
            'data'        => ['items' => $created],
            'raw'         => '',
            'headers'     => [],
            'messages'    => [],
            'fieldErrors' => [],
        ];
    }

}
