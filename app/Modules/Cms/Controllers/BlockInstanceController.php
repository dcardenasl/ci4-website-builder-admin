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

    private const OWNER_PAGE = 'page';
    private const OWNER_ENTRY = 'entry';

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->blockInstanceService = service('blockInstanceApiService');
    }

    private function requireWrite(): ?RedirectResponse
    {
        $ownerType = $this->ownerTypeFromRequest();
        $permission = $ownerType === self::OWNER_ENTRY ? 'cms.entries.write' : 'cms.pages.write';
        if (! has_permission($permission)) {
            return redirect()->to($this->ownerListRoute($ownerType))->with('error', lang('App.access_denied'));
        }
        return null;
    }

    private function ownerTypeFromRequest(): string
    {
        $segments = service('request')->getUri()->getSegments();

        return in_array('entries', $segments, true) ? self::OWNER_ENTRY : self::OWNER_PAGE;
    }

    private function ownerLabel(string $ownerType): string
    {
        return $ownerType === self::OWNER_ENTRY ? 'Entrada' : 'Página';
    }

    private function childLabel(string $ownerType): string
    {
        return $ownerType === self::OWNER_ENTRY ? 'Sub-bloque' : 'Diapositiva';
    }

    private function ownerListRoute(string $ownerType): string
    {
        return $ownerType === self::OWNER_ENTRY ? route_to('admin.cms.entries') : route_to('admin.cms.pages');
    }

    private function ownerShowRoute(string $ownerType): string
    {
        return $ownerType === self::OWNER_ENTRY ? 'admin.cms.entries.show' : 'admin.cms.pages.show';
    }

    /**
     * @return array{index:string,create:string,store:string,edit:string,update:string,delete:string,reorder:string,children:string,childrenReorder:string}
     */
    private function ownerRoutes(string $ownerType): array
    {
        $prefix = $ownerType === self::OWNER_ENTRY ? 'admin.cms.entries.blocks' : 'admin.cms.pages.blocks';

        return [
            'index'           => $prefix,
            'create'          => $prefix . '.create',
            'store'           => $prefix . '.store',
            'edit'            => $prefix . '.edit',
            'update'          => $prefix . '.update',
            'delete'          => $prefix . '.delete',
            'reorder'         => $prefix . '.reorder',
            'children'        => $prefix . '.children',
            'childrenReorder' => $prefix . '.children.reorder',
        ];
    }

    private function ownerPreviewUrl(string $ownerType, array $owner): string
    {
        if ($ownerType !== self::OWNER_PAGE) {
            return '';
        }

        foreach (($owner['translations'] ?? []) as $translation) {
            if (! is_array($translation)) {
                continue;
            }

            $slug = (string) ($translation['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $publicSiteUrl = rtrim((string) env('PUBLIC_SITE_URL'), '/');
            if ($publicSiteUrl === '') {
                return '';
            }

            return $publicSiteUrl . '/' . ltrim($slug, '/');
        }

        return '';
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchOwner(string $ownerType, string $ownerId): array
    {
        $response = $ownerType === self::OWNER_ENTRY
            ? $this->safeApiCall(fn () => service('entryApiService')->get($ownerId))
            : $this->safeApiCall(fn () => service('pageApiService')->get($ownerId));

        return $response['ok'] ? $this->extractData($response) : [];
    }

    private function ownerNotFoundMessage(string $ownerType): string
    {
        return $ownerType === self::OWNER_ENTRY ? 'Entrada no encontrada' : lang('Pages.pages_not_found');
    }

    public function index(string $ownerId): string|RedirectResponse
    {
        $ownerType = $this->ownerTypeFromRequest();
        $page = $this->fetchOwner($ownerType, $ownerId);
        if ($page === []) {
            return redirect()->to($this->ownerListRoute($ownerType))->with('error', $this->ownerNotFoundMessage($ownerType));
        }
        $routes = $this->ownerRoutes($ownerType);

        $blocksResponse = $this->safeApiCall(fn () => $this->blockInstanceService->list($ownerId, $ownerType));
        $allBlocks = $blocksResponse['ok'] ? $this->extractItems($blocksResponse) : [];

        // Only show top-level blocks in the page editor (children managed via their parent's UI)
        $blocks = array_values(array_filter($allBlocks, static fn (array $b) => empty($b['parent_instance_id'])));

        $typesIndexed = $this->fetchBlockTypes();
        $routes = $this->ownerRoutes($ownerType);

        return $this->render('cms/pages/blocks/index', [
            'title'             => 'Bloques de ' . ($page['title'] ?? $this->ownerLabel($ownerType)),
            'page'              => $page,
            'blocks'            => $blocks,
            'blockTypes'        => $typesIndexed,
            'publicSiteUrl'     => rtrim((string) env('PUBLIC_SITE_URL'), '/'),
            'ownerType'         => $ownerType,
            'ownerLabel'        => $this->ownerLabel($ownerType),
            'ownerShowRoute'    => $this->ownerShowRoute($ownerType),
            'ownerBlocksRoute'   => $routes['index'],
            'ownerCreateRoute'   => $routes['create'],
            'ownerStoreRoute'    => $routes['store'],
            'ownerEditRoute'     => $routes['edit'],
            'ownerUpdateRoute'   => $routes['update'],
            'ownerDeleteRoute'   => $routes['delete'],
            'ownerChildrenRoute' => $routes['children'],
            'ownerReorderRoute'  => $routes['reorder'],
            'ownerChildrenReorderRoute' => $routes['childrenReorder'],
            'showPreview'        => $this->ownerPreviewUrl($ownerType, $page) !== '',
            'previewUrl'         => $this->ownerPreviewUrl($ownerType, $page),
        ]);
    }

    public function create(string $ownerId): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $ownerType = $this->ownerTypeFromRequest();
        $page = $this->fetchOwner($ownerType, $ownerId);
        if ($page === []) {
            return redirect()->to($this->ownerListRoute($ownerType))->with('error', $this->ownerNotFoundMessage($ownerType));
        }

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

        $parentIdRaw      = $this->request->getGet('parent_instance_id');
        $parentInstanceId = ($parentIdRaw !== null && is_scalar($parentIdRaw) && (int) $parentIdRaw > 0)
            ? (int) $parentIdRaw
            : null;

        $parentBlockType = null;
        if ($parentInstanceId !== null) {
            $parentResponse = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, (string) $parentInstanceId));
            if ($parentResponse['ok']) {
                $parentBlock = $this->extractData($parentResponse);
                $parentBlockId = $parentBlock['block_id'] ?? null;
                if ($parentBlockId) {
                    $typesIndexed = $this->fetchBlockTypes();
                    $parentBlockType = $typesIndexed[$parentBlockId] ?? null;
                }
            }
        }

        $routes = $this->ownerRoutes($ownerType);

        return $this->render('cms/pages/blocks/create', [
            'title'             => $parentInstanceId !== null ? 'Añadir ' . $this->childLabel($ownerType) : 'Añadir Bloque',
            'page'              => $page,
            'blockTypes'        => $types,
            'languages'         => $languages,
            'parentInstanceId'  => $parentInstanceId,
            'parentBlockType'   => $parentBlockType,
            'ownerType'         => $ownerType,
            'ownerLabel'        => $this->ownerLabel($ownerType),
            'ownerBlocksRoute'   => $routes['index'],
            'ownerCreateRoute'   => $routes['create'],
            'ownerStoreRoute'    => $routes['store'],
            'ownerChildrenRoute' => $routes['children'],
            'ownerChildLabel'    => $this->childLabel($ownerType),
        ]);
    }

    public function store(string $ownerId): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $blockIdRaw    = $this->request->getPost('block_id');
        $sortOrderRaw  = $this->request->getPost('sort_order');
        $isActiveRaw   = $this->request->getPost('is_active');
        $blockId       = is_scalar($blockIdRaw) ? (int) $blockIdRaw : 0;
        $sortOrder     = is_scalar($sortOrderRaw) ? (int) $sortOrderRaw : 0;
        $isActive      = ! empty($isActiveRaw);

        // Parse config: accepts array (schema-driven form inputs) or JSON string (legacy)
        $blockConfigRaw = $this->request->getPost('block_config');
        $blockConfig = [];
        if (is_array($blockConfigRaw)) {
            $blockConfig = $blockConfigRaw;
        } elseif (is_string($blockConfigRaw) && trim($blockConfigRaw) !== '') {
            $decoded = json_decode($blockConfigRaw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $blockConfig = $decoded;
            }
        }

        // Process translations: normalize to drop blank translation rows
        $translationsRaw = $this->request->getPost('translations');
        $translations = [];
        foreach (is_array($translationsRaw) ? $translationsRaw : [] as $t) {
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

        $parentIdRaw       = $this->request->getPost('parent_instance_id');
        $parentInstanceId  = ($parentIdRaw !== null && is_scalar($parentIdRaw) && (int) $parentIdRaw > 0)
            ? (int) $parentIdRaw
            : null;

        $ownerType = $this->ownerTypeFromRequest();
        $payload = [
            'block_id'           => $blockId,
            'owner_type'         => $ownerType,
            'owner_id'           => (int) $ownerId,
            'parent_instance_id' => $parentInstanceId,
            'sort_order'         => $sortOrder,
            'is_active'          => $isActive,
            'block_config'       => $blockConfig,
            'translations'       => $translations,
        ];

        $response = $this->safeApiCall(fn () => $this->blockInstanceService->create($ownerId, $ownerType, $payload));

        if (!$response['ok']) {
            return redirect()->back()->withInput()->with('error', $this->firstMessage($response, 'Error al crear el bloque'));
        }

        if ($parentInstanceId !== null) {
            return redirect()->to(route_to($this->ownerRoutes($ownerType)['children'], $ownerId, (string) $parentInstanceId))->with('success', $this->childLabel($ownerType) . ' añadida con éxito.');
        }

        return redirect()->to(route_to($this->ownerRoutes($ownerType)['index'], $ownerId))->with('success', 'Bloque añadido con éxito.');
    }

    public function edit(string $ownerId, string $id): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $ownerType = $this->ownerTypeFromRequest();
        $page = $this->fetchOwner($ownerType, $ownerId);
        if ($page === []) {
            return redirect()->to($this->ownerListRoute($ownerType))->with('error', $this->ownerNotFoundMessage($ownerType));
        }

        $blockResponse = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, $id));
        if (!$blockResponse['ok']) {
            return redirect()->to(route_to($this->ownerRoutes($ownerType)['index'], $ownerId))->with('error', 'Bloque no encontrado.');
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
            'title'        => 'Editar Bloque',
            'page'         => $page,
            'block'        => $block,
            'blockType'    => $blockType,
            'languages'    => $languages,
            'ownerType'    => $ownerType,
            'ownerLabel'   => $this->ownerLabel($ownerType),
            'ownerBlocksRoute' => $this->ownerRoutes($ownerType)['index'],
            'ownerStoreRoute' => $this->ownerRoutes($ownerType)['store'],
            'ownerEditRoute' => $this->ownerRoutes($ownerType)['edit'],
            'ownerUpdateRoute' => $this->ownerRoutes($ownerType)['update'],
            'ownerDeleteRoute' => $this->ownerRoutes($ownerType)['delete'],
            'ownerChildrenRoute' => $this->ownerRoutes($ownerType)['children'],
        ]);
    }

    public function update(string $ownerId, string $id): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $blockIdRaw   = $this->request->getPost('block_id');
        $sortOrderRaw = $this->request->getPost('sort_order');
        $isActiveRaw  = $this->request->getPost('is_active');
        $blockId      = is_scalar($blockIdRaw) ? (int) $blockIdRaw : 0;
        $sortOrder    = is_scalar($sortOrderRaw) ? (int) $sortOrderRaw : 0;
        $isActive     = ! empty($isActiveRaw);

        // Preserve parent_instance_id from the existing block record
        $ownerType        = $this->ownerTypeFromRequest();
        $existingBlock    = $this->extractData($this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, $id)));
        $parentInstanceId = !empty($existingBlock['parent_instance_id']) ? (int) $existingBlock['parent_instance_id'] : null;

        $blockConfigRaw = $this->request->getPost('block_config');
        $blockConfig = [];
        if (is_array($blockConfigRaw)) {
            $blockConfig = $blockConfigRaw;
        } elseif (is_string($blockConfigRaw) && trim($blockConfigRaw) !== '') {
            $decoded = json_decode($blockConfigRaw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $blockConfig = $decoded;
            }
        }

        $translationsRaw = $this->request->getPost('translations');
        $translations = [];
        foreach (is_array($translationsRaw) ? $translationsRaw : [] as $t) {
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
            'block_id'           => $blockId,
            'owner_type'         => $ownerType,
            'owner_id'           => (int) $ownerId,
            'parent_instance_id' => $parentInstanceId,
            'sort_order'         => $sortOrder,
            'is_active'          => $isActive,
            'block_config'       => $blockConfig,
            'translations'       => $translations,
        ];

        $response = $this->safeApiCall(fn () => $this->blockInstanceService->update($ownerId, $ownerType, $id, $payload));

        if (!$response['ok']) {
            return redirect()->back()->withInput()->with('error', $this->firstMessage($response, 'Error al actualizar el bloque'));
        }

        if ($parentInstanceId !== null) {
            return redirect()->to(route_to($this->ownerRoutes($ownerType)['children'], $ownerId, (string) $parentInstanceId))->with('success', $this->childLabel($ownerType) . ' actualizada con éxito.');
        }

        return redirect()->to(route_to($this->ownerRoutes($ownerType)['index'], $ownerId))->with('success', 'Bloque actualizado con éxito.');
    }

    public function delete(string $ownerId, string $id): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        // Fetch before deleting so we know where to redirect
        $ownerType        = $this->ownerTypeFromRequest();
        $blockResponse    = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, $id));
        $block            = ($blockResponse['ok'] ?? false) ? $this->extractData($blockResponse) : [];
        $parentInstanceId = !empty($block['parent_instance_id']) ? (int) $block['parent_instance_id'] : null;

        $response = $this->safeApiCall(fn () => $this->blockInstanceService->delete($ownerId, $ownerType, $id));

        if (!$response['ok']) {
            if ($parentInstanceId !== null) {
                return redirect()->to(route_to($this->ownerRoutes($ownerType)['children'], $ownerId, (string) $parentInstanceId))->with('error', 'Error al borrar ' . $this->childLabel($ownerType) . '.');
            }
            return redirect()->to(route_to($this->ownerRoutes($ownerType)['index'], $ownerId))->with('error', 'Error al borrar el bloque.');
        }

        if ($parentInstanceId !== null) {
            return redirect()->to(route_to($this->ownerRoutes($ownerType)['children'], $ownerId, (string) $parentInstanceId))->with('success', $this->childLabel($ownerType) . ' eliminada con éxito.');
        }

        return redirect()->to(route_to($this->ownerRoutes($ownerType)['index'], $ownerId))->with('success', 'Bloque eliminado con éxito.');
    }

    public function reorder(string $ownerId): RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $ordersRaw = $this->request->getPost('orders');
        $orders    = is_array($ordersRaw) ? $ordersRaw : [];

        $ownerType = $this->ownerTypeFromRequest();

        foreach ($orders as $id => $order) {
            $blockResponse = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, $id));
            if ($blockResponse['ok']) {
                $block = $this->extractData($blockResponse);
                $this->safeApiCall(fn () => $this->blockInstanceService->update($ownerId, $ownerType, $id, [
                    'block_id'     => (int) $block['block_id'],
                    'owner_type'   => $ownerType,
                    'owner_id'     => (int) $ownerId,
                    'sort_order'   => (int) $order,
                    'is_active'    => (bool) ($block['is_active'] ?? true),
                    'block_config' => $block['block_config'] ?? [],
                    'translations' => $block['translations'] ?? []
                ]));
            }
        }

        if ($this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            return $this->response
                ->setContentType('application/json')
                ->setBody(json_encode(['ok' => true]) ?: '{}');
        }

        return redirect()->to(route_to($this->ownerRoutes($ownerType)['index'], $ownerId))->with('success', 'Orden de bloques actualizado.');
    }

    public function reorderChildren(string $ownerId, string $instanceId): RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $ordersRaw = $this->request->getPost('orders');
        $orders    = is_array($ordersRaw) ? $ordersRaw : [];

        $ownerType = $this->ownerTypeFromRequest();

        foreach ($orders as $id => $order) {
            $blockResponse = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, $id));
            if ($blockResponse['ok']) {
                $block = $this->extractData($blockResponse);
                $this->safeApiCall(fn () => $this->blockInstanceService->update($ownerId, $ownerType, $id, [
                    'block_id'           => (int) $block['block_id'],
                    'owner_type'         => $ownerType,
                    'owner_id'           => (int) $ownerId,
                    'parent_instance_id' => (int) $instanceId,
                    'sort_order'         => (int) $order,
                    'is_active'          => (bool) ($block['is_active'] ?? true),
                    'block_config'       => $block['block_config'] ?? [],
                    'translations'       => $block['translations'] ?? [],
                ]));
            }
        }

        if ($this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            return $this->response
                ->setContentType('application/json')
                ->setBody(json_encode(['ok' => true]) ?: '{}');
        }

        return redirect()->to(route_to($this->ownerRoutes($ownerType)['children'], $ownerId, $instanceId))->with('success', 'Orden actualizado.');
    }

    public function children(string $ownerId, string $instanceId): string|RedirectResponse
    {
        $ownerType = $this->ownerTypeFromRequest();
        $page = $this->fetchOwner($ownerType, $ownerId);
        if ($page === []) {
            return redirect()->to($this->ownerListRoute($ownerType))->with('error', $this->ownerNotFoundMessage($ownerType));
        }

        $parentResponse = $this->safeApiCall(fn () => $this->blockInstanceService->get($ownerId, $ownerType, $instanceId));
        if (!$parentResponse['ok']) {
            return redirect()->to(route_to($this->ownerRoutes($ownerType)['index'], $ownerId))->with('error', 'Bloque contenedor no encontrado.');
        }
        $parentBlock = $this->extractData($parentResponse);

        // Fetch all page blocks and filter to just children of this instance
        $blocksResponse = $this->safeApiCall(fn () => $this->blockInstanceService->list($ownerId, $ownerType));
        $allBlocks      = $blocksResponse['ok'] ? $this->extractItems($blocksResponse) : [];
        $children       = array_values(array_filter($allBlocks, static fn (array $b) => (int) ($b['parent_instance_id'] ?? 0) === (int) $instanceId));

        $typesIndexed = $this->fetchBlockTypes();

        $parentType = $typesIndexed[$parentBlock['block_id']] ?? [];

        return $this->render('cms/pages/blocks/children/index', [
            'title'                => ($ownerType === self::OWNER_ENTRY ? 'Sub-bloques' : 'Diapositivas') . ' de ' . ($parentType['name'] ?? 'Bloque'),
            'page'                 => $page,
            'parentBlock'          => $parentBlock,
            'parentType'           => $parentType,
            'children'             => $children,
            'blockTypes'           => $typesIndexed,
            'ownerType'            => $ownerType,
            'ownerLabel'           => $this->ownerLabel($ownerType),
            'ownerBlocksRoute'     => $this->ownerRoutes($ownerType)['index'],
            'ownerCreateRoute'     => $this->ownerRoutes($ownerType)['create'],
            'ownerStoreRoute'      => $this->ownerRoutes($ownerType)['store'],
            'ownerEditRoute'       => $this->ownerRoutes($ownerType)['edit'],
            'ownerUpdateRoute'     => $this->ownerRoutes($ownerType)['update'],
            'ownerDeleteRoute'     => $this->ownerRoutes($ownerType)['delete'],
            'ownerChildrenReorderRoute' => $this->ownerRoutes($ownerType)['childrenReorder'],
            'childLabel'           => $this->childLabel($ownerType),
        ]);
    }

    /** @return array<int, array<string, mixed>> keyed by block type id */
    private function fetchBlockTypes(): array
    {
        $types = cache()->get('cms_block_types_list');
        if ($types === null) {
            $typesResponse = $this->safeApiCall(fn () => service('blockTypeApiService')->list(['limit' => 100]));
            $types = $typesResponse['ok'] ? $this->extractItems($typesResponse) : [];
            if (! empty($types)) {
                cache()->save('cms_block_types_list', $types, 3600);
            }
        }

        $indexed = [];
        foreach ((array) $types as $t) {
            $indexed[(int) $t['id']] = $t;
        }
        return $indexed;
    }
}
