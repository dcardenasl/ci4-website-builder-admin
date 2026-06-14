<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class SettingStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return [
            'setting_key',
            'setting_type',
            'setting_value_string',
            'setting_value_int',
            'setting_value_bool',
            'setting_value_json',
            'setting_group',
            'is_translatable',
            'sort_order',
            'description',
        ];
    }

    public function rules(): array
    {
        return [
            'setting_key' => 'required|min_length[2]|max_length[255]',
            'setting_value_string' => 'permit_empty|string',
            'setting_value_int' => 'permit_empty|integer',
            'setting_value_bool' => 'permit_empty|in_list[0,1]',
            'setting_value_json' => 'permit_empty|string',
            'setting_type' => 'permit_empty|in_list[string,int,bool,json,file_id]',
            'setting_group' => 'permit_empty|string|max_length[255]',
            'is_translatable' => 'permit_empty|in_list[0,1]',
            'sort_order' => 'permit_empty|integer',
            'description' => 'permit_empty|string',
        ];
    }

    public function payload(): array
    {
        $type = $this->postString('setting_type') ?: 'string';

        return [
            'setting_key' => $this->postString('setting_key'),
            'setting_value' => $this->settingValueForType($type),
            'setting_type' => $type,
            'setting_group' => $this->postString('setting_group'),
            'is_translatable' => $this->postBool('is_translatable') ? '1' : '0',
            'sort_order' => $this->postInt('sort_order', 0),
            'description' => $this->postString('description'),
        ];
    }

    private function settingValueForType(string $type): string
    {
        return match ($type) {
            'int' => $this->postString('setting_value_int'),
            'bool' => $this->postBool('setting_value_bool') ? '1' : '0',
            'json' => $this->postString('setting_value_json'),
            default => $this->postString('setting_value_string'),
        };
    }
}
