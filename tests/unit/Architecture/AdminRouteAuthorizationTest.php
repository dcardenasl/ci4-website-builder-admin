<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Guards modules that use the broad `auth` + `admin` group gate from relying on
 * it as their only authorization. Every route under such a group must declare
 * a fine-grained permission, unless its controller documents and enforces an
 * explicit OR-of-permissions check.
 *
 * Modules are discovered from the repository at test time so a new module that
 * copies the vulnerable route-group pattern is covered automatically.
 *
 * @internal
 */
final class AdminRouteAuthorizationTest extends CIUnitTestCase
{
    /**
     * Routes whose controller must combine multiple permissions dynamically.
     * A single permission filter cannot represent their OR semantics.
     *
     * @var array<string, list<string>>
     */
    private const CONTROLLER_ENFORCED_ROUTES = [
        'Cms' => [
            "'wizard/structure'",
            "'wizard/structure/config'",
            "'wizard/structure/create-collection'",
            "'wizard/structure/create-page'",
            "'wizard/structure/create-menu'",
            "'translate'",
        ],
    ];

    private const BROAD_ADMIN_GATE_PATTERN = "/'filter'\\s*=>\\s*\\[\\s*'auth'\\s*,\\s*'admin'/";

    /** @return array<string, string> module name => route file relative path */
    private static function discoverModulesUsingBroadAdminGate(): array
    {
        $root = rtrim((string) ROOTPATH, DIRECTORY_SEPARATOR);
        $modulesDir = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules';
        $files = glob($modulesDir . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Routes.php') ?: [];
        sort($files);

        $modules = [];
        foreach ($files as $path) {
            $source = file_get_contents($path);
            if (! is_string($source) || ! preg_match(self::BROAD_ADMIN_GATE_PATTERN, $source)) {
                continue;
            }

            $relativePath = ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR);
            $parts = explode('/', str_replace(DIRECTORY_SEPARATOR, '/', $relativePath));
            $module = $parts[2] ?? '';
            if ($module !== '') {
                $modules[$module] = $relativePath;
            }
        }

        return $modules;
    }

    public function testModulesUsingBroadAdminGateDeclarePermissionOnEveryRoute(): void
    {
        $root = rtrim((string) ROOTPATH, DIRECTORY_SEPARATOR);
        $modules = self::discoverModulesUsingBroadAdminGate();
        $violations = [];

        $this->assertNotEmpty($modules, 'No broad admin-gated module was discovered; keep this regression test scoped to the current route model.');

        foreach ($modules as $module => $relativePath) {
            $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $source = file_get_contents($path);

            $this->assertIsString($source, "Unable to read {$relativePath}");
            if (! is_string($source)) {
                continue;
            }

            $exemptRoutes = self::CONTROLLER_ENFORCED_ROUTES[$module] ?? [];

            foreach (preg_split('/\R/', $source) ?: [] as $lineNumber => $line) {
                if (! preg_match('/\$routes->(?:get|post)\s*\(/', $line)) {
                    continue;
                }

                if (str_contains($line, "'permission:")) {
                    continue;
                }

                $isExempt = false;
                foreach ($exemptRoutes as $exemptRoute) {
                    if (str_contains($line, $exemptRoute)) {
                        $isExempt = true;
                        break;
                    }
                }

                if (! $isExempt) {
                    $violations[] = sprintf('%s:%d has no explicit permission filter', $relativePath, $lineNumber + 1);
                }
            }
        }

        $this->assertSame([], $violations, implode("\n", $violations));
    }
}
