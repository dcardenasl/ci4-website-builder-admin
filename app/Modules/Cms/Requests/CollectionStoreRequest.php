<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class CollectionStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['collection_key', 'is_active', 'requires_approval', 'enables_categories', 'enables_tags'];
    }

    public function rules(): array
    {
        return [
            'collection_key' => 'required|min_length[2]|max_length[255]',
            'is_active' => 'permit_empty',
            'requires_approval' => 'permit_empty',
            'enables_categories' => 'permit_empty',
            'enables_tags' => 'permit_empty',
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
                        'name' => isset($trans['name']) ? (string) $trans['name'] : '',
                        'description' => isset($trans['description']) ? (string) $trans['description'] : null,
                    ];
                }
            }
        }

        return [
            'collection_key' => $this->postString('collection_key'),
            'is_active' => $this->postBool('is_active'),
            'requires_approval' => $this->postBool('requires_approval'),
            'enables_categories' => $this->postBool('enables_categories'),
            'enables_tags' => $this->postBool('enables_tags'),
            'translations' => $translations,
        ];
    }
}
