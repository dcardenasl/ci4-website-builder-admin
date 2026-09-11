<?php

declare(strict_types=1);

namespace App\Filters;

use App\Support\SessionKeys;
use App\Support\UiMode;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Keeps the simplified admin surface small on the server as well as in the
 * sidebar. It is presentation policy, not authorization: permission filters
 * remain responsible for deciding what the actor may do.
 */
final class SimpleUiFilter implements FilterInterface
{
    /** @var list<string> */
    private const ALLOWED = [
        '#^dashboard$#',
        '#^profile(/.*)?$#',
        '#^files(/.*)?$#',
        '#^language/set$#',
        '#^cms/editor(/.*)?$#',
        '#^cms/pages(/.*)?$#',
        '#^cms/entries(/.*)?$#',
        '#^cms/form-submissions(/.*)?$#',
        '#^cms/collections$#',
    ];

    public function before(RequestInterface $request, $arguments = null): RequestInterface|ResponseInterface
    {
        $this->refreshIdentity();

        if ($this->mode() !== UiMode::Simple || $this->isAllowed($request)) {
            return $request;
        }

        if ($request instanceof IncomingRequest && $request->isAJAX()) {
            return service('response')->setStatusCode(403)->setJSON([
                'ok' => false,
                'message' => lang('EditorUi.simpleModeBlocked'),
            ]);
        }

        return redirect()->to(route_to('dashboard'))->with('error', lang('EditorUi.simpleModeBlocked'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ResponseInterface
    {
        return $response;
    }

    private function refreshIdentity(): void
    {
        if (is_string(session(SessionKeys::ACCESS_TOKEN->value))) {
            service('permissionsSessionRefresher')->refreshIfStale(60);
        }
    }

    private function mode(): UiMode
    {
        $user = session(SessionKeys::USER->value);

        return UiMode::fromMixed(is_array($user) ? ($user['ui_mode'] ?? null) : null);
    }

    private function isAllowed(RequestInterface $request): bool
    {
        $path = trim((string) parse_url((string) $request->getUri()->getPath(), PHP_URL_PATH), '/');
        $path = preg_replace('#^admin/?#', '', $path) ?? '';

        foreach (self::ALLOWED as $pattern) {
            if (preg_match($pattern, $path) === 1) {
                return true;
            }
        }

        return false;
    }
}
