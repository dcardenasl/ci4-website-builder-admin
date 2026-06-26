<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
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

        return $this->response->setJSON($config);
    }

    public function publish(): ResponseInterface
    {
        $payload = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? ($this->request->getJSON(true) ?? [])
            : [];

        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Empty payload']);
        }

        $domainClient = service('domainApiClient');
        $result = $this->safeApiCall(static fn () => $domainClient->post('/cms/entries', $payload));

        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }

        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? []));
    }

    public function uploadImage(): ResponseInterface
    {
        $file = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? $this->request->getFile('file')
            : null;

        if ($file === null || !$file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'No valid file provided']);
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
        $payload = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? ($this->request->getJSON(true) ?? [])
            : [];

        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Empty payload']);
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

    public function deleteBlock(int $pageId, int $blockId): ResponseInterface
    {
        $domainClient = service('domainApiClient');
        $result       = $this->safeApiCall(static fn () => $domainClient->delete("/cms/pages/{$pageId}/blocks/{$blockId}"));

        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }

        return $this->response->setStatusCode($statusCode)->setJSON(['ok' => $statusCode < 300]);
    }

    public function pageBlocks(int $pageId): ResponseInterface
    {
        $domainClient = service('domainApiClient');
        $result = $this->safeApiCall(static fn () => $domainClient->get("/cms/pages/{$pageId}/blocks", ['include_translations' => 1, 'limit' => 100]));

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
        $payload = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? ($this->request->getJSON(true) ?? [])
            : [];

        if (empty($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Empty payload']);
        }

        $domainClient = service('domainApiClient');
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
}
