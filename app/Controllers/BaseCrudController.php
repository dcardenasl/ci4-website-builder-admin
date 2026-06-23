<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Abstract base for standard CRUD web controllers.
 *
 * Subclasses declare the resource service and module naming, then override
 * only what differs from the generic pattern (extra form options, custom
 * index data, non-standard redirect targets).
 *
 * Canonical flow:
 *   index() → data() [AJAX] → show() → create()/store() → edit()/update() → delete()
 *
 * Each action follows the plan's "flujo CRUD canónico":
 *   - Renders loading/empty/error states (via views)
 *   - Repopulates form fields on failure
 *   - Shows flash on success
 *   - Confirms delete in the view (via $store.confirm)
 *
 * Usage:
 *   class ProductController extends BaseCrudController
 *   {
 *       protected function resourceService(): ProductApiServiceInterface
 *       {
 *           return service('productApiService');
 *       }
 *
 *       protected function modulePrefix(): string { return 'catalog/products'; }
 *       protected function allowedFilters(): array { return ['status']; }
 *       protected function allowedSorts(): array   { return ['name', 'created_at']; }
 *   }
 */
abstract class BaseCrudController extends BaseWebController
{
    // ── Subclass contract ─────────────────────────────────────────────────────

    /**
     * Return the API service instance for this resource.
     * The service must expose list(), get(), create(), update(), delete().
     */
    abstract protected function resourceService(): mixed;

    /**
     * Dot-separated route prefix used for named routes.
     * E.g. 'admin.catalog.products' → route_to('admin.catalog.products')
     */
    abstract protected function routePrefix(): string;

    /**
     * View path prefix relative to app/Views/.
     * E.g. 'catalog/products' → renders catalog/products/index.php, etc.
     */
    abstract protected function viewPrefix(): string;

    // ── Optional overrides ────────────────────────────────────────────────────

    /** @return list<string> Filter keys forwarded to the API list call. */
    protected function allowedFilters(): array
    {
        return [];
    }

    /** @return list<string> Allowed sort columns forwarded to the API list call. */
    protected function allowedSorts(): array
    {
        return ['created_at'];
    }

    /**
     * Extra view data merged into create and edit forms (dropdowns, option
     * lists, related resources, etc.). Called once per form render.
     *
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [];
    }

    /**
     * Extra view data merged into the index view (toolbar options, limit
     * selectors, static filter lists, etc.).
     *
     * @return array<string, mixed>
     */
    protected function indexData(): array
    {
        return [];
    }

    // ── Generic CRUD actions ──────────────────────────────────────────────────

    public function index(): string
    {
        return $this->render($this->viewPrefix() . '/index', $this->indexData());
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            $this->allowedFilters(),
            $this->allowedSorts(),
            fn (array $params) => $this->resourceService()->list($params),
        );
    }

    public function show(string $id): ResponseInterface|string
    {
        $response = $this->safeApiCall(fn () => $this->resourceService()->get($id));

        if (! $response['ok']) {
            return $this->renderApiError($response);
        }

        return $this->render($this->viewPrefix() . '/show', [
            'item' => $this->extractData($response),
        ]);
    }

    public function create(): string
    {
        return $this->render(
            $this->viewPrefix() . '/create',
            $this->formOptions(),
        );
    }

    public function store(): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->resourceService()->create(
            $this->request->getPost(),
        ));

        if (! $response['ok']) {
            return $this->failApi(
                $response,
                lang('App.errors_found'),
                route_to($this->routePrefix() . '.create'),
            );
        }

        return $this->withSuccess(
            $this->firstMessage($response, lang('App.save')),
            route_to($this->routePrefix()),
        );
    }

    public function edit(string $id): ResponseInterface|string
    {
        $response = $this->safeApiCall(fn () => $this->resourceService()->get($id));

        if (! $response['ok']) {
            return $this->renderApiError($response);
        }

        return $this->render(
            $this->viewPrefix() . '/edit',
            array_merge(['item' => $this->extractData($response)], $this->formOptions()),
        );
    }

    public function update(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->resourceService()->update(
            $id,
            $this->request->getPost(),
        ));

        if (! $response['ok']) {
            return $this->failApi(
                $response,
                lang('App.errors_found'),
                route_to($this->routePrefix() . '.edit', $id),
            );
        }

        return $this->withSuccess(
            $this->firstMessage($response, lang('App.update')),
            route_to($this->routePrefix() . '.show', $id),
        );
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->resourceService()->delete($id));

        if (! $response['ok']) {
            return $this->failApi(
                $response,
                lang('App.errors_found'),
                route_to($this->routePrefix() . '.show', $id),
            );
        }

        return $this->withSuccess(
            $this->firstMessage($response, lang('App.delete')),
            route_to($this->routePrefix()),
        );
    }

    // ── Internal helpers ──────────────────────────────────────────────────────

    /**
     * Render an inline API error block inside the authenticated layout.
     * Used when show/edit fetches fail (e.g. 404 from the API).
     *
     * @param array<string, mixed> $response
     */
    protected function renderApiError(array $response): string
    {
        return $this->render('errors/api_error', [
            'status'  => (int) ($response['status'] ?? 500),
            'message' => $this->firstMessage($response, lang('App.connection_error')),
        ]);
    }
}
