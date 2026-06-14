<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class TagStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['is_active'];
    }

    public function rules(): array
    {
        return [
            'is_active' => 'permit_empty|in_list[0,1]',
            'translations' => 'permit_empty',
        ];
    }

    public function payload(): array
    {
        $translations = [];
        $rawTranslations = $this->request->getPost('translations');
        if (is_array($rawTranslations)) {
            foreach ($rawTranslations as $trans) {
                if (is_array($trans) && ! empty($trans['language_id'])) {
                    $translations[] = [
                        'language_id' => (int) $trans['language_id'],
                        'name'        => isset($trans['name']) ? (string) $trans['name'] : '',
                        'slug'        => isset($trans['slug']) ? (string) $trans['slug'] : '',
                    ];
                }
            }
        }

        return [
            'is_active' => $this->postBool('is_active') ? '1' : '0',
            'translations' => $translations,
        ];
    }
}
