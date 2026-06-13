<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class EntryStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['collection_id', 'status', 'sort_order', 'published_at', 'scheduled_at'];
    }

    public function rules(): array
    {
        return [
            'collection_id' => 'permit_empty',
            'status' => 'permit_empty|in_list[draft,published,archived]',
            'sort_order' => 'permit_empty|integer',
            'published_at' => 'permit_empty|valid_date',
            'scheduled_at' => 'permit_empty|valid_date',
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
                        'language_id'      => (int) $trans['language_id'],
                        'slug'             => isset($trans['slug']) ? (string) $trans['slug'] : '',
                        'title'            => isset($trans['title']) ? (string) $trans['title'] : '',
                        'excerpt'          => isset($trans['excerpt']) ? (string) $trans['excerpt'] : null,
                        'meta_title'       => isset($trans['meta_title']) ? (string) $trans['meta_title'] : null,
                        'meta_description' => isset($trans['meta_description']) ? (string) $trans['meta_description'] : null,
                    ];
                }
            }
        }

        return [
            'collection_id' => $this->postInt('collection_id'),
            'status' => $this->postString('status'),
            'sort_order' => $this->postInt('sort_order', 0),
            'published_at' => $this->postString('published_at'),
            'scheduled_at' => $this->postString('scheduled_at'),
            'translations' => $translations,
        ];
    }
}
