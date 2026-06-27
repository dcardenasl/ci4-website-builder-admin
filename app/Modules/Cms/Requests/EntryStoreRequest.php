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
            'is_featured' => 'permit_empty|in_list[0,1]',
            'view_count' => 'permit_empty|integer',
            'sort_order' => 'permit_empty|integer',
            'is_in_sitemap' => 'permit_empty|in_list[0,1]',
            'sitemap_priority' => 'permit_empty|string|max_length[255]',
            'sitemap_changefreq' => 'permit_empty|in_list[always,hourly,daily,weekly,monthly,yearly,never]',
            'published_at' => 'permit_empty|valid_date',
            'scheduled_at' => 'permit_empty|valid_date',
            'translations' => 'permit_empty',
            'translations.*.featured_file_id' => 'permit_empty|integer',
            'translations.*.featured_image_url' => 'permit_empty|string|max_length[2048]',
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

            $title = isset($trans['title']) ? trim((string) $trans['title']) : '';
            $slug = isset($trans['slug']) ? trim((string) $trans['slug']) : '';
            $excerpt = isset($trans['excerpt']) ? trim((string) $trans['excerpt']) : '';
            $featuredFileId = isset($trans['featured_file_id']) && $trans['featured_file_id'] !== '' ? (int) $trans['featured_file_id'] : null;
            $featuredImageUrl = isset($trans['featured_image_url']) ? trim((string) $trans['featured_image_url']) : '';
            $metaTitle = isset($trans['meta_title']) ? trim((string) $trans['meta_title']) : '';
            $metaDescription = isset($trans['meta_description']) ? trim((string) $trans['meta_description']) : '';

            if ($title === '' && $slug === '' && $excerpt === '' && $featuredFileId === null && $featuredImageUrl === '' && $metaTitle === '' && $metaDescription === '') {
                continue;
            }

            $translations[] = [
                'language_id' => (int) $trans['language_id'],
                'slug' => $slug,
                'title' => $title,
                'excerpt' => $excerpt !== '' ? $excerpt : null,
                'featured_file_id' => $featuredFileId,
                'featured_image_url' => $featuredImageUrl !== '' ? $featuredImageUrl : null,
                'meta_title' => $metaTitle !== '' ? $metaTitle : null,
                'meta_description' => $metaDescription !== '' ? $metaDescription : null,
            ];
        }

        return $translations;
    }

    public function payload(): array
    {
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
            'translations' => $this->normalizeTranslations(),
        ];
    }
}
