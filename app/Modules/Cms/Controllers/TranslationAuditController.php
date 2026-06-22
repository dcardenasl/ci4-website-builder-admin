<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Services\TranslationAuditApiServiceInterface;
use App\Modules\Cms\Services\LanguageApiServiceInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class TranslationAuditController extends BaseWebController
{
    protected TranslationAuditApiServiceInterface $auditService;
    protected LanguageApiServiceInterface $languageService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->auditService = service('translationAuditApiService');
        $this->languageService = service('languageApiService');
    }

    private function requireRead(): ?RedirectResponse
    {
        if (! has_permission('cms.languages.read')) {
            return redirect()->to(route_to('dashboard'))->with('error', lang('App.access_denied'));
        }
        return null;
    }

    public function index(): string|RedirectResponse
    {
        $deny = $this->requireRead();
        if ($deny !== null) {
            return $deny;
        }

        // Get overall stats
        $statsRes = $this->safeApiCall(fn () => $this->auditService->getStats());
        $stats = $statsRes['ok'] ? $this->extractData($statsRes) : [];

        // Get languages list for filter dropdown
        $langsRes = $this->safeApiCall(fn () => $this->languageService->list(['is_active' => 1]));
        $languages = $langsRes['ok'] ? $this->extractItems($langsRes) : [];

        return $this->render('cms/translations/index', [
            'title'        => lang('Translations.audit_title') ?? 'Translation Audit',
            'stats'        => $stats,
            'languages'    => $languages,
        ]);
    }

    public function data(): ResponseInterface
    {
        $deny = $this->requireRead();
        if ($deny !== null) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => lang('App.access_denied'),
            ])->setStatusCode(403);
        }

        $langId = $this->request->getGet('language_id');
        $filters = [];
        if ($langId !== null && $langId !== '') {
            $filters['language_id'] = (int) $langId;
        }

        $response = $this->safeApiCall(fn () => $this->auditService->getReport($filters));

        if (! $response['ok']) {
            return $this->response->setJSON([
                'draw' => intval($this->request->getGet('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $this->firstMessage($response, 'Failed to load audit data')
            ]);
        }

        $data = $this->extractData($response);

        return $this->response->setJSON([
            'draw' => intval($this->request->getGet('draw')),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data,
        ]);
    }
}
