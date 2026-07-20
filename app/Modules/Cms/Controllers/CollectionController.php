<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\CollectionStoreRequest;
use App\Modules\Cms\Requests\CollectionUpdateRequest;
use App\Modules\Cms\Services\CollectionApiService;
use App\Modules\Cms\Support\CmsPresetCatalog;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CollectionController extends BaseWebController
{
    protected CollectionApiService $collectionService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->collectionService = service('collectionApiService');
    }

    public function index(): string
    {
        return $this->render('cms/collections/index', [
            'title'        => lang('Collections.collections_title'),
            'limitOptions' => [10, 25, 50, 100],
            'languages'    => $this->getLanguages(),
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['name', 'created_at'],
            fn (array $params) => $this->collectionService->list([...$params, 'include_translations' => 1]),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->collectionService->get($id));

        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
            return $this->render('cms/collections/show', [
                'title' => lang('Collections.collections_details'),
                'collection' => [],
                'error' => $this->firstMessage($response, lang('Collections.collections_not_found')),

            ]);
        }

        return $this->render('cms/collections/show', [
            'title' => lang('Collections.collections_details'),
            'collection' => $this->extractData($response),
            'languages' => $this->getLanguages(),

        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getLanguages(): array
    {
        $response = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));
        $this->maybeFlashDevError($response);

        return $this->extractItems($response);
    }

    public function create(): string
    {
        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['name', 'slug', 'description'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId)
            : [];

        return $this->render('cms/collections/create', [
            'title' => lang('Collections.collections_create'),
            'languages' => $languages,
            'defaultLangId' => $languageContext['defaultLangId'],
            'defaultLangCode' => $languageContext['defaultLangCode'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'translateTargets' => $translateTargets,
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var CollectionStoreRequest $request */
        $request = service('formRequest', CollectionStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->collectionService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Collections.collections_create_failed'));
        }

        return redirect()->to(route_to('admin.cms.collections'))->with('success', lang('Collections.collections_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->collectionService->get($id));
        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
            return $this->withError(lang('Collections.collections_not_found'), route_to('admin.cms.collections'));
        }

        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['name', 'slug', 'description'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId)
            : [];

        $focusLangRaw = $this->request->getGet('focus_lang');
        $focusLangId  = ($focusLangRaw !== null && is_scalar($focusLangRaw) && (int) $focusLangRaw > 0)
            ? (int) $focusLangRaw
            : 0;

        return $this->render('cms/collections/edit', [
            'title' => lang('Collections.collections_edit'),
            'item' => $this->extractData($response),
            'languages' => $languages,
            'focusLangId' => $focusLangId,
            'defaultLangId' => $languageContext['defaultLangId'],
            'defaultLangCode' => $languageContext['defaultLangCode'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'translateTargets' => $translateTargets,
            'returnTo' => $this->incomingReturnTo(),
        ]);
    }

    public function structure(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->collectionService->get($id));
        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
            return $this->withError(lang('Collections.collections_not_found'), route_to('admin.cms.collections'));
        }

        $structureContext = $this->loadStructureContext();

        return $this->render('cms/collections/structure', [
            'title' => lang('Collections.collections_structure'),
            'item' => $this->extractData($response),
            'blockTypes' => $structureContext['blockTypes'],
            'collectionPresets' => $structureContext['collectionPresets'],
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var CollectionUpdateRequest $request */
        $request = service('formRequest', CollectionUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $currentResponse = $this->safeApiCall(fn () => $this->collectionService->get($id));
        if (! $currentResponse['ok']) {
            $this->maybeFlashDevError($currentResponse);
            return $this->withError(lang('Collections.collections_not_found'), route_to('admin.cms.collections'));
        }

        $payload = $request->payload();
        $current = $this->extractData($currentResponse);
        $payload['collection_type'] = (string) ($current['collection_type'] ?? $payload['collection_type'] ?? 'other');
        $payload['block_template'] = $current['block_template'] ?? null;
        $payload['wizard_config'] = $current['wizard_config'] ?? null;

        $response = $this->safeApiCall(fn () => $this->collectionService->update($id, $payload));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Collections.collections_update_failed'));
        }

        return redirect()->to($this->resolveReturnUrl(route_to('admin.cms.collections')))->with('success', lang('Collections.collections_update_success'));
    }

    public function updateStructure(string $id): RedirectResponse
    {
        $currentResponse = $this->safeApiCall(fn () => $this->collectionService->get($id));
        if (! $currentResponse['ok']) {
            $this->maybeFlashDevError($currentResponse);
            return $this->withError(lang('Collections.collections_not_found'), route_to('admin.cms.collections'));
        }

        $current = $this->extractData($currentResponse);
        $blockTemplate = $this->request->getPost('block_template');
        $wizardConfigRaw = $this->request->getPost('wizard_config');
        $wizardConfig = null;

        if (is_string($wizardConfigRaw) && trim($wizardConfigRaw) !== '') {
            $decoded = json_decode($wizardConfigRaw, true);
            $wizardConfig = is_array($decoded) ? $decoded : null;
        } elseif (is_array($wizardConfigRaw)) {
            $wizardConfig = $wizardConfigRaw;
        }

        $payload = $current;
        $payload['block_template'] = is_string($blockTemplate) ? $blockTemplate : $current['block_template'] ?? null;
        $payload['wizard_config'] = $wizardConfig ?? ($current['wizard_config'] ?? null);

        if (is_array($wizardConfig) && ! empty($wizardConfig['type']) && is_string($wizardConfig['type'])) {
            $payload['collection_type'] = $wizardConfig['type'];
        } elseif (! isset($payload['collection_type'])) {
            $payload['collection_type'] = $current['collection_type'] ?? 'other';
        }

        $response = $this->safeApiCall(fn () => $this->collectionService->update($id, $payload));

        if (! $response['ok']) {
            return $this->failApi(
                $response,
                lang('Collections.collections_structure_update_failed'),
                route_to('admin.cms.collections.structure', $id),
            );
        }

        return redirect()
            ->to(route_to('admin.cms.collections.structure', $id))
            ->with('success', lang('Collections.collections_structure_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->collectionService->delete($id));

        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
            $message = $this->firstMessage($response, lang('Collections.collections_delete_failed'));
            return $this->withError($message, route_to('admin.cms.collections'));
        }

        return redirect()->to(route_to('admin.cms.collections'))->with('success', lang('Collections.collections_delete_success'));
    }

    public function checkSlug(): ResponseInterface
    {
        $slugRaw = $this->request->getGet('slug');
        $languageIdRaw = $this->request->getGet('language_id');
        $currentIdRaw = $this->request->getGet('current_id');

        $slug = is_scalar($slugRaw) ? (string) $slugRaw : '';
        $languageId = is_scalar($languageIdRaw) ? (int) $languageIdRaw : 0;
        $currentId = is_scalar($currentIdRaw) ? (string) $currentIdRaw : '';

        if ($slug === '' || $languageId === 0) {
            return $this->response->setJSON(['available' => false]);
        }

        $result = $this->safeApiCall(fn () => $this->collectionService->checkSlug($slug, $languageId, $currentId));
        $data = $this->extractData($result);

        return $this->response->setJSON(['available' => (bool) ($data['available'] ?? false)]);
    }

    /**
     * @return array{blockTypes: array<int, array<string, mixed>>, collectionPresets: array<int, array<string, mixed>>}
     */
    private function loadStructureContext(): array
    {
        $blockCatalog = service('blockCatalogService');
        $blockTypes = $blockCatalog->selectableTopLevel();
        $selectableBlockKeys = array_values(array_unique(array_filter(array_map(
            static fn ($bt) => (string) ($bt['block_key'] ?? ''),
            $blockTypes
        ))));

        return [
            'blockTypes' => $blockTypes,
            'collectionPresets' => CmsPresetCatalog::filterAvailablePresets(CmsPresetCatalog::collectionPresets(), $selectableBlockKeys),
        ];
    }
}
