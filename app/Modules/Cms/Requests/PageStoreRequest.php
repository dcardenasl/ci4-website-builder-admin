<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class PageStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['page_type', 'status', 'parent_id', 'sort_order', 'is_in_sitemap', 'sitemap_priority', 'published_at', 'scheduled_at'];
    }

    public function rules(): array
    {
        return [
            'page_type' => 'permit_empty|in_list[home,generic,contact,privacy,terms,404,500,maintenance]',
            'status' => 'permit_empty|in_list[draft,published,archived]',
            'parent_id' => 'permit_empty',
            'sort_order' => 'permit_empty|integer',
            'is_in_sitemap' => 'permit_empty',
            'sitemap_priority' => 'permit_empty|string|max_length[255]',
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
            'page_type' => $this->postString('page_type'),
            'status' => $this->postString('status'),
            'parent_id' => $this->postInt('parent_id'),
            'sort_order' => $this->postInt('sort_order', 0),
            'is_in_sitemap' => $this->postBool('is_in_sitemap'),
            'sitemap_priority' => $this->postString('sitemap_priority'),
            'published_at' => $this->postString('published_at'),
            'scheduled_at' => $this->postString('scheduled_at'),
            'translations' => $translations,
        ];
    }
}
