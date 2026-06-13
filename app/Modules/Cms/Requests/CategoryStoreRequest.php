<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class CategoryStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['collection_id', 'parent_id', 'sort_order', 'is_active'];
    }

    public function rules(): array
    {
        return [
            'collection_id' => 'permit_empty',
            'parent_id' => 'permit_empty',
            'sort_order' => 'permit_empty|integer',
            'is_active' => 'permit_empty',
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
            'collection_id' => $this->postInt('collection_id'),
            'parent_id' => $this->postInt('parent_id'),
            'sort_order' => $this->postInt('sort_order', 0),
            'is_active' => $this->postBool('is_active'),
            'translations' => $translations,
        ];
    }
}
