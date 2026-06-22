<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class SiteIdentityUpdateRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return [
            'site_name',
            'site_tagline',
            'site_logo_file_id',
            'site_logo_url',
            'site_logo_mime_type',
            'favicon_file_id',
            'favicon_url',
            'favicon_mime_type',
        ];
    }

    public function rules(): array
    {
        return [
            'site_name'          => 'permit_empty|string|max_length[255]',
            'site_tagline'       => 'permit_empty|string|max_length[500]',
            'site_logo_file_id'  => 'permit_empty|integer',
            'site_logo_url'      => 'permit_empty|string',
            'site_logo_mime_type' => 'permit_empty|string',
            'favicon_file_id'    => 'permit_empty|integer',
            'favicon_url'        => 'permit_empty|string',
            'favicon_mime_type'  => 'permit_empty|string',
        ];
    }

    public function payload(): array
    {
        return [
            'site_name'    => $this->postString('site_name'),
            'site_tagline' => $this->postString('site_tagline'),
            'site_logo'    => [
                'value' => $this->postString('site_logo_file_id'),
                'meta'  => json_encode([
                    'url'       => $this->postString('site_logo_url'),
                    'mime_type' => $this->postString('site_logo_mime_type'),
                ]),
            ],
            'favicon' => [
                'value' => $this->postString('favicon_file_id'),
                'meta'  => json_encode([
                    'url'       => $this->postString('favicon_url'),
                    'mime_type' => $this->postString('favicon_mime_type'),
                ]),
            ],
        ];
    }
}
