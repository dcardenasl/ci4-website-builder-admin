<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Services\EditorDocumentApiService;
use App\Modules\Cms\Services\EntryApiService;
use App\Modules\Cms\Services\PageApiService;
use App\Modules\Cms\Support\PreviewToken;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

final class EditorController extends BaseWebController
{
    private EditorDocumentApiService $documents;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->documents = service('editorDocumentApiService');
    }

    public function hub(): string|RedirectResponse
    {
        $canPages = has_permission('cms.pages.write');
        $canEntries = has_permission('cms.entries.write');
        if (! $canPages && ! $canEntries) {
            return $this->withError(lang('App.access_denied'), route_to('dashboard'));
        }

        $pages = $canPages ? $this->listing(service('pageApiService'), 'pages') : [];
        $entries = $canEntries ? $this->listing(service('entryApiService'), 'entries') : [];

        return $this->render('cms/editor/hub', [
            'title' => lang('EditorUi.title'),
            'pages' => $pages,
            'entries' => $entries,
            'canEditPages' => $canPages,
            'canEditEntries' => $canEntries,
        ]);
    }

    public function index(string $ownerType, int $ownerId): string|RedirectResponse
    {
        if (! in_array($ownerType, ['page', 'entry'], true) || $ownerId < 1) {
            return $this->withError(lang('EditorUi.notFound'), route_to('admin.cms.editor'));
        }

        $result = $this->safeApiCall(fn (): array => $this->documents->load($ownerType, $ownerId));
        if (! ($result['ok'] ?? false)) {
            return $this->failApi($result, lang('EditorUi.loadFailed'), route_to('admin.cms.editor'), false);
        }

        $document = $this->extractData($result);
        return $this->render('cms/editor/index', [
            'title' => $this->documentTitle($document) . ' · ' . lang('EditorUi.title'),
            'boot' => $this->boot($ownerType, $ownerId, $document),
        ], 'layouts/editor');
    }

    public function document(string $ownerType, int $ownerId): ResponseInterface
    {
        return $this->jsonDocument($this->documents->load($ownerType, $ownerId));
    }

    public function save(string $ownerType, int $ownerId): ResponseInterface
    {
        $body = $this->request->getBody();
        if (is_string($body) && strlen($body) > $this->documents->maxPayloadBytes()) {
            return $this->jsonDocument(['ok' => false, 'status' => 413, 'data' => [], 'messages' => [lang('EditorUi.payloadTooLarge')]]);
        }

        return $this->jsonDocument($this->safeApiCall(fn (): array => $this->documents->patch(
            $ownerType,
            $ownerId,
            $this->jsonRequestPayload(),
        )));
    }

    public function renewPreview(string $ownerType, int $ownerId): ResponseInterface
    {
        $loaded = $this->documents->load($ownerType, $ownerId);
        if (! ($loaded['ok'] ?? false)) {
            return $this->jsonDocument($loaded);
        }

        $signature = PreviewToken::sign('editor', $ownerType . ':' . $ownerId);
        if ($signature === null) {
            return $this->jsonDocument(['ok' => false, 'status' => 503, 'data' => [], 'messages' => [lang('EditorUi.previewUnavailable')]]);
        }

        return $this->jsonDocument(['ok' => true, 'status' => 200, 'data' => $signature, 'messages' => []]);
    }

    public function publish(string $ownerType, int $ownerId): ResponseInterface
    {
        return $this->jsonDocument($this->safeApiCall(fn (): array => $this->documents->publish($ownerType, $ownerId)));
    }

    /**
     * @param array<string, mixed> $document
     * @return array<string, mixed>
     */
    private function boot(string $ownerType, int $ownerId, array $document): array
    {
        $site = rtrim((string) env('PUBLIC_SITE_URL', ''), '/');
        if ($site === '') {
            $site = rtrim((string) config('App')->baseURL, '/');
        }
        $signature = PreviewToken::sign('editor', $ownerType . ':' . $ownerId);
        $resource = $ownerType === 'entry' ? 'entries' : 'pages';

        return [
            'owner' => ['type' => $ownerType, 'id' => $ownerId],
            'document' => $document,
            'endpoints' => [
                'document' => route_to('admin.cms.editor.' . $resource . '.document', (string) $ownerId),
                'save' => route_to('admin.cms.editor.' . $resource . '.save', (string) $ownerId),
                'previewRenew' => route_to('admin.cms.editor.' . $resource . '.preview_renew', (string) $ownerId),
                'publish' => route_to('admin.cms.editor.' . $resource . '.publish', (string) $ownerId),
                'back' => route_to('admin.cms.editor'),
                'previewBase' => $site,
            ],
            'preview' => [
                'authorized' => $signature !== null,
                'expires' => $signature['expires'] ?? null,
                'sig' => $signature['sig'] ?? null,
                'channel' => bin2hex(random_bytes(12)),
                'origin' => $this->originOf($site),
            ],
            'csrf' => ['name' => csrf_token(), 'token' => csrf_hash()],
            'strings' => [
                'title' => lang('EditorUi.title'),
                'saving' => lang('EditorUi.saving'),
                'saved' => lang('EditorUi.saved'),
                'conflict' => lang('EditorUi.conflict'),
                'previewUnavailable' => lang('EditorUi.previewUnavailable'),
            ],
        ];
    }

    /**
     * @param PageApiService|EntryApiService $service
     * @return list<array{id: int, title: string, meta: string}>
     */
    private function listing(PageApiService|EntryApiService $service, string $type): array
    {
        $response = $this->safeApiCall(fn (): array => $service->list(['limit' => 50, 'projection' => 'list', 'sort' => '-updated_at']));
        if (! ($response['ok'] ?? false)) {
            $this->maybeFlashDevError($response);
            return [];
        }

        $items = [];
        foreach ($this->extractItems($response) as $item) {
            if (! is_array($item) || ! is_numeric($item['id'] ?? null)) {
                continue;
            }
            $items[] = [
                'id' => (int) $item['id'],
                'title' => trim((string) ($item['title'] ?? $item['name'] ?? '')) ?: (($type === 'entries' ? 'Entry' : 'Page') . ' #' . (int) $item['id']),
                'meta' => trim((string) ($item['slug'] ?? $item['collection_key'] ?? $item['status'] ?? '')),
            ];
        }

        return $items;
    }

    /** @param array<string, mixed> $document */
    private function documentTitle(array $document): string
    {
        $owner = is_array($document['owner'] ?? null) ? $document['owner'] : [];
        return trim((string) ($owner['title'] ?? '')) ?: lang('EditorUi.title');
    }

    private function originOf(string $url): string
    {
        $parts = parse_url($url);
        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            return '';
        }

        return $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
    }

    /** @param array<string, mixed> $result */
    private function jsonDocument(array $result): ResponseInterface
    {
        return $this->response->setHeader('Cache-Control', 'no-store')
            ->setStatusCode($this->normalizeUpstreamStatus($result))
            ->setJSON($result);
    }
}
