<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Libraries\Cms\CmsPresetCatalog;
use App\Support\Requests\BaseFormRequest;

class PageStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return [
            'page_type',
            'status',
            'parent_id',
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
        $pageTypes = implode(',', CmsPresetCatalog::pageTypes());

        return [
            'page_type' => 'required|in_list[' . $pageTypes . ']',
            'status' => 'required|in_list[draft,published,archived]',
            'parent_id' => 'permit_empty',
            'sort_order' => 'permit_empty|integer',
            'is_in_sitemap' => 'permit_empty|in_list[0,1]',
            'sitemap_priority' => 'permit_empty|string|max_length[255]',
            'sitemap_changefreq' => 'permit_empty|in_list[always,hourly,daily,weekly,monthly,yearly,never]',
            'published_at' => 'permit_empty|valid_date',
            'scheduled_at' => 'permit_empty|valid_date',
            'translations' => 'permit_empty',
        ];
    }

    public function payload(): array
    {
        $translations = $this->normalizeTranslations();

        return [
            'page_type' => $this->postString('page_type') ?: 'generic',
            'status' => $this->postString('status') ?: 'draft',
            'parent_id' => $this->postNullableInt('parent_id'),
            'sort_order' => $this->postInt('sort_order', 0),
            'is_in_sitemap' => $this->postBool('is_in_sitemap') ? '1' : '0',
            'sitemap_priority' => $this->postString('sitemap_priority'),
            'sitemap_changefreq' => $this->postString('sitemap_changefreq') ?: 'weekly',
            'published_at' => $this->postString('published_at'),
            'scheduled_at' => $this->postString('scheduled_at'),
            'translations' => $translations,
        ];
    }

    /**
     * Keep only meaningful translation rows so empty secondary language slots
     * do not trigger API validation failures.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeTranslations(): array
    {
        $translations = [];
        $rawTranslations = $this->request->getPost('translations');

        if (! is_array($rawTranslations)) {
            return $translations;
        }

        foreach ($rawTranslations as $trans) {
            if (! is_array($trans) || empty($trans['language_id'])) {
                continue;
            }

            $slug = isset($trans['slug']) ? trim((string) $trans['slug']) : '';
            $title = isset($trans['title']) ? trim((string) $trans['title']) : '';
            $excerpt = isset($trans['excerpt']) ? trim((string) $trans['excerpt']) : '';
            $metaTitle = isset($trans['meta_title']) ? trim((string) $trans['meta_title']) : '';
            $metaDescription = isset($trans['meta_description']) ? trim((string) $trans['meta_description']) : '';

            if ($slug === '' && $title === '' && $excerpt === '' && $metaTitle === '' && $metaDescription === '') {
                continue;
            }

            $translations[] = [
                'language_id' => (int) $trans['language_id'],
                'slug' => $slug,
                'title' => $title,
                'excerpt' => $excerpt !== '' ? $excerpt : null,
                'meta_title' => $metaTitle !== '' ? $metaTitle : null,
                'meta_description' => $metaDescription !== '' ? $metaDescription : null,
            ];
        }

        return $translations;
    }
}
