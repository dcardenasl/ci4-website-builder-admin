<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\TagStoreRequest;
use App\Modules\Cms\Requests\TagUpdateRequest;
use App\Modules\Cms\Services\TagApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class TagController extends BaseWebController
{
    protected TagApiServiceInterface $tagService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->tagService = service('tagApiService');
    }

    public function index(): string
    {
        return $this->render('cms/tags/index', [
            'title'        => lang('Cms.tags_title'),
            'limitOptions' => [10, 25, 50, 100],

        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['name', 'created_at'],
            fn (array $params) => $this->tagService->list($params),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->tagService->get($id));

        if (! $response['ok']) {
            return $this->render('cms/tags/show', [
                'title' => lang('Cms.tags_details'),
                'tag' => [],
                'error' => $this->firstMessage($response, lang('Cms.tags_not_found')),

            ]);
        }

        return $this->render('cms/tags/show', [
            'title' => lang('Cms.tags_details'),
            'tag' => $this->extractData($response),

        ]);
    }

    public function create(): string
    {
        return $this->render('cms/tags/create', [
            'title'     => lang('Cms.tags_create'),
            'languages' => $this->getLanguages(),
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var TagStoreRequest $request */
        $request = service('formRequest', TagStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->tagService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Cms.tags_create_failed'));
        }

        return redirect()->to(route_to('admin.cms.tags'))->with('success', lang('Cms.tags_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->tagService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Cms.tags_not_found'), route_to('admin.cms.tags'));
        }

        return $this->render('cms/tags/edit', [
            'title'     => lang('Cms.tags_edit'),
            'item'      => $this->extractData($response),
            'languages' => $this->getLanguages(),
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var TagUpdateRequest $request */
        $request = service('formRequest', TagUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->tagService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Cms.tags_update_failed'));
        }

        return redirect()->to(route_to('admin.cms.tags'))->with('success', lang('Cms.tags_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->tagService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Cms.tags_delete_failed'), route_to('admin.cms.tags'), false);
        }

        return redirect()->to(route_to('admin.cms.tags'))->with('success', lang('Cms.tags_delete_success'));
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
