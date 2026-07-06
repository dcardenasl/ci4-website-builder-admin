<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Support\CmsPresetCatalog;
use App\Support\FileSizeLimits;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class WizardController extends BaseWebController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
    }

    public function index(): string
    {
        return $this->render('cms/wizard/index', [
            'title'     => lang('Wizard.title'),
            'csrfName'  => csrf_token(),
            'csrfToken' => csrf_hash(),
        ]);
    }

    public function config(): ResponseInterface
    {
        $domainClient = service('domainApiClient');

        $wizardResult = $this->safeApiCall(static fn () => $domainClient->get('/cms/wizard/config'));
        if (isset($wizardResult['ok']) && $wizardResult['ok'] === false) {
            return $this->response
                ->setStatusCode(502)
                ->setJSON(['ok' => false, 'message' => 'Could not load wizard config from domain API']);
        }

        $config = $this->extractData($wizardResult);

        // Enrich block_types with id, is_container, allowed_children, icon, category
        $blockTypesResult = $this->safeApiCall(
            static fn () => $domainClient->get('/cms/block-types', ['limit' => 200, 'is_active' => 1])
        );

        if (! isset($blockTypesResult['ok']) || $blockTypesResult['ok'] !== false) {
            $btRaw  = $this->extractData($blockTypesResult);
            $btList = $btRaw['items'] ?? $btRaw['data'] ?? [];

            foreach ($btList as $bt) {
                $key = $bt['block_key'] ?? null;
                if (! $key) {
                    continue;
                }

                $schemaDef                   = $bt['schema_definition'] ?? [];
                $existing                    = $config['block_types'][$key] ?? [];
                $config['block_types'][$key] = array_merge($existing, [
                    'id'               => $bt['id'] ?? null,
                    'icon'             => $bt['icon'] ?? null,
                    'category'         => $bt['category'] ?? null,
                    'is_container'     => (bool) ($bt['is_container'] ?? false),
                    'supports_pages'   => (bool) ($bt['supports_pages'] ?? true),
                    'supports_entries' => (bool) ($bt['supports_entries'] ?? false),
                    'allowed_children' => $schemaDef['allowed_children'] ?? [],
                ]);
            }
        }

        $config['collection_types'] = $this->collectionTypeOptions();
        $config['page_types'] = $this->pageTypeOptions();
        $config['field_primitives'] = $config['field_primitives'] ?? [
            'text',
            'textarea',
            'richtext',
            'image',
            'file',
            'url',
            'number',
            'boolean',
            'select',
            'date',
            'datetime',
        ];
        $config['block_capabilities'] = $config['block_capabilities'] ?? [];
        $config['setup_state'] = array_merge([
            'has_languages' => ! empty($config['languages']),
            'has_collections' => ! empty($config['collections']),
            'has_active_block_types' => ! empty($config['block_types']),
        ], is_array($config['setup_state'] ?? null) ? $config['setup_state'] : []);

        return $this->response->setJSON($config);
    }

    public function publish(): ResponseInterface
    {
        $raw     = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? ($this->request->getJSON(true) ?? [])
            : [];
        $payload = is_array($raw) ? $raw : [];

        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Empty payload']);
        }

        $errors = $this->validatePublishPayload($payload);
        if ($errors !== []) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'errors' => $errors]);
        }

        $domainClient = service('domainApiClient');
        $result = $this->safeApiCall(static fn () => $domainClient->post('/cms/entries', $payload));

        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }

        $body = $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? []);

        if ($statusCode >= 200 && $statusCode < 300 && ! isset($body['ok'])) {
            $body = ['ok' => true] + $body;
        } elseif ($statusCode >= 400) {
            $body = [
                'ok' => false,
                'message' => $result['messages'][0] ?? $result['message'] ?? lang('Wizard.error_publish'),
                'errors' => $result['fieldErrors'] ?? [],
                'data' => $body,
            ];
        }

        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($body);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    private function proxyBlockRequest(string $ownerType, int $ownerId, string $method, ?int $blockId = null, array $payload = [], array $filters = []): array
    {
        $domainClient = service('domainApiClient');
        $path = $ownerType === 'entry'
            ? "/cms/entries/{$ownerId}/blocks"
            : "/cms/pages/{$ownerId}/blocks";

        if ($blockId !== null) {
            $path .= '/' . $blockId;
        }

        return $this->safeApiCall(static function () use ($domainClient, $method, $path, $payload, $filters) {
            return match (strtoupper($method)) {
                'GET'    => $domainClient->get($path, $filters),
                'POST'   => $domainClient->post($path, $payload),
                'PUT'    => $domainClient->put($path, $payload),
                'DELETE' => $domainClient->delete($path),
                default  => throw new \InvalidArgumentException('Unsupported block request method: ' . $method),
            };
        });
    }

    public function uploadImage(): ResponseInterface
    {
        $file = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? $this->request->getFile('file')
            : null;

        if ($file === null || !$file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'No valid file provided']);
        }

        $mimeError = $this->validateImageFile($file);
        if ($mimeError !== null) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => $mimeError]);
        }

        $apiClient = service('apiClient');
        $result = $this->safeApiCall(static fn () => $apiClient->upload('/files/upload', [
            'file' => [
                'path'     => $file->getTempName(),
                'filename' => $file->getClientName(),
                'mimeType' => $file->getMimeType(),
            ],
        ]));

        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }

        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? []));
    }

    // ── WIZ-007: Edit page ────────────────────────────────────────────────────

    public function createBlock(int $pageId): ResponseInterface
    {
        $raw     = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? ($this->request->getJSON(true) ?? [])
            : [];
        $payload = is_array($raw) ? $raw : [];

        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Empty payload']);
        }

        if (empty($payload['block_type_key']) || !is_string($payload['block_type_key'])) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'block_type_key is required']);
        }

        $domainClient = service('domainApiClient');
        $result       = $this->safeApiCall(static fn () => $domainClient->post("/cms/pages/{$pageId}/blocks", $payload));

        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }

        return $this->response->setStatusCode($statusCode)->setJSON(
            $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? [])
        );
    }

    public function createEntryBlock(int $entryId): ResponseInterface
    {
        $raw     = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? ($this->request->getJSON(true) ?? [])
            : [];
        $payload = is_array($raw) ? $raw : [];

        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Empty payload']);
        }

        if (empty($payload['block_type_key']) || !is_string($payload['block_type_key'])) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'block_type_key is required']);
        }

        $result = $this->proxyBlockRequest('entry', $entryId, 'POST', null, $payload);

        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }

        return $this->response->setStatusCode($statusCode)->setJSON(
            $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? [])
        );
    }

    public function deleteBlock(int $pageId, int $blockId): ResponseInterface
    {
        $result = $this->proxyBlockRequest('page', $pageId, 'DELETE', $blockId);

        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }

        return $this->response->setStatusCode($statusCode)->setJSON(['ok' => $statusCode < 300]);
    }

    public function deleteEntryBlock(int $entryId, int $blockId): ResponseInterface
    {
        $result = $this->proxyBlockRequest('entry', $entryId, 'DELETE', $blockId);

        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }

        return $this->response->setStatusCode($statusCode)->setJSON(['ok' => $statusCode < 300]);
    }

    public function pageBlocks(int $pageId): ResponseInterface
    {
        $result = $this->proxyBlockRequest('page', $pageId, 'GET', null, [], ['include_translations' => 1, 'limit' => 100]);

        if (isset($result['ok']) && $result['ok'] === false) {
            return $this->response->setStatusCode(502)->setJSON(['ok' => false, 'message' => 'Could not load blocks']);
        }

        return $this->response->setJSON($this->extractData($result));
    }

    public function entryBlocks(int $entryId): ResponseInterface
    {
        $result = $this->proxyBlockRequest('entry', $entryId, 'GET', null, [], ['include_translations' => 1, 'limit' => 100]);

        if (isset($result['ok']) && $result['ok'] === false) {
            return $this->response->setStatusCode(502)->setJSON(['ok' => false, 'message' => 'Could not load blocks']);
        }

        return $this->response->setJSON($this->extractData($result));
    }

    public function updateBlock(int $pageId, int $blockId): ResponseInterface
    {
        $payload = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? ($this->request->getJSON(true) ?? [])
            : [];

        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Empty payload']);
        }

        $domainClient = service('domainApiClient');
        $result = $this->safeApiCall(static fn () => $domainClient->put("/cms/pages/{$pageId}/blocks/{$blockId}", $payload));

        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }

        return $this->response->setStatusCode($statusCode)->setJSON(
            $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? [])
        );
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

    public function updateEntryBlock(int $entryId, int $blockId): ResponseInterface
    {
        $raw     = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? ($this->request->getJSON(true) ?? [])
            : [];
        $payload = is_array($raw) ? $raw : [];

        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Empty payload']);
        }

        $result = $this->proxyBlockRequest('entry', $entryId, 'PUT', $blockId, $payload);

        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }

        return $this->response->setStatusCode($statusCode)->setJSON(
            $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? [])
        );
    }

    // ── WIZ-008: Edit menu ────────────────────────────────────────────────────

    public function menuItems(int $menuId): ResponseInterface
    {
        $domainClient = service('domainApiClient');
        $result = $this->safeApiCall(static fn () => $domainClient->get('/cms/menu-items', ['menu_id' => $menuId, 'include_translations' => 1, 'limit' => 100, 'sort' => 'sort_order', 'direction' => 'asc']));

        if (isset($result['ok']) && $result['ok'] === false) {
            return $this->response->setStatusCode(502)->setJSON(['ok' => false, 'message' => 'Could not load menu items']);
        }

        return $this->response->setJSON($this->extractData($result));
    }

    public function addMenuItem(int $menuId): ResponseInterface
    {
        $raw = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? ($this->request->getJSON(true) ?? [])
            : [];

        $payload = is_array($raw) ? $raw : [];

        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Empty payload']);
        }

        $payload['menu_id'] = $menuId;
        $domainClient = service('domainApiClient');
        $result = $this->safeApiCall(static fn () => $domainClient->post('/cms/menu-items', $payload));

        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }

        return $this->response->setStatusCode($statusCode)->setJSON(
            $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? [])
        );
    }

    public function updateMenuItem(int $itemId): ResponseInterface
    {
        $payloadRaw = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? $this->request->getJSON(true)
            : null;
        $payload = is_array($payloadRaw) ? $payloadRaw : [];

        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Empty payload']);
        }

        $domainClient = service('domainApiClient');

        // Fetch current item to merge translations and prevent validation failures
        $currentItemResult = $this->safeApiCall(static fn () => $domainClient->get("/cms/menu-items/{$itemId}"));
        if (isset($currentItemResult['ok']) && $currentItemResult['ok']) {
            $currentItem = $currentItemResult['data'] ?? [];
            if (! isset($payload['translations']) && is_array($currentItem) && isset($currentItem['translations'])) {
                $payload['translations'] = $currentItem['translations'];
            }
        }

        $result = $this->safeApiCall(static fn () => $domainClient->put("/cms/menu-items/{$itemId}", $payload));

        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }

        return $this->response->setStatusCode($statusCode)->setJSON(
            $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? [])
        );
    }

    public function deleteMenuItem(int $itemId): ResponseInterface
    {
        $domainClient = service('domainApiClient');
        $result = $this->safeApiCall(static fn () => $domainClient->delete("/cms/menu-items/{$itemId}"));

        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }

        return $this->response->setStatusCode($statusCode)->setJSON(
            $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? [])
        );
    }

    // ── Validation helpers ────────────────────────────────────────────────────

    /**
     * @param array<string, mixed> $payload
     * @return array<string, string>  field → error message
     */
    private function validatePublishPayload(array $payload): array
    {
        $errors = [];

        $collectionId = $payload['collection_id'] ?? null;
        if ($collectionId === null || (int) $collectionId <= 0) {
            $errors['collection_id'] = 'collection_id is required and must be a positive integer';
        }

        $title = $payload['title'] ?? '';
        if (!is_string($title) || trim($title) === '') {
            $errors['title'] = 'title is required';
        }

        $status = $payload['status'] ?? '';
        if (!in_array($status, ['published', 'draft'], true)) {
            $errors['status'] = 'status must be "published" or "draft"';
        }

        return $errors;
    }

    /**
     * Validates that the uploaded file is an image within the allowed size limit.
     * Uses fileinfo (getMimeType) to verify the real MIME type, not just the
     * client-reported Content-Type.
     *
     * @param object $file UploadedFile
     */
    private function validateImageFile(object $file): ?string
    {
        if (!method_exists($file, 'getMimeType') || !method_exists($file, 'getSize')) {
            return null;
        }

        $realMime = strtolower((string) ($file->getMimeType() ?: ''));
        if (!str_starts_with($realMime, 'image/')) {
            return 'Only image files are allowed (received: ' . ($realMime ?: 'unknown') . ')';
        }

        $size = (int) $file->getSize();
        $maxBytes = FileSizeLimits::effectiveMaxBytes();
        if ($size > $maxBytes) {
            $maxMb = FileSizeLimits::bytesToMb($maxBytes);
            return "File size exceeds the {$maxMb} MB limit";
        }

        return null;
    }
}
