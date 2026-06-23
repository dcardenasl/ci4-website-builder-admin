<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class SettingStoreRequest extends BaseFormRequest
{
    /** @var array<mixed> */
    private array $resolvedLanguages = [];

    private ?int $baseLanguageId = null;

    /** @param array<mixed> $languages */
    public function setLanguages(array $languages): void
    {
        $this->resolvedLanguages = $languages;
    }

    public function setBaseLanguageId(?int $languageId): void
    {
        $this->baseLanguageId = $languageId;
    }

    protected function fields(): array
    {
        return [
            'setting_key',
            'setting_type',
            'setting_value',
            'setting_value_string',
            'setting_value_int',
            'setting_value_bool',
            'setting_value_json',
            'setting_group',
            'is_translatable',
            'sort_order',
            'description',
        ];
    }

    public function rules(): array
    {
        return [
            'setting_key' => 'required|min_length[2]|max_length[255]',
            'setting_value' => 'permit_empty|string',
            'setting_value_string' => 'permit_empty|string',
            'setting_value_int' => 'permit_empty|integer',
            'setting_value_bool' => 'permit_empty|in_list[0,1]',
            'setting_value_json' => 'permit_empty|string',
            'setting_type' => 'permit_empty|in_list[string,int,bool,json,file_id]',
            'setting_group' => 'permit_empty|string|max_length[255]',
            'is_translatable' => 'permit_empty|in_list[0,1]',
            'sort_order' => 'permit_empty|integer',
            'description' => 'permit_empty|string',
        ];
    }

    public function payload(): array
    {
        $type           = $this->postString('setting_type') ?: 'string';
        $isTranslatable = $this->postBool('is_translatable');
        $settingValue   = $this->settingValueForType($type);

        $payload = [
            'setting_key'     => $this->postString('setting_key'),
            'setting_value'   => $settingValue,
            'setting_type'    => $type,
            'setting_group'   => $this->postString('setting_group'),
            'is_translatable' => $isTranslatable ? '1' : '0',
            'sort_order'      => $this->postInt('sort_order', 0),
            'description'     => $this->postString('description'),
        ];

        if (!$isTranslatable) {
            return $payload;
        }

        $languages = $this->resolvedLanguages;
        if (empty($languages)) {
            return $payload;
        }

        $postTranslations = $this->request->getPost('translations');
        $baseLanguageId = $this->resolveBaseLanguageId($languages);
        if ($this->baseLanguageId !== null) {
            $baseLanguageId = $this->baseLanguageId;
        }

        if ($payload['setting_value'] === '' && is_array($postTranslations) && $baseLanguageId !== null && isset($postTranslations[$baseLanguageId])) {
            $payload['setting_value'] = (string) $postTranslations[$baseLanguageId];
        }

        $translations = [];
        foreach ($languages as $lang) {
            $langId = (int) ($lang['id'] ?? 0);
            if ($langId <= 0 || ($baseLanguageId !== null && $langId === $baseLanguageId)) {
                continue;
            }

            if (! is_array($postTranslations) || ! array_key_exists($langId, $postTranslations)) {
                continue;
            }

            $translationValue = (string) $postTranslations[$langId];
            if ($translationValue === '') {
                continue;
            }

            $translations[] = [
                'language_id'   => $langId,
                'setting_value' => $translationValue,
            ];
        }

        if (!empty($translations)) {
            $payload['translations'] = $translations;
        }

        return $payload;
    }

    /**
     * @param array<mixed> $languages
     */
    private function resolveBaseLanguageId(array $languages): ?int
    {
        foreach ($languages as $language) {
            if (! is_array($language)) {
                continue;
            }

            if (! empty($language['is_default']) && isset($language['id']) && is_numeric($language['id'])) {
                return (int) $language['id'];
            }
        }

        foreach ($languages as $language) {
            if (! is_array($language)) {
                continue;
            }

            if (isset($language['id']) && is_numeric($language['id'])) {
                return (int) $language['id'];
            }
        }

        return null;
    }

    private function settingValueForType(string $type): string
    {
        $canonical = $this->postString('setting_value');
        if ($canonical !== '') {
            return $canonical;
        }

        return match ($type) {
            'int'  => $this->postString('setting_value_int'),
            'bool' => $this->postBool('setting_value_bool') ? '1' : '0',
            'json' => $this->postString('setting_value_json'),
            default => $this->postString('setting_value_string'),
        };
    }

}
