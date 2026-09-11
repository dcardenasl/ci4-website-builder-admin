<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\BffApiClientInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/** @internal */
final class SimpleUiModeTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const PERMISSIONS = [
        'cms.pages.read', 'cms.pages.write', 'cms.entries.read', 'cms.entries.write',
        'cms.collections.read', 'cms.submissions.read', 'users.read', 'audit.read',
        'metrics.read', 'cms.blocks.read', 'iam.superadmin-access',
    ];

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testSimpleModeBlocksTechnicalAdminRoutes(): void
    {
        $result = $this->withSession($this->session('simple'))
            ->get('/admin/iam/permissions');

        $result->assertStatus(302);
        $result->assertRedirectTo(route_to('dashboard'));
    }

    public function testSimpleModeReturnsJsonForBlockedAjaxRequests(): void
    {
        $result = $this->withSession($this->session('simple'))
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get('/admin/iam/permissions');

        $result->assertStatus(403);
        $result->assertJSONFragment(['ok' => false]);
    }

    public function testSimpleModeUsesTheReducedSidebarOnTheDashboard(): void
    {
        $client = $this->createMock(BffApiClientInterface::class);
        $client->expects($this->once())
            ->method('getAdminDashboard')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => ['status' => 'success', 'data' => [
                    'version' => 1,
                    'generated_at' => '2026-09-11T00:00:00+00:00',
                    'source' => ['hub' => 'ok'],
                    'sections' => ['hub' => []],
                ]],
            ]);
        Services::injectMock('bffApiClient', $client);

        $body = $this->withSession($this->session('simple'))
            ->get('/dashboard')
            ->getBody();

        $decoded = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        self::assertStringContainsString(lang('EditorUi.pages'), $decoded);
        self::assertStringContainsString(lang('EditorUi.messages'), $decoded);
        self::assertStringNotContainsString(route_to('admin.cms.block_types'), $body);
    }

    /** @return array<string, mixed> */
    private function session(string $uiMode): array
    {
        return [
            'access_token' => 'fixture-token',
            'permissions_refreshed_at' => time(),
            'user' => [
                'id' => 991,
                'email' => 'simple-mode@example.test',
                'display_name' => 'Simple Mode',
                'permissions' => self::PERMISSIONS,
                'ui_mode' => $uiMode,
            ],
        ];
    }
}
