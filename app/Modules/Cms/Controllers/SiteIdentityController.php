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

        return $this->render('cms/site-identity/show', [
            'title'       => lang('SiteIdentity.page_title'),
            'settingsMap' => $settingsMap,
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

        $simpleFields = ['site_name', 'site_tagline'];
        $fileFields   = ['site_logo', 'favicon'];

        $failed = false;

        foreach ($simpleFields as $key) {
            if (! isset($idMap[$key])) {
                continue;
            }
            $value  = is_string($payload[$key] ?? null) ? (string) $payload[$key] : '';
            $result = $this->settingService->update($idMap[$key], ['setting_value' => $value]);
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
