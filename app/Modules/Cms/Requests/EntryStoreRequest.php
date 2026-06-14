<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class EntryStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return [
            'collection_id',
            'status',
            'author_id',
            'is_featured',
            'view_count',
            'sort_order',
            'is_in_sitemap',
            'sitemap_priority',
            'sitemap_changefreq',
            'published_at',
            'scheduled_at',
        ];
    }

    public function rules(): array
    {
        return [
            'collection_id' => 'required|integer',
            'status' => 'required|in_list[draft,published,archived]',
            'author_id' => 'permit_empty|integer',
            'is_featured' => 'permit_empty',
            'view_count' => 'permit_empty|integer',
            'sort_order' => 'permit_empty|integer',
            'is_in_sitemap' => 'permit_empty',
            'sitemap_priority' => 'permit_empty|string|max_length[255]',
            'sitemap_changefreq' => 'permit_empty|in_list[always,hourly,daily,weekly,monthly,yearly,never]',
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
            'workflow_status' => $this->postString('status') ?: 'draft',
            'author_id' => $this->postNullableInt('author_id'),
            'is_featured' => $this->postBool('is_featured') ? '1' : '0',
            'view_count' => $this->postInt('view_count', 0),
            'sort_order' => $this->postInt('sort_order', 0),
            'is_in_sitemap' => $this->postBool('is_in_sitemap') ? '1' : '0',
            'sitemap_priority' => $this->postString('sitemap_priority'),
            'sitemap_changefreq' => $this->postString('sitemap_changefreq') ?: 'weekly',
            'published_at' => $this->postString('published_at'),
            'scheduled_at' => $this->postString('scheduled_at'),
            'translations' => $translations,
        ];
    }
}
