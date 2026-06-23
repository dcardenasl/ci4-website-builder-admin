<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Cms\Services\MenuApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class MenuFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/cms/menus');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user'         => ['permissions' => []],
        ])->get('/admin/cms/menus');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user'         => ['permissions' => ['cms.menus.read']],
        ])->get('/admin/cms/menus');

        $result->assertStatus(200);
    }

    public function testDataInjectsMenuItemCounts(): void
    {
        $mock = $this->createMock(MenuApiService::class);
        $mock->method('list')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'status' => 'success',
                    'data' => [
                        ['id' => 1, 'menu_key' => 'main', 'location' => 'header', 'is_active' => true, 'created_at' => '2026-06-23 20:32:58'],
                        ['id' => 2, 'menu_key' => 'footer', 'location' => 'footer', 'is_active' => true, 'created_at' => '2026-06-23 20:32:58'],
                    ],
                    'meta' => [
                        'total' => 2,
                        'per_page' => 25,
                        'page' => 1,
                        'last_page' => 1,
                        'from' => 1,
                        'to' => 2,
                    ],
                ],
                'raw' => '{"status":"success","data":{"items":[]}}',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);
        $mock->method('listItems')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'status' => 'success',
                    'data' => [
                        ['id' => 10, 'menu_id' => 1, 'sort_order' => 1],
                        ['id' => 11, 'menu_id' => 1, 'sort_order' => 2],
                        ['id' => 12, 'menu_id' => 2, 'sort_order' => 1],
                    ],
                    'meta' => [
                        'total' => 3,
                        'page' => 1,
                        'per_page' => 100,
                    ],
                ],
                'raw' => '{"status":"success","data":{"data":[]}}',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('menuApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user'         => ['permissions' => ['cms.menus.read']],
        ])->get('/admin/cms/menus/data');

        $result->assertStatus(200);
        $body = html_entity_decode(strip_tags((string) $result->getBody()), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $this->assertStringContainsString('"items_count": 2', $body);
        $this->assertStringContainsString('"items_count": 1', $body);
    }

    public function testStoreValidationFailureRedirectsBack(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user'         => ['permissions' => ['cms.menus.read', 'cms.menus.write']],
        ])->post('/admin/cms/menus', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirect();
    }

    public function testDeleteSuccessRedirectsToList(): void
    {
        $mock = $this->createMock(MenuApiService::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('menuApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user'         => ['permissions' => ['cms.menus.read', 'cms.menus.write']],
        ])->post('/admin/cms/menus/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/cms/menus'));
    }
}
