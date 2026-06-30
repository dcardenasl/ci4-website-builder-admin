<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Services\CollectionApiServiceInterface;
use App\Modules\Cms\Services\BlockCatalogServiceInterface;
use App\Modules\Cms\Services\MenuApiServiceInterface;
use App\Modules\Cms\Services\PageApiServiceInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class StructureWizardController extends BaseWebController
{
    protected CollectionApiServiceInterface $collectionService;
    protected BlockCatalogServiceInterface $blockCatalogService;
    protected PageApiServiceInterface $pageService;
    protected MenuApiServiceInterface $menuService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->collectionService = service('collectionApiService');
        $this->blockCatalogService = service('blockCatalogService');
        $this->pageService = service('pageApiService');
        $this->menuService = service('menuApiService');
    }

    public function index(): string
    {
        return $this->render('cms/wizard/structure', [
            'title'      => lang('Wizard.structure_title'),
            'csrfName'   => csrf_token(),
            'csrfToken'  => csrf_hash(),
        ]);
    }

    public function config(): ResponseInterface
    {
        $languagesResponse = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));
        $languages = $this->extractItems($languagesResponse);
        $defaultLanguageId = 0;
        foreach ($languages as $language) {
            if (! empty($language['is_default'])) {
                $defaultLanguageId = (int) ($language['id'] ?? 0);
                break;
            }
        }
        if ($defaultLanguageId === 0 && $languages !== []) {
            $defaultLanguageId = (int) ($languages[0]['id'] ?? 0);
        }

        return $this->response->setJSON([
            'ok' => true,
            'data' => [
                'languages' => $languages,
                'default_language_id' => $defaultLanguageId,
                'intent_options' => [
                    ['key' => 'blog', 'label' => lang('Wizard.intent_blog') ?? 'Blog', 'suggestions' => ['requires_approval' => false, 'enables_categories' => true, 'enables_tags' => true, 'default_changefreq' => 'weekly', 'default_sitemap_priority' => 0.7]],
                    ['key' => 'news', 'label' => lang('Wizard.intent_news') ?? 'Noticias', 'suggestions' => ['requires_approval' => true, 'enables_categories' => true, 'enables_tags' => true, 'default_changefreq' => 'daily', 'default_sitemap_priority' => 0.8]],
                    ['key' => 'portfolio', 'label' => lang('Wizard.intent_portfolio') ?? 'Portafolio', 'suggestions' => ['requires_approval' => false, 'enables_categories' => false, 'enables_tags' => false, 'default_changefreq' => 'monthly', 'default_sitemap_priority' => 0.6]],
                    ['key' => 'services', 'label' => lang('Wizard.intent_services') ?? 'Servicios', 'suggestions' => ['requires_approval' => false, 'enables_categories' => false, 'enables_tags' => false, 'default_changefreq' => 'monthly', 'default_sitemap_priority' => 0.6]],
                    ['key' => 'custom', 'label' => lang('Wizard.intent_custom') ?? 'Otro', 'suggestions' => ['requires_approval' => false, 'enables_categories' => true, 'enables_tags' => true, 'default_changefreq' => 'weekly', 'default_sitemap_priority' => 0.5]],
                ],
                'existing_collections' => $this->extractItems($this->safeApiCall(fn () => service('collectionApiService')->list(['limit' => 100, 'is_active' => true]))),
                'page_types' => ['generic' => 'Generic', 'home' => 'Home', 'contact' => 'Contact', 'privacy' => 'Privacy'],
                'block_types' => $this->blockCatalogService->all(),
            ],
        ]);
    }

    public function createPage(): ResponseInterface
    {
        if (! has_permission('cms.pages.write')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => lang('App.access_denied')]);
        }

        $raw = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest ? ($this->request->getJSON(true) ?? []) : [];
        $payload = is_array($raw) ? $raw : [];
        $result = $this->safeApiCall(fn () => $this->pageService->create($payload));
        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }
        return $this->response->setStatusCode($statusCode)->setJSON([
            'ok' => $statusCode >= 200 && $statusCode < 300,
            'data' => $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? []),
        ]);
    }

    public function createMenu(): ResponseInterface
    {
        if (! has_permission('cms.menus.write')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => lang('App.access_denied')]);
        }

        $raw = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest ? ($this->request->getJSON(true) ?? []) : [];
        $payload = is_array($raw) ? $raw : [];
        $result = $this->safeApiCall(fn () => $this->menuService->create($payload));
        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }
        return $this->response->setStatusCode($statusCode)->setJSON([
            'ok' => $statusCode >= 200 && $statusCode < 300,
            'data' => $statusCode >= 200 && $statusCode < 300 ? $this->extractData($result) : ($result['data'] ?? []),
        ]);
    }

    public function createCollection(): ResponseInterface
    {
        if (! has_permission('cms.collections.write')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => lang('App.access_denied')]);
        }

        $raw = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? ($this->request->getJSON(true) ?? [])
            : [];
        $payload = is_array($raw) ? $raw : [];

        $validationError = $this->validateCollectionWizardPayload($payload);
        if ($validationError !== null) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => $validationError]);
        }

        $result = $this->safeApiCall(fn () => $this->collectionService->create($payload));
        $statusCode = (int) ($result['status'] ?? 502);
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 502;
        }

        $ok = $statusCode >= 200 && $statusCode < 300;
        $data = $ok ? $this->extractData($result) : ($result['data'] ?? []);

        return $this->response->setStatusCode($statusCode)->setJSON([
            'ok' => $ok,
            'data' => $data,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateCollectionWizardPayload(array $payload): ?string
    {
        $collectionKey = trim((string) ($payload['collection_key'] ?? ''));
        $urlPrefix = trim((string) ($payload['url_prefix'] ?? ''));

        if ($collectionKey === '' || strlen($collectionKey) < 2) {
            return 'collection_key is required.';
        }

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $collectionKey)) {
            return 'collection_key must use lowercase letters, numbers and hyphens only.';
        }

        if ($urlPrefix === '') {
            return 'url_prefix is required.';
        }

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $urlPrefix)) {
            return 'url_prefix must use lowercase letters, numbers and hyphens only.';
        }

        $translations = $payload['translations'] ?? [];
        if (! is_array($translations) || $translations === []) {
            return 'At least one base translation is required.';
        }

        $hasValidTranslation = false;
        foreach ($translations as $translation) {
            if (! is_array($translation)) {
                continue;
            }

            $languageId = (int) ($translation['language_id'] ?? 0);
            $name = trim((string) ($translation['name'] ?? ''));
            $slug = trim((string) ($translation['slug'] ?? ''));

            if ($languageId > 0 && $name !== '' && $slug !== '') {
                $hasValidTranslation = true;
                break;
            }
        }

        if (! $hasValidTranslation) {
            return 'A valid base translation is required.';
        }

        return null;
    }
}
