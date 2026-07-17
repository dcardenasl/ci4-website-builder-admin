<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Modules\Cms\Support\TranslationRowNormalizer;
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
        return TranslationRowNormalizer::normalize(
            $this->request->getPost('translations'),
            static function (array $row): bool {
                $title = isset($row['title']) ? trim((string) $row['title']) : '';
                $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
                $excerpt = isset($row['excerpt']) ? trim((string) $row['excerpt']) : '';
                $featuredFileId = isset($row['featured_file_id']) && $row['featured_file_id'] !== '' ? (int) $row['featured_file_id'] : null;
                $featuredImageUrl = isset($row['featured_image_url']) ? trim((string) $row['featured_image_url']) : '';
                $metaTitle = isset($row['meta_title']) ? trim((string) $row['meta_title']) : '';
                $metaDescription = isset($row['meta_description']) ? trim((string) $row['meta_description']) : '';

                return $title !== ''
                    || $slug !== ''
                    || $excerpt !== ''
                    || $featuredFileId !== null
                    || $featuredImageUrl !== ''
                    || $metaTitle !== ''
                    || $metaDescription !== '';
            },
            static function (array $row): array {
                $title = isset($row['title']) ? trim((string) $row['title']) : '';
                $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
                $excerpt = isset($row['excerpt']) ? trim((string) $row['excerpt']) : '';
                $featuredFileId = isset($row['featured_file_id']) && $row['featured_file_id'] !== '' ? (int) $row['featured_file_id'] : null;
                $featuredImageUrl = isset($row['featured_image_url']) ? trim((string) $row['featured_image_url']) : '';
                $metaTitle = isset($row['meta_title']) ? trim((string) $row['meta_title']) : '';
                $metaDescription = isset($row['meta_description']) ? trim((string) $row['meta_description']) : '';

                return [
                    'language_id' => (int) ($row['language_id'] ?? 0),
                    'slug' => $slug,
                    'title' => $title,
                    'excerpt' => $excerpt !== '' ? $excerpt : null,
                    'featured_file_id' => $featuredFileId,
                    'featured_image_url' => $featuredImageUrl !== '' ? $featuredImageUrl : null,
                    'meta_title' => $metaTitle !== '' ? $metaTitle : null,
                    'meta_description' => $metaDescription !== '' ? $metaDescription : null,
                ];
            }
        );
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
