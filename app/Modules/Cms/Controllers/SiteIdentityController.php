<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\SiteIdentityUpdateRequest;
use App\Modules\Cms\Services\SettingApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class SiteIdentityController extends BaseWebController
{
    protected SettingApiServiceInterface $settingService;

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

        $response = $this->settingService->getByGroup('identity');
        $items    = $this->extractItems($response);

        /** @var array<string, array<string, mixed>> $settingsMap */
        $settingsMap = [];
        foreach ($items as $item) {
            if (is_array($item) && isset($item['setting_key']) && is_string($item['setting_key'])) {
                $settingsMap[$item['setting_key']] = $item;
            }
        }

        // Get languages list for the translation inputs
        $langsRes = $this->safeApiCall(fn () => service('languageApiService')->list(['is_active' => 1]));
        $languages = $langsRes['ok'] ? $this->extractItems($langsRes) : [];

        return $this->render('cms/site-identity/show', [
            'title'       => lang('SiteIdentity.page_title'),
            'settingsMap' => $settingsMap,
            'languages'   => $languages,
        ]);
    }

    public function update(): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        /** @var SiteIdentityUpdateRequest $formRequest */
        $formRequest = service('formRequest', SiteIdentityUpdateRequest::class, false);
        $invalid     = $this->validateRequest($formRequest);
        if ($invalid !== null) {
            return $invalid;
        }

        $identityResponse = $this->settingService->getByGroup('identity');
        $items            = $this->extractItems($identityResponse);

        /** @var array<string, int> $idMap keyed by setting_key → setting id */
        $idMap = [];
        foreach ($items as $item) {
            if (is_array($item) && isset($item['setting_key'], $item['id'])
                && is_string($item['setting_key']) && is_numeric($item['id'])) {
                $idMap[(string) $item['setting_key']] = (int) $item['id'];
            }
        }

        $payload = $formRequest->payload();
        $fileFields = ['site_logo', 'favicon'];

        $siteNameTranslations = $this->request->getPost('site_name_translations');
        $siteTaglineTranslations = $this->request->getPost('site_tagline_translations');

        $failed = false;

        // Save site_name (supporting translations if available)
        if (isset($idMap['site_name'])) {
            $value = is_string($payload['site_name'] ?? null) ? (string) $payload['site_name'] : '';
            $updateData = ['setting_value' => $value];
            if (is_array($siteNameTranslations)) {
                $translations = [];
                foreach ($siteNameTranslations as $langId => $val) {
                    $translations[] = [
                        'language_id' => (int) $langId,
                        'setting_value' => (string) $val,
                    ];
                }
                $updateData['translations'] = $translations;
                $updateData['setting_value'] = (string) ($siteNameTranslations[1] ?? array_values($siteNameTranslations)[0] ?? $value);
            }
            $result = $this->settingService->update($idMap['site_name'], $updateData);
            if (! ($result['ok'] ?? false)) {
                $failed = true;
            }
        }

        // Save site_tagline (supporting translations if available)
        if (isset($idMap['site_tagline'])) {
            $value = is_string($payload['site_tagline'] ?? null) ? (string) $payload['site_tagline'] : '';
            $updateData = ['setting_value' => $value];
            if (is_array($siteTaglineTranslations)) {
                $translations = [];
                foreach ($siteTaglineTranslations as $langId => $val) {
                    $translations[] = [
                        'language_id' => (int) $langId,
                        'setting_value' => (string) $val,
                    ];
                }
                $updateData['translations'] = $translations;
                $updateData['setting_value'] = (string) ($siteTaglineTranslations[1] ?? array_values($siteTaglineTranslations)[0] ?? $value);
            }
            $result = $this->settingService->update($idMap['site_tagline'], $updateData);
            if (! ($result['ok'] ?? false)) {
                $failed = true;
            }
        }

        foreach ($fileFields as $key) {
            if (! isset($idMap[$key])) {
                continue;
            }
            $fieldData = $payload[$key] ?? [];
            if (! is_array($fieldData)) {
                continue;
            }
            $result = $this->settingService->update($idMap[$key], [
                'setting_value' => is_string($fieldData['value'] ?? null) ? (string) $fieldData['value'] : '',
                'setting_meta'  => is_string($fieldData['meta'] ?? null) ? (string) $fieldData['meta'] : '',
            ]);
            if (! ($result['ok'] ?? false)) {
                $failed = true;
            }
        }

        if ($failed) {
            return redirect()->to(route_to('admin.cms.site_identity'))->with('error', lang('SiteIdentity.update_failed'));
        }

        return redirect()->to(route_to('admin.cms.site_identity'))->with('success', lang('SiteIdentity.update_success'));
    }
}
