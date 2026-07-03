<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;
use App\Modules\Cms\Support\CmsPresetCatalog;

class CollectionStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return [
            'collection_type',
            'collection_key',
            'default_sitemap_priority',
            'default_changefreq',
            'sort_order',
            'is_active',
            'requires_approval',
            'enables_categories',
            'enables_tags',
            'block_template',
            'wizard_config',
        ];
    }

    public function data(): array
    {
        $data = parent::data();
        $data['translations'] = $this->postArray('translations');

        return $data;
    }

    public function rules(): array
    {
        $collectionTypes = implode(',', CmsPresetCatalog::collectionTypes());

        return [
            'collection_type' => 'required|in_list[' . $collectionTypes . ']',
            'collection_key' => 'required|min_length[2]|max_length[255]',
            'default_sitemap_priority' => 'permit_empty|decimal',
            'default_changefreq' => 'permit_empty|in_list[always,hourly,daily,weekly,monthly,yearly,never]',
            'sort_order' => 'permit_empty|integer',
            'is_active' => 'permit_empty|in_list[0,1]',
            'requires_approval' => 'permit_empty|in_list[0,1]',
            'enables_categories' => 'permit_empty|in_list[0,1]',
            'enables_tags' => 'permit_empty|in_list[0,1]',
            'block_template' => 'permit_empty',
            'wizard_config' => 'permit_empty',
            'translations' => 'permit_empty',
            'translations.*.slug' => 'required_with[translations]|string|min_length[1]|max_length[150]',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeTranslations(): array
    {
        $translations = [];
        $rawTranslations = $this->postArray('translations');

        if ($rawTranslations === []) {
            return [];
        }

        foreach ($rawTranslations as $trans) {
            if (! is_array($trans) || empty($trans['language_id'])) {
                continue;
            }

            $slug = isset($trans['slug']) ? trim((string) $trans['slug'], " \t\n\r\0\x0B/") : '';
            $name = isset($trans['name']) ? trim((string) $trans['name']) : '';
            $description = isset($trans['description']) ? trim((string) $trans['description']) : '';

            if ($slug === '' && $name === '' && $description === '') {
                continue;
            }

            $translations[] = [
                'language_id' => (int) $trans['language_id'],
                'slug' => $slug !== '' ? $slug : null,
                'name' => $name,
                'description' => $description !== '' ? $description : null,
            ];
        }

        return $translations;
    }

    public function payload(): array
    {
        $payload = [
            'collection_type' => $this->postString('collection_type') ?: 'other',
            'collection_key' => $this->postString('collection_key'),
            'default_sitemap_priority' => $this->postString('default_sitemap_priority') ?: '0.5',
            'default_changefreq' => $this->postString('default_changefreq') ?: 'weekly',
            'sort_order' => $this->postInt('sort_order', 0),
            'is_active' => $this->postBool('is_active') ? '1' : '0',
            'requires_approval' => $this->postBool('requires_approval') ? '1' : '0',
            'enables_categories' => $this->postBool('enables_categories') ? '1' : '0',
            'enables_tags' => $this->postBool('enables_tags') ? '1' : '0',
            'block_template' => $this->postString('block_template'),
            'wizard_config' => $this->postString('wizard_config'),
            'translations' => $this->normalizeTranslations(),
        ];

        return $payload;
    }
}
