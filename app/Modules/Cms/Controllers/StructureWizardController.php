<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Services\CollectionApiServiceInterface;
use App\Modules\Cms\Services\MenuApiServiceInterface;
use App\Modules\Cms\Services\PageApiServiceInterface;
use App\Modules\Cms\Support\CmsPresetCatalog;
use App\Modules\Cms\Support\PagePresetApplier;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class StructureWizardController extends BaseWebController
{
    protected CollectionApiServiceInterface $collectionService;
    protected PageApiServiceInterface $pageService;
    protected MenuApiServiceInterface $menuService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->collectionService = service('collectionApiService');
        $this->pageService = service('pageApiService');
        $this->menuService = service('menuApiService');
    }

    public function index(): string
    {
        $languages = $this->loadActiveLanguages();

        return $this->render('cms/wizard/structure', [
            'title'      => lang('Wizard.structure_title'),
            'csrfName'   => csrf_token(),
            'csrfToken'  => csrf_hash(),
            'languages'  => $languages,
        ]);
    }

    public function config(): ResponseInterface
    {
        $languages = $this->loadActiveLanguages();

        return $this->response->setJSON([
            'ok' => true,
            'data' => [
                'languages' => $languages,
                'collection_types' => $this->collectionTypeOptions(),
                'collection_presets' => array_column(CmsPresetCatalog::collectionPresets(), null, 'type_key'),
                'page_types' => $this->pageTypeOptions(),
                'page_presets' => array_column(CmsPresetCatalog::pagePresets(), null, 'type_key'),
            ],
        ]);
    }

    public function createPage(): ResponseInterface
    {
        if (! has_permission('cms.pages.write')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => lang('App.access_denied')]);
        }

        $raw = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest ? ($this->request->getJSON(true) ?? []) : [];
        $payload = is_array($raw) ? $raw : [];
        $result = $this->safeApiCall(fn () => $this->pageService->create($payload));
        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }
        if ($statusCode >= 200 && $statusCode < 300) {
            $data = $this->extractData($result);
            $pageId = (int) ($data['id'] ?? 0);
            if ($pageId > 0) {
                PagePresetApplier::fromServices()->apply($pageId, (string) ($payload['page_type'] ?? 'generic'));
            }
        }
        return $this->response->setStatusCode($statusCode)->setJSON([
            'ok' => $statusCode >= 200 && $statusCode < 300,
            'data' => $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? []),
        ]);
    }

    public function createMenu(): ResponseInterface
    {
        if (! has_permission('cms.menus.write')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => lang('App.access_denied')]);
        }

        $raw = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest ? ($this->request->getJSON(true) ?? []) : [];
        $payload = is_array($raw) ? $raw : [];
        $result = $this->safeApiCall(fn () => $this->menuService->create($payload));
        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }
        return $this->response->setStatusCode($statusCode)->setJSON([
            'ok' => $statusCode >= 200 && $statusCode < 300,
            'data' => $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? []),
        ]);
    }

    public function createCollection(): ResponseInterface
    {
        if (! has_permission('cms.collections.write')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => lang('App.access_denied')]);
        }

        $raw = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? ($this->request->getJSON(true) ?? [])
            : [];
        $payload = is_array($raw) ? $raw : [];

        $validationError = $this->validateCollectionWizardPayload($payload);
        if ($validationError !== null) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => $validationError]);
        }

        $result = $this->safeApiCall(fn () => $this->collectionService->create($payload));
        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }

        $ok = $statusCode >= 200 && $statusCode < 300;
        $data = $ok ? $this->extractData($result) : ($result['data'] ?? []);
        $fieldErrors = $this->getFieldErrors($result);
        $message = null;

        if ($ok) {
            $createdId = (string) ($data['id'] ?? '');
            if ($createdId === '') {
                $ok = false;
                $statusCode = 502;
                $message = lang('Wizard.wizard_structure_error_collection_missing_id');
            }
        }

        if (! $ok) {
            $message = $message ?? $this->firstMessage($result, lang('Wizard.wizard_structure_error_collection'));

            if ($fieldErrors !== []) {
                $message = reset($fieldErrors) ?: $message;
            }
        }

        $response = [
            'ok' => $ok,
            'data' => $data,
        ];

        if (! $ok && $message !== null && $message !== '') {
            $response['message'] = $message;
        }

        if (! $ok && $fieldErrors !== []) {
            $response['fieldErrors'] = $fieldErrors;
        }

        if (! $ok && isset($result['detail']) && is_scalar($result['detail'])) {
            $response['detail'] = (string) $result['detail'];
        }

        if (! $ok && isset($result['errors']) && is_array($result['errors'])) {
            $response['errors'] = $result['errors'];
        }

        return $this->response->setStatusCode($statusCode)->setJSON($response);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateCollectionWizardPayload(array $payload): ?string
    {
        $collectionKey = trim((string) ($payload['collection_key'] ?? ''));
        $collectionType = trim((string) ($payload['collection_type'] ?? ''));

        if ($collectionKey === '' || strlen($collectionKey) < 2) {
            return 'collection_key is required.';
        }

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $collectionKey)) {
            return 'collection_key must use lowercase letters, numbers and hyphens only.';
        }

        if ($collectionType === '' || ! in_array($collectionType, CmsPresetCatalog::collectionTypes(), true)) {
            return 'collection_type is required.';
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadActiveLanguages(): array
    {
        $languageService = service('languageApiService');
        $languagesResponse = $this->safeApiCall(fn () => $languageService->list(['limit' => 100, 'is_active' => true]));

        return $this->extractItems($languagesResponse);
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    private function collectionTypeOptions(): array
    {
        return array_map(
            function (string $type): array {
                return [
                    'key' => $type,
                    'label' => lang('Collections.collection_type_' . $type),
                ];
            },
            CmsPresetCatalog::collectionTypes()
        );
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    private function pageTypeOptions(): array
    {
        return array_map(
            function (string $type): array {
                return [
                    'key' => $type,
                    'label' => lang('Pages.page_type_' . $type),
                ];
            },
            CmsPresetCatalog::pageTypes()
        );
    }
}
