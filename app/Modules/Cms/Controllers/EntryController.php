<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\EntryStoreRequest;
use App\Modules\Cms\Requests\EntryUpdateRequest;
use App\Modules\Cms\Services\CollectionApiServiceInterface;
use App\Modules\Cms\Services\EntryApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class EntryController extends BaseWebController
{
    protected EntryApiServiceInterface $entryService;
    protected CollectionApiServiceInterface $collectionService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->entryService      = service('entryApiService');
        $this->collectionService = service('collectionApiService');
    }

    public function index(): string
    {
        return $this->render('cms/entries/index', [
            'title'        => lang('Entries.entries_title'),
            'limitOptions' => [10, 25, 50, 100],
            'collections' => $this->collectionsOptions(),
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            ['collection_id'],
            ['name', 'created_at'],
            fn (array $params) => $this->entryService->list($params),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->entryService->get($id));

        if (! $response['ok']) {
            return $this->render('cms/entries/show', [
                'title' => lang('Entries.entries_details'),
                'entry' => [],
                'error' => $this->firstMessage($response, lang('Entries.entries_not_found')),
            'collections' => $this->collectionsOptions(),
            ]);
        }

        return $this->render('cms/entries/show', [
            'title' => lang('Entries.entries_details'),
            'entry' => $this->extractData($response),
            'collections' => $this->collectionsOptions(),
        ]);
    }

    public function create(): string
    {
        $collectionId = $this->request->getGet('collection_id');
        $item = [];
        if ($collectionId !== null && is_scalar($collectionId) && (int) $collectionId > 0) {
            $item['collection_id'] = (int) $collectionId;
        }

        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['title', 'excerpt', 'meta_title', 'meta_description'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId)
            : [];

        return $this->render('cms/entries/create', [
            'title'            => lang('Entries.entries_create'),
            'collections'      => $this->collectionsOptions(),
            'languages'        => $languages,
            'defaultLangId'    => $languageContext['defaultLangId'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'defaultLangIndex'  => $languageContext['defaultLangIndex'],
            'item'             => $item,
            'translateTargets' => $translateTargets,
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var EntryStoreRequest $request */
        $request = service('formRequest', EntryStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->entryService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Entries.entries_create_failed'));
        }

        return redirect()->to(route_to('admin.cms.entries'))->with('success', lang('Entries.entries_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->entryService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Entries.entries_not_found'), route_to('admin.cms.entries'));
        }

        $item           = $this->extractData($response);
        $blockTemplate  = $this->resolveBlockTemplate($item);
        $languages      = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId  = $languageContext['defaultLangId'];
        $fieldMap       = ['title', 'excerpt', 'meta_title', 'meta_description'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId)
            : [];

        return $this->render('cms/entries/edit', [
            'title'            => lang('Entries.entries_edit'),
            'item'             => $item,
            'collections'      => $this->collectionsOptions(),
            'languages'        => $languages,
            'defaultLangId'    => $languageContext['defaultLangId'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'defaultLangIndex'  => $languageContext['defaultLangIndex'],
            'blockTemplate'    => $blockTemplate,
            'translateTargets' => $translateTargets,
        ]);
    }

    /**
     * Fetches block_template from the entry's parent collection (null if none).
     *
     * @param array<string, mixed> $item
     * @return array<string, mixed>|null
     */
    private function resolveBlockTemplate(array $item): ?array
    {
        $collectionId = $item['collection_id'] ?? null;
        if (empty($collectionId)) {
            return null;
        }

        $response = $this->safeApiCall(fn () => $this->collectionService->get((string) $collectionId));
        if (! $response['ok']) {
            return null;
        }

        $collection = $this->extractData($response);
        $template   = $collection['block_template'] ?? null;

        if (!is_array($template) || empty($template['blocks'])) {
            return null;
        }

        return $template;
    }

    public function update(string $id): RedirectResponse
    {
        /** @var EntryUpdateRequest $request */
        $request = service('formRequest', EntryUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->entryService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Entries.entries_update_failed'));
        }

        return redirect()->to(route_to('admin.cms.entries'))->with('success', lang('Entries.entries_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->entryService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Entries.entries_delete_failed'), route_to('admin.cms.entries'), false);
        }

        return redirect()->to(route_to('admin.cms.entries'))->with('success', lang('Entries.entries_delete_success'));
    }



    public function checkSlug(): ResponseInterface
    {
        $slugRaw       = $this->request->getGet('slug');
        $languageIdRaw = $this->request->getGet('language_id');
        $currentIdRaw  = $this->request->getGet('current_id');
        $slug          = is_scalar($slugRaw) ? (string) $slugRaw : '';
        $languageId    = is_scalar($languageIdRaw) ? (int)    $languageIdRaw : 0;
        $currentId     = is_scalar($currentIdRaw) ? (string) $currentIdRaw : '';

        if ($slug === '' || $languageId === 0) {
            return $this->response->setJSON(['available' => false]);
        }

        $result = $this->safeApiCall(fn () => $this->entryService->checkSlug($slug, $languageId, $currentId));
        $data   = $this->extractData($result);
        return $this->response->setJSON(['available' => (bool) ($data['available'] ?? false)]);
    }

    public function reorder(): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $collections = $this->collectionsOptions();
        $collectionIdParam = $this->request->getGet('collection_id');
        $collectionId = is_numeric($collectionIdParam) ? (int) $collectionIdParam : null;

        if ($collectionId === null && ! empty($collections)) {
            $collectionId = array_key_first($collections);
        }

        $items = [];
        if ($collectionId !== null) {
            $response = $this->safeApiCall(fn () => $this->entryService->list([
                'limit' => 250,
                'sort' => 'sort_order',
                'filter' => [
                    'collection_id' => (int) $collectionId,
                ],
            ]));
            $items = $this->extractItems($response);
        }

        return $this->render('cms/entries/reorder', [
            'title' => lang('Entries.entries_title') . ' - ' . lang('Entries.field_sort_order'),
            'items' => $items,
            'collections' => $collections,
            'selectedCollectionId' => $collectionId,
        ]);
    }

    public function saveOrder(): ResponseInterface
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => lang('App.access_denied'),
            ])->setStatusCode(403);
        }

        $request = $this->request;
        if (! $request instanceof \CodeIgniter\HTTP\IncomingRequest) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Invalid request type',
            ])->setStatusCode(400);
        }

        $json = $request->getJSON(true);
        $jsonArray = is_array($json) ? $json : [];
        $items = $jsonArray['items'] ?? [];

        if (! is_array($items)) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Invalid payload structure',
            ])->setStatusCode(400);
        }

        foreach ($items as $item) {
            $id = (string) ($item['id'] ?? '');
            $value = isset($item['sort_order']) ? (int) $item['sort_order'] : 0;

            if ($id !== '') {
                $this->entryService->update($id, ['sort_order' => $value]);
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'message' => lang('Files.gallery_save_success') ?? 'Order saved.',
        ]);
    }


    public function publish(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->entryService->publish($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Entries.entries_publish_failed'), route_to('admin.cms.entries.show', $id), false);
        }

        return redirect()->to(route_to('admin.cms.entries.show', $id))->with('success', lang('Entries.entries_publish_success'));
    }

    public function archive(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->entryService->archive($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Entries.entries_archive_failed'), route_to('admin.cms.entries.show', $id), false);
        }

        return redirect()->to(route_to('admin.cms.entries.show', $id))->with('success', lang('Entries.entries_archive_success'));
    }


    /** @return array<string, string> */
    private function collectionsOptions(): array
    {
        $response = $this->safeApiCall(fn () => $this->entryService->collections(['limit' => 100, 'is_active' => true]));
        $options = [];

        foreach ($this->extractItems($response) as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            $label = $item['collection_key'] ?? $item['name'] ?? $item['title'] ?? $item['label'] ?? $item['id'];
            $options[(string) $item['id']] = (string) $label;
        }

        return $options;
    }

    private function requireWrite(): ?RedirectResponse
    {
        if (! has_permission('cms.entries.write')) {
            return redirect()->to(route_to('admin.cms.entries'))->with('error', lang('App.access_denied'));
        }
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function getLanguages(): array
    {
        $response = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));
        return $this->extractItems($response);
    }
}
