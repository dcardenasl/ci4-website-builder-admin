<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\CollectionStoreRequest;
use App\Modules\Cms\Requests\CollectionUpdateRequest;
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
        return $this->render('cms/collections/create', [
            'title'     => lang('Collections.collections_create'),
            'languages' => $this->getLanguages(),
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

        return $this->render('cms/collections/edit', [
            'title'     => lang('Collections.collections_edit'),
            'item'      => $this->extractData($response),
            'languages' => $this->getLanguages(),
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
            return $this->failApi($response, lang('Collections.collections_delete_failed'), route_to('admin.cms.collections'), false);
        }

        return redirect()->to(route_to('admin.cms.collections'))->with('success', lang('Collections.collections_delete_success'));
    }




}
