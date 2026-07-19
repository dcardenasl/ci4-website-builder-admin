<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Modules\Cms\Support\CmsPresetCatalog;
use App\Modules\Cms\Support\TranslationRowNormalizer;
use App\Support\Requests\BaseFormRequest;

class PageStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return [
            'page_type',
            'collection_id',
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
            'page_type' => 'permit_empty|in_list[' . $pageTypes . ']',
            'collection_id' => 'permit_empty|integer',
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
            'collection_id' => $this->postNullableInt('collection_id'),
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
        return TranslationRowNormalizer::normalize(
            $this->request->getPost('translations'),
            static function (array $row): bool {
                $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
                $title = isset($row['title']) ? trim((string) $row['title']) : '';
                $excerpt = isset($row['excerpt']) ? trim((string) $row['excerpt']) : '';
                $metaTitle = isset($row['meta_title']) ? trim((string) $row['meta_title']) : '';
                $metaDescription = isset($row['meta_description']) ? trim((string) $row['meta_description']) : '';

                return $slug !== '' || $title !== '' || $excerpt !== '' || $metaTitle !== '' || $metaDescription !== '';
            },
            static function (array $row): array {
                $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
                $title = isset($row['title']) ? trim((string) $row['title']) : '';
                $excerpt = isset($row['excerpt']) ? trim((string) $row['excerpt']) : '';
                $metaTitle = isset($row['meta_title']) ? trim((string) $row['meta_title']) : '';
                $metaDescription = isset($row['meta_description']) ? trim((string) $row['meta_description']) : '';

                return [
                    'language_id' => (int) ($row['language_id'] ?? 0),
                    'slug' => $slug,
                    'title' => $title,
                    'excerpt' => $excerpt !== '' ? $excerpt : null,
                    'meta_title' => $metaTitle !== '' ? $metaTitle : null,
                    'meta_description' => $metaDescription !== '' ? $metaDescription : null,
                ];
            }
        );
    }
}
