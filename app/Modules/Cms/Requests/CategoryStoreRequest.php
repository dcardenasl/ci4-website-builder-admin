<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class CategoryStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['collection_id', 'parent_id', 'is_active'];
    }

    public function rules(): array
    {
        return [
            'collection_id' => 'required|integer',
            'parent_id' => 'permit_empty',
            'is_active' => 'permit_empty|in_list[0,1]',
            'translations' => 'permit_empty',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeTranslations(): array
    {
        $translations = [];
        $rawTranslations = $this->request->getPost('translations');

        if (! is_array($rawTranslations)) {
            return [];
        }

        foreach ($rawTranslations as $trans) {
            if (! is_array($trans) || empty($trans['language_id'])) {
                continue;
            }

            $name = isset($trans['name']) ? trim((string) $trans['name']) : '';
            $slug = isset($trans['slug']) ? trim((string) $trans['slug']) : '';

            if ($name === '' && $slug === '') {
                continue;
            }

            $translations[] = [
                'language_id' => (int) $trans['language_id'],
                'name' => $name,
                'slug' => $slug,
            ];
        }

        return $translations;
    }

    public function payload(): array
    {
        return [
            'collection_id' => $this->postInt('collection_id'),
            'parent_id' => $this->postNullableInt('parent_id'),
            'is_active' => $this->postBool('is_active') ? '1' : '0',
            'translations' => $this->normalizeTranslations(),
        ];
    }
}
