<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\BlockTypeStoreRequest;
use App\Modules\Cms\Requests\BlockTypeUpdateRequest;
use App\Modules\Cms\Services\BlockTypeApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BlockTypeController extends BaseWebController
{
    protected BlockTypeApiServiceInterface $blockTypeService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->blockTypeService = service('blockTypeApiService');
    }

    public function index(): string
    {
        return $this->render('cms/block_types/index', [
            'title'        => lang('BlockTypes.block_types_title'),
            'limitOptions' => [10, 25, 50, 100],

        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['name', 'created_at'],
            fn (array $params) => $this->blockTypeService->list($params),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->blockTypeService->get($id));

        if (! $response['ok']) {
            return $this->render('cms/block_types/show', [
                'title' => lang('BlockTypes.block_types_details'),
                'blockType' => [],
                'error' => $this->firstMessage($response, lang('BlockTypes.block_types_not_found')),

            ]);
        }

        return $this->render('cms/block_types/show', [
            'title' => lang('BlockTypes.block_types_details'),
            'blockType' => $this->extractData($response),

        ]);
    }

    public function create(): string
    {
        $templates = $this->blockTypeService->templates();

        return $this->render('cms/block_types/create', [
            'title'     => lang('BlockTypes.block_types_create'),
            'templates' => $templates,
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var BlockTypeStoreRequest $request */
        $request = service('formRequest', BlockTypeStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->blockTypeService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('BlockTypes.block_types_create_failed'));
        }

        return redirect()->to(route_to('admin.cms.block_types'))->with('success', lang('BlockTypes.block_types_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->blockTypeService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('BlockTypes.block_types_not_found'), route_to('admin.cms.block_types'));
        }

        $templates = $this->blockTypeService->templates();

        return $this->render('cms/block_types/edit', [
            'title'     => lang('BlockTypes.block_types_edit'),
            'item'      => $this->extractData($response),
            'templates' => $templates,
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var BlockTypeUpdateRequest $request */
        $request = service('formRequest', BlockTypeUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->blockTypeService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('BlockTypes.block_types_update_failed'));
        }

        return redirect()->to(route_to('admin.cms.block_types'))->with('success', lang('BlockTypes.block_types_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->blockTypeService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('BlockTypes.block_types_delete_failed'), route_to('admin.cms.block_types'), false);
        }

        return redirect()->to(route_to('admin.cms.block_types'))->with('success', lang('BlockTypes.block_types_delete_success'));
    }




}
