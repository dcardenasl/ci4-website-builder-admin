<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class SettingStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['setting_key', 'setting_value', 'setting_type', 'setting_group', 'is_translatable', 'sort_order', 'description'];
    }

    public function rules(): array
    {
        return [
            'setting_key' => 'required|min_length[2]|max_length[255]',
            'setting_value' => 'permit_empty|string',
            'setting_type' => 'permit_empty|in_list[string,int,bool,json,file_id]',
            'setting_group' => 'permit_empty|string|max_length[255]',
            'is_translatable' => 'permit_empty',
            'sort_order' => 'permit_empty|integer',
            'description' => 'permit_empty|string',
        ];
    }

    public function payload(): array
    {
        return [
            'setting_key' => $this->postString('setting_key'),
            'setting_value' => $this->postString('setting_value'),
            'setting_type' => $this->postString('setting_type'),
            'setting_group' => $this->postString('setting_group'),
            'is_translatable' => $this->postBool('is_translatable'),
            'sort_order' => $this->postInt('sort_order', 0),
            'description' => $this->postString('description'),
        ];
    }
}
