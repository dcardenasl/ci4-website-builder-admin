<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Services\FormApiService;
use App\Modules\Cms\Services\LanguageApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class FormController extends BaseWebController
{
    protected FormApiService $formService;
    protected LanguageApiService $languageService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->formService     = service('formApiService');
        $this->languageService = service('languageApiService');
    }

    public function index(): string
    {
        return $this->render('cms/forms/index', [
            'title'        => lang('Forms.title'),
            'limitOptions' => [10, 25, 50, 100],
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['form_key', 'is_active', 'created_at'],
            fn (array $params) => $this->formService->list($params),
        );
    }

    public function show(string $id): string
    {
        $formResponse = $this->safeApiCall(fn () => $this->formService->get($id));

        if (! $formResponse['ok']) {
            return $this->render('cms/forms/show', [
                'title'     => lang('Forms.show_title'),
                'form'      => [],
                'languages' => [],
                'error'     => $this->firstMessage($formResponse, lang('Forms.not_found')),
            ]);
        }

        $langResponse = $this->safeApiCall(fn () => $this->languageService->list());
        $languages    = $this->extractItems($langResponse) ?: [];
        $form         = $this->normalizeForm($this->extractData($formResponse));

        return $this->render('cms/forms/show', [
            'title'     => lang('Forms.show_title'),
            'form'      => $form,
            'languages' => $languages,
        ]);
    }

    public function create(): string
    {
        $langResponse = $this->safeApiCall(fn () => $this->languageService->list());
        $languages    = $this->extractItems($langResponse) ?: [];
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['name', 'submit_label', 'description', 'success_message', 'error_message'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId, 'translations')
            : [];

        return $this->render('cms/forms/create', [
            'title'            => lang('Forms.create_title'),
            'languages'        => $languages,
            'defaultLangId'    => $languageContext['defaultLangId'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'translateTargets' => $translateTargets,
        ]);
    }

    public function store(): RedirectResponse
    {
        $data = (array) $this->request->getPost();

        $translations = $this->extractTranslationsFromPost($data);

        $payload = [
            'form_key'              => $this->request->getPost('form_key'),
            'is_active'             => (bool) $this->request->getPost('is_active'),
            'has_captcha'           => (bool) $this->request->getPost('has_captcha'),
            'notify_email'          => $this->request->getPost('notify_email') ?: null,
            'autoreply_enabled'     => (bool) $this->request->getPost('autoreply_enabled'),
            'autoreply_email_field' => $this->request->getPost('autoreply_email_field') ?: null,
            'translations'          => $translations,
        ];

        $response = $this->safeApiCall(fn () => $this->formService->create($payload));

        if (! $response['ok']) {
            return $this->withError(
                $this->firstMessage($response, lang('Forms.create_failed')),
                route_to('admin.cms.forms.create')
            );
        }

        $form = $this->extractData($response);
        $id   = $form['id'] ?? null;

        return redirect()
            ->to(route_to('admin.cms.forms.edit', $id))
            ->with('success', lang('Forms.create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $formResponse = $this->safeApiCall(fn () => $this->formService->get($id));

        if (! $formResponse['ok']) {
            return $this->withError(lang('Forms.not_found'), route_to('admin.cms.forms'));
        }

        $langResponse = $this->safeApiCall(fn () => $this->languageService->list());
        $languages    = $this->extractItems($langResponse) ?: [];
        $form         = $this->normalizeForm($this->extractData($formResponse));
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['name', 'submit_label', 'description', 'success_message', 'error_message'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId, 'translations')
            : [];

        return $this->render('cms/forms/edit', [
            'title'            => lang('Forms.edit_title'),
            'form'             => $form,
            'languages'        => $languages,
            'defaultLangId'    => $languageContext['defaultLangId'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'translateTargets' => $translateTargets,
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        $data         = (array) $this->request->getPost();
        $translations = $this->extractTranslationsFromPost($data);

        $payload = [
            'is_active'             => (bool) $this->request->getPost('is_active'),
            'has_captcha'           => (bool) $this->request->getPost('has_captcha'),
            'notify_email'          => $this->request->getPost('notify_email') ?: null,
            'autoreply_enabled'     => (bool) $this->request->getPost('autoreply_enabled'),
            'autoreply_email_field' => $this->request->getPost('autoreply_email_field') ?: null,
            'translations'          => $translations,
        ];

        $response = $this->safeApiCall(fn () => $this->formService->update($id, $payload));

        if (! $response['ok']) {
            return $this->withError(
                $this->firstMessage($response, lang('Forms.update_failed')),
                route_to('admin.cms.forms.edit', $id)
            );
        }

        return redirect()
            ->to(route_to('admin.cms.forms.edit', $id))
            ->with('success', lang('Forms.update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->formService->delete($id));

        if (! $response['ok']) {
            return $this->withError(
                $this->firstMessage($response, lang('Forms.delete_failed')),
                route_to('admin.cms.forms')
            );
        }

        return redirect()
            ->to(route_to('admin.cms.forms'))
            ->with('success', lang('Forms.delete_success'));
    }

    // ── AJAX field management (called from the field builder in edit.php) ───

    public function storeField(string $formId): ResponseInterface
    {
        $data     = $this->jsonOrPost();
        $response = $this->safeApiCall(fn () => $this->formService->createField($formId, $data));

        return $this->ajaxApiResponse($response);
    }

    public function updateField(string $formId, string $fieldId): ResponseInterface
    {
        $data     = $this->jsonOrPost();
        $response = $this->safeApiCall(fn () => $this->formService->updateField($formId, $fieldId, $data));

        return $this->ajaxApiResponse($response);
    }

    public function deleteField(string $formId, string $fieldId): ResponseInterface
    {
        $response = $this->safeApiCall(fn () => $this->formService->deleteField($formId, $fieldId));

        return $this->ajaxApiResponse($response);
    }

    public function reorderFields(string $formId): ResponseInterface
    {
        $data       = $this->jsonOrPost();
        $orderedIds = array_values(array_map('intval', is_array($data['ordered_ids'] ?? null) ? $data['ordered_ids'] : []));
        $response   = $this->safeApiCall(fn () => $this->formService->reorderFields($formId, $orderedIds));

        return $this->ajaxApiResponse($response);
    }

    /**
     * @param array<string, mixed> $response
     */
    private function ajaxApiResponse(array $response): ResponseInterface
    {
        $ok     = (bool) ($response['ok'] ?? false);
        $status = (int) ($response['status'] ?? 0);

        if ($status < 100) {
            $status = $ok ? 200 : 422;
        }

        if (! $ok && $status < 400) {
            $status = 422;
        }

        return $this->response
            ->setStatusCode($status)
            ->setJSON([
                'ok'          => $ok,
                'data'        => $this->extractData($response),
                'messages'    => $response['messages'] ?? [],
                'fieldErrors' => $response['fieldErrors'] ?? [],
            ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function jsonOrPost(): array
    {
        if ($this->request instanceof \CodeIgniter\HTTP\IncomingRequest) {
            $json = $this->request->getJSON(true);
            if (is_array($json)) {
                return $json;
            }
        }

        return (array) $this->request->getPost();
    }

    /**
     * @param array<string, mixed> $form
     * @return array<string, mixed>
     */
    private function normalizeForm(array $form): array
    {
        $form['translations'] = $this->onlyArrayItems($form['translations'] ?? []);
        $form['fields']       = array_map(
            fn (array $field): array => array_merge($field, [
                'translations' => $this->onlyArrayItems($field['translations'] ?? []),
            ]),
            $this->onlyArrayItems($form['fields'] ?? [])
        );

        return $form;
    }

    /**
     * @param mixed $value
     * @return list<array<string, mixed>>
     */
    private function onlyArrayItems(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, static fn (mixed $item): bool => is_array($item)));
    }

    /**
     * Extract language-keyed translations from POST data.
     * Expects keys like translations[1][name], translations[1][submit_label], etc.
     *
     * @param array<string, mixed> $post
     * @return list<array<string, mixed>>
     */
    private function extractTranslationsFromPost(array $post): array
    {
        $raw = $post['translations'] ?? [];
        if (! is_array($raw)) {
            return [];
        }

        $result = [];
        foreach ($raw as $languageId => $trans) {
            if (! is_array($trans)) {
                continue;
            }
            $result[] = array_merge(['language_id' => (int) $languageId], $trans);
        }

        return $result;
    }
}
