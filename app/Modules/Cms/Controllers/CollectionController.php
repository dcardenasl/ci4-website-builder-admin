<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\CollectionStoreRequest;
use App\Modules\Cms\Requests\CollectionUpdateRequest;
use App\Modules\Cms\Support\CmsPresetCatalog;
use App\Modules\Cms\Services\CollectionApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CollectionController extends BaseWebController
{
    protected CollectionApiServiceInterface $collectionService;

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

        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['name', 'created_at'],
            fn (array $params) => $this->collectionService->list($params),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->collectionService->get($id));

        if (! $response['ok']) {
            return $this->render('cms/collections/show', [
                'title' => lang('Collections.collections_details'),
                'collection' => [],
                'error' => $this->firstMessage($response, lang('Collections.collections_not_found')),

            ]);
        }

        return $this->render('cms/collections/show', [
            'title' => lang('Collections.collections_details'),
            'collection' => $this->extractData($response),

        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getLanguages(): array
    {
        $response = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));
        return $this->extractItems($response);
    }

    public function create(): string
    {
        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);

        $blockTypes = service('blockCatalogService')->all();
        $activeBlockKeys = array_values(array_unique(array_filter(array_map(
            static fn ($bt) => (string) ($bt['block_key'] ?? ''),
            $blockTypes
        ))));
        $collectionPresets = CmsPresetCatalog::filterAvailablePresets(CmsPresetCatalog::collectionPresets(), $activeBlockKeys);

        return $this->render('cms/collections/create', [
            'title' => lang('Collections.collections_create'),
            'languages' => $languages,
            'defaultLangId' => $languageContext['defaultLangId'],
            'collectionTypes' => $this->collectionTypeOptions(),
            'blockTypes' => $blockTypes,
            'collectionPresets' => $collectionPresets,
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
            return $this->withError(lang('Collections.collections_not_found'), route_to('admin.cms.collections'));
        }

        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);

        $blockTypes = service('blockCatalogService')->all();
        $activeBlockKeys = array_values(array_unique(array_filter(array_map(
            static fn ($bt) => (string) ($bt['block_key'] ?? ''),
            $blockTypes
        ))));
        $collectionPresets = CmsPresetCatalog::filterAvailablePresets(CmsPresetCatalog::collectionPresets(), $activeBlockKeys);

        return $this->render('cms/collections/edit', [
            'title' => lang('Collections.collections_edit'),
            'item' => $this->extractData($response),
            'languages' => $languages,
            'defaultLangId' => $languageContext['defaultLangId'],
            'collectionTypes' => $this->collectionTypeOptions(),
            'blockTypes' => $blockTypes,
            'collectionPresets' => $collectionPresets,
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

        $response = $this->safeApiCall(fn () => $this->collectionService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Collections.collections_update_failed'));
        }

        return redirect()->to(route_to('admin.cms.collections'))->with('success', lang('Collections.collections_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->collectionService->delete($id));

        if (! $response['ok']) {
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
}
