<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\BffApiClientInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/** @internal */
final class DashboardFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();
    }

    protected function tearDown(): void
    {
        cache()->clean();
        Services::reset();
        parent::tearDown();
    }

    public function testDashboardIndexUsesOneServerRenderedSnapshot(): void
    {
        $this->injectDashboardSummary([
            'users' => ['total' => 42],
            'files' => ['total' => 7, 'recent' => []],
            'metrics' => ['request_stats' => ['availability_percent' => 99.9]],
        ]);

        $result = $this->withSession([
            'access_token' => 'token',
            'user' => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['users.read', 'files.read', 'metrics.read']],
        ])->get('/dashboard');

        $result->assertStatus(200);
        $body = $result->getBody();
        $this->assertStringContainsString(lang('Dashboard.title'), $body);
        $this->assertStringContainsString('42', $body);
        $this->assertStringContainsString('99.9%', $body);
        $this->assertStringNotContainsString('fetch(', $body);
        $this->assertStringNotContainsString('dashboard/widgets/stats', $body);
    }

    public function testLegacyWidgetRouteProjectsTheAggregate(): void
    {
        $this->injectDashboardSummary([
            'users' => ['total' => 42],
            'files' => ['total' => 7, 'recent' => []],
        ]);

        $result = $this->withSession([
            'access_token' => 'token',
            'user' => ['id' => 1, 'permissions' => ['users.read', 'files.read']],
        ])->get('/dashboard/widgets/stats');

        $result->assertStatus(200);
        $this->assertStringContainsString('42', $result->getBody());
        $this->assertStringContainsString('7', $result->getBody());
    }

    public function testDashboardDegradesToVisibleUnavailableStateWhenBffFails(): void
    {
        $this->injectDashboardSummary([], false);

        $result = $this->withSession([
            'access_token' => 'token',
            'user' => ['id' => 1, 'permissions' => ['users.read', 'files.read']],
        ])->get('/dashboard');

        $result->assertStatus(200);
        $this->assertStringContainsString('fuente del escritorio', $result->getBody());
    }

    public function testDashboardDoesNotRenderPermissionDeniedSections(): void
    {
        $this->injectDashboardSummary([
            'users' => ['total' => 42],
            'files' => ['total' => 7, 'recent' => []],
        ]);

        $result = $this->withSession([
            'access_token' => 'token',
            'user' => ['id' => 1, 'permissions' => ['files.read']],
        ])->get('/dashboard');

        $result->assertStatus(200);
        $this->assertStringNotContainsString('>42<', $result->getBody());
        $this->assertStringContainsString('>7<', $result->getBody());
    }

    /** @param array<string, mixed> $sections */
    private function injectDashboardSummary(array $sections, bool $ok = true): void
    {
        $client = $this->createMock(BffApiClientInterface::class);
        $client->expects($this->once())
            ->method('getAdminDashboard')
            ->willReturn([
                'ok' => $ok,
                'status' => $ok ? 200 : 503,
                'data' => $ok ? [
                    'status' => 'success',
                    'data' => [
                        'version' => 1,
                        'generated_at' => '2026-08-31T00:00:00+00:00',
                        'source' => ['hub' => 'ok', 'state' => 'ok'],
                        'sections' => ['hub' => $sections],
                    ],
                ] : [],
            ]);

        Services::injectMock('bffApiClient', $client);
    }
}
