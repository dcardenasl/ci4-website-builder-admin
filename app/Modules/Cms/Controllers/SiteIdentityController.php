<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\SiteIdentityUpdateRequest;
use App\Modules\Cms\Services\SettingApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class SiteIdentityController extends BaseWebController
{
    protected SettingApiService $settingService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->settingService = service('settingApiService');
    }

    private function requireWrite(): ?RedirectResponse
    {
        if (! has_permission('cms.settings.write')) {
            return redirect()->to(route_to('dashboard'))->with('error', lang('App.access_denied'));
        }
        return null;
    }

    public function show(): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }
        helper('cms_settings');

        $response = $this->settingService->getByGroup('identity');
        $items    = $this->extractItems($response);

        $langsRes       = $this->safeApiCall(fn () => service('languageApiService')->list(['is_active' => 1]));
        $languages      = array_values($langsRes['ok'] ? $this->extractItems($langsRes) : []);
        $languageContext = $this->resolveLanguageContext($languages);
        $settingsMap    = $this->indexSettingsByKey($items);
        $sortedSettings = $this->sortSettingsByOrder($settingsMap);
        [$contentSettings, $assetSettings] = $this->splitSettingsByInputType($sortedSettings);
        $translationPanel = cms_settings_build_translation_panel($contentSettings, $languages, $languageContext['defaultLangId']);

        return $this->render('cms/site-identity/show', [
            'title'            => lang('SiteIdentity.page_title'),
            'contentSettings'  => $contentSettings,
            'assetSettings'    => $assetSettings,
            'translationPanel' => $translationPanel,
        ]);
    }

    public function update(): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $identityResponse = $this->settingService->getByGroup('identity');
        $items            = $this->extractItems($identityResponse);

        $langsRes       = $this->safeApiCall(fn () => service('languageApiService')->list(['is_active' => 1]));
        $languages      = array_values($langsRes['ok'] ? $this->extractItems($langsRes) : []);
        $languageContext = $this->resolveLanguageContext($languages);

        /** @var SiteIdentityUpdateRequest $formRequest */
        $formRequest = service('formRequest', SiteIdentityUpdateRequest::class, false);
        $formRequest->setIdentitySettings($items);
        $formRequest->setLanguages($languages);
        $formRequest->setBaseLanguageId($languageContext['defaultLangId']);

        $invalid = $this->validateRequest($formRequest);
        if ($invalid !== null) {
            return $invalid;
        }

        /** @var array<string, int> $idMap keyed by setting_key → setting id */
        $idMap = [];
        foreach ($items as $item) {
            if (is_array($item) && isset($item['setting_key'], $item['id'])
                && is_string($item['setting_key']) && is_numeric($item['id'])) {
                $idMap[(string) $item['setting_key']] = (int) $item['id'];
            }
        }

        $payloads = $formRequest->payload();
        $failed   = false;

        foreach ($payloads as $settingKey => $updateData) {
            if (!isset($idMap[$settingKey])) {
                continue;
            }

            $result = $this->settingService->update($idMap[$settingKey], $updateData);
            if (! ($result['ok'] ?? false)) {
                $failed = true;
            }
        }

        if ($failed) {
            return redirect()->to(route_to('admin.cms.site_identity'))->with('error', lang('SiteIdentity.update_failed'));
        }

        return redirect()->to(route_to('admin.cms.site_identity'))->with('success', lang('SiteIdentity.update_success'));
    }

    /**
     * @param array<int|string, mixed> $items
     * @return array<string, array<string, mixed>>
     */
    private function indexSettingsByKey(array $items): array
    {
        $settingsMap = [];

        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['setting_key']) || ! is_string($item['setting_key'])) {
                continue;
            }

            $settingsMap[$item['setting_key']] = $item;
        }

        return $settingsMap;
    }

    /**
     * @param array<string, array<string, mixed>> $settingsMap
     * @return array<int, array<string, mixed>>
     */
    private function sortSettingsByOrder(array $settingsMap): array
    {
        $sortedSettings = array_values($settingsMap);
        usort($sortedSettings, static fn (array $a, array $b): int => (int) ($a['sort_order'] ?? 0) <=> (int) ($b['sort_order'] ?? 0));

        return $sortedSettings;
    }

    /**
     * @param array<int, array<string, mixed>> $sortedSettings
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    private function splitSettingsByInputType(array $sortedSettings): array
    {
        $assetSettings = [];
        $contentSettings = [];

        foreach ($sortedSettings as $setting) {
            $inputType = (string) ($setting['input_type'] ?? 'text');
            if (in_array($inputType, ['image', 'file'], true)) {
                $assetSettings[] = $setting;
                continue;
            }

            $contentSettings[] = $setting;
        }

        return [$contentSettings, $assetSettings];
    }
}
