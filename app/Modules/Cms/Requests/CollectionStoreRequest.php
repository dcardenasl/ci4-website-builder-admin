<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class CollectionStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return [
            'collection_key',
            'url_prefix',
            'default_sitemap_priority',
            'default_changefreq',
            'sort_order',
            'is_active',
            'requires_approval',
            'enables_categories',
            'enables_tags',
        ];
    }

    public function rules(): array
    {
        return [
            'collection_key' => 'required|min_length[2]|max_length[255]',
            'url_prefix' => 'required|min_length[1]|max_length[255]',
            'default_sitemap_priority' => 'permit_empty|decimal',
            'default_changefreq' => 'permit_empty|in_list[always,hourly,daily,weekly,monthly,yearly,never]',
            'sort_order' => 'permit_empty|integer',
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
            'url_prefix' => $this->postString('url_prefix'),
            'default_sitemap_priority' => $this->postString('default_sitemap_priority') ?: '0.5',
            'default_changefreq' => $this->postString('default_changefreq') ?: 'weekly',
            'sort_order' => $this->postInt('sort_order', 0),
            'is_active' => $this->postBool('is_active'),
            'requires_approval' => $this->postBool('requires_approval'),
            'enables_categories' => $this->postBool('enables_categories'),
            'enables_tags' => $this->postBool('enables_tags'),
            'translations' => $translations,
        ];
    }
}
