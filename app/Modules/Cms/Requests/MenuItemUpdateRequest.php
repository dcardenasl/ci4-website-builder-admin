<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class MenuItemUpdateRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return [
            'menu_id',
            'parent_id',
            'link_type',
            'page_id',
            'link_target',
            'icon',
            'css_class',
            'sort_order',
            'is_active',
            'translations',
        ];
    }

    public function rules(): array
    {
        return [
            'menu_id'     => 'required|integer',
            'parent_id'   => 'permit_empty|integer',
            'link_type'   => 'required|in_list[page,entry,collection_listing,custom_url,no_link]',
            'page_id'     => 'permit_empty|integer',
            'link_target' => 'required|in_list[_self,_blank]',
            'icon'        => 'permit_empty|string|max_length[50]',
            'css_class'   => 'permit_empty|string|max_length[100]',
            'sort_order'  => 'required|integer',
            'is_active'   => 'permit_empty',
        ];
    }

    public function payload(): array
    {
        $payload = [
            'menu_id'       => $this->postInt('menu_id'),
            'parent_id'     => is_numeric($this->request->getPost('parent_id')) ? (int) $this->request->getPost('parent_id') : null,
            'link_type'     => $this->postString('link_type'),
            'page_id'       => is_numeric($this->request->getPost('page_id')) ? (int) $this->request->getPost('page_id') : null,
            'link_target'   => $this->postString('link_target') ?: '_self',
            'icon'          => $this->postString('icon') ?: null,
            'css_class'     => $this->postString('css_class') ?: null,
            'sort_order'    => $this->postInt('sort_order'),
            'is_active'     => $this->postBool('is_active') ? '1' : '0',
            'translations'  => []
        ];

        // Format translations
        $rawTranslations = $this->postArray('translations');
        $translations = [];
        foreach ($rawTranslations as $langId => $t) {
            $label = isset($t['label']) ? trim((string)$t['label']) : '';
            $customUrl = isset($t['custom_url']) ? trim((string)$t['custom_url']) : '';

            $translations[] = [
                'language_id' => (int) $langId,
                'label'       => $label,
                'custom_url'  => $payload['link_type'] === 'custom_url' ? $customUrl : null,
            ];
        }

        $payload['translations'] = $translations;

        return $payload;
    }
}
