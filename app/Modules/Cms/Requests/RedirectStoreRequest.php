<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class RedirectStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['from_path', 'to_path', 'status_code', 'is_active'];
    }

    public function rules(): array
    {
        return [
            'from_path' => 'required|min_length[2]|max_length[255]|starts_with[/]',
            'to_path' => 'required|min_length[2]|max_length[255]',
            'status_code' => 'permit_empty|in_list[301,302]',
            'is_active' => 'permit_empty',
        ];
    }

    public function payload(): array
    {
        return [
            'from_path' => $this->postString('from_path'),
            'to_path' => $this->postString('to_path'),
            'status_code' => $this->postString('status_code'),
            'is_active' => $this->postBool('is_active'),
        ];
    }
}
