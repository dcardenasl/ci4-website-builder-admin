<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class MenuStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['menu_key', 'is_active'];
    }

    public function rules(): array
    {
        return [
            'menu_key' => 'required|min_length[2]|max_length[255]',
            'is_active' => 'permit_empty',
        ];
    }

    public function payload(): array
    {
        return [
            'menu_key' => $this->postString('menu_key'),
            'is_active' => $this->postBool('is_active'),
        ];
    }
}
