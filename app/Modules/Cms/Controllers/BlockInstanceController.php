<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Services\BlockInstanceApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BlockInstanceController extends BaseWebController
{
    protected BlockInstanceApiServiceInterface $blockInstanceService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->blockInstanceService = service('blockInstanceApiService');
    }

    private function requireWrite(): ?RedirectResponse
    {
        if (! has_permission('cms.pages.write')) {
            return redirect()->to(route_to('admin.cms.pages'))->with('error', lang('App.access_denied'));
        }
        return null;
    }

    public function index(string $ownerId): string|RedirectResponse
    {
        $pageResponse = $this->safeApiCall(fn () => service('pageApiService')->get($ownerId));
        if (!$pageResponse['ok']) {
            return redirect()->to(route_to('admin.cms.pages'))->with('error', lang('Pages.pages_not_found'));
        }
        $page = $this->extractData($pageResponse);

        $blocksResponse = $this->safeApiCall(fn () => $this->blockInstanceService->list($ownerId, 'page'));
        $blocks = $blocksResponse['ok'] ? $this->extractItems($blocksResponse) : [];

        // Fetch block types to display their names/descriptions (cached for 1 hour)
        $types = cache()->get('cms_block_types_list');
        if ($types === null) {
            $typesResponse = $this->safeApiCall(fn () => service('blockTypeApiService')->list(['limit' => 100]));
            $types = $typesResponse['ok'] ? $this->extractItems($typesResponse) : [];
            if (! empty($types)) {
                cache()->save('cms_block_types_list', $types, 3600);
            }
        }
        
        $typesIndexed = [];
        foreach ($types as $t) {
            $typesIndexed[$t['id']] = $t;
        }

        return $this->render('cms/pages/blocks/index', [
            'title'      => 'Bloques de ' . ($page['title'] ?? 'Página'),
            'page'       => $page,
            'blocks'     => $blocks,
            'blockTypes' => $typesIndexed
        ]);
    }

    public function create(string $ownerId): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $pageResponse = $this->safeApiCall(fn () => service('pageApiService')->get($ownerId));
        if (!$pageResponse['ok']) {
            return redirect()->to(route_to('admin.cms.pages'))->with('error', lang('Pages.pages_not_found'));
        }
        $page = $this->extractData($pageResponse);

        // Fetch block types (cached)
        $types = cache()->get('cms_block_types_list');
        if ($types === null) {
            $typesResponse = $this->safeApiCall(fn () => service('blockTypeApiService')->list(['limit' => 100]));
            $types = $typesResponse['ok'] ? $this->extractItems($typesResponse) : [];
            if (! empty($types)) {
                cache()->save('cms_block_types_list', $types, 3600);
            }
        }

        // Fetch languages (cached)
        $languages = cache()->get('cms_active_languages');
        if ($languages === null) {
            $languagesResponse = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));
            $languages = $languagesResponse['ok'] ? $this->extractItems($languagesResponse) : [];
            if (! empty($languages)) {
                cache()->save('cms_active_languages', $languages, 3600);
            }
        }

        return $this->render('cms/pages/blocks/create', [
            'title'     => 'Añadir Bloque',
            'page'      => $page,
            'blockTypes'=> $types,
            'languages' => $languages
        ]);
    }

    public function store(string $ownerId): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $blockId = $this->request->getPost('block_id');
        $sortOrder = (int) $this->request->getPost('sort_order');
        $isActive = (bool) $this->request->getPost('is_active');
        
        // Parse config and translations
        $blockConfigRaw = $this->request->getPost('block_config') ?? '';
        $blockConfig = [];
        if (is_string($blockConfigRaw) && trim($blockConfigRaw) !== '') {
            $decoded = json_decode($blockConfigRaw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $blockConfig = $decoded;
            }
        }

        // Process translations: normalize to drop blank translation rows
        $translationsRaw = $this->request->getPost('translations') ?? [];
        $translations = [];
        foreach ($translationsRaw as $t) {
            $langId = (int) ($t['language_id'] ?? 0);
            if ($langId <= 0) {
                continue;
            }
            
            $blockData = $t['block_data'] ?? [];
            // Check if there is actual content in the block data
            $hasData = false;
            foreach ($blockData as $val) {
                if (is_string($val) && trim($val) !== '') {
                    $hasData = true;
                    break;
                }
                if (is_array($val) && !empty($val)) {
                    $hasData = true;
                    break;
                }
            }

            if ($hasData) {
                $translations[] = [
                    'language_id'  => $langId,
                    'block_data'   => $blockData,
                    'is_published' => (bool) ($t['is_published'] ?? true)
                ];
            }
        }

        $payload = [
            'block_id'     => (int) $blockId,
            'owner_type'   => 'page',
            'owner_id'     => (int) $ownerId,
            'sort_order'   => $sortOrder,
            'is_active'    => $isActive,
            'block_config' => $blockConfig,
            'translations' => $translations
        ];

        $response = $this->safeApiCall(fn () => $this->blockInstanceService->create($ownerId, 'page', $payload));

        if (!$response['ok']) {
            return redirect()->back()->withInput()->with('error', $this->firstMessage($response, 'Error al crear el bloque'));
        }

        return redirect()->to(route_to('admin.cms.pages.blocks', $ownerId))->with('success', 'Bloque añadido con éxito.');
    }

    public function edit(string $ownerId, string $id): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $pageResponse = $this->safeApiCall(fn () => service('pageApiService')->get($ownerId));
        if (!$pageResponse['ok']) {
            return redirect()->to(route_to('admin.cms.pages'))->with('error', lang('Pages.pages_not_found'));
        }
        $page = $this->extractData($pageResponse);

        $blockResponse = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, 'page', $id));
        if (!$blockResponse['ok']) {
            return redirect()->to(route_to('admin.cms.pages.blocks', $ownerId))->with('error', 'Bloque no encontrado.');
        }
        $block = $this->extractData($blockResponse);

        // Fetch schema from block type (cached)
        $typeCacheKey = 'cms_block_type_' . $block['block_id'];
        $blockType = cache()->get($typeCacheKey);
        if ($blockType === null) {
            $typeResponse = $this->safeApiCall(fn () => service('blockTypeApiService')->get($block['block_id']));
            $blockType = $typeResponse['ok'] ? $this->extractData($typeResponse) : [];
            if (! empty($blockType)) {
                cache()->save($typeCacheKey, $blockType, 3600);
            }
        }

        // Fetch active languages (cached)
        $languages = cache()->get('cms_active_languages');
        if ($languages === null) {
            $languagesResponse = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));
            $languages = $languagesResponse['ok'] ? $this->extractItems($languagesResponse) : [];
            if (! empty($languages)) {
                cache()->save('cms_active_languages', $languages, 3600);
            }
        }

        return $this->render('cms/pages/blocks/edit', [
            'title'     => 'Editar Bloque',
            'page'      => $page,
            'block'     => $block,
            'blockType' => $blockType,
            'languages' => $languages
        ]);
    }

    public function update(string $ownerId, string $id): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $blockId = $this->request->getPost('block_id');
        $sortOrder = (int) $this->request->getPost('sort_order');
        $isActive = (bool) $this->request->getPost('is_active');
        
        $blockConfigRaw = $this->request->getPost('block_config') ?? '';
        $blockConfig = [];
        if (is_string($blockConfigRaw) && trim($blockConfigRaw) !== '') {
            $decoded = json_decode($blockConfigRaw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $blockConfig = $decoded;
            }
        }

        $translationsRaw = $this->request->getPost('translations') ?? [];
        $translations = [];
        foreach ($translationsRaw as $t) {
            $langId = (int) ($t['language_id'] ?? 0);
            if ($langId <= 0) {
                continue;
            }
            
            $blockData = $t['block_data'] ?? [];
            $hasData = false;
            foreach ($blockData as $val) {
                if (is_string($val) && trim($val) !== '') {
                    $hasData = true;
                    break;
                }
                if (is_array($val) && !empty($val)) {
                    $hasData = true;
                    break;
                }
            }

            if ($hasData) {
                $translations[] = [
                    'language_id'  => $langId,
                    'block_data'   => $blockData,
                    'is_published' => (bool) ($t['is_published'] ?? true)
                ];
            }
        }

        $payload = [
            'block_id'     => (int) $blockId,
            'owner_type'   => 'page',
            'owner_id'     => (int) $ownerId,
            'sort_order'   => $sortOrder,
            'is_active'    => $isActive,
            'block_config' => $blockConfig,
            'translations' => $translations
        ];

        $response = $this->safeApiCall(fn () => $this->blockInstanceService->update($ownerId, 'page', $id, $payload));

        if (!$response['ok']) {
            return redirect()->back()->withInput()->with('error', $this->firstMessage($response, 'Error al actualizar el bloque'));
        }

        return redirect()->to(route_to('admin.cms.pages.blocks', $ownerId))->with('success', 'Bloque actualizado con éxito.');
    }

    public function delete(string $ownerId, string $id): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $response = $this->safeApiCall(fn () => $this->blockInstanceService->delete($ownerId, 'page', $id));

        if (!$response['ok']) {
            return redirect()->to(route_to('admin.cms.pages.blocks', $ownerId))->with('error', 'Error al borrar el bloque.');
        }

        return redirect()->to(route_to('admin.cms.pages.blocks', $ownerId))->with('success', 'Bloque eliminado con éxito.');
    }

    public function reorder(string $ownerId): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $orders = $this->request->getPost('orders') ?? [];
        
        foreach ($orders as $id => $order) {
            $blockResponse = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, 'page', $id));
            if ($blockResponse['ok']) {
                $block = $this->extractData($blockResponse);
                $block['sort_order'] = (int) $order;
                
                // Keep the exact same payload structure, just update the sort_order
                $this->safeApiCall(fn () => $this->blockInstanceService->update($ownerId, 'page', $id, [
                    'block_id'     => (int) $block['block_id'],
                    'owner_type'   => 'page',
                    'owner_id'     => (int) $ownerId,
                    'sort_order'   => (int) $order,
                    'is_active'    => (bool) ($block['is_active'] ?? true),
                    'block_config' => $block['block_config'] ?? [],
                    'translations' => $block['translations'] ?? []
                ]));
            }
        }

        return redirect()->to(route_to('admin.cms.pages.blocks', $ownerId))->with('success', 'Orden de bloques actualizado.');
    }
}
